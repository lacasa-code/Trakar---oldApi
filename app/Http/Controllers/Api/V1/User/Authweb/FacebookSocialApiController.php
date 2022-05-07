<?php

namespace App\Http\Controllers\Api\V1\User\Authweb;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FacebookSocialApiController extends Controller
{
     public function login_facebook(Request $request)
    {
    	return 'vv';
        $validator = Validator::make($request->all(), [
          'email'       => 'required|email',
          'facebook_id' => 'required',
          'name'        => 'nullable',
          'facebook_avatar' => 'nullable',
        ]);

        if ($validator->fails()) {
               return response()->json([
               	'status_code' => 400,
               	'errors'      => $validator->errors(),
               ], 400);
           }

        if ($request->email != '') {
            $user = User::where('facebook_id', $request->facebook_id)
                        ->orWhere('email', $request->email)
                        ->first();
       
	        if ($user) 
	        {
	            $token         = $user->createToken('my_app_token');
	            $auth_token    = $token->plainTextToken;
	            $user['token'] = $auth_token;
	            $roles         = $user->roles;
	            $user['roles'] = $roles;
	            $user->roles->makeHidden(['added_by_id', 'updated_at', 'deleted_at', 'pivot']);
	            $data = new WebsiteLoginUserApiResource($user);
	            return response()->json([
	            	'status_code' => 200,
	                'message'     => 'succcess',
	                'data' => $data,
	                ], 200);
	        } else{ // end if user found
	        	$user = User::create($request->all());
	        	$token         = $user->createToken('my_app_token');
	            $auth_token    = $token->plainTextToken;
	            $user['token'] = $auth_token;
	            $roles         = $user->roles;
	            $user['roles'] = $roles;
	            $user->roles->makeHidden(['added_by_id', 'updated_at', 'deleted_at', 'pivot']);
	            $data = new WebsiteLoginUserApiResource($user);
	            return response()->json([
	            	'status_code' => 200,
	                'message'     => 'succcess',
	                'data' => $data,
	                ], 200);
	        } // end else user not found first create
		}  // end email sent with request
    }
}
