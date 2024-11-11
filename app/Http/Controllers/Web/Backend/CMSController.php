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
    
        // Adjust validation rules based on whether we're updating or creating
        $request->validate([
            'title' => 'required|string',
            'hilight_title' => 'required|string',
            'image' => $data ? 'nullable|image|mimes:png|max:2048' : 'required|image|mimes:png|max:2048',
            'description' => 'required|string',
        ]);
        $firstImagePath = $data->image ?? null;
    
        try {
            // Handle new image upload
            if ($request->hasFile('image')) {
              
                $firstImagePath = ImageHelper::handleImageUpload($request->file('image'), $data->image, '/cms/banner');
       
            } else {
                // Retain old image path if no new image is uploaded
                $imagePath = $data->image ?? null;
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
    
            // Redirect with success or error message
            if ($data->wasRecentlyCreated || $data->wasChanged()) {
                return redirect()->back()->with('t-success', 'Data Updated Successfully');
            } else {
                return redirect()->back()->with('t-error', 'Data update failed!');
            }
    
        } catch (\Throwable $th) {
            dd($th->getMessage());
        }
    }

    public function second_section(Request $request)
    {
        
        $data = C_M_S::where('type', 'land_first')->first();

        // Adjust validation rules based on whether we're updating or creating
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
        // dd($request->all());

        try {
            // Handle new image upload
            if ($request->hasFile('f_image')) {
                // Delete old image if updating and a new image is uploaded
                if ($data && $data->image && file_exists(public_path($data->image))) {
                    unlink(public_path($data->image));
                }

                // Store the new image
                $image = $request->file('f_image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $imagePath = '/cms/section/' . $imageName;
                $image->move(public_path('/cms/section/'), $imageName);
            } else {
                // Retain old image path if no new image is uploaded
                $imagePath = $data->image ?? null;
            }

            // Update or create CMS entry
            $data = C_M_S::updateOrCreate(
                [
                    'type' => 'land_first',
                ],
                [
                    'title' => $request->f_title,
                    'hilight_title' => $request->f_hilight_title,
                    'image' => $imagePath,
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

        // Adjust validation rules based on whether we're updating or creating
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
        // dd($request->all());
        $imagePath = $data->image ?? null;

        try {
            // Handle new image upload
            if ($request->hasFile('s_image')) {
                // Handle the upload for the first image
            $imagePath = ImageHelper::handleImageUpload($request->file('s_image'), $data->image, '/cms/section');
        
            } else {
                // Retain old image path if no new image is uploaded
                $imagePath = $data->image ?? null;
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
        // Initialize paths for images
        $firstImagePath = $data->first_image ?? null;
        $secondImagePath = $data->second_image ?? null;
        $thirdImagePath = $data->third_image ?? null;

        // Handle the upload for the first image
        if ($request->hasFile('t_first_image')) {
            $firstImagePath = ImageHelper::handleImageUpload($request->file('t_first_image'), $data->first_image, '/cms/section');
        }

        // Handle the upload for the second image
        if ($request->hasFile('t_second_image')) {
            $secondImagePath = ImageHelper::handleImageUpload($request->file('t_second_image'), $data->second_image, '/cms/section');
        }

        // Handle the upload for the third image
        if ($request->hasFile('t_third_image')) {
            $thirdImagePath = ImageHelper::handleImageUpload($request->file('t_third_image'), $data->third_image, '/cms/section');
        }

        // Update or create CMS entry
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
