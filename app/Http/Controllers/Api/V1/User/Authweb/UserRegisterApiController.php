<?php

namespace App\Http\Controllers\Api\V1\User\Authweb;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Website\User\WebsiteRegisterUserApiRequest;
use App\Http\Requests\Website\User\AttachDocumentApiRequest;
use App\Models\User;
use Gate;
use Symfony\Component\HttpFoundation\Response;
// use Illuminate\Support\Facades\Schema;
use Auth;
use App\Http\Resources\Website\User\WebsiteRegisterUserApiResource;
use App\Http\Resources\Website\User\WebsiteUserRolesApiResource;
use DB;
use App\Models\AddVendor;
use Illuminate\Validation\Rule;
use Validator;
use App\Http\Resources\Website\User\WebsiteRegisterVendorApiResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Models\Role;
use App\Models\Vendorstaff;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendRegisterMail;
use App\Http\Requests\Website\Verification\VerifyEmailApiRequest;
use App\Http\Requests\Website\Verification\ResendVerifyEmailApiRequest;
use App\Mail\SendVendorStaffJoinedMail;

class UserRegisterApiController extends Controller
{
  use MediaUploadingTrait;

  public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }

  public function register_roles_list()
  {
        $arr = [2, 3];
        $data = Role::whereIn('id', $arr)->get();
        return response()->json(['data' => $data], Response::HTTP_OK);
  }

    public function user_register(WebsiteRegisterUserApiRequest $request)
    {
          $lang = $this->getLang();
          $request['lang'] = $lang;
          $request['added_by_id'] = 0;
          $exist_email = Vendorstaff::where('email', $request->email)->first();
          if ($exist_email) 
          {
            $AddVendor   = AddVendor::where('id', $exist_email->vendor_id)->first();
            $exist_user  = User::where('id', $AddVendor->userid_id)->first(); 
            $role_id     = $exist_email->role_id;
          }else{
            $role_id = 2;
          }
        
            if ($role_id != 2) {
              $latest_user = User::withTrashed()->where('added_by_id', $exist_user->id)
                                    ->orderBy('created_at', 'desc')->limit(1)->first();

                // return $latest_store->id;
                if (!$latest_user) {
                  // $latest_exist = Store::withTrashed()->latest()
                  $serial_id = 'user_'.$request->name.'_ven_'.$AddVendor->vendor_name.'_IDNO_'.$AddVendor->id.'_001';
                }else{
                  $serial_id = 'user_'.$request->name.'_ven_'.$AddVendor->vendor_name.'_IDNO_'.$AddVendor->id.'_00'.($latest_user->id + 1);
                }
                  $request['serial_id'] = $serial_id;
                 
                  $user = User::create($request->all());
                  $user->update(['added_by_id' => $exist_user->id]);
                  $user->roles()->sync($role_id);
                  $auth_token  = $user->createToken('my_app_token')->accessToken;
                  $user['token'] = $auth_token;
                  $code = base64_decode($user->email);
                  $data = new WebsiteRegisterUserApiResource($user);
                  
                  // mail should be sent to vendor that member registered
                  $vendor_email = $exist_email->vendor_email;
                  Mail::to($vendor_email)->send(new SendVendorStaffJoinedMail($user->name));
                  return response()->json([
                  'status_code' => 200,
                  'message'     => 'succcess, Staff Member Joined',
                  'data'        => $data,
                ], Response::HTTP_OK);
            }
            if ($role_id == 2) 
            {
                  $request['serial_id'] = 'normal user';
                  $user = User::create($request->all());
                  $user->roles()->sync($role_id);

                  $auth_token  = $user->createToken('my_app_token')->accessToken;
                  $user['token'] = $auth_token;
                  $code = base64_decode($user->email);
                  $data = new WebsiteRegisterUserApiResource($user);
                  Mail::to($user->email)->send(new SendRegisterMail($user->name, $user->id, $code));
                  return response()->json([
                  'status_code' => 200,
                 // 'message'     => 'succcess, verify your email',
                  'message' => __('site_messages.verify_account'),
                  'data'        => $data,
                ], Response::HTTP_OK);
            }
    }

    public function verify_email(VerifyEmailApiRequest $request)
    {
      $user = User::findOrFail($request->id);
      if ($user->email_verified_at != null) {
         return response()->json([
              'status_code' => 400,
              'errors'     => 'fail, already verified',
            //  'data'        => $data,
            ], 400);
      }else{
        $user->update(['email_verified_at' => now()]);
        return response()->json([
              'status_code' => 200,
              'message'     => 'email verified succcessfully',
             // 'data'        => $data,
            ], Response::HTTP_OK);
      }
    }

    public function resend_verify_email(ResendVerifyEmailApiRequest $request)
    {
     
      $user = User::findOrFail($request->id);
      $id   = $request->id;

        if ($user->email_verified_at != null) {
           return response()->json([
                'status_code' => 400,
                'errors'     => 'fail, already verified',
              //  'data'        => $data,
              ], 400);
        }else{
          Mail::to($user->email)->send(new SendRegisterMail($user->name, $id));
          return response()->json([
                'status_code' => 200,
                'message'     => 'verification email sent succcessfully',
               // 'data'        => $data,
              ], Response::HTTP_OK);
        }
    }
}
