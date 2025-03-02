<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\FaqCreateRequest;
use App\Models\Faq;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use function PHPUnit\Framework\returnArgument;

class FaqController extends Controller
{

    private $faqs;

    public function __construct(Faq $faq)
    {
        $this->faqs = $faq;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->faqs::latest();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($data) {

                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                                  <a href="' . route('faq.edit', $data->id) . '" type="button" class="btn btn-primary text-white" title="Edit">
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
                ->rawColumns(['color', 'action', 'status', 'image'])
                ->make(true);
        }

        return view('backend.layout.faq.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.layout.faq.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FaqCreateRequest $request)
    {
        $faq = new Faq();
        $faq->ques = $request->question;
        $faq->desc = $request->answer;
        $faq->save();

        return redirect()->route('faq.index')->with('t-success', 'Faq Create Successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

        $faq = $this->faqs::find($id);
        return view('backend.layout.faq.edit', compact('faq'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FaqCreateRequest $request, string $id)
    {
        $faq = $this->faqs::find($id);
        $faq->ques = $request->question;
        $faq->desc = $request->answer;
        $faq->save();

        return redirect()->route('faq.index')->with('t-success', 'Faq Update Successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = $this->faqs::find($id);
        if (!$data) {
            return response()->json(['t-success' => false, 'message' => 'Data not found.']);
        }
        $data->delete();

        return response()->json(['t-success' => true, 'message' => 'Deleted successfully.']);
    }
    
    public function status($id)
    {
        $data = $this->faqs::find($id);
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