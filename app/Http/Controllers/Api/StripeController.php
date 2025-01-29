<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
                case 'payment_intent.succeeded':
                    $paymentIntent = $event->data->object; // Contains Stripe\PaymentIntent
                    $payment = Payment::find($paymentIntent->metadata->order_id);  // Retrieve the payment using the order ID
                    if ($payment) {
                        $payment->status = 'success';  // Update the status to 'success'
                        $payment->save();
                    }
                    break;
                case 'payment_intent.payment_failed':
                    $paymentIntent = $event->data->object; // Contains Stripe\PaymentIntent
                    $payment = Payment::find($paymentIntent->metadata->order_id);  // Retrieve the payment using the order ID
                    if ($payment) {
                        $payment->status = 'failed';  // Update the status to 'failed'
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
            // Handle error
            return response()->json(['status' => 'error'], 400);
        }
    }
}
