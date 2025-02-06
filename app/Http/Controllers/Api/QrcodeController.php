<?php

namespace App\Http\Controllers\Api;

use App\Models\OrderItem;
use App\Models\User;
use App\Traits\apiresponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Encryption\DecryptException;

class QrcodeController extends Controller
{
    use apiresponse;

    public function view($code)
    {
        // Raw DB query to fetch the specific order item based on $code (assuming it corresponds to unique_code)
        $orderItem = DB::table('order_items')
            ->select(
                'order_items.unique_code',
                'product__types.name',  // Select the type_name from product__types
                'data.data'                  // Select the 'data' field from the 'data' table (JSON-encoded)
            )
            ->join('product__types', 'order_items.id', '=', 'product__types.order_item_id')  // Join order_items to product__types on order_item_id
            ->join('data', 'product__types.id', '=', 'data.category_id')  // Join product__types to data on category_id
            ->where('data.active', 1)   // Only active data
            ->where('order_items.unique_code', $code)  // Filtering by unique_code (code)
            ->first();  // Use first() to get a single result

        // Check if an order item was found
        if (!$orderItem) {
            return response()->json(['message' => 'Order item not found'], 404);
        }

        // Decode the JSON data field into a PHP array
        if (isset($orderItem->data)) {
            $orderItem->data = json_decode($orderItem->data, true);  // Decoding to an associative array
        }

        // Return the result as JSON
        return response()->json($orderItem);
    }






}
