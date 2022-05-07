<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\EditProfileApiRequest;
use App\Http\Requests\ChangePasswordApiRequest;
use Auth;
use Session;
use App\Models\Product;
use App\Models\AddVendor;

class ProfileApiController extends Controller
{
	// start edit profile 
    public function edit_profile(EditProfileApiRequest $request)
    {
    	$user = auth()->user();
        $user->update($request->validated());
        $user_roles   = $user->roles->pluck('title')->toArray();
        if (in_array('Vendor', $user_roles)) 
        {
            $vendor     = AddVendor::where('userid_id', Auth::user()->id)->first();
            $vendor->update([
                'vendor_name' => $user->name,
                'email'       => $user->email,
              ]);
        }
        // Auth::user()->tokens()->delete();
        // Session::flush();

        return response()->json([
        	'message' => 'profile updated successfully',
        ], Response::HTTP_ACCEPTED);
    }
    // end edit profile 

    // start change password 
    public function change_password(ChangePasswordApiRequest $request)
    {
    	$user = auth()->user(); 
    	$user->update($request->validated());
        // Auth::user()->tokens()->delete();
        // Session::flush();
    	return response()->json([
        	'message' => 'password updated successfully',
        ], Response::HTTP_ACCEPTED);
    }
    // end change password 
}
