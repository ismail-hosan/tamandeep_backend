<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\C_M_S;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CMSController extends Controller
{
    public function index(Request $request)
    {
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
    
        $type = $request->input('type');
    
        // Query the CMS model based on the type provided
        $cmsData = C_M_S::where('type', $type)->get();
    
        if ($cmsData->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No data found for the specified type'
            ], 404);
        }
    
        // Successful response with CMS data
        return response()->json([
            'status' => 'success',
            'data' => $cmsData
        ]);
    }
}
