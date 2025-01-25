<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Data;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Traits\apiresponse;
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
    use apiresponse;
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_item_id' => 'required|integer',
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

        $res = $this->check($request->order_item_id);
        if ($res) {
            return $res;
        }
        $type = $request->input('type');
        $productType = Product_Type::firstOrCreate(
            ['order_item_id' => $request->order_item_id, 'name' => $type]
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

            $existingQrCode = UserQrcode::where('order_item_id', $request->order_item_id)->first();

            if ($existingQrCode) {
                return response()->json([
                    'status' => 'success',
                    'product_type' => $productType,
                    'data' => $dataEntry,
                    'qr_code_url' => asset('storage/' . $existingQrCode->file_path),
                    'qr_code_entry' => $existingQrCode
                ]);
            }

            $encryptedUserId = Crypt::encryptString($request->order_item_id);
            $qrCodeUrl = route('user.view', ['id' => $encryptedUserId]);
            $qrCode = new QrCode($qrCodeUrl);
            $writer = new PngWriter();
            $qrCodeImage = $writer->write($qrCode)->getString();

            // Save QR code image
            $qrCodeFileName = 'qr_code_' . $request->order_item_id . '.png';
            Storage::disk('public')->put('qrcodes/' . $qrCodeFileName, $qrCodeImage);

            // Save QR code entry
            $qrCodeEntry = UserQrcode::create([
                'order_item_id' => $request->order_item_id,
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
            'qr_code_url' => $qrCodeFileName,
            'image_path' => $imagePath ? asset('storage/' . $imagePath) : null,
            'cover_image_path' => $coverImagePath ? asset('storage/' . $coverImagePath) : null
        ]);
    }

    public function show($id)
    {
        $res = $this->check($id);
        if ($res) {
            return $res;
        }
        $data = OrderItem::with(['product.data', 'qrcodes'])->find($id);
        // $data = User::with(['productTypes.data', 'qrcodes'])->find(Auth::user()->id);

        // Check if user exists
        if (!$data) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        // Prepare the response data
        $responseData = [
            // 'user' => $data->only(['id', 'name', 'email']),
            'qrcode' => $data->qrcodes ? Storage::url($data->qrcodes->file_path) : null,
            'product_types' => $data->product->map(function ($product) {
                return $product->data->map(function ($dataEntry) {
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
        // return response()->json([
        //     'status' => 'success',
        //     'data' => $responseData
        // ]);
        return $this->success($responseData, 'Data Fatch success', 200);
    }

    public function status($id)
    {
        $auth = Auth::user();

        // Retrieve all product types for the authenticated user along with their related data
        $check = Product_Type::where('order_item_id', $auth->id)
            ->with('data')
            ->get();

        // Check if the user has any product types with data
        if ($check->isEmpty() || $check->pluck('data')->flatten()->isEmpty()) {
            return $this->error([], 'User is not authenticated or no product type found', 401);
        }

        $data = Data::find($id);
        if (!$data) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data not found'
            ], 404);
        }

        // Iterate over all product types and their related data to deactivate other data
        foreach ($check as $productType) {
            foreach ($productType->data as $relatedData) {
                if ($relatedData->id !== $id) {
                    $relatedData->active = 0; // Set other data records as inactive
                    $relatedData->save();
                }
            }
        }
        $data->active = 1;
        $data->save();
        return $this->success($data, 'Data Fatch', 200);
    }


    private function check($order_item_id)
    {
        $user = auth()->user();
        if (!$user) {
            return $this->error([], 'User not authenticated', 401);
        }
        if (!$user->order()->exists()) {
            return $this->error([], 'Order not found for this user', 404); // 404 Not Found
        }
        $order = $user->order()->first();
        $orderItem = $order->items()->where('id', $order_item_id)->first();

        if (!$orderItem) {
            return $this->error([], 'Order item not found or does not belong to this user\'s order', 404); // 404 Not Found
        }
        return null;
    }






}
