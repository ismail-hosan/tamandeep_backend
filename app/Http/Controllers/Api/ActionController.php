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
        $validator = Validator::make($request->all(), [
            'type' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', 
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
        $productType = Product_Type::firstOrCreate(
            ['user_id' => $userId, 'name' => $type]
        );

        $dataToStore = $request->except(['type', 'image', 'cover_image']); 

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images', 'public');
            $dataToStore['image'] = $imagePath;
        }

        $coverImagePath = null;
        if ($request->hasFile('cover_image')) {
            $coverImagePath = $request->file('cover_image')->store('cover_images', 'public');
            $dataToStore['cover_image'] = $coverImagePath;
        }

        try {
            $dataEntry = Data::create([
                'category_id' => $productType->id,
                'data' => json_encode($dataToStore)
            ]);

            $existingQrCode = UserQrcode::where('user_id', $userId)->first();

            if ($existingQrCode) {
                return response()->json([
                    'status' => 'success',
                    'product_type' => $productType,
                    'data' => $dataEntry,
                    'qr_code_url' => asset('storage/' . $existingQrCode->file_path),
                    'qr_code_entry' => $existingQrCode
                ]);
            }

            $encryptedUserId = Crypt::encryptString($userId);
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
            'qr_code_entry' => $qrCodeEntry,
            'image_path' => $imagePath ? asset('storage/' . $imagePath) : null,
            'cover_image_path' => $coverImagePath ? asset('storage/' . $coverImagePath) : null
        ]);
    }

    public function show()
    {
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
                return $productType->data->map(function ($dataEntry) {
                    $decodedData = json_decode($dataEntry->data, true); // `true` for associative array
                    
                    return array_merge([
                        'id' => $dataEntry->id,
                        'type' => $dataEntry->Category->name,
                        'active' => $dataEntry->active, 
                    ], $decodedData);
                });
            })->flatten(1) 
        ];

        // Return the data as a JSON response
        return response()->json([
            'status' => 'success',
            'data' => $responseData
        ]);
    }


}
