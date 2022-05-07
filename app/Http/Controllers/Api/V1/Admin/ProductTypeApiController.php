<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Gate;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Producttype;
use Auth;
use App\Models\AddVendor;
use App\Models\Vendorstaff;

class ProductTypeApiController extends Controller
{
	public function getLang()
    {
      return $lang = \Config::get('app.locale');
    }

     public function list_all()
     {
     	$lang = $this->getLang();
        $auth_user    = Auth::user();
        $auth_user_id = Auth::user()->id;
        $user_roles   = $auth_user->roles->pluck('title')->toArray();

        if (in_array('Vendor', $user_roles)) 
        {
            $vendor = AddVendor::where('userid_id', Auth::user()->id)->first();
            //$data = Producttype::where('lang', $lang)->get();
            if ($vendor->type == 1) {
                $data = Producttype::whereIn('id', [1])->get();
                return response()->json(['data' => $data], Response::HTTP_OK);
             }
            if ($vendor->type == 2) {
                // $data = Producttype::all();
                $data = Producttype::whereIn('id', [2])->get();
                return response()->json(['data' => $data], Response::HTTP_OK);
             }
            if ($vendor->type == 3) {
              $data = Producttype::all();
              return response()->json(['data' => $data], Response::HTTP_OK);
             }
        }

         if (in_array('Admin', $user_roles)) 
        {
                $data = Producttype::all();
                return response()->json(['data' => $data], Response::HTTP_OK);
        }

        if (in_array('Manager', $user_roles) || in_array('Staff', $user_roles)) 
        {
            $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
            $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
            $vendor_id     = $vendor->id;
            $staff_stores = $exist_staff->stores->pluck('id')->toArray();

                if ($vendor->type == 1) {
                $data = Producttype::whereIn('id', [1])->get();
                return response()->json(['data' => $data], Response::HTTP_OK);
                }
                if ($vendor->type == 2) {
                // $data = Producttype::all();
                $data = Producttype::whereIn('id', [2])->get();
                return response()->json(['data' => $data], Response::HTTP_OK);
                 }
                if ($vendor->type == 3) {
                  $data = Producttype::all();
                  return response()->json(['data' => $data], Response::HTTP_OK);
                 }
        }

    }
}
