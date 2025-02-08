<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brandlogo;
use App\Models\C_M_S;
use App\Models\Features;
use App\Models\Review;
use App\Traits\apiresponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CMSController extends Controller
{
    use apiresponse;
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
        $cmsData = C_M_S::where('type', $type)->first();

        if (!$cmsData) {
            return $this->error([], 'No data found for the specified type', 404);
        }
        return $this->success($cmsData, 'Data Fatch success', 200);

    }

    public function feature()
    {
        $data = Features::where('status', 'active')->orderBy('id', 'desc')->limit(6)->get();
        return $this->success($data, 'Data fetch success', 200);
    }

    public function review()
    {
        $data = Review::where('status', 'Active')->with('user')->orderBy('id', 'desc')->limit(6)->get();
        return $this->success($data, 'Data fetch success', 200);
    }

    public function brand()
    {
        $data = Brandlogo::where('status', 'Active')->orderBy('id', 'desc')->limit(5)->get();
        return $this->success($data, 'Data fetch success', 200);
    }

   
}
