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
            // Fetch the data for the given code, including related productTypes and their active data, where both are active
            $data = OrderItem::with([
                'productTypes.data' => function ($query) {
                    $query->where('active', '1'); // Filter data to only active ones
                }
            ])
                ->where('unique_code', $code)
                ->first();

            // Check if data exists
            if (!$data) {
                return view('error', ['message' => 'Order item not found or inactive']);
            }

            // Prepare the response data for product types and their active data entries
            $responseData = [
                'product_types' => $data->productTypes->filter(function ($productType) {
                    // Only include productTypes that have at least one active data entry
                    return $productType->data->isNotEmpty();
                })->map(function ($productType) {
                    return [
                        'name' => $productType->name,
                        'data' => $productType->data->map(function ($dataEntry) {
                            return [
                                'id' => $dataEntry->id,
                                'category_id' => $dataEntry->category_id,
                                'data' => json_decode($dataEntry->data) // Decoding JSON data
                            ];
                        })
                    ];
                })
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
