<?php

namespace App\Http\Controllers\Web\Backend;

use App\Helper\ImageHelper;
use App\Http\Controllers\Controller;
use App\Models\Brandlogo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class BrandlogoController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Brandlogo::latest();
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
                                  <a href="' . route('brandlogo.edit',  $data->id) . '" type="button" class="btn btn-primary text-white" title="Edit">
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

        return view('backend.layout.cms.brandlogo.index');
    }

    public function create()
    {
        return view('backend.layout.cms.brandlogo.create');
    }

    public function store(Request $request)
    {
        // Validation rules
        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status' => 'required|in:Active,Inactive',
        ]);


        try {
            // Handle image upload if there is a new image
            if ($request->hasFile('image')) {
                $imagePath = ImageHelper::handleImageUpload($request->file('image'), null, '/cms/brandlogo/section');
            } else {
                $imagePath = null;
            }

            $data = new Brandlogo();
            $data->image = $imagePath;
            $data->status = $request->input('status');
            // $data->crated_at = Auth::user()->id;
            $data->save();

            if ($data) {
                return redirect()->action([self::class, 'index'])->with('t-success', 'BrandLogo created successfully.');
            } else {
                return redirect()->action([self::class, 'index'])->with('t-error', 'BrandLogo creation failed.');
            }
        } catch (\Exception $e) {
            dd($e ->getMessage());
            // \Log::error("BrandLogo creation failed: " . $e->getMessage());
            return redirect()->action([self::class, 'index'])->with('t-error', 'An error occurred while creating the BrandLogo.');
        }
    }

    public function edit(string $id)
    {
        $data = Brandlogo::find($id);
        return view('backend.layout.cms.brandlogo.edit', get_defined_vars());
    }

    public function update(Request $request, $id)
    {
        // Validation rules
        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status' => 'required|in:Active,Inactive',
        ]);

        try {
            $data = Brandlogo::findOrFail($id);

            // Handle image upload if there is a new image
            if ($request->hasFile('image')) {
                $imagePath = ImageHelper::handleImageUpload($request->file('image'), $data->image, '/cms/brandlogo/section');
            } else {
                $imagePath = $data->image;
            }

            $data->image = $imagePath;
            $data->status = $request->input('status');
            // $data->crated_at = Auth::user()->id;
            $data->save();

            return redirect()->action([self::class, 'index'])->with('t-success', 'BrandLogo updated successfully.');
        } catch (\Exception $e) {
            dd($e ->getMessage());
            // \Log::error("BrandLogo update failed: " . $e->getMessage());

            // Return error message on failure
            return redirect()->action([self::class, 'index'])->with('t-error', 'An error occurred while updating the BrandLogo.');
        }
    }

    public function destroy(string $id)
    {
        $data = Brandlogo::find($id);

        if (!$data) {
            return response()->json(['t-success' => false, 'message' => 'Data not found.']);
        }

        if ($data->image && file_exists(public_path($data->image))) {
            unlink(public_path($data->image));
        }

        $data->delete();

        return response()->json(['t-success' => true, 'message' => 'Deleted successfully.']);
    }

    public function status($id)
    {
        $data = Brandlogo::where('id', $id)->first();
        if ($data->status == 'active') {
            // If the current status is active, change it to inactive
            $data->status = 'inactive';
            $data->save();

            // Return JSON response indicating success with message and updated data
            return response()->json([
                'success' => false,
                'message' => 'Unpublished Successfully.',
                'data' => $data,
            ]);
        } else {
            // If the current status is inactive, change it to active
            $data->status = 'active';
            $data->save();

            // Return JSON response indicating success with a message and updated data.
            return response()->json([
                'success' => true,
                'message' => 'Published Successfully.',
                'data' => $data,
            ]);
        }
    }
}
