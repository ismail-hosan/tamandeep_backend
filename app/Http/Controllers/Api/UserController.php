<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Traits\apiresponse;
use Illuminate\Http\Request;

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
            ->orderBy('order_items.created_at', 'DESC')  
            ->select('order_items.*')  
            ->get();

        return $this->success($orderItems, 'Data fetched successfully!', 200);
    }
}
