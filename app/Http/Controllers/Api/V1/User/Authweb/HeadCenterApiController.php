<?php

namespace App\Http\Controllers\Api\V1\User\Authweb;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Api\V1\Vendor\AddHeadCenterApiRequest;
use App\Models\Store;
use Gate;
use App\Http\Requests\SearchApisRequest;
use App\Http\Resources\Admin\StoreApiResource;
use Auth;
use App\Models\AddVendor;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\Admin\SpecificStoreApiResource;
use App\Models\User;
use App\Mail\SendAdminVendorRequestMail;
use Illuminate\Support\Facades\Mail;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use App\Models\Country;
use Propaganistas\LaravelPhone\PhoneNumber;
use Validator;
use Illuminate\Validation\Rule;

class HeadCenterApiController extends Controller
{
    public function getLang()
    {
      return $lang = \Config::get('app.locale');
    }

    public function specific_vendor($id)
    {
    	$vendor = AddVendor::findOrFail($id);
    	return $vendor->complete;
    }

    // === Check if phone already exists ===
    function isUniquePhone($phone, $countryCode)
    {
        $db_format = PhoneNumber::make($phone, $countryCode)->formatE164();
        $unique    = Store::where('moderator_phone', $phone)->first();
        
        if($unique)
        {
            return false;
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

    public function vendor_head_center(AddHeadCenterApiRequest $request)
    {
      $lang = $this->getLang();
      $request['lang'] = $lang;

      $user = User::findOrFail($request->user_id);
      $user_roles = $user->roles->pluck('title')->toArray();

      $country = Country::where('id', $request->country_id)->first();
      $country_code = $country->country_code;
        // return $country_code;
        $recipient_phone = $request->moderator_phone;

        $uniqueResult = $this->isUniquePhone($recipient_phone, $country_code);
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
                    'moderator_phone'   => Rule::phone()->country([$country_code]),//->type('mobile'),
                     ]);
                 if($code_validator->fails()) {
                        return response()->json([
                            'errors' => 'Phone format does not match with country code',
                            'status_code' => 400,
                         ], 400);
                }
         } 

        $exist_vendor = AddVendor::where('id', $request->vendor_id)->first();
        if (!$exist_vendor) {
        	return response()->json(['errors' => 'invalid user'], 400);
        }
        if ($exist_vendor->userid_id != $request->user_id) {
        	return response()->json(['errors' => 'invalid user'], 400);
        }

        $vendor = AddVendor::where('userid_id', $user->id)->first();
     
        if ($vendor->complete == 1) {
        	return response()->json(['errors' => 'already completed profile'], 400);
        }

      if (!in_array('Vendor', $user_roles)) {
          return response()->json([
                'status_code' => 401, 
                // 'message'     => 'success',
                'message'  => 'un authorized access page due to permissions',
               ], 401);
      } 

    	$request['vendor_id']      = $vendor->id;
    	$request['moderator_name'] = $request->name;
    	$request['head_center']    = 1;
    	
        $store = Store::create($request->all());
        $store['vendor_name'] = $store->vendor->vendor_name;

        $this->check_profile_complete($vendor->id);

        $complete =  $this->specific_vendor($vendor->id);

        if ($complete == 1) {
        	// send admin email
        	$admin = User::findOrFail(1);
         Mail::to($admin->email)->send(new SendAdminVendorRequestMail($vendor->vendor_name));
        }
      
        $data = new StoreApiResource($store->load('vendor'));
        return response()->json([
            'status_code'   => 201,
          //  'message'       => 'successfull register waiting approval',
            'message' => __('site_messages.vendor_request'),
            'data'          => $data,
          ], Response::HTTP_CREATED);
    }

    public function check_profile_complete($vendor_id)
    {
    	$vendor = AddVendor::findOrFail($vendor_id);
        $exist_center = Store::where('vendor_id', $vendor_id)->where('head_center', 1)->first();
    
          if ($vendor->type == 1)  // start normal vendor
          {
              if ($exist_center != null && $vendor->commercial_no != null && $vendor->tax_card_no != null && $vendor->bank_account != null && $vendor->taxCardDocs != null && $vendor->bankDocs != null && $vendor->commercialDocs != null ) 
              {
                  $vendor->update(['complete' => 1]);
              }
          }  // end normal vendor
          else
          {   // start wholesale or both vendor
            if ($exist_center != null && $vendor->commercial_no != null && $vendor->tax_card_no != null && $vendor->bank_account != null && $vendor->taxCardDocs != null && $vendor->commercialDocs != null && $vendor->bankDocs != null && $vendor->wholesaleDocs != null) 
             {
                 $vendor->update(['complete' => 1]);
             }
          }  // end wholesale or both vendor
    }
}
