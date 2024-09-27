<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\BaseController;
use Validator;

class TourController extends BaseController
{
    public function scheduleTour(Request $request)
    {
        $post = $request->all();

        $validator = Validator::make($post, [
            'name' => 'required|string|max:255',
            'email' => 'required|email:rfc,dns',
            'phone' => 'required|string|max:20',
            'tourType' => 'required|string',
            'propertyId' => 'required|string',
            'title' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'pincode' => 'required|string|max:20',
            'manager_email' => 'required|email:rfc,dns',

        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        // Mail::send('emails.schedule_tour', $post, function ($message) use ($request) {
        //     $subject = '';
        //     $tourtype = $request->tourType ?? '';

        //     if ($tourtype === 'In-person' || $tourtype === 'Virtual') {
        //         $subject = 'Tour Request';
        //     } elseif ($tourtype === 'Wants more information') {
        //         $subject = 'Information Request';
        //     } elseif ($tourtype === 'Broker wants to know what ExtraSlice offering to brokers') {
        //         $subject = 'Broker Inquiry';
        //     } else {
        //         $subject = 'General Inquiry';
        //     }

        //     $message->to('engineering@extraslice.com')
        //             ->subject($subject)
        //             ->from($request->email, $request->name);
        // });


Mail::send([], [], function ($message) use ($request) {
    $subject = '';
    $tourtype = $request->tourType ?? '';

    if ($tourtype === 'In-person' || $tourtype === 'Virtual') {
        $subject = 'Tour Request';
    } elseif ($tourtype === 'Wants more information') {
        $subject = 'Information Request';
    } elseif ($tourtype === 'Broker wants to know what ExtraSlice offering to brokers') {
        $subject = 'Broker Inquiry';
    } else {
        $subject = 'General Inquiry';
    }

    // Define your HTML content
    $htmlContent = "
    <!DOCTYPE html>
    <html>
    <head>
        <title>New Property Related Request Notification</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                background-color: #f4f4f4;
                margin: 0;
                padding: 0;
            }
            .container {
                max-width: 650px;
                margin: auto;
                padding: 30px;
                background: #ffffff;
                border-radius: 8px;
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                border: 1px solid #ddd;
            }
            h1 {
                color: #007bff;
                border-bottom: 2px solid #007bff;
                padding-bottom: 10px;
                margin-bottom: 20px;
                font-size: 24px;
            }
            .info {
                margin-bottom: 20px;
                padding: 15px;
                background: #f9f9f9;
                border-left: 5px solid #007bff;
                border-radius: 5px;
            }
            .info strong {
                display: block;
                margin-bottom: 8px;
                color: #333;
                font-size: 16px;
            }
            .footer {
                margin-top: 30px;
                padding-top: 15px;
                border-top: 1px solid #ddd;
                text-align: center;
                color: #777;
            }
            .button {
                display: inline-block;
                padding: 12px 24px;
                font-size: 16px;
                color: #ffffff;
                background-color: #007bff;
                border-radius: 5px;
                text-decoration: none;
                text-align: center;
                margin-top: 20px;
            }
            .button:hover {
                background-color: #0056b3;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>{$subject}</h1>
            <p class='info'>
                <strong>Name:</strong> {$request->name}
            </p>
            <p class='info'>
                <strong>Email:</strong> {$request->email}
            </p>
            <p class='info'>
                <strong>Phone:</strong> {$request->phone}
            </p>
            <p class='info'>
                <strong>Tour Type Or Mail Purpose:</strong> {$tourtype}
            </p>";

    // Include optional scheduled date if provided
    if (!empty($request->scheduled_date)) {
        $htmlContent .= "
            <p class='info'>
                <strong>Tour Scheduled For:</strong> {$request->scheduled_date}
            </p>";
    }

    $htmlContent .= "
            <p class='info'>
                <strong>Property ID:</strong> {$request->propertyId}
            </p>
            <p class='info'>
                <strong>Property Title:</strong> {$request->title}
            </p>
            <p class='info'>
                <strong>Property Location:</strong> {$request->location}
            </p>
            <p class='info'>
                <strong>Property Country:</strong> {$request->country}
            </p>
            <p class='info'>
                <strong>Property Zipcode:</strong> {$request->pincode}
            </p>
        </div>
    </body>
    </html>";

    $message->to('engineering@extraslice.com')
            ->subject($subject)
            ->from($request->email, $request->name)
            ->setBody($htmlContent, 'text/html'); // Set the content type to HTML
});


        return $this->sendResponse($post, 'Tour scheduled request sent successfully!');

    }
}
