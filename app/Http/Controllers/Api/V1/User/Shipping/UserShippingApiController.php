<?php

namespace App\Http\Controllers\Api\V1\User\Shipping;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\Shipping;
use App\Models\User;
use App\Http\Resources\User\Shipping\UserShippingApiResource;
use App\Http\Resources\User\Shipping\UserSingleShippingApiResource;
use App\Http\Requests\Website\User\Shipping\UserUpdateShippingApiRequest;
use App\Http\Requests\Website\User\Shipping\UserAddShippingApiRequest;
use App\Models\Order;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use App\Models\Country;
use Propaganistas\LaravelPhone\PhoneNumber;
use Validator;
use Illuminate\Validation\Rule;

class UserShippingApiController extends Controller
{
	public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }

    public function index()
    {
      $lang = $this->getLang();
      $user = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();

    //  if (in_array('User', $user_roles)) {
      	$user_id   = Auth::user()->id;
      	$shippings = Shipping::where('user_id', $user_id)->get();
      	$total     = Shipping::where('user_id', $user_id)->count();
        $data      = UserShippingApiResource::collection($shippings);
      
        return response()->json([
        	        'status_code'  => 200,
	        	    'message'      => 'success',
                    'data'         => $data,
                    'total'        => $total,
            ], 200);
      /*} 
      else{
        return response()->json([
        	    'status_code'  => 401,
                'message'  => 'un authorized access page due to permissions',
               ], 401);
      }*/
    }

    public function get_default_shipping()
    {
        $lang = $this->getLang();
     // $request['lang'] = $lang;
        $user = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();
        $exist_default  = Shipping::where('user_id', $user->id)
                                ->where('default', 1)
                                ->first();
        if (!$exist_default) {
           return response()->json([
                        'status_code' => 200,
                        'errors'      => 'no default yet',
                        'data'  => null,
                ], 200);
        }
        $data      = new UserSingleShippingApiResource($exist_default);
            return response()->json([
                        'status_code' => 200,
                        'message'      => 'success',
                        'data'  => $data,
                ], 200);
    }

    public function show($id)
    {
    	$lang = $this->getLang();
     // $request['lang'] = $lang;
    	$user = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

	      if (in_array('User', $user_roles)) {
	      	$user_id   = Auth::user()->id;
	      	$shipping  = Shipping::where('user_id', $user_id)->where('id', $id)->first();
	        $data      = new UserSingleShippingApiResource($shipping);
	      
	        return response()->json([
	        	        'status_code' => 200,
	        	        'message'      => 'success',
	                    'data'  => $data,
	            ], 200);
	      } 
	      else{
	        return response()->json([
	        	    'status_code' => 401,
	                'message'  => 'un authorized access page due to permissions',
	               ], 401);
	      }
    }

    // === Check if phone already exists ===
    function isUniquePhone($phone, $countryCode, $auth_id)
    {
        $db_format = PhoneNumber::make($phone, $countryCode)->formatE164();
        $unique    = Shipping::where('user_id', '!=', $auth_id)
                            ->where('recipient_phone', $phone)->first();
        
        if($unique)
        {
            return true;
        }
        else
        {
            return true;
        }
    }
    //=== End Function ===

     // === Validate e164 format ===
    function check_e164Format($countryCode, $phone)
    {
        $item         = Country::where('country_code', $countryCode)//->select('phonecode')
                               ->first();
        $len          = strlen($item->phonecode);
        $phone_prefix = substr($phone, 1, $len);

        if ($phone_prefix == $item->phonecode) 
        {
          return false;
        }
        else
        {
          return 'number should start with +'.$item->phonecode;
        }
    }
    //=== End Function ===

    public function store(UserAddShippingApiRequest $request)
    {
    	$lang = $this->getLang();
        $request['lang'] = $lang;
    	$user = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

        $country = Country::where('id', $request->country_id)->first();
        $country_code = $country->country_code;
        // return $country_code;
        $recipient_phone = $request->recipient_phone;

        $uniqueResult = $this->isUniquePhone($recipient_phone, $country_code, $user->id);
        // if number already taken 
        if (!$uniqueResult) 
        {
             return response()->json([
                'errors' => 'phone number already taken',
                'status_code' => 400,
             ], 400);
        }


        // check if phone starts with desired format(E.164) according to selected Country Code
            $checkResult = $this->check_e164Format($country_code, $recipient_phone);
 
            if($checkResult)  // if e.164 format fails
            {
                // return $checkResult;
                return response()->json([
                    'errors' => $checkResult,
                    'status_code' => 400,
                 ], 400);
            }
            else
            {    // if e.164 format pass
                // if phone number belongs to country available phone numbers
                $code_validator = Validator::make($request->all(), [
                    'recipient_phone'   => Rule::phone()->country([$country_code]),//->type('mobile'),
                     ]);
                 if($code_validator->fails()) {
                        return response()->json([
                            'errors' => 'Phone format does not match with country code',
                            'status_code' => 400,
                         ], 400);
                }

              /*  $count_shippings = Shipping::where('user_id', $user->id)
                                ->where('default', 1)
                                ->count();

                if ($count_shippings <= 0) {
                    $request['default'] = 1;
                }*/

                $request['user_id'] = $user->id;
                $request['city']  = null;
                $request['state'] = null;
                $request['area']  = null;
        	    $shipping  = Shipping::create($request->all());
    	        $data      = new UserSingleShippingApiResource($shipping);
    	      
    	        return response()->json([
    	                  	'status_code' => 201,
    	        	       // 'message'      => 'success',
                            'message' => __('site_messages.New_shipping_address_added_successfully'),
    	                    'data'  => $data,
    	            ], 201);
	       } 
    }

    public function update(UserUpdateShippingApiRequest $request, $id)
    {
    	$lang = $this->getLang();
        $request['lang'] = $lang;
    	$user = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

        $country = Country::where('id', $request->country_id)->first();
        $country_code = $country->country_code;
        // return $country_code;
        $recipient_phone = $request->recipient_phone;

        $uniqueResult = $this->isUniquePhone($recipient_phone, $country_code, $user->id);
        // if number already taken 
        if (!$uniqueResult) 
        {
             return response()->json([
                'errors' => 'phone number already taken',
                'status_code' => 400,
             ], 400);
        }

        // check if phone starts with desired format(E.164) according to selected Country Code
            $checkResult = $this->check_e164Format($country_code, $recipient_phone);
 
            if($checkResult)  // if e.164 format fails
            {
                // return $checkResult;
                return response()->json([
                    'errors' => $checkResult,
                    'status_code' => 400,
                 ], 400);
            }
            else
            {    // if e.164 format pass
                // if phone number belongs to country available phone numbers
                $code_validator = Validator::make($request->all(), [
                    'recipient_phone'   => Rule::phone()->country([$country_code]),//->type('mobile'),
                     ]);
                 if($code_validator->fails()) {
                        return response()->json([
                            'errors' => 'Phone format does not match with country code',
                            'status_code' => 400,
                         ], 400);
                }
                $user_id   = Auth::user()->id;
                if (!$request->has('telephone_no') || $request->telephone_no == '') {
                    $request['telephone_no'] = NULL;
                }
                if (!$request->has('notices') || $request->notices == '') {
                    $request['notices'] = NULL;
                }
                $shipping  = Shipping::where('user_id', $user_id)->where('id', $id)->first();
                $request['user_id'] = $user_id;
                $request['city']  = null;
                $request['state'] = null;
                $request['area']  = null;
                $shipping->update($request->all());
     
                $data      = new UserSingleShippingApiResource($shipping);
              
                return response()->json([
                        'status_code' => 202,
                      //  'message'      => 'success',
                        'message' => __('site_messages.shipping_edited_successfully'),
                        'data'  => $data,
                ], 202);
           }
    }

    public function destroy($id)
    {
    	$user = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

//	      if (in_array('User', $user_roles)) {
	      	$user_id   = Auth::user()->id;
            $count_orders = Order::where('shipping_address_id', $id)->count();
            if ($count_orders > 0) {
                return response()->json([
                        'status_code' => 400,
                        'errors'      => 'fail, can not be deleted, has orders',
                        'data'        => null,
                ], 400);
            }
	      	$shipping  = Shipping::where('user_id', $user_id)->where('id', $id)->first();
    	    $shipping->delete();
	       // $data      = new UserSingleShippingApiResource($shipping);
	      
	        return response()->json([
	        	        'status_code' => 200,
	        	      //  'message'      => 'success',
                        'message' => __('site_messages.Shipping_address_removed_successfully'),
	                    'data'        => null,
	            ], 200);
	/*      } 
	      else{
	        return response()->json([
	        	    'status_code' => 401,
	                'message'  => 'un authorized access page due to permissions',
	               ], 401);
	      }*/
    }

    public function list_all()
    {
    	$user = Auth::user();
        $data  = Shipping::where('user_id', $user->id)->get();
        foreach ($data as $key => $value) {
            $value['is_country'] = $value->country;
            $value['is_area'] = $value->is_area;
            $value['is_city'] = $value->is_city;
            $value->makeHidden('country');
        }
        return response()->json([
        	        'status_code'  => 200,
	        	    'message'      => 'success',
                    'data'         => $data,
                    // 'total'        => $total,
            ], 200);
    }

    public function select_shipping_address($id)
    {
    	$user = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

    	$shipping  = Shipping::where('user_id', $user->id)->where('id', $id)->first();
    	if (!$shipping) {
    		return response()->json([
	        	    'status_code' => 400,
	                'errors'  => 'wrong address selected',
	               ], 400);
    	}
    	// update pending order
    	$order   = Order::where('user_id', $user->id)
		    	              ->whereNull('paid')
		    	              ->whereNull('approved')
		                      ->where('status', '!=', 4) // not cancelled
                              ->where('status', '!=', 2) // not in progress
		    	              ->where('expired', 0)->first();
	  if ($order) 
      {
          $order->update(['shipping_address_id' => $shipping->id]);
    	  return response()->json([
	        	    'status_code' => 200,
	                'message'  => 'shipping selected successfully',
	               ], 200);
      }else{
        return response()->json([
                    'status_code' => 200,
                    'message'  => 'no pending orders',
                     ], 200);
      }
    }

    public function mark_default_shipping_address($id) // default
    {
    	$user       = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

    	$shipping  = Shipping::where('user_id', $user->id)->where('id', $id)->first();
    	if (!$shipping) {
    		return response()->json([
	        	    'status_code' => 400,
	                'errors'  => 'wrong address selected',
	               ], 400);
    	}
    	if ($shipping->default == 1) {
    		return response()->json([
	        	    'status_code' => 400,
	                'errors'  => 'it is aleady default',
	               ], 400);
    	}else
    	{
    		$exist_default  = Shipping::where('user_id', $user->id)
    		                    ->where('default', 1)
    		                    ->first();
            if (!$exist_default) {
            	$shipping->update(['default' => 1]);
            	return response()->json([
	        	    'status_code' => 200,
	            //    'message'  => 'shipping marked as default successfully',
                    'message' => __('site_messages.shipping_default_successfully'),
	               ], 200);
            }else{
            	$exist_default->update(['default' => 0]);
    		    $shipping->update(['default' => 1]);
    		    return response()->json([
	        	    'status_code' => 200,
	                'message' => __('site_messages.shipping_default_successfully'),
	               ], 200);
            }
    	}
    } // default
}
