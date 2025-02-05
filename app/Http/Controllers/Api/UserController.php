<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Traits\apiresponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    use apiresponse;
    public function index()
    {
        $auth = auth()->user();

        $orderItems = OrderItem::whereHas('order', function ($query) use ($auth) {
            $query->where('user_id', $auth->id);
        })
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('cards', 'cards.id', '=', 'order_items.card_id')  // Join with the cards table
            ->select('order_items.*', 'cards.name as card_name')  // Select card name as card_name
            ->get();

        return $this->success($orderItems, 'Data fetched successfully!', 200);
    }


    public function status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }


        $res = $this->check($request->item_id);
        if ($res) {
            return $res;
        }

        $auth = auth()->user();

        // Retrieve the specific order item by ID and ensure it belongs to the authenticated user
        $orderItem = OrderItem::whereHas('order', function ($query) use ($auth) {
            $query->where('orders.user_id', $auth->id);
        })->find($request->item_id);

        if (!$orderItem) {
            return $this->error('Order item not found or you do not have permission to update this item.', 404);
        }

        OrderItem::whereHas('order', function ($query) use ($auth) {
            $query->where('orders.user_id', $auth->id);
        })
            ->where('id', '!=', $request->item_id)  // Exclude the current order item
            ->update(['status' => 0]);
        $orderItem->update(['status' => 1]);

        return $this->success('Status updated successfully for the order items.', 200);
    }



    private function check($order_item_id)
    {
        $user = auth()->user();

        if (!$user) {
            return $this->error([], 'User not authenticated', 401);
        }
        $orders = $user->orders;

        if ($orders->isEmpty()) {
            return $this->error([], 'Order not found for this user', 404); // 404 Not Found
        }
        $orderItem = null;
        foreach ($orders as $order) {
            $orderItem = $order->items()->where('id', $order_item_id)->first();
            if ($orderItem) {
                break; 
            }
        }

        if (!$orderItem) {
            return $this->error([], 'Order item not found or does not belong to this user\'s order', 404); // 404 Not Found
        }

        return null;
    }

}
