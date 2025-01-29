<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\CardColor;
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

        // Validate request data (assuming product_id, color_id, and quantity are passed in request)
        $request->validate([
            'items' => 'required|array', // 'items' is an array of product data
            'items.*.product_id' => 'required|exists:cards,id',
            'items.*.color_id' => 'nullable|exists:card_colors,id', // Assuming color_id is optional
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // Initialize variables
        $total = 0;
        $product_ids = [];

        // Loop through the items in the cart
        $line_items = [];
        foreach ($request->items as $itemData) {
            $product = Card::find($itemData['product_id']);

            // If product is not found, skip this item or return an error
            if (!$product) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Product with ID {$itemData['product_id']} not found.",
                ], 404);
            }

            // If there's a color_id, ensure it's valid for this product (if applicable)
            if ($itemData['color_id']) {
                $color = CardColor::find($itemData['color_id']);
                if (!$color || !$product->colors->contains($color)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Invalid color selected for this product.",
                    ], 422);
                }
            }

            // Calculate the total for this product (use price and quantity)
            $price = $product->price; // or $product->colors()->where('id', $itemData['color_id'])->first()->price if color affects price
            $total += $price * $itemData['quantity'];
            $product_ids[] = $product->id;

            // Prepare line item for Stripe
            $line_items[] = [
                'price_data' => [
                    'currency' => 'usd', // Adjust currency as needed
                    'product_data' => [
                        'name' => $item->product->name ?? $item->product->code ?? 'Unnamed Product', // Fallback to 'Unnamed Product' if both are missing
                        'description' => $item->product->description ?? 'No description available',
                        'images' => [$item->product->image ?? 'default_image_url'], // Optional image URL
                    ],
                    'unit_amount' => (int) round($price * 100), // Price in cents
                ],
                'quantity' => $itemData['quantity'], // Quantity of the product
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
            // Initialize Stripe API
            Stripe::setApiKey($stripeApiKey);

            // Create payment record (with pending status)
            $payment = Payment::create([
                'user_id' => $user->id,
                'amount' => $total,
                'product_ids' => json_encode($product_ids),
                'payment_method' => 'stripe',
                'status' => 'pending',  // Set status to 'pending'
            ]);

            // Create Stripe Checkout session
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $line_items,
                'mode' => 'payment',
                'success_url' => route('checkout.success') . '?session_id={CHECKOUT_SESSION_ID}&order=' . $payment->id,
                'cancel_url' => route('checkout.cancel'),
                'metadata' => [
                    'order_id' => $payment->id, // Attach order ID to metadata
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


    public function success()
    {
        return response()->json(['status' => 'success']);
    }

    public function cancel()
    {
        return response()->json(['status' => 'cancel']);
    }
}
