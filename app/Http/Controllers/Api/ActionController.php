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

        // Check validation failure
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if order item exists
        $res = $this->check($request->order_item_id);
        if ($res) {
            return $res;
        }
        $orderItemExists = OrderItem::find($request->order_item_id); // Example check
        if (!$orderItemExists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order item does not exist.',
            ], 404);
        }

        $type = $request->input('type');
        $productType = Product_Type::firstOrCreate(
            ['order_item_id' => $request->order_item_id, 'name' => $type]
        );

        $dataToStore = $request->except(['order_item_id', 'type', 'image', 'cover_image']);

        // Handle image file uploads (image and cover image)
        $fileFields = ['image', 'cover_image'];
        foreach ($fileFields as $fileField) {
            if ($request->hasFile($fileField)) {
                $filePath = $request->file($fileField)->store(($fileField === 'image') ? 'images' : 'cover_images', 'public');
                $dataToStore[$fileField] = $filePath;
            }
        }

        // Attempt to store the data
        try {
            $dataEntry = Data::create([
                'category_id' => $productType->id,
                'data' => json_encode($dataToStore),
            ]);
        } catch (\Throwable $e) { // Catch any exception, including fatal ones
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while storing data or generating QR code',
                'error' => $e->getMessage(),
            ], 500);
        }

        // Return success response with stored data
        return response()->json([
            'status' => 'success',
            'product_type' => $productType,
            'data' => $dataEntry,
        ]);
    }

    public function show($id)
    {
        $res = $this->check($id);
        if ($res) {
            return $res;
        }
        $data = OrderItem::with(['product.data', 'qrcodes'])->find($id);

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
                return $product->data->map(function ($dataEntry, $index) {
                    $decodedData = json_decode($dataEntry->data, true); // `true` for associative array
                    $typeBasedIndex = $dataEntry->Category->name . '#' . ($index + 1);
                    return array_merge([
                        'id' => $dataEntry->id,
                        'title' => $typeBasedIndex,
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

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_item_id' => 'required|integer',
            'action_id' => 'required|string',
        ]);

        // Check validation failure
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $res = $this->check($request->order_item_id);
        if ($res) {
            return $res;
        }

        $dataEntry = Data::find($request->action_id);
        if (!$dataEntry) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data not found for the provided action_id.',
            ], 404);
        }

        // Extract all data except files
        $dataToStore = $request->except(['order_item_id', 'action_id', 'image', 'cover_image']);

        $fileFields = ['image', 'cover_image'];

        foreach ($fileFields as $fileField) {
            if ($request->hasFile($fileField)) {
                // Validate the file before proceeding
                $file = $request->file($fileField);

                if (!$file->isValid()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "The {$fileField} file is invalid.",
                    ], 422);
                }

                // Store the file and save the path
                $filePath = $file->store(($fileField === 'image') ? 'images' : 'cover_images', 'public');
                $dataToStore[$fileField] = $filePath;

            } elseif ($request->has($fileField)) {
                // If no file was uploaded but a text value is provided, store the text
                $dataToStore[$fileField] = $request->input($fileField);
            }
        }

        try {
            // Update the existing Data entry with new data
            $dataEntry->data = json_encode($dataToStore);
            $dataEntry->save();

        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating data.',
                'error' => $e->getMessage(),
            ], 500);
        }

        // Return success response with updated data
        return response()->json([
            'status' => 'success',
            'data' => $dataEntry,
        ]);
    }


    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data_id' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }
        $dataEntry = Data::find($request->data_id);

        if (!$dataEntry) {
            return $this->error([], 'Data not found for the provided data_id.', 500);
        }
        $dataEntry->delete();
        return $this->success([], 'Data Deleted Successfully!', 200);
    }


    public function status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_item_id' => 'required|integer',
            'data_id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $check = Product_Type::where('order_item_id', $request->order_item_id)
            ->with('data')
            ->get();

        if ($check->isEmpty() || $check->pluck('data')->flatten()->isEmpty()) {
            return $this->error([], 'User is not authenticated or no product type found', 401);
        }

        $data = Data::find($request->data_id);
        if (!$data) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data not found'
            ], 404);
        }

        foreach ($check as $productType) {
            foreach ($productType->data as $relatedData) {
                if ($relatedData->id !== $request->data_id) {
                    $relatedData->active = 0;
                    $relatedData->save();
                }
            }
        }
        $data->active = 1;
        $data->save();
        return $this->success($data, 'Data Fatch', 200);
    }

    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_item_id' => 'required|integer',
            'action_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if order item exists
        $res = $this->check($request->order_item_id);
        if ($res) {
            return $res;
        }

        $data = Data::find($request->action_id);
        $data['data'] = json_decode($data->data, true);

        return $this->success($data, 'Data Fetch Success', 200);

    }



    private function check($order_item_id)
    {
        $user = auth()->user();

        if (!$user) {
            return $this->error([], 'User not authenticated', 401);  // User is not authenticated
        }

        // Look for the order item in any of the user's orders
        $orderItem = $user->orders()->whereHas('items', function ($query) use ($order_item_id) {
            $query->where('id', $order_item_id);
        })->exists();

        // If no matching order is found
        if (!$orderItem) {
            return $this->error([], 'Order item not found or does not belong to this user\'s order', 404);  // 404 Not Found
        }

        return null;  // Everything is fine, no error
    }






}
