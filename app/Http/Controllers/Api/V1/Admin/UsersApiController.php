<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\Admin\UserResource;
use App\Models\User;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Auth;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\Admin\MetaResource;
use App\Models\MetaTags;
use Session;
use App\Models\Product;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Resources\Admin\LoginDataResource;
use App\Http\Requests\SearchApisRequest;
use App\Http\Requests\MassDestroyUserRequest;
use App\Models\Role;
use App\Http\Resources\Admin\SpecificUsersApiResource;
use Str;
use Artisan;
use DB;
use App\Models\AddVendor;
use App\Models\Vendorstaff;
use Hash;

class UsersApiController extends Controller
{
  public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }
    // get login meta
    public function getLogin()
    {
      // $token = csrf_token();
      $token =  Session::token();
      return response()->json([
        'data'  => MetaTags::all(),
        'token' => $token, 
       ]);
    }
    // end get login meta


  /*  public function userRefreshToken(Request $request)
    {
        $client = DB::table('oauth_clients')
            ->where('password_client', true)
            ->first();

        $data = [
            'grant_type'    => 'refresh_token',
            'refresh_token' => $request->refresh_token,
            'client_id'     => $client->id,
            'client_secret' => $client->secret,
            'scope'         => ''
        ];
        $request = Request::create('/oauth/token', 'POST', $data);
        $content = json_decode(app()->handle($request)->getContent());

        return response()->json([
            'error' => false,
            'data' => [
                'meta' => [
                    'token' => $content->access_token,
                    'refresh_token' => $content->refresh_token,
                    'type' => 'Bearer'
                ]
            ]
        ], Response::HTTP_OK);
  }
*/

    // post login
    public function login(LoginRequest $request)
    {
        Artisan::call('order:expire');
        $credentials = $request->only('email', 'password');
        
        /*if ($user = Auth::attempt($credentials)) 
        {*/
        $user = User::where('email', $request->email)->first();
        
        if ($user) 
        {
          $user_roles = $user->roles->pluck('title')->toArray();

          // case user 
                if (in_array('User', $user_roles)) 
                {
                     return response()->json([
                          'status_code' => 400,
                        //  'errors'     => 'this email is not verified yet',
                          'errors' => __('site_messages.user_not_use_vendor_app'),
                        //  'data'        => $data,
                        ], 400);
                }
          // case user 
                if (!in_array('Staff', $user_roles) && !in_array('Manager', $user_roles)) 
                {
                  if ($user->email_verified_at == null) {
                     return response()->json([
                          'status_code' => 400,
                          'errors'     => 'this email is not verified yet',
                        //  'data'        => $data,
                        ], 400);
                    } 
                }
                  
            if(Hash::check($request->password, $user->password)) 
            {
                  // Authentication passed...
                  // $user          = Auth::user();
                  $user_roles = $user->roles->pluck('title')->toArray();
                  // case logged in user role is Vendor (show only his invoices)
                  if (in_array('Vendor', $user_roles)) 
                  {
                    $vendor = AddVendor::where('userid_id', $user->id)->first();
                    if ($vendor->declined == 1 || $vendor->rejected == 1) {
                      return response()->json([
                          'status_code' => 400,
                          'errors' => 'can not login rejected / declined',
                      ], 400);
                    }
                    if ($vendor->approved != 1) {
                      return response()->json([
                          'status_code' => 400,
                        //   'errors' => 'you are not approved yet',
                          'errors' => __('site_messages.vendor_access_control_panel'),
                      ], 400);
                    }
                  }

                  if (in_array('Staff', $user_roles) || in_array('Manager', $user_roles)) 
                  {
                    $item = Vendorstaff::where('email', $request->email)->first();
                    if ($item->approved != 1) {
                      return response()->json([
                          'status_code' => 400,
                          'errors' => 'you are not approved yet',
                      ], 400);
                    }
                  }

                /*if ($user->email_verified_at == null) {
                 return response()->json([
                      'status_code' => 400,
                      'errors'     => 'this email is not verified yet',
                    //  'data'        => $data,
                    ], 400);
                }*/

               // $token         = $user->createToken('my_app_token');
                //$auth_token    = $token->plainTextToken;

                $auth_token  = $user->createToken('my_app_token')->accessToken;
               // $refresh_token  = $user->createToken('my_app_token')->refreshToken;
                $user['token'] = $auth_token;
                //$user['refresh_token'] = $refresh_token;
                $user['roles'] = $user->roles->makeHidden(['created_at', 'updated_at', 'deleted_at', 'pivot']);

                foreach ($user->roles as $key => $role) {
                   $role['permissions'] = $role->permissions->makeHidden(['created_at', 'updated_at', 'deleted_at', 'pivot']);
                 }
                return response()->json([
                  'status_code' => 200,
                  'message' => 'success',
                    'data' => $user,
                    ], 200);
            }
            else{
              return response()->json([
                     'errors' => __('site_messages.either_email_or_password_is_incorrect'),
                 ], Response::HTTP_UNAUTHORIZED);
            }
      }
      else{
             // case wrong credentials 
            return response()->json([
                     'errors' => __('site_messages.either_email_or_password_is_incorrect'),
                 ], Response::HTTP_UNAUTHORIZED);
        }
    }
    // end login

    // start logout
    public function logout()
    {
      $accessToken = Auth::user()->token();
        DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $accessToken->id)
            ->update([
                'revoked' => true
            ]);

        $accessToken->revoke();
        return response()->json(['message' => 'successfully logged out'], 200);
       /* Auth::user()->tokens()->delete();
        Session::flush();
        return response()->json(['message' => 'successfully logged out'], Response::HTTP_OK);*/
    } 
    // end logout

    public function index(Request $request)
    {
      $lang = $this->getLang();
      // $PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;

      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;

      // logged in user
      $user         = Auth::user();
      $auth_user_id = Auth::user()->id;
      $user_roles   = $user->roles->pluck('title')->toArray();

      // case logged in user role is Admin 
      if (in_array('Admin', $user_roles)) 
      {
        abort_if(Gate::denies('user_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
          $get_users = User::with(['roles'])->where('id', '!=', $auth_user_id)
                           // ->whereDoes('declined', '!=', 1)
                            ->whereDoesntHave('vendor', function($q){
                                    $q->where('declined', 1);
                            })
                            //->where('lang', $lang)
                            ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)->get();
          $data = UserResource::collection($get_users);
          return response()->json([
              'data'  => $data,
              'total' => User::where('id', '!=', $auth_user_id)
                            ->whereDoesntHave('vendor', function($q){
                                    $q->where('declined', 1);
                            })->count(),
          ]);
      } // end admin case
       // case logged in user role is Vendor
      elseif (in_array('Vendor', $user_roles)) {
       // abort_if(Gate::denies('user_access_by_vendor'), Response::HTTP_FORBIDDEN, '403 Forbidden');
          // $vendor   = AddVendor::where('userid_id', Auth::user()->id)->first();
            $get_users = User::with(['roles'])->where('id', '!=', 1)
                            ->where('id', '!=', $auth_user_id)
                           // ->where('lang', $lang)
                            ->where('added_by_id', $auth_user_id)
                            ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)->get();

           //   $staff = Vendorstaff::where('vendor_id', $vendor->id)->get();
              $data = UserResource::collection($get_users);
            return response()->json([
              'data'  => $data,
              'total' => User::where('id', '!=', 1)->where('id', '!=', $auth_user_id)
                              ->where('added_by_id', $auth_user_id)->count()
          ]);
      } // end case vendor
       elseif (in_array('Manager', $user_roles)) 
       {
        $exist_staff   = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
        $exist_user    = User::where('id', $vendor->userid_id)->first(); 
        //$staff_stores = $exist_staff->stores->pluck('id')->toArray();
       // return $staff_stores;
         $get_users = User::with(['roles'])->where('id', '!=', 1)
                            ->where('id', '!=', $exist_user->id)
                            ->where('id', '!=', $auth_user_id)
                            ->where('added_by_id', $exist_user->id)
                            ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)->get();

           //   $staff = Vendorstaff::where('vendor_id', $vendor->id)->get();
              $data = UserResource::collection($get_users);
            return response()->json([
              'data'  => $data,
              'total' => User::where('id', '!=', 1)->where('id', '!=', $auth_user_id)
                              ->where('added_by_id', $auth_user_id)->count()
          ]);
      }
      else{
        return response()->json([
                'message'  => 'un authorized access page due to permissions',
               ], 401);
      } // end else 
    }

    public function store(StoreUserRequest $request)
    {
      $lang = $this->getLang();
      $request['lang'] = $lang;
        $arr  = json_decode($request->roles);
  
        $user         = Auth::user();
        $auth_user_id = Auth::user()->id;
        $user_roles   = $user->roles->pluck('title')->toArray();

         // case logged in user role is Admin 
      if (in_array('Admin', $user_roles)) {
        abort_if(Gate::denies('user_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        // return Gate::allows('user_create');
          $request['added_by_id'] = Auth::user()->id;
          $user = User::create($request->all());
          $user->roles()->sync($arr);

          return (new UserResource($user))
              ->response()
              ->setStatusCode(Response::HTTP_CREATED);
      } // end admin case
       // case logged in user role is Vendor
      elseif (in_array('Vendor', $user_roles)) {
        abort_if(Gate::denies('user_create_by_vendor'), Response::HTTP_FORBIDDEN, '403 Forbidden');
          // $vendor   = AddVendor::where('userid_id', Auth::user()->id)->first();
          $request['added_by_id'] = Auth::user()->id;

          $role_id = $request->roles;
          $role_title = Role::findOrFail($role_id);
          if ($role_title->title != 'Staff' && $role_title->title != 'Manager') {
           return response()->json([
                'status_code' => 400,
                'errors'  => 'invalid role selected',
               ], 400);
          }

          $user = User::create($request->all());
          $user->roles()->sync($arr);

          return (new UserResource($user))
              ->response()
              ->setStatusCode(Response::HTTP_CREATED);
      } // end case vendor
      else{
        return response()->json([
                'message'  => 'un authorized access page due to permissions',
               ], 401);
      } // end else 
    }

    public function show(User $user)
    {
      $lang = $this->getLang();
     // $request['lang'] = $lang;
        //abort_if(Gate::denies('user_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $auth_user    = Auth::user();
        $auth_user_id = Auth::user()->id;
        $user_roles   = $auth_user->roles->pluck('title')->toArray();

        // case logged in user role is Admin 
      if (in_array('Admin', $user_roles)) {
        abort_if(Gate::denies('user_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
            //return new UserResource($user->load(['roles']));
            return new SpecificUsersApiResource($user);
        } // end admin case
       // case logged in user role is Vendor
      elseif (in_array('Vendor', $user_roles)) {
        abort_if(Gate::denies('user_show_by_vendor'), Response::HTTP_FORBIDDEN, '403 Forbidden');
          // $vendor   = AddVendor::where('userid_id', Auth::user()->id)->first();
         // test this user viewable to admin
        if ($user->added_by_id != $auth_user_id) {
         return response()->json(['message' => 'This user does not belong to you to view'], 401);
        }else{
          // return new UserResource($user->load(['roles']));
           return new SpecificUsersApiResource($user);
        }
      } // end case manager
      elseif (in_array('Manager', $user_roles)) {
        abort_if(Gate::denies('user_show_by_vendor'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $exist_staff   = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
        $staff_stores  = $exist_staff->stores->pluck('id')->toArray();

        if ($user->added_by_id != $vendor->userid_id) {
         return response()->json(['message' => 'This user does not belong to you to view'], 401);
        }else{
          // return new UserResource($user->load(['roles']));
           return new SpecificUsersApiResource($user);
        }
      } // end case manager
      else{
        return response()->json([
                'message'  => 'un authorized access page due to permissions',
               ], 401);
      } // end else 
    }

    public function update(UpdateUserRequest $request, User $user)
    {
      $lang = $this->getLang();
      $request['lang'] = $lang;
      $arr  = json_decode($request->roles);
    
        $auth_user    = Auth::user();
        $auth_user_id = Auth::user()->id;
        $user_roles   = $auth_user->roles->pluck('title')->toArray();

        // case logged in user role is Admin 
      if (in_array('Admin', $user_roles)) {
        abort_if(Gate::denies('user_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
       // Gate::allows('user_edit');
        // test this user editable to admin
       //  return $auth_user;
        if ($user->added_by_id != $auth_user_id) {
         return response()->json(['errors' => 'This user does not belong to you to edit'], 401);
        }
          $request['added_by_id'] = Auth::user()->id;
          $user->update($request->all());
          $user->roles()->sync($arr);

          return (new UserResource($user))
              ->response()
              ->setStatusCode(Response::HTTP_ACCEPTED);
        } // end admin case
       // case logged in user role is Vendor
      elseif (in_array('Vendor', $user_roles)) {
       abort_if(Gate::denies('user_edit_by_vendor'), Response::HTTP_FORBIDDEN, '403 Forbidden');
          // $vendor   = AddVendor::where('userid_id', Auth::user()->id)->first();
         // test this user editable to admin
        if ($user->added_by_id != $auth_user_id) {
         return response()->json([
          'errors' => __('site_messages.This_user_does_not_belong_to_you_to_edit'),
          ], 401);
        }
            $request['added_by_id'] = Auth::user()->id;
            $old_email = $user->email;
            $user->update($request->all());
            $user->roles()->sync($request->roles);
            // update vendor if found 
            if ($user->vendor != null) {
              $exist_vendor = AddVendor::where('userid_id', $user->id)->first();
              $exist_vendor->update(['email' => $user->email]);
            }

            $staff = Vendorstaff::where('email', $old_email)->first();
              if ($staff != null) 
              {
                  $staff->update([
                    'email'    => $user->email, 
                    'role_id'  => $request->roles,
                    'role_name' => Role::where('id', $request->roles)->first()->title,
                  ]);
                    $stores_arr  = json_decode($request->stores);
                    $staff->stores()->sync($stores_arr);
                   return response()->json([
                  'status_code'   => 200,
                  'message'       => 'success',
                  'data'          => $user,
                ], 200);
              }
            // update vendor if found
           // $arr = $request->roles;
            

            return (new UserResource($user))
                ->response()
                ->setStatusCode(Response::HTTP_ACCEPTED);
      } // end case vendor
      elseif (in_array('Manager', $user_roles)) {
       abort_if(Gate::denies('user_edit_by_vendor'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        
        $exist_staff   = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
       // $userId        = User::where('id', $vendor->userid_id)->first();
        $vendor_id     = $vendor->id;
        $staff_stores  = $exist_staff->stores->pluck('id')->toArray();

        if ($user->added_by_id != $vendor->userid_id) {
         return response()->json([
          'errors' => __('site_messages.This_user_does_not_belong_to_you_to_edit'),
          ], 401);
        }
            $request['added_by_id'] = $vendor->userid_id;
            $old_email = $user->email;
            $user->update($request->all());
            $user->roles()->sync($request->roles);
            // update vendor if found 
            if ($user->vendor != null) {
              $exist_vendor = AddVendor::where('userid_id', $user->id)->first();
              $exist_vendor->update(['email' => $user->email]);
            }

            $staff = Vendorstaff::where('email', $old_email)->first();
              if ($staff != null) 
              {
                  $staff->update([
                    'email'    => $user->email, 
                    'role_id'  => $request->roles,
                    'role_name' => Role::where('id', $request->roles)->first()->title,
                  ]);
                    $stores_arr  = json_decode($request->stores);
                    $staff->stores()->sync($stores_arr);
                   return response()->json([
                  'status_code'   => 200,
                  'message'       => 'success',
                  'data'          => $user,
                ], 200);
              }

            return (new UserResource($user))
                ->response()
                ->setStatusCode(Response::HTTP_ACCEPTED);
      } // end case vendor
      else{
        return response()->json([
                'message'  => 'un authorized access page due to permissions',
               ], 401);
      } // end else 
    }

    public function destroy(User $user)
    {
        $auth_user    = Auth::user();
        $auth_user_id = Auth::user()->id;
        $user_roles   = $auth_user->roles->pluck('title')->toArray();

        // case logged in user role is Admin 
      if (in_array('Admin', $user_roles)) {
        abort_if(Gate::denies('user_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // test this user deletable to admin
       /*  if ($user->added_by_id != $auth_user_id && $user->added_by_id != 0) {
           return response()->json([
            'errors' => __('site_messages.This_user_does_not_belong_to_you_to_delete'),
            ], 401);
          }   */
         
            if ( ($user->vendor != null) || (count($user->orders) > 0) || (count($user->tickets) > 0) || (count($user->productreviews) > 0) || (count($user->comments) > 0) ) {
           return response()->json([
                          'errors' => __('site_messages.This_user_is_not_empty_to_be_deleted'),
                          ], Response::HTTP_UNAUTHORIZED);
          }
          else{
              $staff = Vendorstaff::where('email', $user->email)->first();
              if ($staff != null) {
                  $user->delete();
                  $stores = $staff->stores->pluck('id')->toArray();
                  $staff->stores()->detach();
                  $staff->delete();
                   return response()->json([
                  'status_code'   => 200,
                  'message'       => 'success',
                  'data'          => null
                ], 200);
              }else{
                  $user->delete();
                   return response()->json([
                  'status_code'   => 200,
                  'message'       => 'success',
                  'data'          => null
                ], 200);
              }
            //return response(null, Response::HTTP_NO_CONTENT);
          }
        } // end admin case
       // case logged in user role is Vendor
      elseif (in_array('Vendor', $user_roles)) {
       // abort_if(Gate::denies('user_delete_by_vendor'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // test this user deletable to vendor
          if ($user->added_by_id != $auth_user_id) {
           return response()->json([
            'status_code' => 400,
            'errors' => __('site_messages.This_user_does_not_belong_to_you_to_delete'),
          ], 401);
          }

          // return $user->orders.$user->tickets;
          
              if ( ($user->vendor != null) || (count($user->orders) > 0) || (count($user->tickets) > 0) || (count($user->productreviews) > 0) || (count($user->comments) > 0) ) {
             return response()->json([
              'status_code' => 400,
              'errors' => __('site_messages.This_user_is_not_empty_to_be_deleted'),
            ], Response::HTTP_UNAUTHORIZED);
            }
          else{
              
              $staff = Vendorstaff::where('email', $user->email)->first();
              if ($staff != null) {
                  $user->delete();
                  $stores = $staff->stores->pluck('id')->toArray();
                  $staff->stores()->detach();
                  $staff->delete();
                   return response()->json([
                  'status_code'   => 200,
                  'message'       => 'success',
                  'data'          => null
                ], 200);
              }else{
                  $user->delete();
                   return response()->json([
                  'status_code'   => 200,
                  'message'       => 'success',
                  'data'          => null
                ], 200);
              }
          // return response(null, Response::HTTP_NO_CONTENT);
          }
      } // end case vendor
      // case logged in user role is manager
      elseif (in_array('Manager', $user_roles)) {
       // abort_if(Gate::denies('user_delete_by_vendor'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $exist_staff   = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
        $exist_user    = User::where('id', $vendor->userid_id)->first(); 

        // test this user deletable to vendor
          if ($user->added_by_id != $vendor->userid_id) {
           return response()->json([
            'status_code' => 400,
            'errors' => __('site_messages.This_user_does_not_belong_to_you_to_delete'),
          ], 401);
          }

              if ( ($user->vendor != null) || (count($user->orders) > 0) || (count($user->tickets) > 0) || (count($user->productreviews) > 0) || (count($user->comments) > 0) ) {
             return response()->json([
              'status_code' => 400,
              'errors' => __('site_messages.This_user_is_not_empty_to_be_deleted'),
            ], Response::HTTP_UNAUTHORIZED);
            }
          else{
              $staff = Vendorstaff::where('email', $user->email)->first();
              if ($staff != null) {
                  $user->delete();
                  $stores = $staff->stores->pluck('id')->toArray();
                  $staff->stores()->detach();
                  $staff->delete();
                   return response()->json([
                  'status_code'   => 200,
                  'message'       => 'success',
                  'data'          => null
                ], 200);
              }else{
                  $user->delete();
                   return response()->json([
                  'status_code'   => 200,
                  'message'       => 'success',
                  'data'          => null
                ], 200);
              }
          // return response(null, Response::HTTP_NO_CONTENT);
          }
      } // end case manager
      else{
        return response()->json([
                'message'  => 'un authorized access page due to permissions',
               ], 401);
      } // end else 
    }

     // start search users with name
     public function search_with_name(SearchApisRequest $request)
     {
      $lang = $this->getLang();
     // $request['lang'] = $lang;
     // $PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
        $search_index = $request->search_index;
        $auth_user    = Auth::user();
        $auth_user_id = Auth::user()->id;
        $user_roles   = $auth_user->roles->pluck('title')->toArray();

        if (in_array('Admin', $user_roles)) 
        {
            $get_users = User::with(['roles'])->where(function ($q) use ($search_index) {
                  $q->where('name', 'like', "%{$search_index}%")
                    ->orWhere('email', 'like', "%{$search_index}%");
                  })->orWhereHas('roles', function($q) use ($search_index){
                                  $q->where('title', 'like', "%{$search_index}%");
                  })->where('id', '!=', $auth_user_id)
                    ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                    ->orderBy($ordered_by, $sort_type)->get();
          $data = UserResource::collection($get_users);

          $total = User::with(['roles'])->where(function ($q) use ($search_index) {
                  $q->where('name', 'like', "%{$search_index}%")
                    ->orWhere('email', 'like', "%{$search_index}%");
                  })->orWhereHas('roles', function($q) use ($search_index){
                                  $q->where('title', 'like', "%{$search_index}%");
                  })->where('id', '!=', $auth_user_id)->count();

          return response()->json([
              'data'  => $data,
              'total' => $total,
          ], 200);
        } // end admin case
       // case logged in user role is Vendor
        elseif (in_array('Vendor', $user_roles)) 
        {
          $users = User::with(['roles'])->where('name', 'like', "%{$search_index}%")
                              ->orWhere('email', 'like', "%{$search_index}%")
                              ->orWhereHas('roles', function($q) use ($search_index){
                                  $q->where('title', 'like', "%{$search_index}%");
                              })
                              ->where('id', '!=', $auth_user_id)
                    //->where('added_by_id', $auth_user_id)
                    ->skip(($page-1)*$PAGINATION_COUNT)
                    ->take($PAGINATION_COUNT)
                    ->orderBy($ordered_by, $sort_type)
                    ->get();
          $get_users = $users->where('added_by_id', $auth_user_id);
          $data  = UserResource::collection($get_users);

          $total_get = User::with(['roles'])->where('name', 'like', "%{$search_index}%")
                              ->orWhere('email', 'like', "%{$search_index}%")
                              ->orWhereHas('roles', function($q) use ($search_index){
                                  $q->where('title', 'like', "%{$search_index}%");
                              })
                              ->where('id', '!=', $auth_user_id)
                              ->get();
          $total = $total_get->where('added_by_id', $auth_user_id)->count();

          return response()->json([
              'data'  => $data,
              'total' => $total,
          ], 200);
        } // end case manager
         elseif (in_array('Manager', $user_roles)) 
        {
        $exist_staff   = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
        $exist_user    = User::where('id', $vendor->userid_id)->first(); 

          $users = User::with(['roles'])->where('name', 'like', "%{$search_index}%")
                              ->orWhere('email', 'like', "%{$search_index}%")
                              ->orWhereHas('roles', function($q) use ($search_index){
                                  $q->where('title', 'like', "%{$search_index}%");
                              })
                              ->where('id', '!=', $auth_user_id)
                    //->where('added_by_id', $auth_user_id)
                    ->skip(($page-1)*$PAGINATION_COUNT)
                    ->take($PAGINATION_COUNT)
                    ->orderBy($ordered_by, $sort_type)
                    ->get();
          $get_users = $users->where('added_by_id', $vendor->userid_id);
          $data  = UserResource::collection($get_users);

          $total_get = User::with(['roles'])->where('name', 'like', "%{$search_index}%")
                              ->orWhere('email', 'like', "%{$search_index}%")
                              ->orWhereHas('roles', function($q) use ($search_index){
                                  $q->where('title', 'like', "%{$search_index}%");
                              })
                              ->where('id', '!=', $auth_user_id)
                              ->where('added_by_id', $vendor->userid_id)
                              ->get();
          $total = $total_get->where('added_by_id', $vendor->userid_id)->count();

          return response()->json([
              'data'  => $data,
              'total' => $total,
          ], 200);
        } // end case vendor
        else{
          return response()->json([
                  'message'  => 'un authorized access page due to permissions',
                 ], 401);
        } // end else 
     }
    // end search users with name

     // start mass delete users
     public function mass_delete(MassDestroyUserRequest $request)
     {
        abort_if(Gate::denies('user_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ids = json_decode($request->ids);
        $auth_user    = Auth::user();
        $auth_user_id = Auth::user()->id;
        $user_roles   = $auth_user->roles->pluck('title')->toArray();

        // case logged in user role is Admin 
        if (in_array('Admin', $user_roles)) 
        {
          abort_if(Gate::denies('user_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

          foreach ($ids as $id) {
               $user = User::findOrFail($id);
              if ($user->added_by_id != $auth_user_id && $user->added_by_id != 0) {
                return response()->json([
                'errors' => 'This user does not belong to you ('. $user->name. ' )'], 401);
              }
             /* if ( ($user->vendor !== null && count($user->vendor) > 0) || ($user->orders !== null && count($user->orders) > 0) || ($user->auditLogs !== null && count($user->auditLogs) > 0) || ($user->tickets !==null && count($user->tickets) > 0 ) ) {*/
              if ( ($user->vendor != null) || (count($user->orders) > 0) || (count($user->tickets) > 0) || (count($user->productreviews) > 0) || (count($user->comments) > 0) ) {

                 return response()->json([
                  'errors' => 'this item is not empty te be deleted ('. $user->name. ' )',
                  ], Response::HTTP_UNAUTHORIZED);
              }
          } // end foreach
          if(User::whereIn('id', $ids)->count() <= 0){
              return response()->json([
                'status_code'   => 400,
                'message'       => 'fail',
                'errors'          => 'invalid items selected'
              ], 400);
          }
          User::whereIn('id', $ids)->delete();
          return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => null
          ], 200);
          // return response(null, Response::HTTP_NO_CONTENT);
        } // end admin case
       // case logged in user role is Vendor
      elseif (in_array('Vendor', $user_roles)) 
      {
        abort_if(Gate::denies('user_delete_by_vendor'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        foreach ($ids as $id) {
               $user = User::findOrFail($id);
              if ($user->added_by_id != $auth_user_id) {
                return response()->json([
                'errors' => 'This user does not belong to you ('. $user->name. ' )'], 401);
              }
        }

              foreach ($ids as $id) 
              {
                $user = User::findOrFail($id);
                if ( ($user->vendor != null) || (count($user->orders) > 0) || (count($user->tickets) > 0) || (count($user->productreviews) > 0) || (count($user->comments) > 0) ) {
                   return response()->json([
                          'errors' => __('site_messages.This_user_is_not_empty_to_be_deleted').$user->name,
                          ], Response::HTTP_UNAUTHORIZED);
                }
              }

               User::whereIn('id', $ids)->delete();
               return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => null
          ], 200);
      } // end case vendor
       // case logged in user role is Vendor
      elseif (in_array('Manager', $user_roles)) 
      {
        abort_if(Gate::denies('user_delete_by_vendor'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $exist_staff   = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
        $exist_user    = User::where('id', $vendor->userid_id)->first(); 

        foreach ($ids as $id) {
               $user = User::findOrFail($id);
              if ($user->added_by_id != $vendor->userid_id) {
                return response()->json([
                'errors' => 'This user does not belong to you ('. $user->name. ' )'], 401);
              }
        }
             
              foreach ($ids as $id) 
              {
                $user = User::findOrFail($id);
                if ( ($user->vendor != null) || (count($user->orders) > 0) || (count($user->tickets) > 0) || (count($user->productreviews) > 0) || (count($user->comments) > 0) ) {
                   return response()->json([
                          'errors' => __('site_messages.This_user_is_not_empty_to_be_deleted').$user->name,
                          ], Response::HTTP_UNAUTHORIZED);
                }
              }

              User::whereIn('id', $ids)->delete();
              return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => null
          ], 200);
      } // end case manager
      else{
        return response()->json([
                'message'  => 'un authorized access page due to permissions',
               ], 401);
      } // end else 
    }
     // end mass delete users
}
