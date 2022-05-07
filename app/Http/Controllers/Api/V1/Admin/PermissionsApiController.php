<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Http\Resources\Admin\PermissionResource;
use App\Models\Permission;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Auth;

class PermissionsApiController extends Controller
{
    public function index(Request $request)
    {
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
       
        abort_if(Gate::denies('permission_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        return response()->json([
            'status_code'     => 200,
            'message'         => 'success',
            'data' => new PermissionResource(Permission::skip(($page-1)*$PAGINATION_COUNT)
                                        ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get()),
            'total' => Permission::count()
        ], 200);
    }

    public function store(StorePermissionRequest $request)
    {
        $permission = Permission::create($request->all());
        return response()->json([
            'status_code'   => 201,
            'message'       => 'success',
            'data'          => new PermissionResource($permission)
        ], Response::HTTP_CREATED);
    }

    public function show(Permission $permission)
    {
        abort_if(Gate::denies('permission_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data' => [
            "id"          => $permission->id,
            "title"       => $permission->title,
            "created_at"  => $permission->created_at,
            "updated_at"  => $permission->updated_at,
            "deleted_at"  => $permission->deleted_at,
          ],
        ], 200);
       // return new PermissionResource($permission);
    }

    public function update(UpdatePermissionRequest $request, Permission $permission)
    {
        $permission->update($request->all());

        return response()->json([
            'status_code'     => 202,
            'message'       => 'success',
            'data'          => new PermissionResource($permission)
        ], Response::HTTP_ACCEPTED);
    }

    public function destroy(Permission $permission)
    {
        abort_if(Gate::denies('permission_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $permission->delete();
        return response(null, Response::HTTP_NO_CONTENT);
    }

     // start list all
     public function list_all()
     {
        $auth_user    = Auth::user();
        $auth_user_id = Auth::user()->id;
        $user_roles   = $auth_user->roles->pluck('title')->toArray();

        // case logged in user role is Admin 
      if (in_array('Admin', $user_roles)) {
            $data = Permission::all();
            return response()->json([
            'status_code'     => 200,
            'message'         => 'success',
              'data' => $data,
            ], Response::HTTP_OK);
        } // end admin case
       // case logged in user role is Vendor
      elseif (in_array('Vendor', $user_roles)) {
           // $data = Permission::all();
        $auth_user_roles    = $auth_user->roles;
        $custom_permissions = array();
        foreach ($auth_user_roles as $auth_user_role) {
            foreach($auth_user_role->permissions as $permission){
                array_push($custom_permissions, $permission->id);
            }
        }
            $ids = $custom_permissions;
            $data = Permission::whereIn('id', $ids)->get();
            return response()->json([
              'status_code'     => 200,
              'message'         => 'success',
              'data' => $data], Response::HTTP_OK);
      } // end case vendor
      else{
        return response()->json([
                'message'  => 'un authorized access page due to permissions',
                'status_code'     => 401,
               ], 401);
      } // end else 
     }
     // end list all 
}
