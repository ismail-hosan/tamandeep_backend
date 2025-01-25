<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Traits\apiresponse;
use Illuminate\Http\Request;

class CardController extends Controller
{
    use apiresponse;
    public function index()
    {
        $cards = Card::where('status','active')
                        ->orderBy("id","desc")
                        ->with('colors')
                        ->get();

        return $this->success($cards,'Data fatched',200);
    }
}
