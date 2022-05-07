<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\MakeOrderApiRequest;
use App\Models\Order;
use App\Models\Orderdetail;
use App\Models\Invoice;
use App\Models\Product;
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
use App\Http\Requests\Api\V1\Vendor\TotalOrdersFilterApiRequest;
use App\Models\Vendorstaff;

class TotalOrdersAccessApiController extends Controller
{
    public function show_orders(TotalOrdersFilterApiRequest $request)
    {
      Artisan::call('order:expire');
     abort_if(Gate::denies('show_orders_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
     
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
      $user = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();

      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'DESC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      $request->fetch == '' ? $fetch = 'all' : $fetch = $request->fetch;

      /**************************** fetch all ************************/
        if($fetch == 'all')
        {
           if (in_array('Admin', $user_roles)) 
	       {
		      	$result = Order::where('paid', 1)->skip(($page-1)*$PAGINATION_COUNT)
		                        ->take($PAGINATION_COUNT)
		                        ->orderBy($ordered_by, $sort_type)
		                        ->get();
		        
		        $total = Order::where('paid', 1)->count();
		        $data = AdminOrdersApiResource::collection($result);

		            return response()->json([
		                    'status_code' => 200, 
		                    'message'     => 'success',
		                    'data'  => $data,
		                    'total' => $total, //Order::count(),
		            ], 200);
	      } 
	       // case logged in user role is Vendor (show only his invoices)
	      elseif (in_array('Vendor', $user_roles)) {
	              $vendor        = AddVendor::where('userid_id', Auth::user()->id)->first();
	              $vendor_id     = $vendor->id;
	             
	              $get_orders = Order::whereHas('orderDetails', function($q) use ($vendor_id){
	                      $q->where('vendor_id', $vendor_id);
	                      })
	                      ->where('paid', 1)
	                      ->orderBy('checkout_time', 'DESC')
	                      ->skip(($page-1)*$PAGINATION_COUNT)
	                      ->take($PAGINATION_COUNT)->get();
	                      // $get_orders = $get_orderss->sortByDesc('leftApproval');
	                     // $get_orders->skip(($page-1)*$PAGINATION_COUNT)
	                       //                       ->take($PAGINATION_COUNT);

	              foreach ($get_orders as $one) {
	               /*   $one['need_approval'] = $one->orderDetails->where('vendor_id', $vendor_id)
	                                            ->where('producttype_id', 1)
	                                            ->where('approved', 0)->count() > 0 && $one->expired != 1 && $one->status != 'cancelled'  && $one->status != 'in progress' ? 1 : 0; */

	                 $one['need_approval'] = $one->orderDetails->where('vendor_id', $vendor_id)
	                                       ->where('producttype_id', 1)
	                                       ->where('approved', 0)->count() > 0 && $one->expired != 1 ? 1 : 0;

	                  $one['orderDetails'] = $one->orderDetails->where('vendor_id', $vendor_id);
	                  $one['order_total']  = $one->orderDetails->where('vendor_id', $vendor_id)                                     ->sum('total');
	                 // $one['leftt']  = $one->orderDetails->where('vendor_id', $vendor_id)
                        //                   ->where('producttype_id', 1)->first();
	              }

	            $orders        = VendorOrdersApiResource::collection($get_orders);
	          //  $sorted        = $orders->orderBy('leftApproval', 'DESC');
	            $total = Order::whereHas('orderDetails', function($q) use ($vendor_id){
	                                    $q->where('vendor_id', $vendor_id);
	                    })->where('paid', 1)->count();

	                return response()->json([
	                        'status_code' => 200, 
	                        'message'     => 'success',
	                        'data'  => $orders,
	                        'total' => $total,
	                       ], 200);
	      }

	      /* start manager case */

	   elseif (in_array('Manager', $user_roles)) 
       {
       // abort_if(Gate::denies('stores_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $exist_staff   = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
        //$staff_stores = $exist_staff->stores->pluck('id')->toArray();
       // return $staff_stores;

        $get_orders = Order::whereHas('orderDetails', function($q) use ($vendor_id){
	                      $q->where('vendor_id', $vendor_id);
	                      })
	                      ->where('paid', 1)
	                      ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
	                      ->orderBy($ordered_by, $sort_type)->get();
	              foreach ($get_orders as $one) {
	              /*	$one['need_approval'] = $one->orderDetails->where('vendor_id', $vendor_id)
	                                            ->where('approved', 0)->count() > 0 && $one->expired != 1 && $one->status != 'cancelled'  && $one->status != 'in progress' ? 1 : 0; */
	                 $one['need_approval'] = $one->orderDetails->where('vendor_id', $vendor_id)
	                                       ->where('producttype_id', 1)
	                                       ->where('approved', 0)->count() > 0 && $one->expired != 1 ? 1 : 0;
	                  $one['orderDetails'] = $one->orderDetails->where('vendor_id', $vendor_id);
	                  $one['order_total']  = $one->orderDetails->where('vendor_id', $vendor_id)                                     ->sum('total');
	              }

	            $orders        = VendorOrdersApiResource::collection($get_orders);
	            $total = Order::whereHas('orderDetails', function($q) use ($vendor_id){
	                                    $q->where('vendor_id', $vendor_id);
	                    })->where('paid', 1)->count();

	                return response()->json([
	                        'status_code' => 200, 
	                        'message'     => 'success',
	                        'data'  => $orders,
	                        'total' => $total,
	                       ], 200);
       }
	    /* end manager case */
	      else{
	        return response()->json([
	                'status_code' => 401, 
	                // 'message'     => 'success',
	                'message'  => 'un authorized access page due to permissions',
	               ], 401);
	      }
	  }

      /**************************** fetch all ************************/

      /**************************** fetch specific status ************************/
        else
        {
	          if (in_array('Admin', $user_roles)) 
		      {
		      	/* $result = Order::where('status', $fetch)->where('paid', 1)
		      	                ->skip(($page-1)*$PAGINATION_COUNT)
		                        ->take($PAGINATION_COUNT)
		                        ->orderBy($ordered_by, $sort_type)->get(); */

		        $result = Order::whereHas('orderDetails', function($q) use ($fetch){
		                             $q->where('approved', $fetch);
		                              })
		                        ->where('paid', 1)
		      	                ->skip(($page-1)*$PAGINATION_COUNT)
		                        ->take($PAGINATION_COUNT)
		                        ->orderBy($ordered_by, $sort_type)->get();
		        
		        $data = AdminOrdersApiResource::collection($result);
		        $total = Order::where('status', $fetch)->where('paid', 1);
		        return response()->json([
		                    'status_code' => 200, 
		                    'message'     => 'success',
		                    'data'  => $data,
		                    'total' => $total,
		            ], 200);
		      } 
		       // case logged in user role is Vendor (show only his invoices)
		      elseif (in_array('Vendor', $user_roles)) {
		              $vendor     = AddVendor::where('userid_id', Auth::user()->id)->first();
		              $vendor_id  = $vendor->id;
		             
		            /* $get_orders = Order::where('status', $fetch)
		                            ->whereHas('orderDetails', function($q) use ($vendor_id){
		                               $q->where('vendor_id', $vendor_id);
		                            })->where('paid', 1)
			                        ->skip(($page-1)*$PAGINATION_COUNT)
			                        ->take($PAGINATION_COUNT)
			                        ->orderBy($ordered_by, $sort_type)->get(); */

			    $get_orders = Order::whereHas('orderDetails', function($q) use ($fetch, $vendor_id){
		                             $q->where('approved', $fetch)
		                               ->where('vendor_id', $vendor_id);
		                              })
		                            ->where('paid', 1)
			                        ->skip(($page-1)*$PAGINATION_COUNT)
			                        ->take($PAGINATION_COUNT)
			                        ->orderBy($ordered_by, $sort_type)->get();

		              foreach ($get_orders as $one) {
		              	/*$one['need_approval'] = $one->orderDetails->where('vendor_id', $vendor_id)
	                                            ->where('approved', 0)->count() > 0 && $one->expired != 1 && $one->status != 'cancelled'  && $one->status != 'in progress' ? 1 : 0;*/

	                     $one['need_approval'] = $one->orderDetails->where('vendor_id', $vendor_id)
	                                       ->where('producttype_id', 1)
	                                       ->where('approved', $fetch)
	                                       ->where('approved', 0)->count() > 0 && $one->expired != 1 ? 1 : 0;
		                  $one['orderDetails'] = $one->orderDetails->where('approved', $fetch)
		                                                    ->where('vendor_id', $vendor_id);
		                  $one['order_total']  = $one->orderDetails->where('approved', $fetch)
		                                            ->where('vendor_id', $vendor_id)->sum('total');
		              }

		            $orders        = VendorOrdersApiResource::collection($get_orders);
		            $total = Order::whereHas('orderDetails', function($q) use ($fetch, $vendor_id){
		                             $q->where('approved', $fetch)
		                               ->where('vendor_id', $vendor_id);
		                              })->where('paid', 1)->count();
		                            
		                return response()->json([
		                        'status_code' => 200, 
		                        'message'     => 'success',
		                        'data'  => $orders,
		                        'total' => $total,
		                       ], 200);
		      }
		      /* start manager case */

      elseif (in_array('Manager', $user_roles)) 
      {
       // abort_if(Gate::denies('stores_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
        // $staff_stores = $exist_staff->stores->pluck('id')->toArray();
       // return $staff_stores;
             
              /* $get_orders = Order::where('status', $fetch)
		                            ->whereHas('orderDetails', function($q) use ($vendor_id){
		                               $q->where('vendor_id', $vendor_id);
		                            })->where('paid', 1)
			                        ->skip(($page-1)*$PAGINATION_COUNT)
			                        ->take($PAGINATION_COUNT)
			                        ->orderBy($ordered_by, $sort_type)->get(); */

			$get_orders = Order::whereHas('orderDetails', function($q) use ($fetch, $vendor_id){
		                               $q->where('vendor_id', $vendor_id)->where('approved', $fetch);
		                            })
			                        ->where('paid', 1)
			                        ->skip(($page-1)*$PAGINATION_COUNT)
			                        ->take($PAGINATION_COUNT)
			                        ->orderBy($ordered_by, $sort_type)->get();

		              foreach ($get_orders as $one) {
		              /*	$one['need_approval'] = $one->orderDetails->where('vendor_id', $vendor_id)
	                                            ->where('approved', 0)->count() > 0 && $one->expired != 1 && $one->status != 'cancelled'  && $one->status != 'in progress' ? 1 : 0; */
	                     $one['need_approval'] = $one->orderDetails->where('approved', $fetch)
	                                       ->where('vendor_id', $vendor_id)
	                                       ->where('producttype_id', 1)
	                                       ->where('approved', 0)->count() > 0 && $one->expired != 1 ? 1 : 0;
	                                       
		                  $one['orderDetails'] = $one->orderDetails->where('approved', $fetch)
		                                                    ->where('vendor_id', $vendor_id);
		                  $one['order_total']  = $one->orderDetails->where('approved', $fetch)
		                                            ->where('vendor_id', $vendor_id)->sum('total');
		              }

		            $orders        = VendorOrdersApiResource::collection($get_orders);
		            $total = Order::whereHas('orderDetails', function($q) use ($fetch, $vendor_id){
		                             $q->where('approved', $fetch)
		                               ->where('vendor_id', $vendor_id);
		                              })->where('paid', 1)->count();
		                            
		                return response()->json([
		                        'status_code' => 200, 
		                        'message'     => 'success',
		                        'data'  => $orders,
		                        'total' => $total,
		                       ], 200);
      }

		      /* end manager case */
		      else{
		        return response()->json([
		                'status_code' => 401, 
		                // 'message'     => 'success',
		                'message'  => 'un authorized access page due to permissions',
		               ], 401);
		      }
		}

      /**************************** fetch specific status ************************/

    }
}
