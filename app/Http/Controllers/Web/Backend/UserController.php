<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Log;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = User::where('role', 'user')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($data) {

                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                                  <a href="' . route('user.edit', $data->id) . '" type="button" class="btn btn-primary text-white" title="Edit">
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

                    $status .= "checked";

                    $status .= '><label for="customSwitch' . $data->id . '" class="form-check-label" for="customSwitch"></label></div>';

                    return $status;
                })
                ->rawColumns(['action', 'status', 'image'])
                ->make(true);
        }

        return view('backend.layout.user.index');
    }

    public function edit($id)
    {
        $customer = User::find($id);
        return view('backend.layout.user.edit', get_defined_vars());
    }

    public function update(UserUpdateRequest $request, $id)
    {
        try {
            $data = User::find($id);
            $data->name = $request->input('name');
            $data->email = $request->input('email');
            if ($request->password_confirmation == $request->password) {
                $data->password = Hash::make($request->password);
            }
            $data->save();
            return redirect()->route('user.index')->with('t-success', 'Customer Data Updated Successfully!');
        } catch (\Throwable $th) {
           Log::error($th->getMessage());
        }

    }
}
