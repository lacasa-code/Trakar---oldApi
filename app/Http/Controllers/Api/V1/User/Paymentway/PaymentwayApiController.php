<?php

namespace App\Http\Controllers\Api\V1\User\Paymentway;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Paymentway;
use App\Http\Requests\Website\User\Paymentway\UserSelectPaymentwayApiRequest;
use Auth;
use App\Models\Userpaymentway;
use App\Models\Order;

class PaymentwayApiController extends Controller
{
  public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }

    public function paymentways_all()
    {
      $lang = $this->getLang();
    	$paymentways = Paymentway::where('id', '!=', 1)->get();
     // $paymentways = Paymentway::where('lang', $lang)->get();
      //$total       = Paymentway::where('lang', $lang)->count();
      $total      = Paymentway::where('id', '!=', 1)->count();
        //$data        = UserShippingApiResource::collection($shippings);
      
        return response()->json([
        	          'status_code'  => 200,
	        	        'message'      => 'success',
                    'data'         => $paymentways,
                    'total'        => $total,
            ], 200);
    }

    public function user_select_paymentway(UserSelectPaymentwayApiRequest $request)
    {

       	$user = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

	    //  if (in_array('User', $user_roles)) {
	      	$user_id   = Auth::user()->id;

            $userpayment_way = Userpaymentway::create([
            	'user_id'       => $user_id,
            	'paymentway_id' => $request->paymentway_id
            ]);
	      
	        return response()->json([
	        	        'status_code'  => 200,
		        	    'message'      => 'success',
	                    'data'         => $userpayment_way,
	            ], 200);
	  //    } 
	    /*  else{
	        return response()->json([
                    'status_code' => 401,
	                'message'  => 'un authorized access page due to permissions',
	               ], 401);
	      }*/
    }

    public function user_select_paymentway_checkout(UserSelectPaymentwayApiRequest $request)
    {

        $user = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

        // if (in_array('User', $user_roles) || in_array('Vendor', $user_roles)) {
          $user_id   = Auth::user()->id;
          $paymentway_id = $request->paymentway_id;

            // update pending order
      $order   = Order::where('user_id', $user->id)
                        ->whereNull('paid')
                        ->whereNull('approved')
                        ->where('status', '!=', 'cancelled') // not cancelled
                        ->where('status', '!=', 'in progress') // not in progress
                        ->where('expired', 0)->first();
      if ($order) 
      {
          $order->update(['payment_id' => $paymentway_id]);
          return response()->json([
                    'status_code' => 200,
                      'message'  => 'payment way selected successfully',
                     ], 200);
      }else{
        return response()->json([
                    'status_code' => 200,
                    'message'  => 'no pending orders',
                     ], 200);
      }

      /*  } 
        else{
          return response()->json([
                    'status_code' => 401,
                  'message'  => 'un authorized access page due to permissions',
                 ], 401);
        }*/
    } 
}
