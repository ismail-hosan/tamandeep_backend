<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\Contact;
use App\Models\Contact as cont;
use App\Models\Paypal;
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
            'phone_home' => 'nullable',
            'phone_office' =>'nullable',
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
        $contact = cont::create([
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
            'phone_office' => $request->phone_office,
        ]);

        return $this->success($contact, 'Data Store Successfully!', 200);
    }

    public function contactShow($id)
    {
        // Check if order item exists
        // $res = $this->check($id);
        // if ($res) {
        //     return $res;
        // }

        $contact = cont::where('order_item_id', $id)->get();
        if (!$contact) {
            return $this->error([], 'Data Not Found');
        }
        return $this->success($contact, 'Data Fetch Successfully!', 200);
    }

    public function addPaypal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'website' => 'nullable|string|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $url = new Paypal();
        $url->url = $request->website;
        $url->save();

        return $this->success($url,'Data Store Successfully!',200);
    }

    private function check($order_item_id)
    {
        $user = auth()->user();
        if (!$user) {
            return $this->error([], 'User not authenticated', 401);
        }
        if (!$user->orders()->exists()) {
            return $this->error([], 'Order not found for this user', 404); // 404 Not Found
        }
        $order = $user->orders()->first();
        $orderItem = $order->items()->where('id', $order_item_id)->first();

        if (!$orderItem) {
            return $this->error([], 'Order item not found or does not belong to this user\'s order', 404); // 404 Not Found
        }
        return null;
    }
}
