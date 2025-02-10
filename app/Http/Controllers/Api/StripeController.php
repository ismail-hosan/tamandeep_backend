<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Webhook;
use App\Models\Payment;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;


class StripeController extends Controller
{
    public function handle(Request $request)
    {
        $stripeApiKey = config('services.stripe.secret');
        Stripe::setApiKey($stripeApiKey);
        $endpoint_secret = config('services.stripe.webhook_secret');

        $sig_header = $request->header('Stripe-Signature');
        $payload = $request->getContent();

        try {
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (\Exception $e) {
            \Log::error('Stripe Webhook Signature Verification Failed', [
                'error_message' => $e->getMessage(),
                'payload' => $payload,
                'signature' => $sig_header
            ]);
            return response()->json(['status' => 'error', 'message' => 'Webhook signature verification failed'], 400);
        }

        // Handle different webhook event types
        if ($event) {
            switch ($event->type) {
                case 'checkout.session.completed':
                    $session = $event->data->object;

                    // Check if it's a subscription or a one-time payment
                    if ($session->mode === 'subscription') {
                        $this->handleSubscriptionCompleted($session);
                    } else {
                        $this->handleOneTimePaymentCompleted($session);
                    }
                    break;

                case 'invoice.payment_succeeded':
                    $invoice = $event->data->object;
                    $this->handleSubscriptionPaymentSuccess($invoice);
                    break;

                case 'invoice.payment_failed':
                    $invoice = $event->data->object;
                    $this->handleSubscriptionPaymentFailed($invoice);
                    break;

                case 'customer.subscription.created':
                case 'customer.subscription.updated':
                case 'customer.subscription.deleted':
                    $subscription = $event->data->object;
                    $this->handleSubscriptionEvents($subscription);
                    break;

                default:
                    \Log::info("Unhandled event type: " . $event->type);
                    break;
            }
        }

        return response()->json(['status' => 'success']);
    }

    private function handleOneTimePaymentCompleted($session)
    {
        $payment = Payment::find($session->metadata->order_id);
        $user = $session->metadata->user_id;

        if ($payment) {
            $payment->status = 'success';
            $payment->save();

            $items = json_decode($session->metadata->items, true);
            \Log::info('Order items from metadata', ['items' => $items]);

            // Create Order
            $order = Order::create([
                'user_id' => $payment->user_id,
                'payment_id' => $payment->id,
            ]);

            foreach ($items as $item) {
                $product = Card::find($item['product_id']);
                if ($product) {
                    for ($i = 0; $i < $item['quantity']; $i++) {
                        $uniqueId = Str::uuid();
                        $orderItem = OrderItem::create([
                            'order_id' => $order->id,
                            'card_id' => $product->id,
                            'unique_code' => $uniqueId,
                        ]);

                        $qrCodeUrl = 'http://localhost:5173/' . $orderItem->unique_code;
                        $qrCode = new QrCode($qrCodeUrl);
                        $writer = new PngWriter();
                        $qrCodeImage = $writer->write($qrCode)->getString();

                        $qrCodeFileName = 'qr_code_' . $orderItem->id . '.png';
                        Storage::disk('public')->put('qrcodes/' . $qrCodeFileName, $qrCodeImage);

                        $orderItem->qr_code = 'qrcodes/' . $qrCodeFileName;
                        $orderItem->save();
                    }
                } else {
                    \Log::warning('Product not found', ['product_id' => $item['product_id']]);
                }
            }

            // Clear cart
            $cartItems = Cart::where('user_id', $user)->with('items')->first();
            foreach ($cartItems->items as $cartItem) {
                $cartItem->delete();  // Remove cart item from the cart
            }
            $cartItems->delete();

        } else {
            \Log::warning('Payment not found', ['order_id' => $session->metadata->order_id]);
        }
    }

    private function handleSubscriptionCompleted($session)
    {
        $userId = $session->metadata->user_id ?? null;
        $paymentId = $session->metadata->order_id ?? null;
        $subscriptionId = $session->subscription ?? null;

        if ($paymentId) {
            $payment = Payment::find($paymentId);
            if ($payment) {
                $payment->status = 'success';
                $payment->save();
            }
        }

        // Store subscription in the database
        $subscription = Subscription::create([
            'user_id' => $userId,
            'stripe_subscription_id' => $subscriptionId,
            'status' => 'active',
        ]);

        \Log::info('Subscription created successfully', ['subscription_id' => $subscriptionId]);
    }

    private function handleSubscriptionPaymentSuccess($invoice)
    {
        $subscriptionId = $invoice->subscription;
        $subscription = Subscription::where('stripe_subscription_id', $subscriptionId)->first();

        if ($subscription) {
            $subscription->status = 'active';
            $subscription->save();
            \Log::info('Subscription payment succeeded', ['subscription_id' => $subscriptionId]);
        }
    }

    private function handleSubscriptionPaymentFailed($invoice)
    {
        $subscriptionId = $invoice->subscription;
        $subscription = Subscription::where('stripe_subscription_id', $subscriptionId)->first();

        if ($subscription) {
            $subscription->status = 'past_due';
            $subscription->save();
            \Log::warning('Subscription payment failed', ['subscription_id' => $subscriptionId]);
        }
    }

    private function handleSubscriptionEvents($subscription)
    {
        $stripeSubscriptionId = $subscription->id;
        $status = $subscription->status;

        $dbSubscription = Subscription::where('stripe_subscription_id', $stripeSubscriptionId)->first();

        if ($dbSubscription) {
            $dbSubscription->status = $status;
            $dbSubscription->save();
        }

        \Log::info('Subscription updated', [
            'subscription_id' => $stripeSubscriptionId,
            'status' => $status
        ]);
    }
}
