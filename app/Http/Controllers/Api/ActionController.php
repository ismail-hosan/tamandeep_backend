<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Data;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Models\UserQrcode;
use App\Models\Product_Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;

class ActionController extends Controller
{
    public function store(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'type' => 'required|string',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
    
        $userId = auth()->id();
        $type = $request->input('type');
    
        // Store the type in Product_Type table
        $productType = Product_Type::firstOrCreate(
            ['user_id' => $userId, 'name' => $type]
        );
    
        $dataToStore = $request->except('type');
    
        // Check if dataToStore is an array
        if (!is_array($dataToStore)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data to store is not an array',
            ], 422);
        }
    
        try {
            // Insert new JSON-encoded data
            $dataEntry = Data::create([
                'category_id' => $productType->id,
                'data' => json_encode($dataToStore)
            ]);
    
            // Check if a QR code already exists for the user
            $existingQrCode = UserQrcode::where('user_id', $userId)->first();
    
            if ($existingQrCode) {
                // Return the existing QR code if it already exists
                return response()->json([
                    'status' => 'success',
                    'product_type' => $productType,
                    'data' => $dataEntry,
                    'qr_code_url' => asset('storage/' . $existingQrCode->file_path),
                    'qr_code_entry' => $existingQrCode
                ]);
            }
            $encryptedUserId = Crypt::encryptString($userId); 
    
            // Generate the QR code URL with the encrypted user ID
            $qrCodeUrl = route('user.view', ['id' => $encryptedUserId]);
            $qrCode = new QrCode($qrCodeUrl);
            $writer = new PngWriter();
            $qrCodeImage = $writer->write($qrCode)->getString();
    
            // Save QR code image
            $qrCodeFileName = 'qr_code_' . $userId . '.png';
            Storage::disk('public')->put('qrcodes/' . $qrCodeFileName, $qrCodeImage);
    
            // Save QR code entry
            $qrCodeEntry = UserQrcode::create([
                'user_id' => $userId,
                'file_path' => 'qrcodes/' . $qrCodeFileName,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while storing data or generating QR code',
                'error' => $e->getMessage()
            ], 500);
        }
    
        return response()->json([
            'status' => 'success',
            'product_type' => $productType,
            'data' => $dataEntry,
            'qr_code_url' => asset('storage/qrcodes/' . $qrCodeFileName),
            'qr_code_entry' => $qrCodeEntry
        ]);
    }

    public function show()
    {
        // Fetch the user with related product types and their associated data, and QR code
        $data = User::with(['productTypes.data', 'qrcodes'])->find(Auth::user()->id);

        // Check if user exists
        if (!$data) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        // Prepare the response data
        $responseData = [
            'user' => $data->only(['id', 'name', 'email']),
            'qrcode' => $data->qrcodes ? Storage::url($data->qrcodes->file_path) : null,
            'product_types' => $data->productTypes->map(function ($productType) {
                return [
                    'id' => $productType->id,
                    'name' => $productType->name,
                    'data' => $productType->data->map(function ($dataEntry) {
                        return [
                            'id' => $dataEntry->id,
                            'category_id' => $dataEntry->category_id,
                            'data' => json_decode($dataEntry->data) // Assuming you want to decode the JSON data
                        ];
                    })
                ];
            })
        ];

        // Return the data as a JSON response
        return response()->json([
            'status' => 'success',
            'data' => $responseData
        ]);
    }

    
}
