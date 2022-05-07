<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Resources\Admin\RoleResource;
use App\Models\Role;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\MassDestroyRoleRequest;
use App\Models\Permission;
use Auth;
use App\Http\Resources\Admin\SpecificRoleApiResource;
use Illuminate\Validation\Rule;
use Validator;
use App\Models\Vendorstaff;

class RolesApiController extends Controller
{
    public function index(Request $request)
    {
      abort_if(Gate::denies('role_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
     
      $auth_user    = Auth::user();
      $auth_user_id = Auth::user()->id;
      $user_roles   = $auth_user->roles->pluck('title')->toArray();

      // case logged in user role is Admin 
      if (in_array('Admin', $user_roles)) {
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
        'data'  => new RoleResource(Role::with(['permissions'])
                                    // ->where('added_by_id', $auth_user_id)
                                    ->skip(($page-1)*$PAGINATION_COUNT)
                                    ->take($PAGINATION_COUNT)
                                    ->orderBy($ordered_by, $sort_type)->get()),
        'total' => Role::count()
        ], 200);
      } // end admin case
       // case logged in user role is Vendor
      elseif (in_array('Vendor', $user_roles)) {
          // $vendor   = AddVendor::where('userid_id', Auth::user()->id)->first();
            return response()->json([
              'status_code'     => 200,
              'message'         => 'success',
            'data'  => new RoleResource(Role::with(['permissions'])
                                    ->where('title', 'Staff')
                                    ->orWhere('title', 'Manager')
                                    ->skip(($page-1)*$PAGINATION_COUNT)
                                    ->take($PAGINATION_COUNT)
                                    ->orderBy($ordered_by, $sort_type)
                                    ->get()),
            'total' => count(Role::with(['permissions'])
                                    ->where('title', 'Staff')
                                    ->orWhere('title', 'Manager')),
            ], 200);
      } // end case vendor
      elseif (in_array('Staff', $user_roles) || in_array('Manager', $user_roles)) {
        
        $exist_staff   = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
       // $staff_stores  = $exist_staff->stores->pluck('id')->toArray();

            return response()->json([
              'status_code'     => 200,
              'message'         => 'success',
            'data'  => new RoleResource(Role::with(['permissions'])
                                    ->where('title', 'Staff')
                                    ->orWhere('title', 'Manager')
                                    ->skip(($page-1)*$PAGINATION_COUNT)
                                    ->take($PAGINATION_COUNT)
                                    ->orderBy($ordered_by, $sort_type)
                                    ->get()),
            'total' => count(Role::with(['permissions'])
                                    ->where('title', 'Staff')
                                    ->orWhere('title', 'Manager')),
            ], 200);
      } // end case vendor
      else{
        return response()->json([
          'status_code'     => 401,
                'message'  => 'un authorized access page due to permissions',
               ], 401);
      } // end else 
    }

    public function store(StoreRoleRequest $request)
    {
        $arr  = json_decode($request->permissions);
        /*if (count($arr) <= 0) {
            return response()->json([
                'errors'  => 'please select at least one permission from list',
            ], 400);
        }
        $result = Permission::whereIn('id', $arr)->count();
        if($result != count($arr))
        {
            return response()->json([
                'errors'  => 'please select valid existing permissions',
            ], 400); 
        }*/
        $auth_user    = Auth::user();
        $auth_user_id = Auth::user()->id;
        $user_roles   = $auth_user->roles->pluck('title')->toArray();

      // case logged in user role is Admin 
      if (in_array('Admin', $user_roles)) {
            $request['added_by_id'] = Auth::user()->id;
            $role = Role::create($request->all());
            $role->permissions()->sync($arr);

            return response()->json([
               'status_code'   => 201,
               'message'       => 'success',
               'data'          => new RoleResource($role),
            ], Response::HTTP_CREATED);
      } // end admin case
       // case logged in user role is Vendor
      elseif (in_array('Vendor', $user_roles)) {
          // $vendor   = AddVendor::where('userid_id', Auth::user()->id)->first();
            $v = Validator::make($request->all(), [
                'title' => [
                  'string',
                  'required',
                  'unique:roles,title,NULL,id,deleted_at,NULL',
                  Rule::in('Staff', 'Manager'),
                ]
              ]);
              if ($v->fails()) {
                return response()->json(['errors' => $v->errors()], 400);
              }
            $request['added_by_id'] = Auth::user()->id;
            $role = Role::create($request->all());
            $role->permissions()->sync($arr);

            return response()->json([
               'status_code'   => 201,
               'message'       => 'success',
               'data'          => new RoleResource($role),
            ], Response::HTTP_CREATED);
      } // end case vendor
      else{
        return response()->json([
          'status_code'     => 401,
                'message'  => 'un authorized access page due to permissions',
               ], 401);
      } // end else 
    }

    public function show(Role $role)
    {
        abort_if(Gate::denies('role_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $auth_user    = Auth::user();
        $auth_user_id = Auth::user()->id;
        $user_roles   = $auth_user->roles->pluck('title')->toArray();

        // case logged in user role is Admin 
          if (in_array('Admin', $user_roles)) {
            return response()->json([
                     'status_code'   => 200,
                     'message'       => 'success',
                     'data'          => new SpecificRoleApiResource($role),
                  ], Response::HTTP_OK);
          } // end admin case
           // case logged in user role is Vendor
          elseif (in_array('Vendor', $user_roles)) {
                if ($role->added_by_id != $auth_user_id) {
                 return response()->json([
                     'status_code'   => 401,
                    // 'message'       => 'success',
                     'errors' => 'This role does not belong to you to view'], 401);
                }else{
                    return response()->json([
                     'status_code'   => 200,
                     'message'       => 'success',
                     'data'          => new SpecificRoleApiResource($role),
                  ], Response::HTTP_OK);
                }
          } // end case vendor
          else{
            return response()->json([
                    'status_code'   => 401,
                    // 'message'       => 'success',
                    'message'  => 'un authorized access page due to permissions',
                   ], 401);
          } // end else 
    }

    public function update(UpdateRoleRequest $request, $id)
    {
        $role = Role::findOrFail($id);
        $arr  = json_decode($request->permissions);
        
        $auth_user    = Auth::user();
        $auth_user_id = Auth::user()->id;
        $user_roles   = $auth_user->roles->pluck('title')->toArray();
      // case logged in user role is Admin 
      if (in_array('Admin', $user_roles)) {
            if ($role->added_by_id != $auth_user_id) {
             return response()->json([
                 'status_code'     => 401,
                'errors' => 'This role does not belong to you to edit'], 401);
            }
            $request['added_by_id'] = Auth::user()->id;
            $role->update($request->all());
            $role->permissions()->sync($arr);

            return response()->json([
                     'status_code'   => 202,
                     'message'       => 'success',
                     'data'          => new RoleResource($role),
                  ], Response::HTTP_ACCEPTED);
      } // end admin case
       // case logged in user role is Vendor
      elseif (in_array('Vendor', $user_roles)) {
            if ($role->added_by_id != $auth_user_id) {
             return response()->json([
                'status_code'     => 401,
                'errors' => 'This role does not belong to you to edit'], 401);
            }
          // $vendor   = AddVendor::where('userid_id', Auth::user()->id)->first();
                $request['added_by_id'] = Auth::user()->id;
                $role->update($request->all());
                $role->permissions()->sync($arr);

                 return response()->json([
                     'status_code'   => 202,
                     'message'       => 'success',
                     'data'          => new RoleResource($role),
                  ], Response::HTTP_ACCEPTED);
    
               /* return (new RoleResource($role))
                    ->response()
                    ->setStatusCode(Response::HTTP_ACCEPTED);*/
      } // end case vendor
      else{
        return response()->json([
                'status_code'     => 401,
                'message'  => 'un authorized access page due to permissions',
               ], 401);
      } // end else 
    }

    public function destroy(Role $role)
    {
        abort_if(Gate::denies('role_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $auth_user    = Auth::user();
        $auth_user_id = Auth::user()->id;
        $user_roles   = $auth_user->roles->pluck('title')->toArray();

        // case logged in user role is Admin 
      if (in_array('Admin', $user_roles)) {
        // test this user deletable to admin
          if ($role->added_by_id != $auth_user_id) {
           return response()->json([
            'errors' => 'This role does not belong to you to delete'], 401);
          }  
          else{
               $role->permissions()->detach();
               $role->delete();
               return response(null, Response::HTTP_NO_CONTENT);
          }
        } // end admin case
       // case logged in user role is Vendor
      elseif (in_array('Vendor', $user_roles)) {
        // test this user deletable to vendor
          if ($role->added_by_id != $auth_user_id) {
           return response()->json([
            'errors' => 'This role does not belong to you to delete'], 401);
          }
          else{
                $role->permissions()->detach();
                $role->delete();
                 return response()->json([
                'status_code'   => 200,
                'message'       => 'success',
                'data'          => null
              ], 200);
               // return response(null, Response::HTTP_NO_CONTENT);
          }
      } // end case vendor
      else{
        return response()->json([
                'message'  => 'un authorized access page due to permissions',
               ], 401);
      } // end else 
    }

        // start mass delete roles
     public function mass_delete(MassDestroyRoleRequest $request)
     {
        $auth_user    = Auth::user();
        $auth_user_id = Auth::user()->id;
        $user_roles   = $auth_user->roles->pluck('title')->toArray();
        $ids = json_decode($request->ids);

        foreach ($ids as $id) {
            $role = Role::findOrFail($id);
              if ($role->added_by_id != $auth_user_id) {
             return response()->json([
              'errors' => 'This role does not belong to you to delete'], 401);
            }
        }
       
        foreach ($ids as $id) {
            $role = Role::findOrFail($id);
            $role->permissions()->detach();
            $role->delete();
        }
         return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => null
          ], 200);
        // return response(null, Response::HTTP_NO_CONTENT);
    }
     // end mass delete roles

     // start list all
     public function list_all()
     {
        $auth_user    = Auth::user();
        $auth_user_id = Auth::user()->id;
        $user_roles   = $auth_user->roles->pluck('title')->toArray();

      // case logged in user role is Admin 
      if (in_array('Admin', $user_roles)) {
        $data = Role::get();
        return response()->json(['data' => $data], Response::HTTP_OK);
      } // end admin case
       // case logged in user role is Vendor
      elseif (in_array('Vendor', $user_roles) || in_array('Staff', $user_roles) || in_array('Manager', $user_roles)) {
          // $vendor   = AddVendor::where('userid_id', Auth::user()->id)->first();
        $data = Role::where('title', 'Staff')->orWhere('title', 'Manager')->get();
        return response()->json(['data' => $data], Response::HTTP_OK);
      } // end case vendor
      else{
        return response()->json([
                'message'  => 'un authorized access page due to permissions',
               ], 401);
      } // end else 
     }
     // end list all 
}
