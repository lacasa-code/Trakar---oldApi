<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\SearchApisRequest;
use Auth;
use App\Models\Order;
use App\Models\Orderdetail;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use App\Models\AddVendor;
use Carbon\Carbon;
use App\Http\Resources\Vendor\VendorOrdersApiExceptResource;
use Illuminate\Support\Facades\Schema;

class VendorSearchApprovalOrdersApiController extends Controller
{
    public function search_orders_need_approval(SearchApisRequest $request)
    {
 // default 1 id asc for page ordered_by sort_type
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      //$PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
      $search_index = $request->search_index;

        $user       = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();
// case admin search
        if (in_array('Admin', $user_roles)) {
             $get_orders   = Order::whereHas('orderDetails', function($q) use ($search_index){
                                      $q->where('approved', 0)
                                      ->whereHas('store', function($q) use ($search_index){
                                        $q->where('name', 'like', "%{$search_index}%");
                                      })
                                      ->orWhereHas('vendor', function($q) use ($search_index){
                                        $q->where('vendor_name', 'like', "%{$search_index}%");
                                      })
                                      ->orWhereHas('product', function($q) use ($search_index){
                                        $q->where('name', 'like', "%{$search_index}%");
                                      })
                                      ->orWhereHas('order', function($q) use ($search_index){
                                        $q->where('order_number', 'like', "%{$search_index}%")
                                        ->orWhere('order_total', 'like', "%{$search_index}%");
                                      });
                    })->where('expired', 0)
                      ->where('status', '!=', 'in progress')
                      ->where('status', '!=', 'cancelled')
                      ->where('status', 'pending')
                      // ->orWhereNull('status')
                      ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                      ->orderBy($ordered_by, $sort_type)->get();

        $orders        = VendorOrdersApiExceptResource::collection($get_orders);

        $total = Order::whereHas('orderDetails', function($q) use ($search_index){
                                      $q->where('approved', 0)
                                      ->whereHas('store', function($q) use ($search_index){
                                        $q->where('name', 'like', "%{$search_index}%");
                                      })
                                      ->orWhereHas('vendor', function($q) use ($search_index){
                                        $q->where('vendor_name', 'like', "%{$search_index}%");
                                      })
                                      ->orWhereHas('product', function($q) use ($search_index){
                                        $q->where('name', 'like', "%{$search_index}%");
                                      })
                                      ->orWhereHas('order', function($q) use ($search_index){
                                        $q->where('order_number', 'like', "%{$search_index}%")
                                        ->orWhere('order_total', 'like', "%{$search_index}%");
                                      });
                    })->where('expired', 0)
                     ->where('status', '!=', 'in progress')
                      ->where('status', '!=', 'cancelled')
                      ->where('status', 'pending')
                     // ->orWhereNull('status')
                      ->count();
                    return response()->json([
                       'status_code' => 200,
                       'message' => 'success',
                        'data' => $orders,
                        'total' => $total,
                    ], 200);
        } 
           // case vendor search
        elseif (in_array('Vendor', $user_roles)) {
        	    $vendor        = AddVendor::where('userid_id', Auth::user()->id)->first();
              $vendor_id     = $vendor->id;
                /*$get_orders   = Order::where('order_number', 'like', "%{$search_index}%")
                                ->orWhere('order_total', '=', "%{$search_index}%")
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
                                })->where('approved', '!=', 1)
                                ->where('expired', 0)->where('status', '!=', 4)
                                ->orWhereNull('status')->whereHas('orderDetails', function($q) use ($vendor){
                              $q->where('approved', 0)->where('vendor_id', $vendor->id);
                              })
                    ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                    ->orderBy($ordered_by, $sort_type)->get();*/

          $get_orders   = Order::whereHas('orderDetails', function($q) use ($search_index, $vendor){
                                      $q->where('vendor_id', $vendor->id)->where('approved', 0)
                                      ->whereHas('store', function($q) use ($search_index){
                                        $q->where('name', 'like', "%{$search_index}%");
                                      })
                                      ->orWhereHas('vendor', function($q) use ($search_index){
                                        $q->where('vendor_name', 'like', "%{$search_index}%");
                                      })
                                      ->orWhereHas('product', function($q) use ($search_index){
                                        $q->where('name', 'like', "%{$search_index}%");
                                      })
                                      ->orWhereHas('order', function($q) use ($search_index){
                                        $q->where('order_number', 'like', "%{$search_index}%")
                                          ->orWhere('order_total', 'like', "%{$search_index}%");
                                      });
                                })
                                ->where('approved', '!=', 1)
                                ->where('expired', 0)
                                ->where('status', '!=', 'in progress')
                                ->where('status', '!=', 'cancelled')
                                ->where('status', 'pending')
                               // ->orWhereNull('status')
                                ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                ->orderBy($ordered_by, $sort_type)->get();
            foreach ($get_orders as $one) {
                  $one['orderDetails'] = $one->orderDetails->where('vendor_id', $vendor->id);
                }
        $orders        = VendorOrdersApiExceptResource::collection($get_orders);

        $total = Order::whereHas('orderDetails', function($q) use ($search_index, $vendor){
                                      $q->where('vendor_id', $vendor->id)->where('approved', 0)
                                      ->whereHas('store', function($q) use ($search_index){
                                        $q->where('name', 'like', "%{$search_index}%");
                                      })
                                      ->orWhereHas('vendor', function($q) use ($search_index){
                                        $q->where('vendor_name', 'like', "%{$search_index}%");
                                      })
                                      ->orWhereHas('product', function($q) use ($search_index){
                                        $q->where('name', 'like', "%{$search_index}%");
                                      })
                                      ->orWhereHas('order', function($q) use ($search_index){
                                        $q->where('order_number', 'like', "%{$search_index}%")
                                          ->orWhere('order_total', 'like', "%{$search_index}%");
                                      });
                                })
                                ->where('approved', '!=', 1)
                                ->where('expired', 0)
                                ->where('status', '!=', 'in progress')
                                ->where('status', '!=', 'cancelled')
                                ->where('status', 'pending')
                                //->orWhereNull('status')
                                ->count();
                    return response()->json([
                      'status_code' => 200,
                       'message' => 'success',
                        'data' => $orders,
                        'total' => $total,
                    ], 200);
        }
        else{
            return response()->json([
                    'status_code' => 401,
                    'message'  => 'un authorized access page due to permissions',
                   ], 401);
          }
     }
    // end search orders
}
