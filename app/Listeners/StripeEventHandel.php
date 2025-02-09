<?php

namespace App\Listeners;

use App\Models\Card;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Payment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Subscription;
use App\Models\Cart;
use Illuminate\Support\Str;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Invoice;
use Stripe\Checkout\Session;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class StripeEventHandel
{
    public function handle(object $event): void
    {
        $stripeApiKey = config('services.stripe.secret');
        Stripe::setApiKey($stripeApiKey);
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            // Verify the webhook signature
            $this->verifyWebhookSignature($event, $endpointSecret);

            // Handle event types
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
            }
        } catch (\Exception $e) {
            Log::error('Error handling Stripe webhook', [
                'error_message' => $e->getMessage(),
                'event' => $event
            ]);
        }
    }

    /**
     * Verify the Stripe webhook signature.
     *
     * @param object $event
     * @param string $endpointSecret
     * @return void
     */
    protected function verifyWebhookSignature(object $event, string $endpointSecret): void
    {
        $sigHeader = request()->header('Stripe-Signature');
        $payload = request()->getContent();

        try {
            Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\Exception $e) {
            throw new \Exception('Stripe Webhook Signature Verification Failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle 'checkout.session.completed' event.
     *
     * @param object $event
     * @return void
     */
    protected function handleCheckoutSessionCompleted(object $event): void
    {
        $paymentIntent = $event->data->object;
        $payment = Payment::find($paymentIntent->metadata->order_id);
        $user = $paymentIntent->metadata->user_id;

        if ($payment) {
            $payment->status = 'success';
            $payment->save();

            // Handle order and items
            $items = json_decode($paymentIntent->metadata->items, true);
            Log::info('Order items from metadata', ['items' => $items]);

            $order = Order::create([
                'user_id' => $payment->user_id,
                'payment_id' => $payment->id,
            ]);

            // Process each order item
            foreach ($items as $item) {
                $product = Card::find($item['product_id']);
                if ($product) {
                    for ($i = 0; $i < $item['quantity']; $i++) {
                        $this->createOrderItem($order, $product);
                    }

                    // Remove items from the cart
                    $this->removeCartItems($user);
                } else {
                    Log::warning('Product not found', ['product_id' => $item['product_id']]);
                }
            }
        } else {
            Log::warning('Payment not found', ['order_id' => $paymentIntent->metadata->order_id]);
        }
    }

    /**
     * Create an order item and save the QR code.
     *
     * @param \App\Models\Order $order
     * @param \App\Models\Card $product
     * @return void
     */
    protected function createOrderItem(Order $order, $product): void
    {
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

        // Save QR code image
        $qrCodeFileName = 'qr_code_' . $orderItem->id . '.png';
        Storage::disk('public')->put('qrcodes/' . $qrCodeFileName, $qrCodeImage);

        // Update the order item with the QR code path
        $orderItem->qr_code = 'qrcodes/' . $qrCodeFileName;
        $orderItem->save();
    }

    /**
     * Remove cart items after successful payment.
     *
     * @param int $userId
     * @return void
     */
    protected function removeCartItems(int $userId): void
    {
        $cartItems = Cart::where('user_id', $userId)->with('items')->first();
        foreach ($cartItems->items as $cartItem) {
            $cartItem->delete();
        }
    }

    /**
     * Handle 'invoice.payment_succeeded' event.
     *
     * @param object $event
     * @return void
     */
    protected function handleInvoicePaymentSucceeded(object $event): void
    {
        $invoice = $event->data->object;
        $subscription = Subscription::find($invoice->subscription);

        if ($subscription) {
            $subscription->status = 'active';
            $subscription->save();
        }
    }

    /**
     * Handle 'invoice.payment_failed' event.
     *
     * @param object $event
     * @return void
     */
    protected function handleInvoicePaymentFailed(object $event): void
    {
        $invoice = $event->data->object;
        $subscription = Subscription::find($invoice->subscription);

        if ($subscription) {
            $subscription->status = 'failed';
            $subscription->save();
        }
    }

    /**
     * Handle 'customer.subscription.created' event.
     *
     * @param object $event
     * @return void
     */
    protected function handleSubscriptionCreated(object $event): void
    {
        $subscription = $event->data->object;
        Log::info('New subscription created', ['subscription' => $subscription]);

        $subscriptionModel = new Subscription();
        $subscriptionModel->stripe_subscription_id = $subscription->id;
        $subscriptionModel->user_id = $subscription->customer;
        $subscriptionModel->status = 'active';
        $subscriptionModel->save();
    }

    /**
     * Handle 'customer.subscription.updated' event.
     *
     * @param object $event
     * @return void
     */
    protected function handleSubscriptionUpdated(object $event): void
    {
        $subscription = $event->data->object;
        Log::info('Subscription updated', ['subscription' => $subscription]);

        $existingSubscription = Subscription::find($subscription->id);
        if ($existingSubscription) {
            $existingSubscription->status = $subscription->status;
            $existingSubscription->save();
        }
    }

    /**
     * Handle 'customer.subscription.deleted' event.
     *
     * @param object $event
     * @return void
     */
    protected function handleSubscriptionDeleted(object $event): void
    {
        $subscription = $event->data->object;
        Log::info('Subscription canceled', ['subscription' => $subscription]);

        $existingSubscription = Subscription::find($subscription->id);
        if ($existingSubscription) {
            $existingSubscription->status = 'canceled';
            $existingSubscription->save();
        }
    }
}
