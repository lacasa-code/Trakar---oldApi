<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Http\Resources\Admin\AdminOrdersApiResource;
use App\Http\Resources\Admin\OrderGetItsDetailsResource;
use App\Http\Resources\Admin\AdminApiSpecificOrderResource;
use App\Http\Requests\SearchApisRequest;
use Gate;
use Symfony\Component\HttpFoundation\Response;
use Auth;
use App\Http\Requests\AdminAcessSpecificVendorOrdersRequest;
use App\Http\Resources\Vendor\VendorOrdersApiResource;
use App\Models\AddVendor;
use App\Models\Orderdetail;
use App\Http\Requests\SearchVendorOrdersApisRequest;
use App\Http\Resources\Vendor\VendorSpecificOrderApiResource;
use App\Http\Resources\Vendor\AdminShowVendorOrdersApiResource;

class AdminOrdersApiController extends Controller
{
    // admin access specific vendor orders
    public function access_specific_vendor_orders(AdminAcessSpecificVendorOrdersRequest $request)
    {
     // $PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;

      $vendor_id     = $request->vendor_id;
      $vendor        = AddVendor::where('userid_id', $vendor_id)->first();
      $get_orders    = Order::whereHas('orderDetails', function($q) use ($vendor_id){
                                    $q->where('vendor_id', $vendor_id);
                    })
                    ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                    ->orderBy($ordered_by, $sort_type)->get();
     // return $get_orders;
      foreach ($get_orders as $one) {
        $one['orderDetails'] = $one->orderDetails->where('vendor_id', $vendor_id);
        $one['order_total']  = $one->orderDetails->where('vendor_id', $vendor_id)->sum('total');
        $left = $one->orderDetails->where('vendor_id', $vendor_id)
                                           ->where('producttype_id', 1)->first();
            if ($left == null)  // start left 
            {
                    $lefttadmin = $one->orderDetails->where('producttype_id', 2)->first();
                    if ($lefttadmin == null) // etart lefttadmin
                    {
                        $arr = $one->orderDetails->pluck('approved')->toArray();
                        if(in_array(1, $arr)){
                                $leftt = 1;
                        }
                        else{ // else ahmed
                            if( in_array(0, $arr) && !in_array(2, $arr) && !in_array(3, $arr) ){
                                $leftt = 0;
                            }
                            elseif(!in_array(0, $arr) && in_array(2, $arr) && !in_array(3, $arr) ){
                                $leftt = 2;
                            }
                            elseif(!in_array(0, $arr) && !in_array(2, $arr) && in_array(3, $arr) ){
                                $leftt = 3;
                            }
                            elseif( in_array(0, $arr) && in_array(2, $arr) && !in_array(3, $arr) ){
                                $leftt = 0;
                            }
                            elseif( in_array(0, $arr) && !in_array(2, $arr) && in_array(3, $arr) ){
                                $leftt = 0;
                            }
                            elseif( !in_array(0, $arr) && in_array(2, $arr) && in_array(3, $arr) ){
                                $leftt = 2;
                            }
                            else{
                                $leftt = 0;
                            }
                        } // end else ahmed
                    }else{  // end lefttadmin
                            $leftt = $lefttadmin->approved;
                    }
            }else{  // end left 
                    $leftt = $left->approved;
            }
            if ($leftt == 1) {
                    $left_approval = 'in progress';
                }
                elseif ($leftt == 2) {
                    $left_approval = 'cancelled';
                }
                elseif ($leftt == 3) {
                    $left_approval = 'expired';
                }
                elseif ($leftt == 0) {
                    $left_approval = 'pending';
                }
                elseif ($leftt == 4) {
                    $left_approval = 'new';
                }
            $one['leftt'] = $left_approval;
      }
      $total      = Order::whereHas('orderDetails', function($q) use ($vendor_id){
                                    $q->where('vendor_id', $vendor_id);
                    })
                    ->count();

      $orders        = AdminShowVendorOrdersApiResource::collection($get_orders);
        return response()->json([
                'status_code' => 200,
                'message' => 'success',
                'data'  => $orders,
                'total' => $total,
               ], 200);
    }

    // admin access specific vendor specific order
    public function access_specific_vendor_specific_order(AddVendor $vendor, Order $order)
    {
        abort_if(Gate::denies('admin_access_specific_vendor_specific_order'), Response::HTTP_FORBIDDEN, '403 Forbidden');

            $order['vendor_id'] = $vendor->id;
            $vendor_id = $vendor->id;
            $left = $order->orderDetails->where('vendor_id', $vendor_id)
                                           ->where('producttype_id', 1)->first();
            if ($left == null)  // start left 
            {
                    $lefttadmin = $order->orderDetails->where('producttype_id', 2)->first();
                    if ($lefttadmin == null) // etart lefttadmin
                    {
                        $arr = $order->orderDetails->pluck('approved')->toArray();
                        if(in_array(1, $arr)){
                                $leftt = 1;
                        }
                        else{ // else ahmed
                            if( in_array(0, $arr) && !in_array(2, $arr) && !in_array(3, $arr) ){
                                $leftt = 0;
                            }
                            elseif(!in_array(0, $arr) && in_array(2, $arr) && !in_array(3, $arr) ){
                                $leftt = 2;
                            }
                            elseif(!in_array(0, $arr) && !in_array(2, $arr) && in_array(3, $arr) ){
                                $leftt = 3;
                            }
                            elseif( in_array(0, $arr) && in_array(2, $arr) && !in_array(3, $arr) ){
                                $leftt = 0;
                            }
                            elseif( in_array(0, $arr) && !in_array(2, $arr) && in_array(3, $arr) ){
                                $leftt = 0;
                            }
                            elseif( !in_array(0, $arr) && in_array(2, $arr) && in_array(3, $arr) ){
                                $leftt = 2;
                            }
                            else{
                                $leftt = 0;
                            }
                        } // end else ahmed
                    }else{  // end lefttadmin
                            $leftt = $lefttadmin->approved;
                    }
            }else{  // end left 
                    $leftt = $left->approved;
            }
            if ($leftt == 1) {
                    $left_approval = 'in progress';
                }
                elseif ($leftt == 2) {
                    $left_approval = 'cancelled';
                }
                elseif ($leftt == 3) {
                    $left_approval = 'expired';
                }
                elseif ($leftt == 0) {
                    $left_approval = 'pending';
                }
                elseif ($leftt == 4) {
                    $left_approval = 'new';
                }
            $order['leftt'] = $left_approval;
            $order_data = new VendorSpecificOrderApiResource($order);
            return response()->json([
              'status_code' => 200,
              'message' => 'success',
              'data' => $order_data], 200);
    }

    // start search orders
     public function search_with_name(SearchVendorOrdersApisRequest $request)
     {
        // default 1 id asc for page ordered_by sort_type
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      //$PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
        $search_index = $request->search_index;
        $vendor_id    = $request->vendor_id;
    
        $get_orders   = Order::whereHas('orderDetails', function($q) use ($search_index, $vendor_id){
                                    $q->where('vendor_id', $vendor_id)
                                       ->where('total', 'like', "%{$search_index}%")
                                      ->orWhereHas('order', function($q) use ($search_index){
                                        $q->where('order_number', 'like', "%{$search_index}%")
                                        ->orWhere('order_total', 'like', "%{$search_index}%");
                                      })->where('vendor_id', $vendor_id)
                                     /* ->orWhereHas('store', function($q) use ($search_index){
                                        $q->where('name', 'like', "%{$search_index}%");
                                      })
                                      ->orWhereHas('vendor', function($q) use ($search_index){
                                        $q->where('vendor_name', 'like', "%{$search_index}%");
                                      })*/
                                      ->orWhereHas('product', function($q) use ($search_index){
                                        $q->where('name', 'like', "%{$search_index}%");
                                      })->where('vendor_id', $vendor_id);
                    })
                    ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                    ->orderBy($ordered_by, $sort_type)->get();

       // $get_orders = $get_orders->where('', );
        foreach ($get_orders as $one) {
          $one['orderDetails'] = $one->orderDetails->where('vendor_id', $vendor_id);
          $one['order_total']  = $one->orderDetails->where('vendor_id', $vendor_id)->sum('total');
      }
        $orders        = VendorOrdersApiResource::collection($get_orders);

        $total = Order::whereHas('orderDetails', function($q) use ($search_index, $vendor_id){
                                    $q->where('vendor_id', $vendor_id)
                                       ->where('total', 'like', "%{$search_index}%")
                                      ->orWhereHas('order', function($q) use ($search_index){
                                        $q->where('order_number', 'like', "%{$search_index}%")
                                        ->orWhere('order_total', 'like', "%{$search_index}%");
                                      })
                                      ->orWhereHas('store', function($q) use ($search_index){
                                        $q->where('name', 'like', "%{$search_index}%");
                                      })
                                      ->orWhereHas('vendor', function($q) use ($search_index){
                                        $q->where('vendor_name', 'like', "%{$search_index}%");
                                      })
                                      ->orWhereHas('product', function($q) use ($search_index){
                                        $q->where('name', 'like', "%{$search_index}%");
                                      });
                    })
                    ->count();

        return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data' => $orders,
            'total' => $total,
        ], 200);
     }
    // end search orders
}
