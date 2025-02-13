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

        // Query the CMS model to fetch all records
        $cmsData = C_M_S::all();  // Fetch all data, no filtering by 'type'

        if ($cmsData->isEmpty()) {
            return $this->error([], 'No data found', 404);
        }

        // Prepare the response grouped by type
        $response = [];

        foreach ($cmsData as $data) {
            if (!isset($response[$data->type])) {
                $response[$data->type] = [];
            }
            $response[$data->type][] = [
                'id' => $data->id,
                'type' => $data->type,
                'title' => $data->title,
                'hilight_title' => $data->hilight_title,
                'descriptions' => $data->descriptions,
                'image' => $data->image,
                'first_image' => $data->first_image,
                'first_title' => $data->first_title,
                'first_desc' => $data->first_desc,
                'second_image' => $data->second_image,
                'second_title' => $data->second_title,
                'second_desc' => $data->second_desc,
                'third_image' => $data->third_image,
                'third_title' => $data->third_title,
                'third_desc' => $data->third_desc,
                'created_at' => $data->created_at,
                'updated_at' => $data->updated_at,
            ];
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data fetched successfully',
            'data' => $response,
            'code' => 200
        ], 200);
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
