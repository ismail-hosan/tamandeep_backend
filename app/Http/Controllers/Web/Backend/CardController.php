<?php

namespace App\Http\Controllers\Web\Backend;

use App\Helper\ImageHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCardRequest;
use App\Http\Requests\UpdateCardRequest;
use App\Models\Card;
use App\Models\CardColor;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CardController extends Controller
{
    private $cards;
    public function __construct(Card $card)
    {
        $this->cards = $card;
    }
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->cards::with('colors')->latest();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('color', function ($data) {
                    if ($data->colors->isNotEmpty()) {
                        return $data->colors->map(function ($color) {
                            return '<span class="badge bg-info text-white">' . $color->name . '</span>';
                        })->implode(' ');
                    }
                    return '<span class="text-muted">No colors available</span>';
                })
                ->addColumn('image', function ($data) {
                    if ($data->image && file_exists(public_path($data->image))) {
                        return '<img src="' . asset($data->image) . '" width="100px" alt="Category Image">';
                    } else {
                        return '<img src="' . asset($data->image) . '" width="100px" alt="Default Image">';
                    }
                })
                ->addColumn('action', function ($data) {

                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                                  <a href="' . route('card.edit', $data->id) . '" type="button" class="btn btn-primary text-white" title="Edit">
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

        return view('backend.layout.card.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.layout.card.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateCardRequest $request)
    {
        $imagePath = $request->hasFile('image')
            ? ImageHelper::handleImageUpload($request->file('image'), null, 'card')
            : null;
        $card = new $this->cards();
        $card->name = $request->name;
        $card->price = $request->price;
        $card->image = $imagePath;


        // Generate a serial-wise unique code
        $lastCard = $this->cards->orderBy('id', 'desc')->first(); // Get the latest card (based on ID)
        $nextSerial = $lastCard ? intval(substr($lastCard->code, 5)) + 1 : 1; // Get the next serial number
        $card->code = 'CARD-' . str_pad($nextSerial, 4, '0', STR_PAD_LEFT);

        // Format like 'CARD-0001'
        $card->save();
        if ($request->has('color') && is_array($request->color)) {
            foreach ($request->color as $color) {
                CardColor::create([
                    'card_id' => $card->id,
                    'name' => $color,
                ]);
            }
        }

        return redirect()->route('card.index')->with('success', 'Card created successfully!');
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
        $card = $this->cards->with('colors')->find($id);
        return view('backend.layout.card.edit', get_defined_vars());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id, UpdateCardRequest $request)
    {
        $card = $this->cards->findOrFail($id);
        $card->name = $request->name;
        $card->price = $request->price;
        if ($request->hasFile('image')) {
            if ($card->image && file_exists(public_path($card->image))) {
                unlink(public_path($card->image)); // Delete the old image file
            }
            $imagePath = ImageHelper::handleImageUpload($request->file('image'), null, 'card');
            $card->image = $imagePath;
        }
        $card->save();
        if ($request->has('color') && is_array($request->color)) {
            $card->colors()->delete();

            foreach ($request->color as $color) {
                CardColor::create([
                    'card_id' => $card->id,
                    'name' => $color,
                ]);
            }
        }

        return redirect()->route('card.index')->with('success', 'Card updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = $this->cards::find($id);
        if (!$data) {
            return response()->json(['t-success' => false, 'message' => 'Data not found.']);
        }
        if ($data->image && file_exists(public_path($data->image))) {
            unlink(public_path($data->image));
        }
        foreach ($data->colors as $color) {
            $color->delete();
        }
        $data->delete();

        return response()->json(['t-success' => true, 'message' => 'Deleted successfully.']);
    }

    public function status($id)
    {
        $data = $this->cards::where('id', $id)->first();
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
