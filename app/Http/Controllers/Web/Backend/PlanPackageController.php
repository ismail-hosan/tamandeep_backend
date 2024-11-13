<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\PlanPackage;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class PlanPackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = PlanPackage::latest();
            return DataTables::of($data)
                ->addIndexColumn()

                // ->addColumn('user_id', function ($data) {
                //     return $data->user ? $data->user->name : 'N/A'; // Display user's name or 'N/A' if not found
                // })

                ->addColumn('action', function ($data) {

                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                                  <a href="' . route('planpackage.edit',  $data->id) . '" type="button" class="btn btn-primary text-white" title="Edit">
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

                ->rawColumns(['action', 'status'])
                ->make(true);
        }

        return view('backend.layout.cms.planpackage.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.layout.cms.planpackage.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation rules
        $request->validate([
            'title' => 'required|max:255',
            'price' => 'required|numeric|min:0.01',
            'description' => 'required',
            'status' => 'required|in:Active,Inactive',
        ]);

        try {

            $data = new PlanPackage();
            $data->title = $request->input('title');
            // $data->price = $request->input('price');

            $price = preg_replace('/[^\d.]/', '', $request->input('price'));
            $data->price = $price;
            $data->description = $request->input('description');
            $data->status = $request->input('status');

            // $data->crated_at = Auth::user()->id;
            $data->save();

            if ($data) {
                return redirect()->action([self::class, 'index'])->with('t-success', 'Plan Package created successfully.');
            } else {
                return redirect()->action([self::class, 'index'])->with('t-error', 'Plan Package creation failed.');
            }
        } catch (\Exception $e) {
            // dd($e->getMessage());
            return redirect()->action([self::class, 'index'])->with('t-error', 'An error occurred while creating the Plan Package.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = PlanPackage::find($id);

        return view('backend.layout.cms.planpackage.edit', get_defined_vars());
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|max:255',
            'price' => 'required|numeric|min:0.01',
            'description' => 'required',
            'status' => 'required|in:Active,Inactive',
        ]);

        try {
            $data = PlanPackage::findOrFail($id);

            $data->title = $request->input('title');
            // $data->price = $request->input('price');

            $price = preg_replace('/[^\d.]/', '', $request->input('price'));
            $data->price = $price;
            $data->description = $request->input('description');
            $data->status = $request->input('status');

            // $data->crated_at = Auth::user()->id; // Store the ID of the user who updated the planpackage
            $data->save();

            return redirect()->action([self::class, 'index'])->with('t-success', 'Plan Package updated successfully.');
        } catch (\Exception $e) {
            // dd($e->getMessage());

            return redirect()->action([self::class, 'index'])->with('t-error', 'An error occurred while updating the planpackage.');
        }
    }

    public function destroy(string $id)
    {
        $data = PlanPackage::find($id);

        if (!$data) {
            return response()->json(['t-success' => false, 'message' => 'Data not found.']);
        }

        $data->delete();
        return response()->json(['t-success' => true, 'message' => 'Deleted successfully.']);
    }


    public function status($id)
    {
        $data = PlanPackage::where('id', $id)->first();
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
