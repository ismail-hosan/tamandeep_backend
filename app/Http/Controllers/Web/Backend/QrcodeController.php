<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class QrcodeController extends Controller
{
    public function view($id)
    {
        try {

            $decryptedData = Crypt::decryptString($id);
    
        } catch (DecryptException $e) {
            // If decryption fails, handle the error
            return redirect()->route('error.page');
        }
    
        dd($decryptedData); // Use the unserialized data
    }
}
