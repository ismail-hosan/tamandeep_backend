<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use App\Models\Payment;


class SubscriptionController extends Controller
{
    public function createSubscriptionSession(Request $request)
    {
        // Get the Stripe API key from your config
        $stripeApiKey = config('services.stripe.secret');

        if (!$stripeApiKey) {
            return response()->json([
                'status' => 'error',
                'message' => 'Stripe API key not set.',
            ], 500);
        }

        // Set the Stripe API key
        \Stripe\Stripe::setApiKey($stripeApiKey);

        // Subscription price ID (make sure this is a valid price ID for your subscription plan)
        $stripePriceId = 'price_1Qis3LSAVzIaJZPHVCN8osuS'; // Update this with your actual price ID
        $user = auth()->user();

        try {
            // Create a payment record in your database (optional, if you need this record)
            $payment = Payment::create([
                'user_id' => $user->id,
                'amount' => 100, // Amount can be adjusted
                'product_ids' => null,
                'payment_method' => 'subscriptions',
                'status' => 'pending',
            ]);

            // Create a Stripe Checkout session for a subscription
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price' => $stripePriceId,  // Your Stripe Price ID for the subscription
                        'quantity' => 1,  // Adjust quantity if necessary
                    ],
                ],
                'mode' => 'subscription',  // This ensures it's a subscription instead of a one-time payment
                'success_url' => route('checkout.success') . '?session_id={CHECKOUT_SESSION_ID}&order=' . $payment->id,
                'cancel_url' => route('checkout.cancel'),
                'metadata' => [
                    'order_id' => $payment->id,  // Optionally include the order ID
                ],
                'customer_email' => $user->email,  // Optional: Store the customer's email
            ]);

            // Return the checkout session URL for redirection
            return response()->json([
                'status' => 'success',
                'session_id' => $session->id,
                'url' => $session->url,  // The URL to redirect the user to Stripe's hosted checkout page
            ]);
        } catch (\Exception $e) {
            // Handle error
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function success()
    {
        return response()->json(['success' => true]);
    }

    public function cancel()
    {
        return redirect()->route('home');
    }
}
