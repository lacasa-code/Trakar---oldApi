<?php

namespace App\Http\Controllers\Api\V1\Vendor\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendStaffRegisterEmail;
use App\Models\User;
use App\Models\AddVendor;
use Gate;
use Symfony\Component\HttpFoundation\Response;
use Auth;
use App\Http\Requests\Api\V1\Vendor\Staff\VendorAddStaffApiRequest;
use App\Models\Vendorstaff;
use App\Models\Role;
use App\Http\Requests\Api\V1\Vendor\Staff\VendorAssignStoresStaffApiRequest;
use App\Mail\VendorStaffGotApprovedMail;
use App\Http\Requests\Api\V1\Vendor\Staff\VendorApproveStaffApiRequest;
use App\Http\Resources\Api\V1\Vendor\RejectReasonsApiResource;

class VendorStaffApiController extends Controller
{
	public function getLang()
    {
      return $lang = \Config::get('app.locale');
    }

	public function vendor_add_staff(VendorAddStaffApiRequest $request)
	{
	   abort_if(Gate::denies('vendor_add_staff'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $user       = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

        if (in_array('Vendor', $user_roles)) {
        	$vendor = AddVendor::where('userid_id', Auth::user()->id)->first();
        	$vendor_name = $vendor->vendor_name;
        	$email = $request->email;
			$role_id = $request->role;
			$role_title = Role::findOrFail($role_id);

			if (!$role_title) {
				return response()->json([
	              'status_code' => 400,
	              'errors'     => 'this role not found',
	              // 'data'        => $data,
	            ], 400);
			}
          if ($role_title->title != 'Staff' && $role_title->title != 'Manager') {
           return response()->json([
                'status_code' => 400,
                'errors'  => 'invalid role selected',
               ], 400);
          }

			$item = Vendorstaff::create([
				'email'     => $email,
		        'role_name' => $role_title->title,
		        'role_id'   => $role_title->id,
		        'vendor_id'    => $vendor->id,
		        'vendor_email' => $vendor->email,
			]);
			$stores_arr  = json_decode($request->stores);
		    $item->stores()->sync($stores_arr);
			Mail::to($email)->send(new SendStaffRegisterEmail($vendor_name, $role_title->title));  
	            return response()->json([
	              'status_code' => 200,
	            //  'message'     => 'succcess, register invitation mail sent successfully',
	              'message' => __('site_messages.vendor_add_staff'),
	              // 'data'        => $data,
	            ], Response::HTTP_OK);
        } else{
        	return response()->json([
                    'status_code' => 401, 
                   // 'message'     => 'success',
                    'message'  => 'un authorized access page due to permissions',
                   ], 401);
        }
	}

	public function vendor_approve_staff(VendorApproveStaffApiRequest $request)
	{
		$user       = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

        if (in_array('Vendor', $user_roles)) {
        	$staff_id = $request->staff_id;
        	$exist_user = User::findOrFail($staff_id);
		    $item = Vendorstaff::where('email', $exist_user->email)->first();
		    if ($item->approved == 1) {
	    		return response()->json([
	              'status_code' => 400,
	              'errors' => 'fail, already approved',
	              // 'data'  => $data,
	            ], 400);
	    	}
		    $item->update(['approved' => 1]);
		    $AddVendor   = AddVendor::where('id', $item->vendor_id)->first();
		    Mail::to($item->email)->send(new VendorStaffGotApprovedMail($AddVendor->vendor_name));
		    
		    return response()->json([
	              'status_code' => 200,
	              'message'     => 'staff member approved successfully',
	              // 'data'        => $data,
	            ], Response::HTTP_OK);
		// mail should be sent to member that he is approved
        } else{
        	return response()->json([
                    'status_code' => 401, 
                   // 'message'     => 'success',
                    'message'  => 'un authorized access page due to permissions',
                   ], 401);
        }
	}

	public function vendor_assign_stores_staff(VendorAssignStoresStaffApiRequest $request)
	{
		$user = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

        if (in_array('Vendor', $user_roles)) 
        {
        	$staff_id = $request->staff_id;
		    $item = Vendorstaff::findOrFail($staff_id);

		    if ($item->approved != 1) {
	    		return response()->json([
	              'status_code' => 400,
	              'errors' => 'fail, not approved yet',
	              // 'data'  => $data,
	            ], 400);
	    	}

		    $stores_arr  = json_decode($request->stores);
		    $item->stores()->sync($stores_arr);

		    return response()->json([
	              'status_code' => 200,
	              'message'     => 'stores assigned successfully',
	              // 'data'        => $data,
	            ], Response::HTTP_OK);
	    }else{
	    	return response()->json([
	              'status_code' => 400,
	              'errors' => 'Unauthorized access',
	              // 'data'  => $data,
	            ], 400);
	    }
	}

	public function vendor_reject_reasons(Request $request)
	{
		$user = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

        if (in_array('Vendor', $user_roles)) 
        {
        	$vendor = AddVendor::where('userid_id', $user->id)->first();
			        if ($vendor->approved == 1) {
			        return response()->json([
			            'status_code' => 400,
			            'errors' => 'vendor already approved',
			        ], 400);
			      }
        	$reasons = $vendor->rejectedreason;
        	$data = RejectReasonsApiResource::collection($reasons);
        	/*foreach ($reasons as $value) {
        		$value['rej_reason'] = $value['pivot']['reason'];
        		//$value->makeHidden('pivot');
        	}*/

		    return response()->json([
	              'status_code' => 200,
	              'message'     => 'success',
	              'data'        => $data,
	            ], Response::HTTP_OK);
	    }else{
	    	return response()->json([
	              'status_code' => 400,
	              'errors' => 'Unauthorized access',
	              // 'data'  => $data,
	            ], 400);
	    }
	}
}
