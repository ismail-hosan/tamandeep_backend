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
        try {
            // Fetch the order item with the matching unique_code
            $orderItem = DB::table('order_items')
                ->select('order_items.*') // Select order item fields
                ->where('order_items.unique_code', $code)
                ->first(); // Get the first matching order item

            if (!$orderItem) {
                return response()->json([
                    'message' => 'Order item not found',
                ], 404);
            }

            // Now fetch the related product_type for the order item
            $productType = DB::table('product_types')
                ->where('product_types.id', $orderItem->product_type_id)
                ->first(); // Fetch the matching product type

            if (!$productType) {
                return response()->json([
                    'message' => 'Product type not found',
                ], 404);
            }

            // Fetch the active data related to the product type
            $data = DB::table('data')
                ->where('data.category_id', $productType->id) // category_id links to product_type_id
                ->where('data.active', 1) // Only active data
                ->get(); // Fetch the data entries

            // Convert data to array
            $dataArray = $data->toArray();

            // Return success response with the structure
            return response()->json([
                'message' => 'Data fetched successfully',
                'order_item' => $orderItem,
                'product_type' => $productType,
                'data' => $dataArray,
            ], 200);

        } catch (\Exception $e) {
            // Handle unexpected errors
            return response()->json([
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }




}
