<?php

namespace App\Http\Controllers;

use App\Mail\DitAdsEmail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class EmailController extends Controller
{
    public function sendEmail(Request $request)
    {
        $data = [

            'subject' => $request->input('subject'),
            'message' => $request->input('message'),
            // Add any other data you want to pass to the email view here
        ];

        $receiverEmail  = $request->input('email') ; // Replace with the actual receiver's email address

        Mail::to($receiverEmail)->send(new DitAdsEmail($data));

        return response()->json(['success' => true, 'message' => "Email sent successfully!"],200);
    }
}
