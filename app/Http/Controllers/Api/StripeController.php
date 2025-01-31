<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Webhook;
use App\Models\Payment;


class StripeController extends Controller
{
    public function handle(Request $request)
    {
        // Set the Stripe secret key for verification
        $stripeApiKey = config('services.stripe.secret');
        Stripe::setApiKey($stripeApiKey);

        // Webhook secret found in Stripe Dashboard
        $endpoint_secret = config('services.stripe.webhook_secret');

        // Get the Stripe signature header
        $sig_header = $request->header('Stripe-Signature');
        $payload = $request->getContent();

        // Verify the webhook signature
        try {
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);

            // Handle the event
            switch ($event->type) {
                case 'checkout.session.completed':
                    $paymentIntent = $event->data->object; // Contains Stripe\PaymentIntent
                    $payment = Payment::find($paymentIntent->metadata->order_id);  // Retrieve the payment using the order ID

                    if ($payment) {
                        $payment->status = 'success';  // Update the payment status to 'success'
                        $payment->save();

                        // Retrieve the order items from the metadata
                        $items = json_decode($paymentIntent->metadata->items, true);  // Decode the items array stored as JSON

                        // Create the Order record
                        $order = Order::create([
                            'user_id' => $payment->user_id,  // Assuming you saved the user_id in the payment model
                            'payment_id' => $payment->id,    // Use the payment ID for the order
                            // 'status' => 'completed',         // Set the order status as 'completed' or as per your flow
                        ]);

                        // Now, store order items in your database
                        foreach ($items as $item) {
                            // Find the product based on product_id
                            $product = Card::find($item['product_id']);
                            if ($product) {
                                // Save each order item to the order_items table
                                OrderItem::create([
                                    'order_id' => $order->id,         // Link the order item to the created order
                                    'product_id' => $product->id,     // The product associated with this item
                                    'quantity' => $item['quantity'],  // The quantity of the product
                                    'price' => $product->price,       // The price of the product at the time of purchase
                                ]);
                            }
                        }
                    }
                    break;

                case 'payment_intent.payment_failed':
                    $paymentIntent = $event->data->object; // Contains Stripe\PaymentIntent
                    $payment = Payment::find($paymentIntent->metadata->order_id);  // Retrieve the payment using the order ID

                    if ($payment) {
                        $payment->status = 'failed';  // Update the payment status to 'failed'
                        $payment->save();
                    }
                    break;

                // You can handle other events as needed
                default:
                    // Unexpected event type
                    return response()->json(['status' => 'error'], 400);
            }

            // Return a success response to Stripe to acknowledge the webhook
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            // Log the error
            \Log::error("Stripe Webhook Error: " . $e->getMessage());

            // Handle error
            return response()->json(['status' => 'error'], 400);
        }
    }
}
