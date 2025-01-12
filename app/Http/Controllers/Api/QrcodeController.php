<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Encryption\DecryptException;

class QrcodeController extends Controller
{
    public function view($id)
    {
        try {
            // Decrypt the encrypted user ID
            $decryptedData = Crypt::decryptString($id);

            // Fetch user with active productTypes data and qrcodes
            $data = User::with([
                'productTypes.data' => function ($query) {
                    $query->where('active', true); // Filter only active data entries
                },
                'qrcodes'
            ])->find($decryptedData);

            // Check if user exists
            if (!$data) {
                return view('error', ['message' => 'User not found']);
            }

            // Prepare the response data
            $responseData = [
                'user' => $data->only(['id', 'name', 'email']),
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

            // Return the data as a view
            return view('view', ['data' => $responseData]);

        } catch (DecryptException $e) {
            // Handle decryption failure
            return view('error', ['message' => 'Invalid user ID']);
        }
    }
}
