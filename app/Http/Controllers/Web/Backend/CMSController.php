<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\C_M_S;
use Illuminate\Http\Request;
use App\Helper\ImageHelper;

class CMSController extends Controller
{
    public function index()
    {
        $banner = C_M_S::where('type', 'banner')->first();
        $second_section = C_M_S::where('type','land_first')->first();
        $third_section = C_M_S::where('type','land_second')->first();
        $four_section = C_M_S::where('type','work')->first();
        $feature = C_M_S::where('type','features')->first();
        $footer = C_M_S::where('type','footer')->first();
        return view('backend.layout.cms.index',get_defined_vars());
    }

    public function banner(Request $request)
    {
        $data = C_M_S::where('type', 'banner')->first();
    
        $request->validate([
            'title' => 'required|string',
            'hilight_title' => 'required|string',
            'image' => $data ? 'nullable|image|mimes:png|max:2048' : 'required|image|mimes:png|max:2048',
            'description' => 'required|string',
        ]);
    
        // Initialize `$firstImagePath` only if `$data` exists, otherwise set to null
        $firstImagePath = $data ? $data->image : null;
    
        try {
            if ($request->hasFile('image')) {
                $firstImagePath = ImageHelper::handleImageUpload($request->file('image'), $firstImagePath, '/cms/banner');
            }
    
            // Update or create CMS entry
            $data = C_M_S::updateOrCreate(
                [
                    'type' => 'banner',
                ],
                [
                    'title' => $request->title,
                    'hilight_title' => $request->hilight_title,
                    'image' => $firstImagePath,
                    'descriptions' => $request->description,
                ]
            );
    
            return redirect()->back()->with('t-success', 'Data Updated Successfully');
    
        } catch (\Throwable $th) {
            dd($th->getMessage());
            return redirect()->back()->with('t-error', 'Data update failed!');
        }
    }
    public function second_section(Request $request)
{
    $data = C_M_S::where('type', 'land_first')->first();

    $request->validate([
        'f_title' => 'required|string',
        'f_hilight_title' => 'required|string',
        'f_image' => $data ? 'nullable|image|mimes:png|max:2048' : 'required|image|mimes:png|max:2048',
        'f_description' => 'required|string',
        'f_first_title' => 'required|string',
        'f_first_desc' => 'required|string',
        'f_second_title' => 'required|string',
        'f_second_desc' => 'required|string',
        'f_third_title' => 'required|string',
        'f_third_desc' => 'required|string',
    ]);

    // Initialize `$firstImagePath` with existing image path or null
    $firstImagePath = $data ? $data->f_image : null;

    try {
        // Handle new image upload
        if ($request->hasFile('f_image')) {
            $firstImagePath = ImageHelper::handleImageUpload($request->file('f_image'), $firstImagePath, '/cms/section');
        }

        // Update or create CMS entry
        $data = C_M_S::updateOrCreate(
            [
                'type' => 'land_first',
            ],
            [
                'title' => $request->f_title,
                'hilight_title' => $request->f_hilight_title,
                'image' => $firstImagePath, // Use `$firstImagePath` consistently
                'descriptions' => $request->f_description,
                'first_title' => $request->f_first_title,
                'first_desc' => $request->f_first_desc,
                'second_title' => $request->f_second_title,
                'second_desc' => $request->f_second_desc,
                'third_title' => $request->f_third_title,
                'third_desc' => $request->f_third_desc,
            ]
        );

        // Redirect with success or error message
        if ($data->wasRecentlyCreated || $data->wasChanged()) {
            return redirect()->back()->with('t-success', 'Data Updated Successfully');
        } else {
            return redirect()->back()->with('t-error', 'Data update failed!');
        }

    } catch (\Throwable $th) {
        return redirect()->back()->with('t-error', 'Error: ' . $th->getMessage());
    }
}

public function third_section(Request $request)
{
    $data = C_M_S::where('type', 'land_second')->first();

    $request->validate([
        's_title' => 'required|string',
        's_hilight_title' => 'required|string',
        's_image' => $data ? 'nullable|image|mimes:png|max:2048' : 'required|image|mimes:png|max:2048',
        's_description' => 'required|string',
        's_first_title' => 'required|string',
        's_first_desc' => 'required|string',
        's_second_title' => 'required|string',
        's_second_desc' => 'required|string',
        's_third_title' => 'required|string',
        's_third_desc' => 'required|string',
    ]);

    // Initialize `$imagePath` with existing image path or null
    $imagePath = $data ? $data->image : null;

    try {
        // Handle new image upload
        if ($request->hasFile('s_image')) {
            $imagePath = ImageHelper::handleImageUpload($request->file('s_image'), $imagePath, '/cms/section');
        }

        // Update or create CMS entry
        $data = C_M_S::updateOrCreate(
            [
                'type' => 'land_second',
            ],
            [
                'title' => $request->s_title,
                'hilight_title' => $request->s_hilight_title,
                'image' => $imagePath,
                'descriptions' => $request->s_description,
                'first_title' => $request->s_first_title,
                'first_desc' => $request->s_first_desc,
                'second_title' => $request->s_second_title,
                'second_desc' => $request->s_second_desc,
                'third_title' => $request->s_third_title,
                'third_desc' => $request->s_third_desc,
            ]
        );

        // Redirect with success or error message
        if ($data->wasRecentlyCreated || $data->wasChanged()) {
            return redirect()->back()->with('t-success', 'Data Updated Successfully');
        } else {
            return redirect()->back()->with('t-error', 'Data update failed!');
        }

    } catch (\Throwable $th) {
        return redirect()->back()->with('t-error', 'Error: ' . $th->getMessage());
    }
}

public function four_section(Request $request)
{
    // Retrieve the existing data if available
    $data = C_M_S::where('type', 'work')->first();

    // Adjust validation rules based on whether we're updating or creating
    $request->validate([
        't_title' => 'required|string',
        't_hilight_title' => 'required|string',
        't_first_title' => 'required|string',
        't_first_desc' => 'required|string',
        't_first_image' => $data ? 'nullable|image|mimes:png|max:2048' : 'required|image|mimes:png|max:2048',
        't_second_title' => 'required|string',
        't_second_desc' => 'required|string',
        't_second_image' => $data ? 'nullable|image|mimes:png|max:2048' : 'required|image|mimes:png|max:2048',
        't_third_title' => 'required|string',
        't_third_desc' => 'required|string',
        't_third_image' => $data ? 'nullable|image|mimes:png|max:2048' : 'required|image|mimes:png|max:2048',
    ]);

    try {
        // Initialize paths for images from existing data if available
        $firstImagePath = $data->first_image ?? null;
        $secondImagePath = $data->second_image ?? null;
        $thirdImagePath = $data->third_image ?? null;

        // Handle the upload for each image if present in the request
        if ($request->hasFile('t_first_image')) {
            $firstImagePath = ImageHelper::handleImageUpload($request->file('t_first_image'), $firstImagePath, '/cms/section');
        }

        if ($request->hasFile('t_second_image')) {
            $secondImagePath = ImageHelper::handleImageUpload($request->file('t_second_image'), $secondImagePath, '/cms/section');
        }

        if ($request->hasFile('t_third_image')) {
            $thirdImagePath = ImageHelper::handleImageUpload($request->file('t_third_image'), $thirdImagePath, '/cms/section');
        }

        // Update or create CMS entry with updated image paths
        $data = C_M_S::updateOrCreate(
            [
                'type' => 'work',
            ],
            [
                'title' => $request->t_title,
                'hilight_title' => $request->t_hilight_title,
                'first_title' => $request->t_first_title,
                'first_desc' => $request->t_first_desc,
                'first_image' => $firstImagePath,
                'second_title' => $request->t_second_title,
                'second_desc' => $request->t_second_desc,
                'second_image' => $secondImagePath,
                'third_title' => $request->t_third_title,
                'third_desc' => $request->t_third_desc,
                'third_image' => $thirdImagePath,
            ]
        );

        // Redirect with success or error message
        if ($data->wasRecentlyCreated || $data->wasChanged()) {
            return redirect()->back()->with('t-success', 'Data Updated Successfully');
        } else {
            return redirect()->back()->with('t-error', 'Data update failed!');
        }
    } catch (\Throwable $th) {
        return redirect()->back()->with('t-error', 'Error: ' . $th->getMessage());
    }
}

    public function features(Request $request)
    {
        $data = C_M_S::where('type', 'features')->first();
        $request->validate([
            'p_title' => 'required|string',
            'p_hilight_title' => 'required|string',
            'p_description' => 'required|string',
        ]);


        try {
           
            // Update or create CMS entry
            $data = C_M_S::updateOrCreate(
                [
                    'type' => 'features',
                ],
                [
                    'title' => $request->p_title,
                    'hilight_title' => $request->p_hilight_title,
                    'descriptions' => $request->p_description,
                ]
            );

            // Redirect with success or error message
            if ($data->wasRecentlyCreated || $data->wasChanged()) {
                return redirect()->back()->with('t-success', 'Data Updated Successfully');
            } else {
                return redirect()->back()->with('t-error', 'Data update failed!');
            }

        } catch (\Throwable $th) {
            return redirect()->back()->with('t-error', 'Error: ' . $th->getMessage());
        }
    }

    public function footer(Request $request)
    {
        $data = C_M_S::where('type', 'footer')->first();
        $request->validate([
            'footer_description'=>'required|string',
        ]);
        try { 
            // Update or create CMS entry
            $data = C_M_S::updateOrCreate(
                [
                    'type' => 'footer',
                ],
                [
                    'descriptions' => $request->footer_description,
                ]
            );

            // Redirect with success or error message
            if ($data->wasRecentlyCreated || $data->wasChanged()) {
                return redirect()->back()->with('t-success', 'Data Updated Successfully');
            } else {
                return redirect()->back()->with('t-error', 'Data update failed!');
            }

        } catch (\Throwable $th) {
            return redirect()->back()->with('t-error', 'Error: ' . $th->getMessage());
        }
    }
}
