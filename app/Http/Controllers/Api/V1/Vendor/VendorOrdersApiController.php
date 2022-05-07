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
use Auth;
use App\Http\Requests\VendorApproveOrderApiRequest;
use App\Http\Requests\CancelOrderApiRequest;
use Carbon\Carbon;
use App\Http\Resources\Vendor\OrderGetItsDetailsResource;
use App\Http\Resources\Vendor\VendorOrdersApiResource;
use App\Http\Resources\Vendor\VendorApiSpecificInvoiceResource;
use Symfony\Component\HttpFoundation\Response;
use Gate;
use Illuminate\Support\Facades\Schema;
use App\Http\Resources\Vendor\VendorOrdersApiExceptResource;
use App\Http\Resources\Vendor\VendorInvoicesApiResource;
use DB;
use Laravel\Sanctum\Sanctum;
use Validator;
use Artisan;
use App\Mail\SendCustomerApprovedOrderMail;
use App\Mail\SendCustomerCancelledOrderMail;
use Illuminate\Support\Facades\Mail;
use App\Models\Vendorstaff;

class VendorOrdersApiController extends Controller
{
   /* public function ss(Request $request)
    {
      $startDate = '2021-03-04';
      $endDate   = '2021-07-04';

      $startDate_month = Carbon::parse($startDate)->format('m');
      $endDate_month   = Carbon::parse($endDate)->format('m');
      $diff = $endDate_month - $startDate_month;
      if ($diff >= 2) {
        
      }

      return $users = User::whereMonth('created_at', '04')->get();

     // return Auth::user();
     // $user_sanctum  = Sanctum::actingAs($user, ['*']);
        //return ($user_sanctum);
       // return AddVendor::find(1);
       // return  mt_rand(20000000,99999999);
        // return  mt_rand(40000000,99999999);
       // $order  = Order::find(9);
       // $now    = Carbon::now();
       // return $now->diffInHours($order->created_at);  
    }*/

    // get orders need approval
    public function orders_need_approval(Request $request)
    {
      Artisan::call('order:expire');
//     return  $latest_invoice = Invoice::withTrashed()->latest()->first();
      abort_if(Gate::denies('orders_need_approval_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
      //$PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
     
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      if ($ordered_by != '') {
        if (!Schema::hasColumn('products', $ordered_by)) {
          return response()->json(['message'  =>'order column not found',], 400);
        }
        if ($ordered_by == 'tags' || $ordered_by == 'categories') {
          $ordered_by = 'id';
        }
      }

      $auth_user  = Auth::user();
      $user_roles = $auth_user->roles->pluck('title')->toArray();
        if (in_array('Admin', $user_roles)) {
             $target_orders = Order::whereHas('orderDetails', function($q){
                              $q->where('approved', 0);
                              })->where('expired', 0)
                                   // ->where('status', '!=', 4)
                                   // ->where('status', '!=', 2)
                                    ->where('status', '!=', 'cancelled')
                                    ->where('status', '!=', 'in progress')
                                    ->where('status', 'pending')
                                    ->orWhereNull('status')
                                    ->skip(($page-1)*$PAGINATION_COUNT)
                                    ->take($PAGINATION_COUNT)
                                ->orderBy($ordered_by, $sort_type)
                                ->get();
             $orders        = VendorOrdersApiExceptResource::collection($target_orders);

             $total = Order::whereHas('orderDetails', function($q){
                              $q->where('approved', 0);
                              })->where('expired', 0)
                                   // ->where('status', '!=', 4)
                                   // ->where('status', '!=', 2)
                                    ->where('status', '!=', 'cancelled')
                                    ->where('status', '!=', 'in progress')
                                    ->where('status', 'pending')
                                    ->orWhereNull('status')
                                    ->count();

                return response()->json([
                        'status_code' => 200,
                        'message' => 'success',
                        'data'  => $orders,
                        'total' => $total,
                       ], 200);
        } // end case admin
         // case logged in user role is Vendor 
        elseif (in_array('Vendor', $user_roles)) {
                $vendor        = AddVendor::where('userid_id', Auth::user()->id)->first();
                $vendor_id     = $vendor->id;
                $target_orders = Order::whereHas('orderDetails', function($q) use ($vendor){
                              $q->where('vendor_id', $vendor->id)
                                ->where('approved', 0);
                              })->where('expired', 0)
                                ->where('status', '!=', 'cancelled')
                                ->where('status', '!=', 'in progress')
                                ->where('status', 'pending')
                                ->where('paid', 1)
                                ->skip(($page-1)*$PAGINATION_COUNT)
                                ->take($PAGINATION_COUNT)
                                ->orderBy($ordered_by, $sort_type)
                                ->get();
                foreach ($target_orders as $one) {
                  $one['orderDetails'] = $one->orderDetails->where('vendor_id', $vendor->id);
                }

                $total = Order::whereHas('orderDetails', function($q) use ($vendor){
                              $q->where('vendor_id', $vendor->id)
                                ->where('approved', 0);
                              })->where('expired', 0)
                                ->where('status', '!=', 'cancelled')
                                ->where('status', '!=', 'in progress')
                                ->where('status', 'pending')
                                ->where('paid', 1)
                                ->count();
                $orders        = VendorOrdersApiExceptResource::collection($target_orders);

                return response()->json([
                        'status_code' => 200,
                        'message' => 'success',
                        'data'  => $orders,
                        'total' => $total,
                       ], 200);
        } // end case vendor
        else{
          return response()->json([
                       'status_code' => 401,
                       // 'message' => 'success',
                        'message'  => 'un authorized access page due to permissions',
                 ], 401);
        } // end cases 
    }

    // vendor approve one order (multiple)
    public function approve_order(VendorApproveOrderApiRequest $request)
    {
      Artisan::call('order:expire');
      abort_if(Gate::denies('approve_orders'), Response::HTTP_FORBIDDEN, '403 Forbidden');
      	$order  = Order::findOrFail($request->order_id);
        
        $customer_name  = $order->user->name;
        $customer_email = $order->user->email;

        $user = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

         if (in_array('Admin', $user_roles)) 
         {
              $admin_name = Auth::user()->name;
              if (!in_array(2, $order->orderDetails->pluck('producttype_id')->toArray())) {
                  return response()->json([
                    'status_code' => 400,
                    'message' => 'order does not contain wholesale products',
                  ], 400);
              }
        $sum            = $order->orderDetails->where('producttype_id', 2)->sum('total');
        $count_approved = $order->orderDetails->where('producttype_id', 2)
                                              ->where('approved', 0)->count();
        // if ($order->status == 4) {
        /*if ($order->status == 'cancelled') {
            return response()->json([
                'message' => 'this order has been cancelled',
               ], 400);
        }*/
        if ($count_approved <= 0) {
            return response()->json([
                'status_code' => 400,                        
                'message' => 'you have already approved your orders',
               ], 400);
        }
        if ($order->expired == 1) {
            return response()->json([
                'status_code' => 400,
                'message' => 'this order is recently expired',
               ], 400);
        }
        
        $latest_invoice = Invoice::withTrashed()->latest()->first();
        if(is_null($latest_invoice)){
            $sequence_invoice = 40000000;
        }
        else{
            $sequence_invoice = $latest_invoice->invoice_number + 1; 
        }
        $target_details = $order->orderDetails->where('producttype_id', 2)->pluck('id');
        // check if vendor id is identical to order details vendor id
        Orderdetail::whereIn('id', $target_details)->update(['approved' => 1]);

        $target_products = $order->orderDetails->where('producttype_id', 2);
        if (count($target_products) > 1) 
        {
              foreach ($target_products as $target_product) 
              {
                  $exist_product = Product::find($target_product->product_id);
                  $exist_qty     = $exist_product->quantity;
                  //$exist_product->update(['quantity' => ($exist_qty - $target_product->quantity)]);
               }
               $invoice = Invoice::create([
                    'order_id'       => $order->id,
                    'vendor_id'      => $target_product->vendor_id,
                    'invoice_number' => $sequence_invoice,
                    'invoice_total'  => $sum,
                    'status'         => 1,
                  ]);
          // $sequence_invoice++;
                // $order->update(['status' => 2]);
        $order->update(['status' => 'in progress']);
       // $order->update(['approved' => 1]);
     
        // update quantity when user payment step
        // $order->load('orderDetails');

        $invoice_data = new VendorApiSpecificInvoiceResource($invoice);
        
        Mail::to($customer_email)->send(new SendCustomerApprovedOrderMail($customer_name, $admin_name, $order->order_number));
        return response()->json([
                'status_code' => 200,
              //  'message' => 'success',
                'message' => __('site_messages.vendor_accept_order'),
                'data' => $invoice_data,
               ], 200);
        } 
    }  // end case admin
         elseif (in_array('Vendor', $user_roles)) 
         {

          $vendor = AddVendor::where('userid_id', Auth::user()->id)->first();
          if (!in_array($vendor->id, $order->orderDetails->pluck('vendor_id')->toArray())) {
              return response()->json([
                  'message' => 'Unuauthorized, order does not match yours',
                 ], 401);
          }
        $sum            = $order->orderDetails->where('vendor_id', $vendor->id)->sum('total');
        $count_approved = $order->orderDetails->where('vendor_id', $vendor->id)
                                              ->where('approved', 0)->count();
        // if ($order->status == 4) {
        /*if ($order->status == 'cancelled') {
            return response()->json([
                'message' => 'this order has been cancelled',
               ], 400);
        }*/
        if ($count_approved <= 0) {
            return response()->json([
                'status_code' => 400,                        
                'message' => 'you have already approved your orders',
               ], 400);
        }
        if ($order->expired == 1) {
            return response()->json([
                'status_code' => 400,
                'message' => 'this order is recently expired',
               ], 400);
        }

        $target_products = $order->orderDetails->where('vendor_id', $vendor->id);
        foreach ($target_products as $target_product) {

          $exist_product = Product::find($target_product->product_id);
          if ($exist_product->producttype_id == 1) 
          {
              $exist_qty     = $exist_product->quantity;
              if ($exist_qty < $target_product->quantity) {
                return response()->json([
                    'status_code' => 400,
                    'message' => 'sorry, quantity not available of product '. $target_product->product->name,
                   ], 400);
              }
          }
         } 

        $latest_invoice = Invoice::withTrashed()->latest()->first();
        if(is_null($latest_invoice)){
            $sequence_invoice = 40000000;
        }
        else{
            $sequence_invoice = $latest_invoice->invoice_number + 1; 
        }
        $target_details = $order->orderDetails->where('vendor_id', $vendor->id)
                                ->where('producttype_id', 1)->pluck('id');
        // check if vendor id is identical to order details vendor id
        Orderdetail::whereIn('id', $target_details)->update(['approved' => 1]);

        $target_products = $order->orderDetails->where('vendor_id', $vendor->id);
        foreach ($target_products as $target_product) {
          $exist_product = Product::find($target_product->product_id);
            if ($exist_product->producttype_id == 1) 
            {
                $exist_qty     = $exist_product->quantity;
                $exist_product->update(['quantity' => ($exist_qty - $target_product->quantity)]);
            }
         } 
        // $order->update(['status' => 2]);
        $order->update(['status' => 'in progress']);
       // $order->update(['approved' => 1]);
        $invoice = Invoice::create([
            'order_id'       => $order->id,
            'vendor_id'      => $vendor->id,
            'invoice_number' => $sequence_invoice,
            'invoice_total'  => $sum,
            'status'         => 1,
        ]);
       // $sequence_invoice++;
     
        // update quantity when user payment step
        // $order->load('orderDetails');

        $invoice_data = new VendorApiSpecificInvoiceResource($invoice);
        
        Mail::to($customer_email)->send(new SendCustomerApprovedOrderMail($customer_name, $vendor->vendor_name, $order->order_number));
        return response()->json([
                'status_code' => 200,
              //  'message' => 'success',
                'message' => __('site_messages.vendor_accept_order'),
                'data' => $invoice_data,
               ], 200);
            
         } // end case vendor
         elseif (in_array('Manager', $user_roles))  // star manager
         {
            $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
            $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
            $vendor_id     = $vendor->id;
            $staff_stores = $exist_staff->stores->pluck('id')->toArray();

          if (!in_array($vendor->id, $order->orderDetails->pluck('vendor_id')->toArray())) {
              return response()->json([
                  'message' => 'Unuauthorized, order does not match yours',
                 ], 401);
          }
        $sum            = $order->orderDetails->where('vendor_id', $vendor->id)->sum('total');
        $count_approved = $order->orderDetails->where('vendor_id', $vendor->id)
                                              ->where('approved', 0)->count();
        // if ($order->status == 4) {
        /*if ($order->status == 'cancelled') {
            return response()->json([
                'message' => 'this order has been cancelled',
               ], 400);
        }*/
        if ($count_approved <= 0) {
            return response()->json([
                'status_code' => 400,                        
                'message' => 'you have already approved your orders',
               ], 400);
        }
        if ($order->expired == 1) {
            return response()->json([
                'status_code' => 400,
                'message' => 'this order is recently expired',
               ], 400);
        }

        $target_products = $order->orderDetails->where('vendor_id', $vendor->id);
        foreach ($target_products as $target_product) {

          $exist_product = Product::find($target_product->product_id);
          if ($exist_product->producttype_id == 1) 
          {
              $exist_qty     = $exist_product->quantity;
              if ($exist_qty < $target_product->quantity) {
                return response()->json([
                    'status_code' => 400,
                    'message' => 'sorry, quantity not available of product '. $target_product->product->name,
                   ], 400);
              }
          }
         } 

        $latest_invoice = Invoice::withTrashed()->latest()->first();
        if(is_null($latest_invoice)){
            $sequence_invoice = 40000000;
        }
        else{
            $sequence_invoice = $latest_invoice->invoice_number + 1; 
        }
        $target_details = $order->orderDetails->where('vendor_id', $vendor->id)
                                ->where('producttype_id', 1)->pluck('id');
        // check if vendor id is identical to order details vendor id
        Orderdetail::whereIn('id', $target_details)->update(['approved' => 1]);

        $target_products = $order->orderDetails->where('vendor_id', $vendor->id);
        foreach ($target_products as $target_product) {
          $exist_product = Product::find($target_product->product_id);
            if ($exist_product->producttype_id == 1) 
            {
                $exist_qty     = $exist_product->quantity;
                $exist_product->update(['quantity' => ($exist_qty - $target_product->quantity)]);
            }
         } 
        // $order->update(['status' => 2]);
        $order->update(['status' => 'in progress']);
       // $order->update(['approved' => 1]);
        $invoice = Invoice::create([
            'order_id'       => $order->id,
            'vendor_id'      => $vendor->id,
            'invoice_number' => $sequence_invoice,
            'invoice_total'  => $sum,
            'status'         => 1,
        ]);
      //  $sequence_invoice++;
     
        // update quantity when user payment step
        // $order->load('orderDetails');

        $invoice_data = new VendorApiSpecificInvoiceResource($invoice);
        
        Mail::to($customer_email)->send(new SendCustomerApprovedOrderMail($customer_name, $vendor->vendor_name, $order->order_number));
        return response()->json([
                'status_code' => 200,
                // 'message' => 'success',
                'message' => __('site_messages.vendor_accept_order'),
                'data' => $invoice_data,
               ], 200);
            
         } // end case manager
         else{
          return response()->json([
                      'status_code' => 401, 
                      // 'message'     => 'success',
                      'message'  => 'un authorized access page due to permissions',
                     ], 401);
         }
    }

    /*
public function approve_order(VendorApproveOrderApiRequest $request)
    {
      Artisan::call('order:expire');
      abort_if(Gate::denies('approve_orders'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $order  = Order::findOrFail($request->order_id);
        
        $customer_name  = $order->user->name;
        $customer_email = $order->user->email;

        if ($order->expired == 1) {
            return response()->json([
                'status_code' => 400,
                'message' => 'this order is recently expired',
               ], 400);
        }
        $vendor = AddVendor::where('userid_id', Auth::user()->id)->first();
        if (!in_array($vendor->id, $order->orderDetails->pluck('vendor_id')->toArray())) {
            return response()->json([
                'message' => 'Unuauthorized, order does not match yours',
               ], 401);
        }
        $sum            = $order->orderDetails->where('vendor_id', $vendor->id)->sum('total');
        $count_approved = $order->orderDetails->where('vendor_id', $vendor->id)
                                              ->where('approved', 0)->count();
        // if ($order->status == 4) {
        if ($order->status == 'cancelled') {
            return response()->json([
                'message' => 'this order has been cancelled',
               ], 400);
        }
        if ($count_approved <= 0) {
            return response()->json([
                'status_code' => 400,                        
                'message' => 'you have already approved your orders',
               ], 400);
        }
        /*if ($order->approved == 1) {
             return response()->json([
                'message' => 'this order has already been totallyyyy approved',
               ], 400);
        }*/
        /*$latest_invoice = Invoice::withTrashed()->latest()->first();
        if(is_null($latest_invoice)){
            $sequence_invoice = 40000000;
        }
        else{
            $sequence_invoice = $latest_invoice->invoice_number + 1; 
        }
        $target_details = $order->orderDetails->where('vendor_id', $vendor->id)->pluck('id');
        // check if vendor id is identical to order details vendor id
        Orderdetail::whereIn('id', $target_details)->update(['approved' => 1]);

        $target_products = $order->orderDetails->where('vendor_id', $vendor->id);
        foreach ($target_products as $target_product) {
          $exist_product = Product::find($target_product->product_id);
          $exist_qty     = $exist_product->quantity;
          $exist_product->update(['quantity' => ($exist_qty - $target_product->quantity)]);
         } 
        // $order->update(['status' => 2]);
        $order->update(['status' => 'in progress']);
       // $order->update(['approved' => 1]);
        $invoice = Invoice::create([
            'order_id'       => $order->id,
            'vendor_id'      => $vendor->id,
            'invoice_number' => $sequence_invoice,
            'invoice_total'  => $sum,
            'status'         => 1,
        ]);
     
        // update quantity when user payment step
        // $order->load('orderDetails');

        $invoice_data = new VendorApiSpecificInvoiceResource($invoice);
        
        Mail::to($customer_email)->send(new SendCustomerApprovedOrderMail($customer_name, $vendor->vendor_name, $order->order_number));
        return response()->json([
                'status_code' => 200,
                'message' => 'success',
                'data' => $invoice_data,
               ], 200);
    }
    */

    public function cancel_order(CancelOrderApiRequest $request)
    {
       Artisan::call('order:expire');
       //abort_if(Gate::denies('cancel_orders'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $order  = Order::findOrFail($request->order_id);
        
        $customer_name  = $order->user->name;
        $customer_email = $order->user->email;

        $user = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

        //if ($order->paid == 0 && $order->approved == 0 && $order->expired == 0) {
          if ($order->expired == 0) 
          {
              if ($order->status == 'delivered') {
                  return response()->json([
                  'status_code' => 400,
                  'message' => 'this order can not be cancelled',
                 ], 400);
              }
            // case pending
             else{
                    if (in_array('Admin', $user_roles)) 
                    {
                            $admin_name = Auth::user()->name;
                            if (!in_array(2, $order->orderDetails->pluck('producttype_id')->toArray())) {
                                return response()->json([
                                  'status_code' => 400,
                                  'message' => 'order does not contain wholesale products',
                                ], 400);
                            }
                      $sum            = $order->orderDetails->where('producttype_id', 2)->sum('total');
                      $count_approved = $order->orderDetails->where('producttype_id', 2)
                                                            ->where('approved', 0)->count();
                      // if ($order->status == 4) {
                      /*if ($order->status == 'cancelled') {
                          return response()->json([
                              'message' => 'this order has been cancelled',
                             ], 400);
                      }*/
                      if ($count_approved <= 0) {
                          return response()->json([
                              'status_code' => 400,                        
                              'message' => 'you have already approved your orders',
                             ], 400);
                      }
                      if ($order->expired == 1) {
                          return response()->json([
                              'status_code' => 400,
                              'message' => 'this order is recently expired',
                             ], 400);
                      }
                    
                      $target_details = $order->orderDetails->where('producttype_id', 2)->pluck('id');
                      // check if vendor id is identical to order details vendor id
                      Orderdetail::whereIn('id', $target_details)->update(['approved' => 2]);

                      $target_products = $order->orderDetails->where('producttype_id', 2);
                      $order->update(['status' => 'cancelled']);
                
                    // mail sent to user 
                        Mail::to($customer_email)->send(new SendCustomerCancelledOrderMail($customer_name, $admin_name, $order->order_number));
                         
                          return response()->json([
                          'status_code' => 400,
                          // 'message' => 'your order has been cancelled successfully',
                          'message' => __('site_messages.vendor_reject_order'),
                         ], 200);
                  }  // end case admin
                  elseif (in_array('Vendor', $user_roles)) 
                  {

                        $vendor = AddVendor::where('userid_id', Auth::user()->id)->first();
                        if (!in_array($vendor->id, $order->orderDetails->pluck('vendor_id')->toArray())) {
                            return response()->json([
                                'message' => 'Unuauthorized, order does not match yours',
                               ], 401);
                        }
                      $sum   = $order->orderDetails->where('vendor_id', $vendor->id)->sum('total');
                      $count_approved = $order->orderDetails->where('vendor_id', $vendor->id)
                                                            ->where('approved', 0)->count();
                      // if ($order->status == 4) {
                     /* if ($order->status == 'cancelled') {
                          return response()->json([
                              'message' => 'this order has been cancelled',
                             ], 400);
                      }*/
                      if ($count_approved <= 0) {
                          return response()->json([
                              'status_code' => 400,                        
                              'message' => 'you have already approved your orders',
                             ], 400);
                      }
                      if ($order->expired == 1) {
                          return response()->json([
                              'status_code' => 400,
                              'message' => 'this order is recently expired',
                             ], 400);
                      }

                      $target_products = $order->orderDetails->where('vendor_id', $vendor->id);
                      $target_details = $order->orderDetails->where('vendor_id', $vendor->id)
                                              ->where('producttype_id', 1)->pluck('id');
                      // check if vendor id is identical to order details vendor id
                      Orderdetail::whereIn('id', $target_details)->update(['approved' => 2]);
                      $target_products = $order->orderDetails->where('vendor_id', $vendor->id);
                      $order->update(['status' => 'cancelled']);

                       $vendor_name = $vendor->vendor_name;
                        Mail::to($customer_email)->send(new SendCustomerCancelledOrderMail($customer_name, $vendor_name, $order->order_number));
                         
                          return response()->json([
                          'status_code' => 400,
                         // 'message' => 'your order has been cancelled successfully',
                          'message' => __('site_messages.vendor_reject_order'),
                         ], 200);
              } // end case vendor   
                  elseif (in_array('Manager', $user_roles)) 
                  {
                      $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
                      $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
                      $vendor_id     = $vendor->id;
                      $staff_stores = $exist_staff->stores->pluck('id')->toArray();
                        
                        if (!in_array($vendor->id, $order->orderDetails->pluck('vendor_id')->toArray())) {
                            return response()->json([
                                'message' => 'Unuauthorized, order does not match yours',
                               ], 401);
                        }
                      $sum   = $order->orderDetails->where('vendor_id', $vendor->id)->sum('total');
                      $count_approved = $order->orderDetails->where('vendor_id', $vendor->id)
                                                            ->where('approved', 0)->count();
                      // if ($order->status == 4) {
                     /* if ($order->status == 'cancelled') {
                          return response()->json([
                              'message' => 'this order has been cancelled',
                             ], 400);
                      }*/
                      if ($count_approved <= 0) {
                          return response()->json([
                              'status_code' => 400,                        
                              'message' => 'you have already approved your orders',
                             ], 400);
                      }
                      if ($order->expired == 1) {
                          return response()->json([
                              'status_code' => 400,
                              'message' => 'this order is recently expired',
                             ], 400);
                      }

                      $target_products = $order->orderDetails->where('vendor_id', $vendor->id);
                      $target_details = $order->orderDetails->where('vendor_id', $vendor->id)
                                              ->where('producttype_id', 1)->pluck('id');
                      // check if vendor id is identical to order details vendor id
                      Orderdetail::whereIn('id', $target_details)->update(['approved' => 2]);
                      $target_products = $order->orderDetails->where('vendor_id', $vendor->id);
                      $order->update(['status' => 'cancelled']);

                       $vendor_name = $vendor->vendor_name;
                        Mail::to($customer_email)->send(new SendCustomerCancelledOrderMail($customer_name, $vendor_name, $order->order_number));
                         
                          return response()->json([
                          'status_code' => 400,
                          // 'message' => 'your order has been cancelled successfully',
                           'message' => __('site_messages.vendor_reject_order'),
                         ], 200);
              } // end case manager     
              else{
                   return response()->json([
                      'status_code' => 401,
                      'message' => 'restricted due to permissions',
                     ], 401);
              }                
            }
        } // end if
        elseif($order->approved == 1) {
               return response()->json([
                'status_code' => 400,
                'message' => 'this order can not be cancelled',
               ], 400);
        }
        else{
            return response()->json([
                'status_code' => 400,
                'message' => 'this order is not pending to be cancelled',
               ], 400);
        } // end else 
    }

    public function cancel_order_old(CancelOrderApiRequest $request)
    {
    //    abort_if(Gate::denies('cancel_orders'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        // make integers 1 pending 2 inprogress 3 delivered 4 cancelled
        $order      = Order::findOrFail($request->order_id);
        $customer_name  = $order->user->name;
        $customer_email = $order->user->email;

        //if ($order->paid == 0 && $order->approved == 0 && $order->expired == 0) {
          if ($order->approved == 0 && $order->expired == 0) {
            // case cancelled
            if ($order->status == 'cancelled') {
            //if ($order->status == 4) {
               return response()->json([
                'status_code' => 400,
                'message' => 'this order has already been cancelled',
               ], 400);
            }
            // case delivered
            //elseif ($order->status == 3) {
            elseif ($order->status == 'delivered') {
                return response()->json([
                'status_code' => 400,
                'message' => 'this order can not be cancelled',
               ], 400);
            }
            // case inprogress
            //elseif ($order->status == 2) {
            elseif ($order->status == 'in progress') {
                return response()->json([
                'status_code' => 400,
                'message' => 'this order can not be cancelled',
               ], 400);
            }
            // case pending
            else{
               // $order->update(['status' => 4]);
              $vendor = AddVendor::where('userid_id', Auth::user()->id)->first();
          if (!in_array($vendor->id, $order->orderDetails->pluck('vendor_id')->toArray())) {
              return response()->json([
                  'message' => 'Unuauthorized, order does not match yours',
                 ], 401);
            }
        $v_total = $order->orderDetails->where('vendor_id', $vendor->id)->sum('total');
        Orderdetail::where('order_id', $order->id)->where('vendor_id', $vendor->id)
                                  ->update(['approved' => 2]);
        $actual_total = $order->order_total - $v_total;

              $order->update(['order_total' => $actual_total]);
              $vendor_name = $vendor->vendor_name;
              Mail::to($customer_email)->send(new SendCustomerCancelledOrderMail($customer_name, $vendor_name, $order->order_number));
                $order->update(['status' => 'cancelled']);
                return response()->json([
                'status_code' => 400,
                'message' => 'your order has been cancelled successfully',
               ], 200);
            }
        } // end if
        elseif($order->approved == 1) {
               return response()->json([
                'status_code' => 400,
                'message' => 'this order can not be cancelled',
               ], 400);
        }
        else{
            return response()->json([
                'status_code' => 400,
                'message' => 'this order is not pending to be cancelled',
               ], 400);
        } // end else 
    }

// vendor access his all orders
    public function show_orders(Request $request)
    {
      abort_if(Gate::denies('show_orders_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
      //$PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
      $user = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();

      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;

      if (in_array('Admin', $user_roles)) {
        $data = AdminOrdersApiResource::collection(Order::skip(($page-1)*$PAGINATION_COUNT)
                        ->take($PAGINATION_COUNT)->orderBy($ordered_by, $sort_type)->get());
            return response()->json([
                    'status_code' => 200, 
                    'message'     => 'success',
                    'data'  => $data,
                    'total' => Order::count(),
            ], 200);
      } 
       // case logged in user role is Vendor (show only his invoices)
      elseif (in_array('Vendor', $user_roles)) {
              $vendor        = AddVendor::where('userid_id', Auth::user()->id)->first();
              $mine          = Orderdetail::where('vendor_id', $vendor->id)->pluck('order_id');
              $target_orders = Order::whereIn('id', $mine)->skip(($page-1)*$PAGINATION_COUNT)
                                        ->take($PAGINATION_COUNT)->orderBy($ordered_by, $sort_type)
                                        ->get();
              $total         = Order::whereIn('id', $mine)->count();
              $orders        = VendorOrdersApiResource::collection($target_orders);
                return response()->json([
                        'status_code' => 200, 
                        'message'     => 'success',
                        'data'  => $orders,
                        'total' => $total,
                       ], 200);
      }
      else{
        return response()->json([
                'status_code' => 400, 
               // 'message'     => 'success',
                'message'  => 'un authorized access page due to permissions',
               ], 401);
      }
    }

// vendor access specific order
    public function show_specific_order(Order $order)
    {
        abort_if(Gate::denies('show_specific_order'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $user = Auth::user();
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
            $vendor = AddVendor::where('userid_id', Auth::user()->id)->first();
            $order_data = Orderdetail::where('vendor_id', $vendor->id)->where('order_id', $order->id)
                                                                      ->get();
                return response()->json([
                        'status_code' => 200, 
                        'message'     => 'success',
                        'data' => $order_data], 200);
        }
        else{
            return response()->json([
                    'status_code' => 400, 
                    'message'     => 'success',
                    'message'  => 'un authorized access page due to permissions',
                   ], 401);
          }
    }
}
