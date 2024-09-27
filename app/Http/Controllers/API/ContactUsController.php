<?php

namespace App\Http\Controllers\API;


use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Models\ContactUs;
use Validator;
use Illuminate\Support\Facades\Mail;

class ContactUsController extends BaseController
{


    public function index(Request $request)
    {
        if($request->get('perPage') == "all") {
            $contact_us = ContactUs::all();
        } else {
            $contact_us = ContactUs::paginate($request->get('perPage'));
        }
        return $this->sendResponse($contact_us->toArray(), 'ContactUs fetched successfully');
    }
    public function storeContactUs(Request $request)
    {
        $post = $request->all();

        $validator = Validator::make($post, [
            'name' => 'required|max:100',
            'email' => 'required|email:rfc,dns',
            'subject' => 'required|string',
            'description' => 'required|max:5000'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $contact_us = ContactUs::create(
            [
                'name' => $post['name'],
                'email' => $post['email'],
                'subject' => $post['subject'],
                'description' => $post['description'],
                'phone_number' => isset($post['phone_number'])?$post['phone_number']:"",
            ]
        );

        $contact_us = ContactUs::find($contact_us->id);

        // Mail::send('emails.contact_us', !empty($contact_us)?$contact_us->toArray():array(), function ($message) use ($request) {
        //     $message->to("engineering@extraslice.com","Contact Us")->subject('Contact Us Mail');
        //     $message->from($request->email,$request->name);
        // });

        return $this->sendResponse($contact_us, 'Contact us created successfully');
    }
    public function show_contact_us($id)
    {
        $contact_us = ContactUs::where('id', $id)->first();
        if($contact_us) {
            return $this->sendResponse($contact_us, 'ContactUs fetched successfully');
        } else {
            return $this->sendError('Error', 'Record not found', 404);
        }
    }
}
