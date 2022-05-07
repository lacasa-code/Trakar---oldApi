<?php

namespace App\Http\Controllers\Api\V1\Admin;

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
use App\Http\Resources\Admin\AdminWholesaleOrdersApiResource;
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
use App\Models\Productview;

class AdminWholesaleOrdersApiController extends Controller
{
	public function wholesale_orders(Request $request)
	{
		    Artisan::call('order:expire');
		    abort_if(Gate::denies('wholesale_orders_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
		     
		      $default_count = \Config::get('constants.pagination.items_per_page');
		      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
		      
		      $user = Auth::user();
		      $user_roles = $user->roles->pluck('title')->toArray();

		      $request->page == '' ? $page = 1 : $page = $request->page;
		      $request->sort_type == '' ? $sort_type = 'DESC' : $sort_type = $request->sort_type;
		      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
		      $request->fetch == '' ? $fetch = 'all' : $fetch = $request->fetch;

		 
	    if (in_array('Admin', $user_roles)) 
	    {
			if($fetch == 'all')
            {
                    	
                    	$wholesale_orderss = Order::whereHas('orderDetails', function($q){
		                             $q->where('producttype_id', '!=', 1);
		                               // ->where('approved', 1);
		                              })->where('paid', 1)->orderBy('id', 'DESC')->get();

		        $wholesale_orderss->sortByDesc('leftApproval');
		        $wholesale_orders = $wholesale_orderss->skip(($page-1)*$PAGINATION_COUNT)
			                                        ->take($PAGINATION_COUNT);

			        foreach ($wholesale_orders as $one) {
			        	$one['need_approval'] = $one->orderDetails->where('producttype_id', 2)
	                                       ->where('approved', 0)->count() > 0 && $one->expired != 1 ? 1 : 0;
		               $one['orderDetails'] = $one->orderDetails->where('producttype_id', '!=', 1);
		               $one['wholesale_total']  = $one->orderDetails->where('producttype_id', '!=', 1)->sum('total');
		              }

			          $total = Order::whereHas('orderDetails', function($q){
		                             $q->where('producttype_id', '!=', 1);
		                               // ->where('approved', 1);
		                              })
		              ->where('paid', 1)
		              //->where('status', '!=', 'cancelled')
		              //->where('expired', '!=', 1)
		              ->count();
		              
				        $data = AdminWholesaleOrdersApiResource::collection($wholesale_orders);
				            return response()->json([
				                    'status_code' => 200, 
				                    'message'     => 'success',
				                    'data'  => $data,
				                    'total' => $total, //Order::count(),
				            ], 200);
            }else{
                    	/* $wholesale_orders = Order::where('status', $fetch)->whereHas('orderDetails', function($q){
		                             $q->where('producttype_id', '!=', 1);
		                              })
						              ->where('paid', 1)
						              ->skip(($page-1)*$PAGINATION_COUNT)
							          ->take($PAGINATION_COUNT)
							          ->orderBy($ordered_by, $sort_type)
							          ->get(); */

			          $wholesale_orders = Order::whereHas('orderDetails', function($q) use ($fetch){
		                             $q->where('approved', $fetch)->where('producttype_id', '!=', 1);
		                              })
				                        ->where('paid', 1)
				      	                ->skip(($page-1)*$PAGINATION_COUNT)
				                        ->take($PAGINATION_COUNT)
				                        ->orderBy($ordered_by, $sort_type)->get();

			        foreach ($wholesale_orders as $one) {
			        	$one['need_approval'] = $one->orderDetails->where('producttype_id', 2)
	                                       ->where('approved', 0)->count() > 0 && $one->expired != 1 ? 1 : 0;
		               $one['orderDetails'] = $one->orderDetails->where('producttype_id', '!=', 1);
		               $one['wholesale_total']  = $one->orderDetails->where('producttype_id', '!=', 1)->sum('total');
		              }

			          $total = Order::whereHas('orderDetails', function($q) use ($fetch){
		                             $q->where('approved', $fetch)->where('producttype_id', '!=', 1);
		                              })->where('paid', 1)->count();
		              
				        $data = AdminWholesaleOrdersApiResource::collection($wholesale_orders);
				            return response()->json([
				                    'status_code' => 200, 
				                    'message'     => 'success',
				                    'data'  => $data,
				                    'total' => $total, //Order::count(),
				            ], 200);
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

    public function search_wholesale_orders(SearchApisRequest $request)
	{
		
		/*return $created_at = Productview::latest()->first()->created_at->strtotime("-30 days");
		
		$one = date(Carbon::createFromFormat('Y-m-d H:i:s', $created_at)->format('Y-m-d'));
		return $one->subDays(7)->toDateString();
		$to   = Carbon::today()->subDays(7)->toDateString();
		
	   // return $to;
	   // return $created_at;
		//$start = \Carbon\Carbon::parse()->format('Y-m-d');
		//return $start;
		return $from = $to->subDays(7)->toDateString();
		return $from = $created_at->subDays(7)->toDateString();*/
		
		    Artisan::call('order:expire');
		    $default_count = \Config::get('constants.pagination.items_per_page');
		    $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
		      
		      $user = Auth::user();
		      $user_roles = $user->roles->pluck('title')->toArray();

		      $request->page == '' ? $page = 1 : $page = $request->page;
		      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
		      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
		      $search_index = $request->search_index;

		           if (in_array('Admin', $user_roles)) 
			       {
			       	$wholesale_orders = Order::whereHas('orderDetails', function($q){
		                            $q->where('producttype_id', '!=', 1);
		                               // ->where('approved', 1);
		                             })
		              ->where('paid', 1)
		             // ->where('status', '!=', 'cancelled')
		             // ->where('expired', '!=', 1)
		              ->where('order_number', 'like', "%{$search_index}%")
			       	  //->orWhere('order_total', 'like', "%{$search_index}%")
		              ->skip(($page-1)*$PAGINATION_COUNT)
			          ->take($PAGINATION_COUNT)
			          ->orderBy($ordered_by, $sort_type)
			          ->get();

			        foreach ($wholesale_orders as $one) {
			        	$one['need_approval'] = $one->orderDetails->where('producttype_id', 2)
	                                       ->where('approved', 0)->count() > 0 && $one->expired != 1 ? 1 : 0;
		               $one['orderDetails'] = $one->orderDetails->where('producttype_id', '!=', 1);
		               $one['wholesale_total']  = $one->orderDetails->where('producttype_id', '!=', 1)->sum('total');
		              }

			          $total = Order::whereHas('orderDetails', function($q){
		                             $q->where('producttype_id', '!=', 1);
		                               // ->where('approved', 1);
		                              })
			          ->where('paid', 1)
		             // ->where('status', '!=', 'cancelled')
		             // ->where('expired', '!=', 1)
			          ->where('order_number', 'like', "%{$search_index}%")
			       	 // ->orWhere('order_total', 'like', "%{$search_index}%")
		              ->count();
		              
				        $data = AdminWholesaleOrdersApiResource::collection($wholesale_orders);
				            return response()->json([
				                    'status_code' => 200, 
				                    'message'     => 'success',
				                    'data'  => $data,
				                    'total' => $total, //Order::count(),
				            ], 200);

			       }else{
			        return response()->json([
			                'status_code' => 401, 
			                // 'message'     => 'success',
			                'message'  => 'un authorized access page due to permissions',
			               ], 401);
	 		      }
	}

	public function search_wholesale_invoices(SearchApisRequest $request)
	{
		    Artisan::call('order:expire');
		    abort_if(Gate::denies('wholesale_invoices_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
	
		      $default_count = \Config::get('constants.pagination.items_per_page');
		      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
		      
		      $user = Auth::user();
		      $user_roles = $user->roles->pluck('title')->toArray();

		      $request->page == '' ? $page = 1 : $page = $request->page;
		      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
		      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
		      $search_index = $request->search_index;

		           if (in_array('Admin', $user_roles)) 
			       {
			       	   $wholesale_orders = Order::whereHas('orderDetails', function($q){
		                             $q->where('producttype_id', '!=', 1)
		                              ->where('approved', 1);
		                              })
		              ->where('paid', 1)
		              ->where('status', '!=', 'cancelled')
		              ->where('expired', '!=', 1)
		              ->pluck('id')->toArray();

		        $top_total_invoices  = Invoice::whereIn('order_id', $wholesale_orders)
		                                    ->where('invoice_number', 'like', "%{$search_index}%")
		                                    ->orWhere('invoice_total', 'like', "%{$search_index}%")
		                                    ->whereIn('order_id', $wholesale_orders)
		                                   ->orWhereHas('order', function($q) use ($search_index, $wholesale_orders){
                                            $q->where('order_number', 'like', "%{$search_index}%")
                                             ->whereIn('id', $wholesale_orders);
                                           })
                                            ->skip(($page-1)*$PAGINATION_COUNT)
									        ->take($PAGINATION_COUNT)
									        ->orderBy($ordered_by, $sort_type)
									        ->get();

                    $total_invoices  = Invoice::whereIn('order_id', $wholesale_orders)
                                            ->where('invoice_number', 'like', "%{$search_index}%")
                                           ->orWhere('invoice_total', 'like', "%{$search_index}%")
		                                    ->whereIn('order_id', $wholesale_orders)
		                                   ->orWhereHas('order', function($q) use ($search_index, $wholesale_orders){
                                            $q->where('order_number', 'like', "%{$search_index}%")
                                             ->whereIn('id', $wholesale_orders);
                                           })
                                            ->count();

                    $data  = AdminInvoicesApiResource::collection($top_total_invoices);
			        $total = $total_invoices;
				        return response()->json([
				                    'status_code' => 200, 
				                    'message'     => 'success',
				                    'data'  => $data,
				                    'total' => $total, //Order::count(),
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

    public function wholesale_invoices(Request $request)
	{
		    Artisan::call('order:expire');
		    // abort_if(Gate::denies('wholesale_invoices_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
	
		      $default_count = \Config::get('constants.pagination.items_per_page');
		      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
		      
		      $user = Auth::user();
		      $user_roles = $user->roles->pluck('title')->toArray();

		      $request->page == '' ? $page = 1 : $page = $request->page;
		      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
		      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
		      $request->fetch == '' ? $fetch = 'all' : $fetch = $request->fetch;

		 
		           if (in_array('Admin', $user_roles)) 
			       {
				      	$wholesale_orders = Order::whereHas('orderDetails', function($q){
		                             $q->where('producttype_id', '!=', 1)
		                              ->where('approved', 1);
		                              })
		              ->where('paid', 1)
		              ->where('status', '!=', 'cancelled')
		              ->where('expired', '!=', 1)
		              ->pluck('id')->toArray();
		             // return $wholesale_orders;

		              $top_total_invoices  = Invoice::whereIn('order_id', $wholesale_orders)
		                                            ->skip(($page-1)*$PAGINATION_COUNT)
											        ->take($PAGINATION_COUNT)
											        ->orderBy($ordered_by, $sort_type)
											        ->get();

                      $total_invoices  = Invoice::whereIn('order_id', $wholesale_orders)
                                                    ->count();

                    $data  = AdminInvoicesApiResource::collection($top_total_invoices);
			        $total = $total_invoices;
				        return response()->json([
				                    'status_code' => 200, 
				                    'message'     => 'success',
				                    'data'  => $data,
				                    'total' => $total, //Order::count(),
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
