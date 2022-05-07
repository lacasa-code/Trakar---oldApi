<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuditLog;
use Gate;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\Admin\AuditLogApiReource;
use App\Http\Requests\SearchApisRequest;
use Carbon\Carbon;

class AuditLogsApiController extends Controller
{
    public function index(Request $request)
    {
      abort_if(Gate::denies('audit_log_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
      // $PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;

        return response()->json([
            'data'  => new AuditLogApiReource(AuditLog::skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)->orderBy($ordered_by, $sort_type)->get()),
            'total' => AuditLog::count()
        ]);
    }

    public function show(AuditLog $auditLog)
    {
        abort_if(Gate::denies('audit_log_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        return new AuditLogApiReource($auditLog);
    }

    // start search audit-logs with name
     public function search_with_name(SearchApisRequest $request)
     {
      // $PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page; 
      
        $search_index = $request->search_index;
        $audit_logs = AuditLog::where(function ($q) use ($search_index) {
                $q->where('subject_id', 'like', "%{$search_index}%")
                ->orWhere('subject_type', 'like', "%{$search_index}%")
                ->orWhere('description', 'like', "%{$search_index}%")
                ->orWhere('host', 'like', "%{$search_index}%")
                ->orWhere('created_at', 'like', "%{$search_index}%");
                })
                ->orWhereHas('usersids', function($q) use ($search_index){
                                $q->where('name', 'like', "%{$search_index}%")->
                                orWhere('email', 'like', "%{$search_index}%");
                })->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();

         $total = AuditLog::where(function ($q) use ($search_index) {
                $q->where('subject_id', 'like', "%{$search_index}%")
                ->orWhere('subject_type', 'like', "%{$search_index}%")
                ->orWhere('description', 'like', "%{$search_index}%")
                ->orWhere('host', 'like', "%{$search_index}%")
                ->orWhere('created_at', 'like', "%{$search_index}%");
                })
                ->orWhereHas('usersids', function($q) use ($search_index){
                                $q->where('name', 'like', "%{$search_index}%")->
                                orWhere('email', 'like', "%{$search_index}%");
                })->count();

        return response()->json([
            'data' => $audit_logs,
            'total' => $total,
        ], 200);
     }
    // end search audit-logs with name
}
