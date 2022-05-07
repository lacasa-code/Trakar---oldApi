<?php

namespace App\Http\Controllers\Api\V1\User\Orders;

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

class UserOrdersApiController extends Controller
{
   /* public function show_orders(Request $request)
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
        $validator = Validator::make($request->all(), [
          'from' => 'required_with:to|date|date_format:Y-m-d|before_or_equal:to',
          'to'   => 'required_with:from|date|date_format:Y-m-d|after_or_equal:from',
        ]);
        if ($validator->fails()) {
          return response()->json([
            'status_code' => 400, 
            'message'     => 'fail',
            'errors' => $validator->errors()], 400);
        }

        $from = $request->from;
        $to   = $request->to;

        $startDate = $from.' 00:00:00';
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
    }*/

public function show_orders(Request $request)
    {
      //if (!$request->has('from') && !$request->has('to') || ($request->from == '' && $request->to == ''))
      if (!$request->has('from') || ($request->from == ''))
      {
       // $from = Carbon::today()->subMonth()->toDateString();
      // $from = Carbon::today()->subDays(10)->toDateString();
       $from_first = User::first()->created_at;
       $from = Carbon::parse($from_first)->format('Y-m-d');
       $to   = Carbon::today()->toDateString();

        $startDate = $from.' 00:00:00';
        $endDate   = $to.' 23:59:59';
      }
      else // case sent date filter (make validation)
      {
        $v = Validator::make($request->all(), [
          'from' => 'required|integer|min:1|max:12',
        ]);
        if ($v->fails()) {
          return response()->json([
            'status_code' => 400,
            'errors' => $v->errors(),
          ]);
        }
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
      $request->sort_type == '' ? $sort_type = 'DESC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;

      if (in_array('User', $user_roles) || in_array('Vendor', $user_roles)) {
        
        $user_orders = Order::where('user_id', $user->id)->where('paid', 1)
                      ->whereHas('orderDetails', function($q) use ($startDate, $endDate)
                        {
                          $q->where('checkout_time', '>=', $startDate)
                            ->where('checkout_time', '<=', $endDate);
                            //->where('approved', 1);
                        })
                      //->skip(($page-1)*$PAGINATION_COUNT)
                      //->take($PAGINATION_COUNT)
                      ->orderBy($ordered_by, $sort_type)
                      ->get();
        //$data = AdminOrdersApiResource::collection($user_orders);
        $data  = CartContentsApiResource::collection($user_orders);
        $total = Order::where('user_id', $user->id)->where('paid', 1)->whereHas('orderDetails', function($q) use ($startDate, $endDate)
              {
                $q->where('checkout_time', '>=', $startDate)
                  ->where('checkout_time', '<=', $endDate);
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


// vendor access specific order
    public function show_specific_order(Order $order)
    {
        // abort_if(Gate::denies('show_specific_order'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $user       = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

        if (in_array('User', $user_roles) || in_array('Vendor', $user_roles)) {
        	if ($order->user_id == $user->id) {
        		$order_data = new AdminApiSpecificOrderResource($order);
                return response()->json([
                  'status_code' => 200, 
                  'message'     => 'success',
                  'data' => $order_data], 200);
        	}
        	else{
        		return response()->json([
                    'status_code' => 401, 
                    //'message'     => 'success',
                    'message'  => 'not applicable',
                   ], 401);
        	}
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
