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
            // Fetch the data for the given code, including related productTypes and data, where both are active
            $data = DB::table('order_items')
                ->select('order_items.*') // Select order_item fields
                ->join('product_types', 'order_items.product_type_id', '=', 'product_types.id') // Adjust with your actual foreign key
                ->join('data', 'product_types.id', '=', 'data.product_type_id') // Join with 'data' table
                ->where('order_items.unique_code', $code)
                ->where('data.active', 1) // Ensure 'data' is active
                ->groupBy('order_items.id') // Ensure to group by the order item
                ->first();

            // Fetch productTypes along with their associated data
            $productTypes = DB::table('product_types')
                ->whereIn('product_types.id', function ($query) use ($code) {
                    $query->select('product_types.id')
                        ->from('order_items')
                        ->join('data', 'product_types.id', '=', 'data.product_type_id')
                        ->where('order_items.unique_code', $code)
                        ->where('data.active', 1);
                })
                ->get();

            // To structure your data as per your need:
            $dataArray = [];
            foreach ($productTypes as $productType) {
                $productTypeData = DB::table('data')
                    ->where('product_type_id', $productType->id)
                    ->where('active', 1) // Active data only
                    ->get()
                    ->toArray(); // Convert to array

                $dataArray[] = [
                    'productType' => $productType,
                    'data' => $productTypeData
                ];
            }

            // Return success response with filtered active data
            return response()->json([
                'message' => 'Data fetch success',
                'data' => $dataArray
            ], 200);

        } catch (\Exception $e) {
            // Handle unexpected errors and return a view or json response
            // Option 1: Return error view with message
            return view('error', ['message' => 'An error occurred: ' . $e->getMessage()]);

            // Option 2: Return JSON response (if your API returns JSON)
            // return response()->json([
            //     'message' => 'An error occurred: ' . $e->getMessage(),
            // ], 500);
        }
    }



}
