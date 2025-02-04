<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Traits\apiresponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use apiresponse;
    public function index()
    {
        $auth = auth()->user();

        $order = Order::where("user_id", $auth->id)->orderBy("created_at", "DESC")->with('items')->get();
        return $this->success($order, 'Data fatch Successfully!!', 200);
    }
}
