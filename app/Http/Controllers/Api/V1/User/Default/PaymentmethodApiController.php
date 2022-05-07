<?php

namespace App\Http\Controllers\Api\V1\User\Default;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentmethodApiController extends Controller
{
    public function default_paymentway(DefaultPaymentApiRequest $request)
    {
    	$user       = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

        // if (in_array('User', $user_roles) || in_array('Vendor', $user_roles)) {
          $user_id   = Auth::user()->id;
          $paymentway_id = $request->paymentway_id;

          $method       = Userpaymentway::create([
				          	'user_id'        => $user_id,
				          	'paymentway_id'  => $paymentway_id,
				          ]);


          return response()->json([
                    'status_code' => 200, 
                    'message'     => 'success',
                    'data'        => $method,
                    //'total' => $total,
            ], 200);


    }
}
