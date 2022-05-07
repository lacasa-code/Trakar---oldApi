<?php

namespace App\Http\Controllers\Api\V1\User\Authweb;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Validator;
use App\Http\Resources\Website\User\WebsiteRegisterVendorApiResource;
use App\Http\Resources\Website\User\WebsiteRegisterUserApiResource;
use App\Http\Resources\Website\User\WebsiteLoginUserApiResource;
use Carbon\Carbon;

class SocialMediaLoginApiController extends Controller
{
    public function login_facebook(Request $request)
    {
       // return 'vv';
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
               // $token         = $user->createToken('my_app_token');
                $auth_token  = $user->createToken('my_app_token')->accessToken;
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
                $request['added_by_id'] = 0;
                $role_id = 2;
                
                $user = User::create($request->all());
                $user->roles()->sync($role_id);
                $now = Carbon::now();
                $user->update(['email_verified_at' => $now]);
                // $token         = $user->createToken('my_app_token');
                $auth_token  = $user->createToken('my_app_token')->accessToken;
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
    // Google login
   /* public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }*/

    // Google callback
   /* public function handleGoogleCallback()
    {
        $user = Socialite::driver('google')->user();
        $this->_registerOrLoginUser($user);
        // Return home after login
        return $user;
        return redirect()->route('home');
    }

    // Facebook login
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    // Facebook callback
    public function handleFacebookCallback()
    {
        $user = Socialite::driver('facebook')->user();
        $this->_registerOrLoginUser($user);
        // Return home after login
        return $user;
        return redirect()->route('home');
    }

    // Github login
    public function redirectToGithub()
    {
        return Socialite::driver('github')->redirect();
    }

    // Github callback
    public function handleGithubCallback()
    {
        $user = Socialite::driver('github')->user();
        $this->_registerOrLoginUser($user);
        // Return home after login
        return $user;
        return redirect()->route('home');
    }*/

    protected function _registerOrLoginUser($data)
    {
        $user = User::where('email', '=', $data->email)->first();
        if (!$user) {
            $user = new User();
            $user->name = $data->name;
            $user->email = $data->email;
            $user->provider_id = $data->id;
            $user->avatar = $data->avatar;
            $user->save();
        }

        Auth::login($user);
    }
}
