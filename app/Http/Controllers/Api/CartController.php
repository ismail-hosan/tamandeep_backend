<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItems;
use App\Traits\apiresponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    use apiresponse;

    public function index()
    {
        $cart = Cart::where('user_id', auth()->user()->id)
            ->with(['items.product', 'items.color'])
            ->first();

        if (!$cart) {
            return $this->success([], 'Cart Not Found', 200);
        } else {
            $cart->items = $cart->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'image' => $item->product->image,
                    'quantity' => $item->quantity,
                    'product_price' => $item->product ? $item->product->price : null,
                    'color_name' => $item->color ? $item->color->name : null,
                    'color_id' =>$item->color_id,
                ];
            });
        }
        return $this->success($cart->items, 'Data fetched successfully', 200);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'quantity' => 'required|integer',
            'color_id' => 'required|integer',
        ]);

        // Get the authenticated user
        $user = auth()->user();

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $cart = Cart::firstOrCreate(
            ['user_id' => $user->id],
            ['created_at' => now(), 'updated_at' => now()]
        );

        CartItems::updateOrCreate(
            [
                'cart_id' => $cart->id,
                'card_id' => $request['id'],
                'color_id' => $request['color_id'],
            ],
            [
                'quantity' => $request['quantity'],  
            ]
        );
        $cart->load(['items.product', 'items.color']);
        $cart->items = $cart->items->map(function ($item) {
            return [
                'id' => $item->id,
                'image' => $item->product->image,
                'quantity' => $item->quantity,
                'product_price' => $item->product ? $item->product->price : null,
                'color_name' => $item->color ? $item->color->name : null,
            ];
        });

        return $this->success($cart->items, 'Cart Store Successfully!', 200);

    }

    public function quantity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer',
            'quantity' => 'required|integer',
        ]);

        // Get the authenticated user
        $user = auth()->user();

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Create or get the user's cart
        $cart = Cart::firstOrCreate(
            ['user_id' => $user->id],
            ['created_at' => now(), 'updated_at' => now()]
        );
        $cart_items = CartItems::updateOrCreate(
            [
                'cart_id' => $cart->id,
                'card_id' => $request['product_id'], // Make sure the key is 'product_id' not 'card_id'
            ],
            [
                'quantity' => $request['quantity'],
            ]
        );

        return $this->success($cart_items, 'Quantity Changes Successfully!!', 200);
    }
}
