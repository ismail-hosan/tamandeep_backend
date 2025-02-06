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
        $data = OrderItem::with([
            'productTypes.data' => function ($query) {
                $query->where('active', 1); // Filter only active data entries
            }
        ])->where('unique_code', $code)->first();

        try {
            // Fetch user with active productTypes data and qrcodes
           

            // Check if user exists
            if (!$data) {
                return view('error', ['message' => 'User not found']);
            }

            // Prepare the response data
            $responseData = [
                'product_types' => $data->productTypes->map(function ($productType) {
                    return [
                        'id' => $productType->id,
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

            return $this->success($responseData,'data fatch success',200);

        } catch (DecryptException $e) {
            // Handle decryption failure
            return view('error', ['message' => 'Invalid user ID']);
        }
    }
}
