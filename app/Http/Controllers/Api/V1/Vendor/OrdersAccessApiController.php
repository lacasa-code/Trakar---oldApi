<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\MakeOrderApiRequest;
use App\Models\Order;
use App\Models\Orderdetail;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Vendorstaff;
use App\Models\User;
use App\Models\AddVendor;
use App\Http\Requests\VendorApproveOrderApiRequest;
use App\Http\Requests\CancelOrderApiRequest;
use Carbon\Carbon;
use App\Http\Resources\Vendor\OrderGetItsDetailsResource;
use App\Http\Resources\Vendor\VendorOrdersApiResource;
use App\Http\Resources\Vendor\VendorApiSpecificInvoiceResource;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\Vendor\VendorInvoicesApiResource;
use Gate;
use App\Http\Resources\Admin\AdminOrdersApiResource;
use App\Http\Resources\Admin\AdminApiSpecificOrderResource;
use App\Http\Requests\SearchApisRequest;
use Auth;
use App\Http\Requests\AdminAcessSpecificVendorOrdersRequest;
use App\Http\Resources\Admin\AdminInvoicesApiResource;
use App\Http\Resources\Admin\AdminApiSpecificInvoiceResource;
use App\Http\Requests\AdminAcessSpecificVendorInvoicesRequest;
use App\Http\Resources\Vendor\VendorSpecificOrderApiResource;
use DB;
use Validator;
use Illuminate\Support\Facades\Storage;
use Artisan;
use App\Models\CarMade;
use App\Http\Resources\User\Cart\CheckoutContentsApiResource;
use App\Models\Productreview;

class OrdersAccessApiController extends Controller
{
// vendor access specific order
    public function show_specific_order(Order $order)
    {
        abort_if(Gate::denies('show_specific_order'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $user       = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

        if (in_array('Admin', $user_roles)) {
          $order_data = new AdminApiSpecificOrderResource($order);
            return response()->json([
                        'status_code' => 200, 
                        'message'     => 'success',
                        'data' => $order_data], 200);
        } 
           // case logged in user role is Vendor (show only his invoices)
        elseif (in_array('Vendor', $user_roles)) {
            $vendor     = AddVendor::where('userid_id', Auth::user()->id)->first();
            $vendor_id     = $vendor->id;
            $vendors = $order->orderDetails->pluck('vendor_id')->toArray();
            if (!in_array($vendor->id, $vendors)) {
                return response()->json([
                  'status_code' => 400, 
                  'errors'     => 'fail, can not access',
                  'data' => null,], 400);
            }
            $order['vendor_id'] = $vendor->id;
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
              'message'     => 'success',
              'data' => $order_data], 200);
        }
        /* manager case */
        elseif (in_array('Manager', $user_roles)) 
      {
       // abort_if(Gate::denies('stores_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
     //   $staff_stores = $exist_staff->stores->pluck('id')->toArray();
       // return $staff_stores;
        $vendors = $order->orderDetails->pluck('vendor_id')->toArray();
            if (!in_array($vendor_id, $vendors)) {
                return response()->json([
                  'status_code' => 400, 
                  'errors'     => 'fail, can not access',
                  'data' => null,], 400);
            }
            $order['vendor_id'] = $vendor_id;
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
              'message'     => 'success',
              'data' => $order_data], 200);

      }
        /* manager case */
        else{
            return response()->json([
                    'status_code' => 401, 
                   // 'message'     => 'success',
                    'message'  => 'un authorized access page due to permissions',
                   ], 401);
          }
    }

    public function search_with_order_total($search_index, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT){
      $user       = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();

// case admin search
        if (in_array('Admin', $user_roles)) {
          $get_orders   = Order::where('order_total', 'like', "%{$search_index}%")
                          ->where('paid', 1)
                          ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                          ->orderBy($ordered_by, $sort_type)->get();

        $orders = AdminOrdersApiResource::collection($get_orders);
        $total = count($get_orders);

                    return response()->json([
                        'status_code' => 200, 
                        'message'     => 'success',
                        'data'  => $orders,
                        'total' => $total,
                    ], 200);
        } 
           // case vendor search
        elseif (in_array('Vendor', $user_roles)) {
            $vendor       = AddVendor::where('userid_id', Auth::user()->id)->first();
            $vendor_id    = $vendor->id;
            $get_orders   = Order::whereHas('orderDetails', function($query) use ($search_index, $vendor_id){
                                      $query->whereHas('order', function($query1) use ($search_index){
                                        $query1->where('order_total', 'like', "%{$search_index}%")
                                        ->where('paid', 1);
                                      })->where('vendor_id', $vendor_id);
                    })->where('paid', 1)
                    ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                    ->orderBy($ordered_by, $sort_type)->get();
                    foreach ($get_orders as $one) {
                    /*  $one['need_approval'] = $one->orderDetails->where('vendor_id', $vendor_id)
                                              ->where('approved', 0)->count() > 0 && $one->expired != 1 && $one->status != 'cancelled'  && $one->status != 'in progress' ? 1 : 0; */
                      $one['need_approval'] = $one->orderDetails->where('vendor_id', $vendor_id)
                                         ->where('producttype_id', 1)
                                         ->where('approved', 0)->count() > 0 && $one->expired != 1 ? 1 : 0;
                      $one['orderDetails'] = $one->orderDetails->where('vendor_id', $vendor_id);
                      $one['order_total']  = $one->orderDetails->where('vendor_id', $vendor_id)->sum('total');
                  }
        $orders        = VendorOrdersApiResource::collection($get_orders);
        $total = count($get_orders);

                    return response()->json([
                        'status_code' => 200, 
                        'message'     => 'success',
                        'data' => $orders,
                        'total' => $total,
                    ], 200);
        }
         elseif (in_array('Manager', $user_roles) ) 
      {
        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
        $staff_stores = $exist_staff->stores->pluck('id')->toArray();
        $get_orders   = Order::whereHas('orderDetails', function($query) use ($search_index, $vendor_id){
                                      $query->whereHas('order', function($query1) use ($search_index){
                                        $query1->where('order_total', 'like', "%{$search_index}%")
                                        ->where('paid', 1);
                                      })->where('vendor_id', $vendor_id);
                    })->where('paid', 1)
                    ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                    ->orderBy($ordered_by, $sort_type)->get();
                    foreach ($get_orders as $one) {
                     /* $one['need_approval'] = $one->orderDetails->where('vendor_id', $vendor_id)
                                              ->where('approved', 0)->count() > 0 && $one->expired != 1 && $one->status != 'cancelled'  && $one->status != 'in progress' ? 1 : 0; */
                    $one['need_approval'] = $one->orderDetails->where('vendor_id', $vendor_id)
                                         ->where('producttype_id', 1)
                                         ->where('approved', 0)->count() > 0 && $one->expired != 1 ? 1 : 0;
                      $one['orderDetails'] = $one->orderDetails->where('vendor_id', $vendor_id);
                      $one['order_total']  = $one->orderDetails->where('vendor_id', $vendor_id)->sum('total');
                  }
        $orders        = VendorOrdersApiResource::collection($get_orders);
        $total = count($get_orders);

                    return response()->json([
                        'status_code' => 200, 
                        'message'     => 'success',
                        'data' => $orders,
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

    public function search_with_order_number($search_index, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT){
      $user       = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();

// case admin search
        if (in_array('Admin', $user_roles)) {
          $get_orders   = Order::where('order_number', 'like', "%{$search_index}%")
                          ->where('paid', 1)
                          ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                          ->orderBy($ordered_by, $sort_type)->get();

        $orders = AdminOrdersApiResource::collection($get_orders);
        $total = count($get_orders);

                    return response()->json([
                        'status_code' => 200, 
                        'message'     => 'success',
                        'data'  => $orders,
                        'total' => $total,
                    ], 200);
        } 
           // case vendor search
        elseif (in_array('Vendor', $user_roles)) {
            $vendor       = AddVendor::where('userid_id', Auth::user()->id)->first();
            $vendor_id    = $vendor->id;
            $get_orders   = Order::whereHas('orderDetails', function($query) use ($search_index, $vendor_id){
                                      $query->whereHas('order', function($query1) use ($search_index){
                                        $query1->where('order_number', 'like', "%{$search_index}%")
                                        ->where('paid', 1);
                                      })->where('vendor_id', $vendor_id);
                    })->where('paid', 1)
                    ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                    ->orderBy($ordered_by, $sort_type)->get();
                    foreach ($get_orders as $one) {
                      /*$one['need_approval'] = $one->orderDetails->where('vendor_id', $vendor_id)
                                              ->where('approved', 0)->count() > 0 && $one->expired != 1 && $one->status != 'cancelled'  && $one->status != 'in progress' ? 1 : 0;*/
                      $one['need_approval'] = $one->orderDetails->where('vendor_id', $vendor_id)
                                         ->where('producttype_id', 1)
                                         ->where('approved', 0)->count() > 0 && $one->expired != 1 ? 1 : 0;
                      $one['orderDetails'] = $one->orderDetails->where('vendor_id', $vendor_id);
                      $one['order_total']  = $one->orderDetails->where('vendor_id', $vendor_id)->sum('total');
                  }
        $orders        = VendorOrdersApiResource::collection($get_orders);
        $total = count($get_orders);

                    return response()->json([
                        'status_code' => 200, 
                        'message'     => 'success',
                        'data' => $orders,
                        'total' => $total,
                    ], 200);
        }
        elseif (in_array('Manager', $user_roles) ) 
      {
        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
        $staff_stores = $exist_staff->stores->pluck('id')->toArray();
        $get_orders   = Order::whereHas('orderDetails', function($query) use ($search_index, $vendor_id){
                                      $query->whereHas('order', function($query1) use ($search_index){
                                        $query1->where('order_number', 'like', "%{$search_index}%")
                                        ->where('paid', 1);
                                      })->where('vendor_id', $vendor_id);
                    })->where('paid', 1)
                    ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                    ->orderBy($ordered_by, $sort_type)->get();
                    foreach ($get_orders as $one) {
                     /* $one['need_approval'] = $one->orderDetails->where('vendor_id', $vendor_id)
                                              ->where('approved', 0)->count() > 0 && $one->expired != 1 && $one->status != 'cancelled'  && $one->status != 'in progress' ? 1 : 0;*/
                      $one['need_approval'] = $one->orderDetails->where('vendor_id', $vendor_id)
                                         ->where('producttype_id', 1)
                                         ->where('approved', 0)->count() > 0 && $one->expired != 1 ? 1 : 0;
                      $one['orderDetails'] = $one->orderDetails->where('vendor_id', $vendor_id);
                      $one['order_total']  = $one->orderDetails->where('vendor_id', $vendor_id)->sum('total');
                  }
        $orders        = VendorOrdersApiResource::collection($get_orders);
        $total = count($get_orders);

                    return response()->json([
                        'status_code' => 200, 
                        'message'     => 'success',
                        'data' => $orders,
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

    public function search_with_all($search_index, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT){
      $user       = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();

      // case admin search
        if (in_array('Admin', $user_roles)) {
          $get_orders   = Order::where('order_number', 'like', "%{$search_index}%")
                              ->orWhere('order_total', 'like', "%{$search_index}%")
                              ->orWhere('status', 'like', "%{$search_index}%")
                              ->orWhereHas('orderDetails', function($q) use ($search_index){
                                      $q->whereHas('store', function($q) use ($search_index){
                                        $q->where('name', 'like', "%{$search_index}%");
                                      })
                                      ->orWhereHas('vendor', function($q) use ($search_index){
                                        $q->where('vendor_name', 'like', "%{$search_index}%");
                                      })
                                      ->orWhereHas('product', function($q) use ($search_index){
                                        $q->where('name', 'like', "%{$search_index}%");
                                      });
                    })
                    ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                    ->orderBy($ordered_by, $sort_type)->get();
        $paid_orders = $get_orders->where('paid', 1);
        $orders = AdminOrdersApiResource::collection($paid_orders);
        $total = count($paid_orders);

                    return response()->json([
                        'status_code' => 200, 
                        'message'     => 'success',
                        'data' => $orders,
                        'total' => $total,
                    ], 200);
        } 
           // case vendor search
        elseif (in_array('Vendor', $user_roles)) {
            $vendor       = AddVendor::where('userid_id', Auth::user()->id)->first();
            $vendor_id    = $vendor->id;
            $get_orders   = Order::whereHas('orderDetails', function($query) use ($search_index, $vendor_id){
                                      $query->whereHas('order', function($query1) use ($search_index){
                                        $query1->where('order_number', 'like', "%{$search_index}%")
                                        ->orWhere('order_total', 'like', "%{$search_index}%")
                                        ->orWhere('status', 'like', "%{$search_index}%");
                                      })->where('vendor_id', $vendor_id);
                    })
                    ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                    ->orderBy($ordered_by, $sort_type)->get();
                    foreach ($get_orders as $one) {
                      /*$one['need_approval'] = $one->orderDetails->where('vendor_id', $vendor_id)
                                              ->where('approved', 0)->count() > 0 && $one->expired != 1 && $one->status != 'cancelled'  && $one->status != 'in progress' ? 1 : 0;*/

                       $one['need_approval'] = $one->orderDetails->where('vendor_id', $vendor_id)
                                         ->where('producttype_id', 1)
                                         ->where('approved', 0)->count() > 0 && $one->expired != 1 ? 1 : 0;

                      $one['orderDetails'] = $one->orderDetails->where('vendor_id', $vendor_id);
                      $one['order_total']  = $one->orderDetails->where('vendor_id', $vendor_id)->sum('total');
                  }
        $paid_orders = $get_orders->where('paid', 1);
        $orders  = VendorOrdersApiResource::collection($paid_orders);
        $total   = count($paid_orders);

                    return response()->json([
                        'status_code' => 200, 
                        'message'     => 'success',
                        'data' => $orders,
                        'total' => $total,
                    ], 200);
        }
         elseif (in_array('Manager', $user_roles) ) 
      {
        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
        $staff_stores = $exist_staff->stores->pluck('id')->toArray();
        $get_orders   = Order::whereHas('orderDetails', function($query) use ($search_index, $vendor_id){
                                      $query->whereHas('order', function($query1) use ($search_index){
                                        $query1->where('order_number', 'like', "%{$search_index}%")
                                        ->orWhere('order_total', 'like', "%{$search_index}%")
                                        ->orWhere('status', 'like', "%{$search_index}%");
                                      })->where('vendor_id', $vendor_id);
                    })
                    ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                    ->orderBy($ordered_by, $sort_type)->get();
                    foreach ($get_orders as $one) {
                      /*$one['need_approval'] = $one->orderDetails->where('vendor_id', $vendor_id)
                                              ->where('approved', 0)->count() > 0 && $one->expired != 1 && $one->status != 'cancelled'  && $one->status != 'in progress' ? 1 : 0; */

                      $one['need_approval'] = $one->orderDetails->where('vendor_id', $vendor_id)
                                         ->where('producttype_id', 1)
                                         ->where('approved', 0)->count() > 0 && $one->expired != 1 ? 1 : 0;

                      $one['orderDetails'] = $one->orderDetails->where('vendor_id', $vendor_id);
                      $one['order_total']  = $one->orderDetails->where('vendor_id', $vendor_id)->sum('total');
                  }
        $paid_orders = $get_orders->where('paid', 1);
        $orders  = VendorOrdersApiResource::collection($paid_orders);
        $total   = count($paid_orders);

                    return response()->json([
                        'status_code' => 200, 
                        'message'     => 'success',
                        'data' => $orders,
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

    // start search orders
     public function search_with_name(SearchApisRequest $request)
     {
        // default 1 id asc for page ordered_by sort_type
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'DESC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'status' : $ordered_by = $request->ordered_by;
      //$PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      $request->column_name == '' ? $column_name = '' : $column_name = $request->column_name;
      
      $search_index = $request->search_index;

        $user       = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

        if ($column_name == 'order_total') {
          return $this->search_with_order_total($search_index, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }
        if ($column_name == 'order_number') {
          return $this->search_with_order_number($search_index, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }
        if ($column_name == '') {
          return $this->search_with_all($search_index, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }
     }  // end search orders
}
