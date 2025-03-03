<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Traits\apiresponse;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    use apiresponse;
    public function index()
    {
        $faqs = Faq::where('status', 'active')->orderBy('id','DESC')->get();
        if (!$faqs) {
            return $this->error([], 'Faq Not Found!');
        }
        return $this->success($faqs, 'Faq Fetch Success!', 200);
    }
}
