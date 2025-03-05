<?php

namespace App\Http\Controllers\Api;

use App\Models\OrderItem;
use App\Models\Tap;
use App\Models\User;
use App\Traits\apiresponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Encryption\DecryptException;
use function PHPUnit\Framework\returnArgument;

class QrcodeController extends Controller
{
    use apiresponse;

    public function view($code)
    {
        // Raw DB query to fetch the specific order item based on $code (assuming it corresponds to unique_code)
        $orderItem = DB::table('order_items')
            ->select(
                'order_items.id',  // Select the id column from order_items
                'order_items.unique_code',
                'product__types.name',
                'data.data'
            )
            ->join('product__types', 'order_items.id', '=', 'product__types.order_item_id')  // Join order_items to product__types on order_item_id
            ->join('data', 'product__types.id', '=', 'data.category_id')  // Join product__types to data on category_id
            ->where('data.active', 1)
            ->where('order_items.unique_code', $code)
            ->first();

        if (!$orderItem) {
            return $this->error([], 'Action Not Found!', 400);
        }

        // Decode the JSON data field into a PHP array
        if (isset($orderItem->data)) {
            $orderItem->data = json_decode($orderItem->data, true);  // Decoding to an associative array
        }


        // Perform additional logic (assuming this method exists and you want to process the id)
        $this->taps($orderItem->id);

        // Return the result as JSON
        return $this->success($orderItem, 'Data Fetch Successfully!', 200);
    }


    private function taps($order_items_id)
    {
        Tap::create([
            'order_item_id' => $order_items_id,
            'date' => now(),
        ]);
    }

    public function tapsData($id)
    {
        $res = $this->check($id);
        if ($res) {
            return $res;
        }
        $data = Tap::where('order_item_id', $id)->get();

        if (!$data) {
            return $this->error([], 'Data Not Found!');
        }

        return $this->success($data, 'Data Fetch Successfully!', 200);
    }


    private function check($order_item_id)
    {
        $user = auth()->user();
        if (!$user) {
            return $this->error([], 'User not authenticated', 401);
        }
        if (!$user->orders()->exists()) {
            return $this->error([], 'Order not found for this user', 404); // 404 Not Found
        }
        $order = $user->orders()->first();
        $orderItem = $order->items()->where('id', $order_item_id)->first();

        if (!$orderItem) {
            return $this->error([], 'Order item not found or does not belong to this user\'s order', 404); // 404 Not Found
        }
        return null;
    }





}
