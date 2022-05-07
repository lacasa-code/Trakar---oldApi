<?php

namespace App\Http\Controllers\Api\V1\User\Authweb;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Website\User\WebsiteLoginUserApiRequest;
use App\Models\User;
use Gate;
use Symfony\Component\HttpFoundation\Response;
// use Illuminate\Support\Facades\Schema;
use Auth;
use App\Http\Resources\Website\User\WebsiteLoginUserApiResource;
use App\Http\Resources\Website\User\WebsiteUserRolesApiResource;
use GuzzleHttp\Client;
use Laravel\Passport\Client as OClient; 
use App\Http\Resources\Website\User\WebsiteLoginVendorApiResource;
use App\Models\AddVendor;
use Hash;

class UserLoginApiController extends Controller
{
    public function check_session(Request $request)
    {
        if (Auth::guard('api')->check() && Auth::user()) {
            $user       = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();

            if (in_array('User', $user_roles)) {
                $roles         = $user->roles;
                $user['roles'] = $roles;
                $user->roles->makeHidden(['added_by_id', 'updated_at', 'deleted_at', 'pivot']);

                $data = new WebsiteLoginUserApiResource($user);

                return response()->json([
                    'status_code' => 200,
                    'message'     => 'valid token',
                    'data' => $user,
                    ], 200);
             // return response()-> json(['message' => 'valid token']);
            } 
               // case logged in user role is Vendor (show only his invoices)
            if(in_array('Vendor', $user_roles)) 
            {
                // $user['token'] = $auth_token;
                $roles         = $user->roles;
                $user['roles'] = $roles;
                $user['vendor_details'] = AddVendor::where('userid_id', $user->id)->first();
                $user->roles->makeHidden(['added_by_id', 'updated_at', 'deleted_at', 'pivot']);

                $data = new WebsiteLoginVendorApiResource($user);

                return response()->json([
                    'status_code' => 200,
                    'message'     => 'valid token',
                    'data' => $user,
                    ], 200);
               // return response()-> json(['message' => 'valid token']);
            }
            if(in_array('Staff', $user_roles) || in_array('Manager', $user_roles)) 
            {
                $roles         = $user->roles;
                $user['roles'] = $roles;
             //   $user['vendor_details'] = AddVendor::where('userid_id', $user->id)->first();
                $user->roles->makeHidden(['added_by_id', 'updated_at', 'deleted_at', 'pivot']);

                $data = new WebsiteLoginVendorApiResource($user);

                return response()->json([
                    'status_code' => 200,
                    'message'     => 'valid token',
                    'data' => $user,
                    ], 200);
               // return response()-> json(['message' => 'valid token']);
            }
        }
        else{
            return 'off';
        }
    }

    public function user_login(WebsiteLoginUserApiRequest $request)
    {
       /* if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) 
        { 
            //$user          = Auth::user();
           // $roles         = $user->roles;
           // $user['roles'] = $roles;
           // $user->roles->makeHidden(['added_by_id', 'updated_at', 'deleted_at', 'pivot']);
            $oClient = OClient::where('password_client', 1)->first();
            return $this->getTokenAndRefreshToken($oClient, request('email'), request('password'));
        } 
        else { 
            return response()->json(['error' => 'Unauthorised'], 401); 
        } */
        $credentials = $request->only('email', 'password');
        // if (Auth::attempt($credentials)) {
        $user = User::where('email', $request->email)->first();
        if ($user) 
        {
            if(Hash::check($request->password, $user->password)) 
            {
             // return 'is '.$user->id;
            // Authentication passed...
            // $user          = Auth::user();
             $auth_token  = $user->createToken('my_app_token')->accessToken;
             $user_roles = $user->roles->pluck('title')->toArray();

             if (in_array('Admin', $user_roles)) 
            {
                return response()->json([
                    'status_code' => 400,
                    'message'     =>  'un authorized access',
                   // 'data' => $user,
                    ], 400);
            } 


                    if ($user->email_verified_at == null) {
                     return response()->json([
                          'status_code' => 400,
                          'errors'     => 'this email is not verified yet',
                        //  'data'        => $data,
                        ], 400);
                    }

                    if (in_array('Vendor', $user_roles)) {
                       /* $exist_v = AddVendor::where('userid_id', $user->id)->first();
                        if ($exist_v->approved != 1) {
                            return response()->json([
                                  'status_code' => 400,
                                  'errors'     => 'this email is not approved yet',
                                //  'data'        => $data,
                                ], 400);
                        }*/
                        $user['token'] = $auth_token;
                        $roles         = $user->roles;
                        $user['roles'] = $roles;
                        $user['vendor_details'] = AddVendor::where('userid_id', $user->id)->first();
                        $user->roles->makeHidden(['added_by_id', 'updated_at', 'deleted_at', 'pivot']);

                        $data = new WebsiteLoginVendorApiResource($user);

                        return response()->json([
                            'status_code' => 200,
                            'message'     => 'succcess',
                            'data' => $data,
                            ], 200);
                    }
                    else{
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
                    }
            } // true credentials 
            else{
                return response()->json([
                    'status_code' => 401,
                    'errors' => 'either email or password is incorrect',
                 ], Response::HTTP_UNAUTHORIZED);
            }
        }  // true credentials 
        else{
             // case wrong credentials 
            return response()->json([
                    'status_code' => 401,
                    'errors' => 'either email or password is incorrect',
                 ], Response::HTTP_UNAUTHORIZED);
        }
    }

    public function getTokenAndRefreshToken(OClient $oClient, $email, $password) 
    { 
        $oClient = OClient::where('password_client', 1)->first();
        $http = new Client;
        $response = $http->request('POST', 'http://localhost/PROJECTS/DEV/TRKAR-master/public/oauth/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $oClient->id,
                'client_secret' => $oClient->secret,
                'username' => $email,
                'password' => $password,
                'scope' => '*',
                // 'data' => $user,
            ],
        ]);
        $result = json_decode((string) $response->getBody(), true);
        return response()->json($result, 200);
    }
    // end login
}
