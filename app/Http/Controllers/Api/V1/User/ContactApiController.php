<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Api\V1\User\Contact\ContactApiRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Contact;
use Gate;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendAdminContactMail;

class ContactApiController extends Controller
{
    public function saveContact(ContactApiRequest $request)
    {
    	$contact = Contact::create($request->all());
    	$admin = User::findOrFail(1);

                $name =  $request->name;
                $email =  $request->email;
                $phone_number =  $request->phone_number;
                $message =  $request->message;
                $subject =  $request->subject;

        Mail::to($admin->email)->send(new SendAdminContactMail($name, $email, $phone_number, $message, $subject));

          return response()->json([
          'status_code' => 200,
          'message'     => 'succcess, Staff Member Joined',
          'data'        => $contact,
        ], Response::HTTP_OK);
    }
}
