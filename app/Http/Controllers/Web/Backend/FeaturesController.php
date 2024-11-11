<?php

namespace App\Http\Controllers\Web\Backend;

use App\Helper\ImageHelper;
use App\Http\Controllers\Controller;
use App\Models\Features;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class FeaturesController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Features::latest();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('image', function ($data) {
                    // Check if the image path exists and return the image tag
                    if ($data->image && file_exists(public_path($data->image))) {
                        return '<img src="' . asset($data->image) . '" width="100px" alt="Category Image">';
                    } else {
                        // Return a default image or placeholder if image does not exist
                        return '<img src="' . asset($data->image) . '" width="100px" alt="Default Image">';
                    }
                })
                ->addColumn('action', function ($data) {

                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                                  <a href="' . route('features.edit',  $data->id) . '" type="button" class="btn btn-primary text-white" title="Edit">
                                  <i class="bi bi-pencil"></i>
                                  </a>
                                  <a href="#" onclick="showDeleteConfirm(' . $data->id . ')" type="button" class="btn btn-danger text-white" title="Delete">
                                  <i class="bi bi-trash"></i>
                                </a>
                                </div>';
                })
                ->addColumn('status', function ($data) {
                    $status = ' <div class="form-check form-switch" style="margin-left:40px;">';
                    $status .= ' <input onclick="showStatusChangeAlert(' . $data->id . ')" type="checkbox" class="form-check-input" id="customSwitch' . $data->id . '" getAreaid="' . $data->id . '" name="status"';
                    if ($data->status == "active") {
                        $status .= "checked";
                    }
                    $status .= '><label for="customSwitch' . $data->id . '" class="form-check-label" for="customSwitch"></label></div>';

                    return $status;
                })
                ->rawColumns(['action', 'status', 'image'])
                ->make(true);
        }

        return view('backend.layout.cms.features.index');
    }

    public function create()
    {
        return view('backend.layout.cms.features.create');
    }

    public function store(Request $request)
    {
        // Validation rules
        $request->validate([
            'title' => 'required|max:255',
            'description' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Image validation
        ]);


        try {
            // Handle image upload if there is a new image
            if ($request->hasFile('image')) {
                $imagePath = ImageHelper::handleImageUpload($request->file('image'), null, '/cms/featurs/section');
            } else {
                $imagePath = null;
            }

            $data = new Features();
            $data->title = $request->input('title');
            $data->description = $request->input('description');
            $data->image = $imagePath;
            $data->crated_at = Auth::user()->id;
            $data->save();
            // Check if the category was created successfully
            if ($data) {
                return redirect()->action([self::class, 'index'])->with('t-success', 'Category created successfully.');
            } else {
                return redirect()->action([self::class, 'index'])->with('t-error', 'Category creation failed.');
            }
        } catch (\Exception $e) {
            \Log::error("Category creation failed: " . $e->getMessage());
            return redirect()->action([self::class, 'index'])->with('t-error', 'An error occurred while creating the category.');
        }
    }

    public function edit(string $id)
    {
        $data = Features::find($id);
        return view('backend.layout.cms.features.edit', get_defined_vars());
    }

    public function update(Request $request, $id)
    {
        // Validation rules
        $request->validate([
            'title' => 'required|max:255',
            'description' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Image validation
        ]);

        try {
            // Find the existing feature by ID
            $data = Features::findOrFail($id);

            // Handle image upload if there is a new image
            if ($request->hasFile('image')) {
                $imagePath = ImageHelper::handleImageUpload($request->file('image'), $data->image, '/cms/features/section');
            } else {
                $imagePath = $data->image;
            }

            // Update the feature with the new data
            $data->title = $request->input('title');
            $data->description = $request->input('description');
            $data->image = $imagePath;
            $data->crated_at = Auth::user()->id; // Store the ID of the user who updated the feature
            $data->save();

            // Return success message upon successful update
            return redirect()->action([self::class, 'index'])->with('t-success', 'Feature updated successfully.');
        } catch (\Exception $e) {
            // Log error message if something goes wrong
            \Log::error("Feature update failed: " . $e->getMessage());

            // Return error message on failure
            return redirect()->action([self::class, 'index'])->with('t-error', 'An error occurred while updating the feature.');
        }
    }

    public function destroy(string $id)
    {
        // Find the feature by ID
        $data = Features::find($id);

        // Check if the feature exists
        if (!$data) {
            return response()->json(['t-success' => false, 'message' => 'Data not found.']);
        }

        // Check if there is an associated image and delete it
        if ($data->image && file_exists(public_path($data->image))) {
            // Unlink the image file from storage
            unlink(public_path($data->image));
        }

        // Delete the feature record from the database
        $data->delete();

        // Return a success response
        return response()->json(['t-success' => true, 'message' => 'Deleted successfully.']);
    }
}
