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
use Illuminate\Support\Facades\Mail;
use App\Mail\SendRegisterMail;
use App\Models\Store;

class VendorRegisterApiController extends Controller
{
    use MediaUploadingTrait;

  public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }

  public function vendor_saved_center(Request $request)
    {
          $user = Auth::user();
          $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if (in_array('Vendor', $user_roles)) 
          {
              $addVendor = AddVendor::where('userid_id', $user->id)->first();
             // $addVendor = User::where('id', $user->id)->first();
              $exist_center = Store::where('vendor_id', $addVendor->id)->where('head_center', 1)->first();
              $addVendor['user_details'] = $user;
              // $data = new WebsiteRegisterVendorApiResource($user);
              return response()->json([
              'status_code' => 200,
              'message'     => 'succcess',
              'data'        => $exist_center,
            ], Response::HTTP_OK);
          }else{
            return response()->json([
              'status_code' => 400,
              'errors'     => 'not a vendor',
            //  'data'        => $data,
            ], 400);
          }
    }

    public function vendor_saved_docs(Request $request)
    {
          $user = Auth::user();
          $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if (in_array('Vendor', $user_roles)) 
          {
              $addVendor = AddVendor::where('userid_id', $user->id)->first();
             // $addVendor = User::where('id', $user->id)->first();
              $addVendor['user_details']  = $user;
              $addVendor['ven_type'] = $addVendor->type == 0 ? 'type not selected yet' : $addVendor->vendor_type->type;
              $exist_store = Store::where('vendor_id', $addVendor->id)
                                  ->where('head_center', 1)->first();

              if ($exist_store) {
                $addVendor['compete_store'] = 1;
                $addVendor['store_details'] = $exist_store;
              }else{
                $addVendor['compete_store'] = 0;
                $addVendor['store_details'] = $exist_store;
              }
              if ($addVendor->commercial_no != null && $addVendor->tax_card_no != null && $addVendor->bank_account != null && $addVendor->taxCardDocs != null && $addVendor->bankDocs != null && $addVendor->commercialDocs != null ) 
              {
                  $addVendor['compete_docs'] = 1;
              }else{
                  $addVendor['compete_docs'] = 0;
              }
  
              // $data = new WebsiteRegisterVendorApiResource($user);
              return response()->json([
              'status_code' => 200,
              'message'     => 'succcess',
              'data'        => $addVendor,
            ], Response::HTTP_OK);
          }else{
            return response()->json([
              'status_code' => 400,
              'errors'     => 'not a vendor',
            //  'data'        => $data,
            ], 400);
          }
    }

    public function vendor_register(WebsiteRegisterUserApiRequest $request)
    {
          $lang = $this->getLang();
          $request['lang'] = $lang;
          $request['added_by_id'] = 0;
          $role_id = 3;

              $user = User::create($request->all());
              $user->roles()->sync($role_id);
              
              // create available data as avendor 
              $request['commercial_no']  = null;
              $request['tax_card_no']    = null;
              $request['commercial_doc'] = null;
              $request['tax_card_doc']   = null;
              $request['bank_account']   = null;
              $request['vendor_name']    = $user->name;
              $request['userid_id']      = $user->id;
              $request['type']           = 0;
              $request['lang']           = $lang;
            // create available data as avendor 

              $addVendor = AddVendor::create($request->all());
              $auth_token  = $user->createToken('my_app_token')->accessToken;
              $user['token'] = $auth_token;
              $code = base64_decode($user->email);
              $user['vendor_details'] = $addVendor;
              $data = new WebsiteRegisterVendorApiResource($user);
             Mail::to($user->email)->send(new SendRegisterMail($user->name, $user->id, $code));
            
            return response()->json([
              'status_code' => 200,
            //  'message'     => 'succcess, complete your profile',
              'message' => __('site_messages.vendor_registered_successfully'),
              'data'        => $data,
            ], Response::HTTP_OK);
    }
}
