<?php

namespace App\Http\Controllers\Api;

use App\Models\OrderItem;
use App\Models\User;
use App\Traits\apiresponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Encryption\DecryptException;

class QrcodeController extends Controller
{
    use apiresponse;

    public function view($code)
    {
        try {
            // Fetch the data for the given code, including related productTypes and data, where both are active
            $data = OrderItem::with([
                'productTypes' => function ($query) {
                    // Fetch only productTypes that have at least one active data
                    $query->whereHas('data', function ($query) {
                        $query->where('active', 1); // Ensure 'data' is active
                    });
                }
            ])
                ->where('unique_code', $code)
                ->first(); // Fetch the first matching order item


            // Check if data exists
            if (!$data) {
                return view('error', ['message' => 'Order item not found or inactive']);
            }

            // Prepare the response data for one product type with its active data entries
            $responseData = [
                'product_type' => $data->productTypes->map(function ($productType) {
                    // Check if productType itself is active
                    if ($productType->active) {
                        return [
                            'id' => $productType->id,
                            'name' => $productType->name,
                            'data' => $productType->data->map(function ($dataEntry) {
                                // Only include active data entries
                                if ($dataEntry->active) {
                                    return [
                                        'id' => $dataEntry->id,
                                        'category_id' => $dataEntry->category_id,
                                        'data' => json_decode($dataEntry->data) // Decoding JSON data
                                    ];
                                }
                            })->filter() // Remove null values if any dataEntry is inactive
                        ];
                    }
                })->first() // Fetch only the first productType
            ];

            // Return success response with filtered active data
            return response()->json([
                'message' => 'Data fetch success',
                'data' => $responseData
            ], 200);

        } catch (\Exception $e) {
            // Handle unexpected errors
            return view('error', ['message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }


}
