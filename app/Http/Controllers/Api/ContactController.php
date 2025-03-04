<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\Contact;
use App\Traits\apiresponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    use apiresponse;
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|string|email',
            'message' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Send the email to the admin
        // Mail::to('mdrobinhosan57@gmail.com')->send(new Contact(
        //     $request->first_name,
        //     $request->last_name,
        //     $request->email,
        //     $request->message
        // ));

        return $this->success([], 'Your message has been sent successfully!', 200);
    }


    public function addContact(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_item_id' => 'nullable|integer',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'industry' => 'nullable|string|max:255',
            'birthday' => 'nullable|date',
            'phone_home' => ['nullable', 'regex:/^(\+?[\d\s-]{10,})$/'],
            'website' => 'nullable|string|url',
            'address' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Save the contact to the database
        $contact = Contact::create([
            'order_item_id' => $request->order_item_id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'company_name' => $request->company_name,
            'job_title' => $request->job_title,
            'industry' => $request->industry,
            'birthday' => $request->birthday,
            'phone_home' => $request->phone_home,
            'website' => $request->website,
            'address' => $request->address,
            'email' => $request->email,
        ]);

        return $this->success($contact,'Data Store Successfully!',200);
    }
}
