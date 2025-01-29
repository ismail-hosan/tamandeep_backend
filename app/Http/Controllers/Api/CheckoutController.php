<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Hash;
use Stripe\Checkout\Session;
use Illuminate\Support\Facades\Validator;
use Stripe\Stripe;

class CheckoutController extends Controller
{
    public function checkout(Request $request)
    {
        // Check if user is authenticated
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'error' => 'User is not authenticated.',
                'code' => 401,
            ], 401);
        }

        // Fetch the user's cart
        $cart = Cart::with('items.product')->where('user_id', $user->id)->first();
        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No items in cart.',
            ], 422);
        }

        // Calculate the total amount for the cart
        $total = 0;
        $product_ids = [];
        foreach ($cart->items as $cartItem) {
            $productPrice = $cartItem->product->price;
            $total += $productPrice * $cartItem->quantity;
            $product_ids[] = $cartItem->product->id;
        }

        // Ensure total is greater than 0
        if ($total <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid total amount.',
            ], 422);
        }

        // Set Stripe API key
        $stripeApiKey = config('services.stripe.secret');
        if (!$stripeApiKey) {
            return response()->json([
                'status' => 'error',
                'message' => 'Stripe API key not set.',
            ], 500);
        }

        try {
            // Initialize Stripe API
            Stripe::setApiKey($stripeApiKey);

            // Create payment record (with pending status)
            $payment = Payment::create([
                'user_id' => $user->id,
                'amount' => $total,
                'product_ids' => json_encode($product_ids),
                'payment_method' => 'stripe',
                'status' => 'pending',  // Set status to 'pending'
                // 'phone' => $request->phone,
                // 'address' => $request->address,
                // 'town' => $request->town,
                // 'state' => $request->state,
                // 'postal_code' => $request->postal_code,
            ]);

            // Create Stripe Checkout session
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $cart->items->map(function ($item) {
                    return [
                        'price_data' => [
                            'currency' => 'usd',
                            'product_data' => [
                                'name' => $item->product->code,
                                'description' => $item->product->description ?? 'No description available',
                                'images' => [$item->product->image],
                            ],
                            'unit_amount' => (int) round($item->product->price * 100), // Price in cents
                        ],
                        'quantity' => $item->quantity,
                    ];
                })->toArray(),
                'mode' => 'payment',
                'success_url' => route('checkout.success') . '?session_id={CHECKOUT_SESSION_ID}&order=' . $payment->id,
                'cancel_url' => route('checkout.cancel'),
            ]);

            // Return the session URL
            return response()->json([
                'status' => 'success',
                'message' => 'Stripe Session created. Redirect to this URL',
                'url' => $session->url,
            ]);
        } catch (\Exception $e) {
            // Log the error
            dd($e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again later.',
            ], 500);
        }
    }


    public function success()
    {
        return response()->json(['status' => 'success']);
    }

    public function cancel()
    {
        return response()->json(['status' => 'cancel']);
    }
}
