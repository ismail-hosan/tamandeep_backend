<?php

namespace App\Listeners;

use App\Models\User;
use Stripe\Stripe;
use Stripe\Webhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Payment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\Card;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Str;
class StripeEventHandel
{
    public function handleWebhook(Request $request)
    {
        // Use Cashier's handleWebhook method for signature verification and event handling
        try {
            // Cashier handles the webhook signature verification and event processing.
            $event = Cashier::handleWebhook($request);

            // Now you can handle custom logic based on the event type.
            $this->handleCustomEvent($event);

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error handling Stripe webhook', [
                'error_message' => $e->getMessage(),
                'event' => $request->getContent()
            ]);
            return response()->json(['status' => 'error', 'message' => 'Error processing webhook'], 500);
        }
    }

    // Handle custom logic based on the event type
    private function handleCustomEvent($event)
    {
        switch ($event->type) {
            case 'checkout.session.completed':
                $this->handleCheckoutSessionCompleted($event);
                break;

            case 'invoice.payment_succeeded':
                $this->handleInvoicePaymentSucceeded($event);
                break;

            case 'invoice.payment_failed':
                $this->handleInvoicePaymentFailed($event);
                break;

            case 'customer.subscription.created':
                $this->handleSubscriptionCreated($event);
                break;

            case 'customer.subscription.updated':
                $this->handleSubscriptionUpdated($event);
                break;

            case 'customer.subscription.deleted':
                $this->handleSubscriptionDeleted($event);
                break;

            default:
                Log::warning('Unhandled Stripe event type', ['event_type' => $event->type]);
                break;
        }
    }

    // Handle 'checkout.session.completed' event (One-time payment)
    private function handleCheckoutSessionCompleted($event)
    {
        $paymentIntent = $event->data->object; // Stripe\PaymentIntent
        $payment = Payment::find($paymentIntent->metadata->order_id);
        $user = $paymentIntent->metadata->user_id;

        if ($payment) {
            // Update payment status to 'success'
            $payment->status = 'success';
            $payment->save();

            // Decode order items stored in metadata
            $items = json_decode($paymentIntent->metadata->items, true);

            Log::info('Order items from metadata', ['items' => $items]);

            // Create the order record
            $order = Order::create([
                'user_id' => $payment->user_id,
                'payment_id' => $payment->id,
            ]);

            // Process each item in the order
            foreach ($items as $item) {
                $product = Card::find($item['product_id']);
                if ($product) {
                    $this->processOrderItems($item, $order, $product);
                    $this->clearCart($user);
                } else {
                    Log::warning('Product not found', ['product_id' => $item['product_id']]);
                }
            }
        } else {
            Log::warning('Payment not found', ['order_id' => $paymentIntent->metadata->order_id]);
        }
    }

    // Process each order item and generate QR codes
    private function processOrderItems($item, $order, $product)
    {
        for ($i = 0; $i < $item['quantity']; $i++) {
            $uniqueId = Str::uuid();
            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'card_id' => $product->id,
                'unique_code' => $uniqueId,
            ]);

            $this->generateQRCode($orderItem);
        }
    }

    // Generate and store QR code for order item
    private function generateQRCode($orderItem)
    {
        $qrCodeUrl = 'http://localhost:5173/' . $orderItem->unique_code;
        $qrCode = new QrCode($qrCodeUrl);
        $writer = new PngWriter();
        $qrCodeImage = $writer->write($qrCode)->getString();

        $qrCodeFileName = 'qr_code_' . $orderItem->id . '.png';
        Storage::disk('public')->put('qrcodes/' . $qrCodeFileName, $qrCodeImage);

        $orderItem->qr_code = 'qrcodes/' . $qrCodeFileName;
        $orderItem->save();
    }

    // Clear the user's cart after purchase
    private function clearCart($userId)
    {
        $cartItems = Cart::where('user_id', $userId)->with('items')->first();
        foreach ($cartItems->items as $cartItem) {
            $cartItem->delete();
        }
    }

    // Handles 'customer.subscription.created' event (Subscription Created)
    private function handleSubscriptionCreated($event)
    {
        $subscription = $event->data->object; // Stripe\Subscription
        $user = $subscription->customer;

        // Here, handle subscription creation (you can store this in the database)
        $userModel = User::find($user);

        if ($userModel) {
            $userModel->newSubscription('main', $subscription->plan->id)
                ->create($subscription->payment_method);
        }

        Log::info('Subscription created', ['subscription' => $subscription]);
    }

    // Handles 'customer.subscription.updated' event (Subscription Updated)
    private function handleSubscriptionUpdated($event)
    {
        $subscription = $event->data->object; // Stripe\Subscription

        Log::info('Subscription updated', ['subscription' => $subscription]);
    }

    // Handles 'customer.subscription.deleted' event (Subscription Canceled)
    private function handleSubscriptionDeleted($event)
    {
        $subscription = $event->data->object; // Stripe\Subscription
        $user = $subscription->customer;

        $userModel = User::find($user);

        if ($userModel) {
            // Cancel the subscription in your database
            $userModel->subscription('main')->cancel();
        }

        Log::info('Subscription canceled', ['subscription' => $subscription]);
    }

    // Handles 'invoice.payment_succeeded' event (Invoice Payment Succeeded)
    private function handleInvoicePaymentSucceeded($event)
    {
        // Implement logic for handling successful invoice payments
        Log::info('Invoice payment succeeded', ['event' => $event]);
    }

    // Handles 'invoice.payment_failed' event (Invoice Payment Failed)
    private function handleInvoicePaymentFailed($event)
    {
        // Implement logic for handling failed invoice payments
        Log::info('Invoice payment failed', ['event' => $event]);
    }
}
