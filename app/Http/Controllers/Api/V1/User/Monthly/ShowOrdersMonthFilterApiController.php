<?php

namespace App\Http\Controllers\Api\V1\User\Monthly;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\Admin\AdminOrdersApiResource;
use App\Http\Resources\Admin\AdminApiSpecificOrderResource;
use Auth;
use Gate;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Orderdetail;
use App\Models\Invoice;
use Validator;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\User\Cart\CartContentsApiResource;

class ShowOrdersMonthFilterApiController extends Controller
{
    public function show_orders(Request $request)
    {
      if (!$request->has('from') && !$request->has('to') || ($request->from == '' && $request->to == ''))
      {
       // $from = Carbon::today()->subMonth()->toDateString();
       $from = Carbon::today()->subDays(10)->toDateString();
       $to   = Carbon::today()->toDateString();

        $startDate = $from.' 00:00:00';
        $endDate   = $to.' 23:59:59';
      }
      else // case sent date filter (make validation)
      {
        $from = $request->from;
        $from_m = Carbon::now()->subMonths($from);
        $to     = Carbon::today()->toDateString();

        $startDate = $from_m.' 00:00:00';
        $endDate   = $to.' 23:59:59';
      }
      // abort_if(Gate::denies('show_orders_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
      //$PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;

      $user = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();

      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;

      if (in_array('User', $user_roles) || in_array('Vendor', $user_roles)) {
      	
        $user_orders = Order::whereHas('orderDetails', function($q) use ($startDate, $endDate)
                        {
                          $q->where('created_at', '>=', $startDate)
                            ->where('created_at', '<=', $endDate);
                                // ->where('approved', 1);
                        })->skip(($page-1)*$PAGINATION_COUNT)
				              ->take($PAGINATION_COUNT)
				              ->orderBy($ordered_by, $sort_type)
				              ->get();
        //$data = AdminOrdersApiResource::collection($user_orders);
        $data = CartContentsApiResource::collection($user_orders);
        $total = Order::whereHas('orderDetails', function($q) use ($startDate, $endDate)
              {
                $q->where('created_at', '>=', $startDate)
                  ->where('created_at', '<=', $endDate);
                                // ->where('approved', 1);
              })->count();
            return response()->json([
                    'status_code' => 200, 
                    'message'     => 'success',
                    'data'  => $data,
                    'total' => $total,
            ], 200);
      } 
      else{
        return response()->json([
                'status_code' => 401, 
                // 'message'     => 'success',
                'message'  => 'un authorized access page due to permissions',
               ], 401);
      }
    }
}
