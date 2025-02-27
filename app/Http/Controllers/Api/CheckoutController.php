<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\CardColor;
use App\Models\CartItems;
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

        // Get the user's cart
        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            return response()->json([
                'status' => 'error',
                'message' => 'No cart found for the user.',
            ], 404);
        }

        // Retrieve all items in the user's cart
        $cartItems = CartItems::where('cart_id', $cart->id)->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cart is empty.',
            ], 400);
        }

        // Initialize variables
        $total = 0;
        $product_ids = [];
        $line_items = [];
        $metadata_items = [];

        // Loop through each cart item
        foreach ($cartItems as $item) {
            $product = Card::find($item->card_id);

            // If product not found, return an error
            if (!$product) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Product with ID {$item->product_id} not found.",
                ], 404);
            }

            // Check if there's a valid color_id (if applicable)
            if ($item->color_id) {
                $color = CardColor::find($item->color_id);
                if (!$color || !$product->colors->contains($color)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Invalid color selected for this product.",
                    ], 422);
                }
            }

            // Calculate the total for this item (using product price * quantity)
            $price = $product->price;
            $total += $price * $item->quantity;
            $product_ids[] = $product->id;

            // Prepare line item for Stripe
            $line_items[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $product->name ?? 'Unnamed Product',
                        'description' => $product->description ?? 'No description available',
                        'images' => [$product->image ?? 'default_image_url'],
                    ],
                    'unit_amount' => (int) round($price * 100),
                ],
                'quantity' => $item->quantity,
            ];

            // Add product_id and quantity to metadata
            $metadata_items[] = [
                'product_id' => (string) $product->id,
                'quantity' => (string) $item->quantity,
            ];
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
            Stripe::setApiKey($stripeApiKey);
            $payment = Payment::create([
                'user_id' => $user->id,
                'amount' => $total,
                'product_ids' => json_encode($product_ids),
                'payment_method' => 'stripe',
                'status' => 'pending',
            ]);

            // Create Stripe Checkout session
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $line_items,
                'mode' => 'payment',
                'success_url' => route('checkout.success') . '?session_id={CHECKOUT_SESSION_ID}&order=' . $payment->id,
                'cancel_url' => route('checkout.cancel'),
                'metadata' => [
                    'user_id'=> auth()->user()->id,
                    'order_id' => (string) $payment->id,
                    'items' => json_encode($metadata_items),
                ],
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


    public function success(Request $request)
    {
        return redirect()->away('https://onetapcard.uk/dashboard/home');
    }


    public function cancel()
    {
        return response()->json(['status' => 'cancel']);
    }
}
