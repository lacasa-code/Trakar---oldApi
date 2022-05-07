<?php

namespace App\Http\Controllers\Api\V1\User\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\Vendor\VendorOrdersApiResource;
use App\Models\Order;
use App\Models\User;
use App\Models\Invoice;
use App\Models\AddVendor;
use App\Models\Product;
use App\Models\Orderdetail;
use Auth;
use Gate;
use App\Http\Requests\ReportApiRequest;  
use Validator;
use Carbon\Carbon;
use App\Models\Ticket;
use App\Models\Productquestion;
use Artisan;
use App\Models\Vendorstaff;

class DayMonthFilterApiController extends Controller
{
    public function vendor_month_days_filter(Request $request)
    {
        Artisan::call('order:expire');
        if (!$request->header('Authorization'))
        {
           return response()->json(['errors' => 'No Authorization token Found'], 400);
        }

      if (!$request->has('from') && !$request->has('to') || ($request->from == '' && $request->to == ''))
      {
       // $from = Carbon::today()->subMonth()->toDateString();
       $from_first = User::first()->created_at;
       $from = Carbon::parse($from_first)->format('Y-m-d');
       // return $from;
      // $from = Carbon::today()->subDays(7)->toDateString();
       $to   = Carbon::today()->toDateString();
      // return $to;

        $startDate = $from;//.' 00:00:00';
        $endDate   = $to;//.' 23:59:59';
      }
      else // case sent date filter (make validation)
      {
        $validator = Validator::make($request->all(), [
          'from' => 'required_with:to|date|date_format:Y-m-d|before_or_equal:to',
          'to'   => 'required_with:from|date|date_format:Y-m-d|after_or_equal:from',
        ]);
        if ($validator->fails()) {
          return response()->json(['errors' => $validator->errors()], 400);
        }

        $from = $request->from;
        $to   = $request->to;

        $startDate = $from;//.' 00:00:00';
        $endDate   = $to;//.' 23:59:59';
      }

      $user = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();

      $begin = Carbon::createFromFormat('Y-m-d', $startDate);
      $end   = Carbon::createFromFormat('Y-m-d', $endDate);
      $diff  = $end->diffInDays($begin);
      //return $diff;

      if ($diff > 30) 
      {  // if search with months 
         if (in_array('Admin', $user_roles)) 
        {        
              $startDate_month = Carbon::parse($startDate)->format('m');
              $endDate_month   = Carbon::parse($endDate)->format('m');
        
            $top_total_products = Product::where('approved', 1)
                                          ->whereMonth('created_at', '>=', $startDate_month)
                                          ->whereMonth('created_at', '<=', $endDate_month)->count();

            $tickets = Ticket::whereMonth('created_at', '>=', $startDate_month)
                            ->whereMonth('created_at', '<=', $endDate_month)->count();

            $top_total_customers = User::whereHas('roles', function($q){
                                          $q->where('title', '!=', 'Admin');
                                        })->whereMonth('created_at', '>=', $startDate_month)
                                          ->whereMonth('created_at', '<=', $endDate_month)->count();

            $pending_vendors = AddVendor::where('approved', '!=', 1)
                                          ->where('complete', 1)
                                          ->where('declined', 0)
                                          ->where('rejected', 0)
                                          ->whereMonth('created_at', '>=', $startDate_month)
                                          ->whereMonth('created_at', '<=', $endDate_month)
                                          ->count();

            $actual_vendors = AddVendor::where('complete', 1)
                                          ->where('declined', 0)
                                          ->where('rejected', 0)
                                          ->whereMonth('created_at', '>=', $startDate_month)
                                          ->whereMonth('created_at', '<=', $endDate_month)
                                          ->count();

            $prod_questions = Productquestion::whereMonth('created_at', '>=', $startDate_month)
                                          ->whereMonth('created_at', '<=', $endDate_month)->count();
            
            $top_total_orders = Order::whereHas('orderDetails', function($q) use ($startDate_month, $endDate_month){
                             $q->whereMonth('created_at', '>=', $startDate_month)
                                ->whereMonth('created_at', '<=', $endDate_month)
                                ->where('approved', 1);
                              })->count();

           /* $pending_wholesale_orders = Order::whereHas('user', function($q){
              $q->whereHas('roles', function($q)
              {
                $q->where('title', 'Vendor');
              });
            })
              ->whereHas('orderDetails', function($q) use ($startDate_month, $endDate_month){
                             $q->whereMonth('created_at', '>=', $startDate_month)
                                ->whereMonth('created_at', '<=', $endDate_month)
                                ->where('approved', '!=', 1);
                              })
              ->where('paid', 1)
              ->where('status', 'pending')
              ->count();

              $wholesale_orders = Order::whereHas('user', function($q){
              $q->whereHas('roles', function($q)
              {
                $q->where('title', 'Vendor');
              });
            })
              ->whereHas('orderDetails', function($q) use ($startDate_month, $endDate_month){
                             $q->whereMonth('created_at', '>=', $startDate_month)
                                ->whereMonth('created_at', '<=', $endDate_month);
                               // ->where('approved', 1);
                              })
              ->where('paid', 1)
              ->where('status', '!=', 'cancelled')
              ->where('expired', '!=', 1)
              ->count();*/
              $pending_wholesale_orders = Order::whereHas('orderDetails', function($q) use ($startDate_month, $endDate_month){
                             $q->whereMonth('created_at', '>=', $startDate_month)
                                ->whereMonth('created_at', '<=', $endDate_month)
                                ->where('producttype_id', '!=', 1)
                                ->where('approved', '!=', 1);
                              })
              ->where('paid', 1)
              ->where('status', 'pending')
              ->count();

              $wholesale_orders = Order::whereHas('orderDetails', function($q) use ($startDate_month, $endDate_month){
                             $q->whereMonth('created_at', '>=', $startDate_month)
                                ->whereMonth('created_at', '<=', $endDate_month)
                                ->where('producttype_id', '!=', 1);
                               // ->where('approved', 1);
                              })
              ->where('paid', 1)
              ->where('status', '!=', 'cancelled')
              ->where('expired', '!=', 1)
              ->count();
// return $wholesale_orders;

            $top_total_orders = Order::whereHas('orderDetails', function($q) use ($startDate_month, $endDate_month){
                             $q->whereMonth('created_at', '>=', $startDate_month)
                                ->whereMonth('created_at', '<=', $endDate_month)
                                ->where('approved', 1);
                              })->count();

            $wholesale_total_sale  = Orderdetail::where('approved', 1)
                                        ->whereMonth('created_at', '>=', $startDate_month)
                                        ->whereMonth('created_at', '<=', $endDate_month)
                                        ->where('producttype_id', '!=', 1)
                                        ->sum('total');

            $top_total_sale      = Invoice::whereMonth('created_at', '>=', $startDate_month)
                                          ->whereMonth('created_at', '<=', $endDate_month)
                                          ->sum('invoice_total');

            $top_total_invoices  = Invoice::whereMonth('created_at', '>=', $startDate_month)
                                          ->whereMonth('created_at', '<=', $endDate_month)
                                          ->count();
// start flow chart section
            $order_days = Order::whereHas('orderDetails', function($q) use ($startDate_month, $endDate_month){
                              $q->whereMonth('created_at', '>=', $startDate_month)
                                ->whereMonth('created_at', '<=', $endDate_month)
                                ->where('approved', 1);
                              })->pluck('created_at');
            //  return $total_orders;
              $unique_days   = array();
              $details_array = array();

              foreach ($order_days as $order_day) {   // start foreach unique days
                   $created_at = Carbon::parse($order_day)->format('m');
                   if (in_array($created_at, $unique_days)) {
                        continue;
                    }
                    else{
                        array_push($unique_days, $created_at);
                    }
              }   // end foreach unique days
             // return $unique_days;

              foreach ($unique_days as $unique_day)   // start foreach day
              {  
                // fetch report data here
                    $period_total_sale  = Invoice::whereMonth('created_at', '=', $unique_day)
                                              //->whereMonth('created_at', '<=', $unique_day)
                                              ->sum('invoice_total');

              array_push($details_array, [
                'day'      => $unique_day,
                'day_name' => date("F", mktime(0, 0, 0, $unique_day, 10)),
                'reports'  => [
                    'total_sale'       => $period_total_sale,
                  ], // end reports
              ]); // end array push
            } // end foreach unique day
                  
// end flow chart section
                
            return response()->json([
                'total_orders'     => $top_total_orders,
                'total_invoices'   => $top_total_invoices,
                'pending_vendors'    => $pending_vendors,
                'actual_vendors'    => $actual_vendors,
                'wholesale_orders'    => $wholesale_orders,
                'pending_wholesale_orders' => $pending_wholesale_orders,
                'wholesale_total_sale' => $wholesale_total_sale,
                'total_sale'       => $top_total_sale,
                'total_products'   => $top_total_products,
                'tickets'            => $tickets,
                'total_customers'  => $top_total_customers,
                'prod_questions'     => $prod_questions,
               // 'sales_nalytics'   => $orders,
                'period_details'   => $details_array,
            ]); 
        } // end case vendor
        elseif (in_array('Vendor', $user_roles)) 
        {
              $vendor        = AddVendor::where('userid_id', Auth::user()->id)->first();
              $vendor_id     = $vendor->id;
              
              $startDate_month = Carbon::parse($startDate)->format('m');
              $endDate_month   = Carbon::parse($endDate)->format('m');
        
            $top_total_products = Product::where('vendor_id', $vendor_id)
                                          ->whereMonth('created_at', '>=', $startDate_month)
                                          ->whereMonth('created_at', '<=', $endDate_month)->count();

            $tickets = Ticket::whereMonth('created_at', '>=', $startDate_month)
                              ->whereMonth('created_at', '<=', $endDate_month)
                              ->where('vendor_id', $vendor_id)->count();
            $prod_questions = Productquestion::where('vendor_id', $vendor_id)
                                ->whereMonth('created_at', '>=', $startDate_month)
                                ->whereMonth('created_at', '<=', $endDate_month)
                                ->count();
            
            $top_total_orders = Order::whereHas('orderDetails', function($q) use ($vendor_id, $startDate_month, $endDate_month){
                             $q->where('vendor_id', $vendor_id)
                                ->whereMonth('created_at', '>=', $startDate_month)
                                ->whereMonth('created_at', '<=', $endDate_month)
                                ->where('approved', 1);
                              })->count();

            $pending_orders = Order::whereHas('orderDetails', function($q) use ($vendor_id, $startDate_month, $endDate_month){
                                $q->where('vendor_id', $vendor_id)
                                  ->whereMonth('created_at', '>=', $startDate_month)
                                  ->whereMonth('created_at', '<=', $endDate_month)
                                  ->where('approved', '!=', 1);
                                })->where('expired', '!=', 1)->where('paid', 1)
                                  ->where('status', 'pending')->count();

            $top_total_sale      = Invoice::where('vendor_id', $vendor_id)
                                          ->whereMonth('created_at', '>=', $startDate_month)
                                          ->whereMonth('created_at', '<=', $endDate_month)
                                          ->sum('invoice_total');

            $top_total_invoices  = Invoice::where('vendor_id', $vendor_id)
                                          ->whereMonth('created_at', '>=', $startDate_month)
                                          ->whereMonth('created_at', '<=', $endDate_month)
                                          ->count();
// start flow chart section
            $order_days = Order::whereHas('orderDetails', function($q) use ($vendor_id, $startDate_month, $endDate_month){
                              $q->where('vendor_id', $vendor_id)
                                ->whereMonth('created_at', '>=', $startDate_month)
                                ->whereMonth('created_at', '<=', $endDate_month)
                                ->where('approved', 1);
                              })->pluck('created_at');
            //  return $total_orders;
              $unique_days   = array();
              $details_array = array();

              foreach ($order_days as $order_day) {   // start foreach unique days
                   $created_at = Carbon::parse($order_day)->format('m');
                   if (in_array($created_at, $unique_days)) {
                        continue;
                    }
                    else{
                        array_push($unique_days, $created_at);
                    }
              }   // end foreach unique days
             // return $unique_days;

              foreach ($unique_days as $unique_day)   // start foreach day
              {  
                // fetch report data here
                    $period_total_sale  = Invoice::whereMonth('created_at', '=', $unique_day)
                                              //->whereMonth('created_at', '<=', $unique_day)
                                              ->sum('invoice_total');

              array_push($details_array, [
                'day'      => $unique_day,
                'day_name' => date("F", mktime(0, 0, 0, $unique_day, 10)),
                'reports'  => [
                    'total_sale'       => $period_total_sale,
                  ], // end reports
              ]); // end array push
            } // end foreach unique day
                  
// end flow chart section
                
            return response()->json([
                'total_orders'     => $top_total_orders,
                'total_invoices'   => $top_total_invoices,
                'total_sale'       => $top_total_sale,
                'total_products'   => $top_total_products,
                'tickets'            => $tickets,
                'prod_questions'     => $prod_questions,
                'pending_orders'     => $pending_orders,
               // 'sales_nalytics'   => $orders,
                'period_details'   => $details_array,
            ]); 
        } // end case vendor

        /* start case manager */
        elseif (in_array('Manager', $user_roles)) 
      {
       // abort_if(Gate::denies('stores_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;

              $startDate_month = Carbon::parse($startDate)->format('m');
              $endDate_month   = Carbon::parse($endDate)->format('m');
        
            $top_total_products = Product::where('vendor_id', $vendor_id)
                                          ->whereMonth('created_at', '>=', $startDate_month)
                                          ->whereMonth('created_at', '<=', $endDate_month)->count();

            $tickets = Ticket::whereMonth('created_at', '>=', $startDate_month)
                              ->whereMonth('created_at', '<=', $endDate_month)
                              ->where('vendor_id', $vendor_id)->count();
            $prod_questions = Productquestion::where('vendor_id', $vendor_id)
                                ->whereMonth('created_at', '>=', $startDate_month)
                                ->whereMonth('created_at', '<=', $endDate_month)
                                ->count();
            
            $top_total_orders = Order::whereHas('orderDetails', function($q) use ($vendor_id, $startDate_month, $endDate_month){
                             $q->where('vendor_id', $vendor_id)
                                ->whereMonth('created_at', '>=', $startDate_month)
                                ->whereMonth('created_at', '<=', $endDate_month)
                                ->where('approved', 1);
                              })->count();

            $pending_orders = Order::whereHas('orderDetails', function($q) use ($vendor_id, $startDate_month, $endDate_month){
                                $q->where('vendor_id', $vendor_id)
                                  ->whereMonth('created_at', '>=', $startDate_month)
                                  ->whereMonth('created_at', '<=', $endDate_month)
                                  ->where('approved', '!=', 1);
                                })->where('expired', '!=', 1)->where('paid', 1)
                                  ->where('status', 'pending')->count();

            $top_total_sale      = Invoice::where('vendor_id', $vendor_id)
                                          ->whereMonth('created_at', '>=', $startDate_month)
                                          ->whereMonth('created_at', '<=', $endDate_month)
                                          ->sum('invoice_total');

            $top_total_invoices  = Invoice::where('vendor_id', $vendor_id)
                                          ->whereMonth('created_at', '>=', $startDate_month)
                                          ->whereMonth('created_at', '<=', $endDate_month)
                                          ->count();
// start flow chart section
            $order_days = Order::whereHas('orderDetails', function($q) use ($vendor_id, $startDate_month, $endDate_month){
                              $q->where('vendor_id', $vendor_id)
                                ->whereMonth('created_at', '>=', $startDate_month)
                                ->whereMonth('created_at', '<=', $endDate_month)
                                ->where('approved', 1);
                              })->pluck('created_at');
            //  return $total_orders;
              $unique_days   = array();
              $details_array = array();

              foreach ($order_days as $order_day) {   // start foreach unique days
                   $created_at = Carbon::parse($order_day)->format('m');
                   if (in_array($created_at, $unique_days)) {
                        continue;
                    }
                    else{
                        array_push($unique_days, $created_at);
                    }
              }   // end foreach unique days
             // return $unique_days;

              foreach ($unique_days as $unique_day)   // start foreach day
              {  
                // fetch report data here
                    $period_total_sale  = Invoice::whereMonth('created_at', '=', $unique_day)
                                              //->whereMonth('created_at', '<=', $unique_day)
                                              ->sum('invoice_total');

              array_push($details_array, [
                'day'      => $unique_day,
                'day_name' => date("F", mktime(0, 0, 0, $unique_day, 10)),
                'reports'  => [
                    'total_sale'       => $period_total_sale,
                  ], // end reports
              ]); // end array push
            } // end foreach unique day
                  
// end flow chart section
                
            return response()->json([
                'total_orders'     => $top_total_orders,
                'total_invoices'   => $top_total_invoices,
                'total_sale'       => $top_total_sale,
                'total_products'   => $top_total_products,
                'tickets'            => $tickets,
                'prod_questions'     => $prod_questions,
                'pending_orders'     => $pending_orders,
               // 'sales_nalytics'   => $orders,
                'period_details'   => $details_array,
            ]); 
        //$staff_stores = $exist_staff->stores->pluck('id')->toArray();
      }
        /* end case manager */
        /* start case manager */
        elseif (in_array('Staff', $user_roles)) 
      {
       // abort_if(Gate::denies('stores_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;

              $startDate_month = Carbon::parse($startDate)->format('m');
              $endDate_month   = Carbon::parse($endDate)->format('m');
        
            $top_total_products = Product::where('vendor_id', $vendor_id)
                                          ->whereMonth('created_at', '>=', $startDate_month)
                                          ->whereMonth('created_at', '<=', $endDate_month)->count();

            $tickets = Ticket::whereMonth('created_at', '>=', $startDate_month)
                              ->whereMonth('created_at', '<=', $endDate_month)
                              ->where('vendor_id', $vendor_id)->count();
            $prod_questions = Productquestion::where('vendor_id', $vendor_id)
                                ->whereMonth('created_at', '>=', $startDate_month)
                                ->whereMonth('created_at', '<=', $endDate_month)
                                ->count();
            
          /*  $top_total_orders = Order::whereHas('orderDetails', function($q) use ($vendor_id, $startDate_month, $endDate_month){
                             $q->where('vendor_id', $vendor_id)
                                ->whereMonth('created_at', '>=', $startDate_month)
                                ->whereMonth('created_at', '<=', $endDate_month)
                                ->where('approved', 1);
                              })->count();

            $pending_orders = Order::whereHas('orderDetails', function($q) use ($vendor_id, $startDate_month, $endDate_month){
                                $q->where('vendor_id', $vendor_id)
                                  ->whereMonth('created_at', '>=', $startDate_month)
                                  ->whereMonth('created_at', '<=', $endDate_month)
                                  ->where('approved', '!=', 1);
                                })->where('expired', '!=', 1)->where('paid', 1)
                                  ->where('status', 'pending')->count();

            $top_total_sale      = Invoice::where('vendor_id', $vendor_id)
                                          ->whereMonth('created_at', '>=', $startDate_month)
                                          ->whereMonth('created_at', '<=', $endDate_month)
                                          ->sum('invoice_total');

            $top_total_invoices  = Invoice::where('vendor_id', $vendor_id)
                                          ->whereMonth('created_at', '>=', $startDate_month)
                                          ->whereMonth('created_at', '<=', $endDate_month)
                                          ->count(); */
// start flow chart section
          /*  $order_days = Order::whereHas('orderDetails', function($q) use ($vendor_id, $startDate_month, $endDate_month){
                              $q->where('vendor_id', $vendor_id)
                                ->whereMonth('created_at', '>=', $startDate_month)
                                ->whereMonth('created_at', '<=', $endDate_month)
                                ->where('approved', 1);
                              })->pluck('created_at');
            //  return $total_orders;
              $unique_days   = array();
              $details_array = array();

              foreach ($order_days as $order_day) {   // start foreach unique days
                   $created_at = Carbon::parse($order_day)->format('m');
                   if (in_array($created_at, $unique_days)) {
                        continue;
                    }
                    else{
                        array_push($unique_days, $created_at);
                    }
              }   // end foreach unique days
             // return $unique_days;

              foreach ($unique_days as $unique_day)   // start foreach day
              {  
                // fetch report data here
                    $period_total_sale  = Invoice::whereMonth('created_at', '=', $unique_day)
                                              //->whereMonth('created_at', '<=', $unique_day)
                                              ->sum('invoice_total');

              array_push($details_array, [
                'day'      => $unique_day,
                'day_name' => date("F", mktime(0, 0, 0, $unique_day, 10)),
                'reports'  => [
                    'total_sale'       => $period_total_sale,
                  ], // end reports
              ]); // end array push
            } // end foreach unique day */
                  
// end flow chart section
                
            return response()->json([
               // 'total_orders'     => $top_total_orders,
               // 'total_invoices'   => $top_total_invoices,
               // 'total_sale'       => $top_total_sale,
                'total_products'   => $top_total_products,
                'tickets'            => $tickets,
                'prod_questions'     => $prod_questions,
              //  'pending_orders'     => $pending_orders,
               // 'sales_nalytics'   => $orders,
              //  'period_details'   => $details_array,
            ]); 
        //$staff_stores = $exist_staff->stores->pluck('id')->toArray();
      }
        /* end case staff */
        else{
          return response()->json([
                  'message'  => 'un authorized access page due to permissions',
                 ], 401);
        } // end if
      }  // end if search with months 
      else
      {  // start search with days  
        $startDate = $from.' 00:00:00';
        $endDate   = $to.' 23:59:59';  
       // return $startDate;   
        if (in_array('Admin', $user_roles)) 
        {          
              $top_total_orders = Order::whereHas('orderDetails', function($q) use ($startDate, $endDate){
                                $q->where('approved', 1);
                                })->where('checkout_time', '>=', $startDate)
                                  ->where('checkout_time', '<=', $endDate)
                                  ->where('paid', 1)
                                  ->count();

              $tickets = Ticket::where('created_at', '>=', $startDate)
                                  ->where('created_at', '<=', $endDate)->count();

              $prod_questions = Productquestion::where('created_at', '>=', $startDate)
                                ->where('created_at', '<=', $endDate)->count();

              $top_total_orders_ids = Order::whereHas('orderDetails', function($q) use ($startDate, $endDate){
                                $q->where('approved', 1);
                                })->where('checkout_time', '>=', $startDate)
                                  ->where('checkout_time', '<=', $endDate)
                                  ->where('paid', 1)
                                  ->pluck('id')->toArray();

              $top_total_customers = User::whereHas('roles', function($q){
                                          $q->where('title', '!=', 'Admin');
                                        })->where('created_at', '>=', $startDate)
                                          ->where('created_at', '<=', $endDate)->count();

              $pending_wholesale_orders = Order::whereHas('orderDetails', function($q) use ($startDate, $endDate){
                             $q->where('producttype_id', '!=', 1)
                                ->where('approved', 0);
                              })
              ->where('checkout_time', '>=', $startDate)
              ->where('checkout_time', '<=', $endDate)
              ->where('paid', 1)
             // ->where('status', 'pending')
              ->count();

              $wholesale_orders = Order::whereHas('orderDetails', function($q) use ($startDate, $endDate){
                             $q->where('producttype_id', '!=', 1);
                               //->where('approved', '!=', 1);
                              })
              ->where('checkout_time', '>=', $startDate)
              ->where('checkout_time', '<=', $endDate)
              ->where('paid', 1)
              ->where('expired', '!=', 1)
              ->count();

              $wholesale_orders_ids = Order::whereHas('orderDetails', function($q) use ($startDate, $endDate){
                             $q->where('producttype_id', '!=', 1);
                               //->where('approved', '!=', 1);
                              })
              ->where('checkout_time', '>=', $startDate)
              ->where('checkout_time', '<=', $endDate)
              ->where('paid', 1)
              ->where('expired', '!=', 1)
              ->pluck('id')->toArray();
// return $wholesale_orders;

              $pending_vendors = AddVendor::where('approved', '!=', 1)
                                          ->where('complete', 1)
                                          ->where('declined', 0)
                                          ->where('rejected', 0)
                                          ->where('created_at', '>=', $startDate)
                                          ->where('created_at', '<=', $endDate)
                                          ->count();

              $actual_vendors = AddVendor::where('complete', 1)
                                          ->where('declined', 0)
                                          ->where('rejected', 0)
                                          ->where('created_at', '>=', $startDate)
                                          ->where('created_at', '<=', $endDate)
                                          ->count();

              $top_total_invoices  = Invoice::whereIn('order_id', $top_total_orders_ids)
                                        ->count();

              $top_total_sale = Orderdetail::where('approved', 1)
                                        ->whereIn('order_id', $top_total_orders_ids)
                                        ->sum('total');

          $wholesale_total_sale  = Orderdetail::where('approved', 1)->where('producttype_id', '!=', 1)
                                        ->whereIn('order_id', $wholesale_orders_ids)
                                        ->sum('total');

          $top_total_products = Product::where('created_at', '>=', $startDate)
                                        ->where('created_at', '<=', $endDate)
                                        ->where('approved', 1)->count(); 
              // start flow chart section
              $order_days = Order::whereHas('orderDetails', function($q) use ($startDate, $endDate){
                                $q->where('approved', 1);
                                })->where('checkout_time', '>=', $startDate)
                                  ->where('checkout_time', '<=', $endDate)
                                  ->where('paid', 1)
                                  ->pluck('created_at');

//              return $order_days;
                $unique_days   = array();
                $details_array = array();

                foreach ($order_days as $order_day) {   // start foreach unique days
                    $created_at = \Carbon\Carbon::parse($order_day)->format('Y-m-d');//->toDateString();
                   // return $created_at;
                     if (in_array($created_at, $unique_days)) {
                          continue;
                      }
                      else{
                          array_push($unique_days, $created_at);
                      }
                }   // end foreach unique days

               //return $vendor_id;

                foreach ($unique_days as $unique_day)   // start foreach day
                {  
                  // fetch report data here
                       $startDateDay = $unique_day.' 00:00:00';
                       // return $startDateDay;
                       $endDateDay   = $unique_day.' 23:59:59';

                  /*$period_total_sale    = Invoice::where('vendor_id', $vendor_id)
                                            ->where('created_at', '>=', $startDateDay)
                                            ->where('created_at', '<=', $endDateDay)
                                            ->sum('invoice_total');*/
                  $period_total_sale    = Orderdetail::where('approved', 1)
                                            ->where('created_at', '>=', $startDateDay)
                                            ->where('created_at', '<=', $endDateDay)
                                            ->sum('total');

                   /**/
                array_push($details_array, [
                  'day'     => $unique_day,
                  'reports' => [
                      'total_sale'       => $period_total_sale,
                    ], // end reports
                ]); // end array push
              } // end foreach unique day
                    
  // end flow chart section
              //return $details_array;
              return response()->json([
                //'data'   => $data,
                'total_orders'     =>  $top_total_orders,
                'pending_vendors'    => $pending_vendors,
                'total_customers'  => $top_total_customers,
                'tickets'          => $tickets,
                'prod_questions'          => $prod_questions,
                'actual_vendors'    => $actual_vendors,
                'wholesale_orders'    => $wholesale_orders,
                'pending_wholesale_orders' => $pending_wholesale_orders,
                'wholesale_total_sale' => $wholesale_total_sale,
                'total_invoices'   =>  $top_total_invoices,
                'total_sale'       =>  $top_total_sale,
                'total_products'   =>  $top_total_products,
                // 'sales_analytics'  =>  $orders,
                'period_details'   =>  $details_array,
              ]);
        } // end case vendor
        elseif (in_array('Vendor', $user_roles)) 
        {
              $vendor        = AddVendor::where('userid_id', Auth::user()->id)->first();
              $vendor_id     = $vendor->id;

              $tickets = Ticket::where('vendor_id', $vendor_id)
                                ->where('created_at', '>=', $startDate)
                                ->where('created_at', '<=', $endDate)->count();

              $prod_questions = Productquestion::where('vendor_id', $vendor_id)
                                ->where('created_at', '>=', $startDate)
                                ->where('created_at', '<=', $endDate)->count();
            
              $top_total_orders = Order::whereHas('orderDetails', function($q) use ($vendor_id, $startDate, $endDate){
                                $q->where('vendor_id', $vendor_id)
                                  ->where('approved', 1);
                                })->where('checkout_time', '>=', $startDate)
                                  ->where('checkout_time', '<=', $endDate)
                                  ->where('paid', 1)
                                  ->count();

              $pending_orders = Order::whereHas('orderDetails', function($q) use ($vendor_id, $startDate, $endDate){
                                $q->where('vendor_id', $vendor_id)
                                  ->where('approved', 0);
                                })->where('checkout_time', '>=', $startDate)
                                  ->where('checkout_time', '<=', $endDate)
                                  ->where('paid', 1)
                                  ->where('expired', '!=', 1)
                                  //->where('status', 'pending')
                                  ->count();

              $top_total_orders_ids = Order::whereHas('orderDetails', function($q) use ($vendor_id, $startDate, $endDate){
                                $q->where('vendor_id', $vendor_id)
                                  ->where('approved', 1);
                                })->where('checkout_time', '>=', $startDate)
                                  ->where('checkout_time', '<=', $endDate)
                                  ->where('paid', 1)
                                  ->pluck('id')->toArray();

              $top_total_invoices  = Invoice::where('vendor_id', $vendor_id)
                                        ->whereIn('order_id', $top_total_orders_ids)
                                        ->count();

              $top_total_sale      = Orderdetail::where('vendor_id', $vendor_id)
                                        ->where('approved', 1)
                                        ->whereIn('order_id', $top_total_orders_ids)
                                        ->sum('total');

              $top_total_products = Product::where('vendor_id', $vendor_id)
                                        ->where('created_at', '>=', $startDate)
                                        ->where('created_at', '<=', $endDate)
                                        ->count(); 
              // start flow chart section
              $order_days = Order::whereHas('orderDetails', function($q) use ($vendor_id, $startDate, $endDate){
                                $q->where('vendor_id', $vendor_id)
                                  ->where('approved', 1);
                                })->where('checkout_time', '>=', $startDate)
                                  ->where('checkout_time', '<=', $endDate)
                                  ->where('paid', 1)
                                  ->pluck('created_at');

//              return $order_days;
                $unique_days   = array();
                $details_array = array();

                foreach ($order_days as $order_day) {   // start foreach unique days
                    $created_at = \Carbon\Carbon::parse($order_day)->format('Y-m-d');//->toDateString();
                   // return $created_at;
                     if (in_array($created_at, $unique_days)) {
                          continue;
                      }
                      else{
                          array_push($unique_days, $created_at);
                      }
                }   // end foreach unique days

               //return $vendor_id;

                foreach ($unique_days as $unique_day)   // start foreach day
                {  
                  // fetch report data here
                       $startDateDay = $unique_day.' 00:00:00';
                       // return $startDateDay;
                       $endDateDay   = $unique_day.' 23:59:59';

                  /*$period_total_sale    = Invoice::where('vendor_id', $vendor_id)
                                            ->where('created_at', '>=', $startDateDay)
                                            ->where('created_at', '<=', $endDateDay)
                                            ->sum('invoice_total');*/
                  $period_total_sale    = Orderdetail::where('vendor_id', $vendor_id)
                                            ->where('approved', 1)
                                            ->where('created_at', '>=', $startDateDay)
                                            ->where('created_at', '<=', $endDateDay)
                                            ->sum('total');

                   /**/
                array_push($details_array, [
                  'day'     => $unique_day,
                  'reports' => [
                      'total_sale'       => $period_total_sale,
                    ], // end reports
                ]); // end array push
              } // end foreach unique day
                    
  // end flow chart section
              //return $details_array;
              return response()->json([
                //'data'   => $data,
                'total_orders'     =>  $top_total_orders,
                'pending_orders'   =>  $pending_orders,
                'tickets'          => $tickets,
                'prod_questions'   => $prod_questions,
                'total_invoices'   =>  $top_total_invoices,
                'total_sale'       =>  $top_total_sale,
                'total_products'   =>  $top_total_products,
                // 'sales_analytics'  =>  $orders,
                'period_details'   =>  $details_array,
              ]);
        } // end case vendor

        /* start manager case */
        elseif (in_array('Manager', $user_roles)) 
      {
       // abort_if(Gate::denies('stores_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
       // $staff_stores = $exist_staff->stores->pluck('id')->toArray();
       // return $staff_stores;            

       $tickets = Ticket::where('vendor_id', $vendor_id)
                                ->where('created_at', '>=', $startDate)
                                ->where('created_at', '<=', $endDate)->count();

              $prod_questions = Productquestion::where('vendor_id', $vendor_id)
                                ->where('created_at', '>=', $startDate)
                                ->where('created_at', '<=', $endDate)->count();
            
             $top_total_orders = Order::whereHas('orderDetails', function($q) use ($vendor_id, $startDate, $endDate){
                                $q->where('vendor_id', $vendor_id)
                                  ->where('approved', 1);
                                })->where('checkout_time', '>=', $startDate)
                                  ->where('checkout_time', '<=', $endDate)
                                  ->where('paid', 1)
                                  ->count();

              $pending_orders = Order::whereHas('orderDetails', function($q) use ($vendor_id, $startDate, $endDate){
                                $q->where('vendor_id', $vendor_id)
                                  ->where('approved', 0);
                                })->where('checkout_time', '>=', $startDate)
                                  ->where('checkout_time', '<=', $endDate)
                                  ->where('paid', 1)
                                  ->where('expired', '!=', 1)
                                  //->where('status', 'pending')
                                  ->count();

              $top_total_orders_ids = Order::whereHas('orderDetails', function($q) use ($vendor_id, $startDate, $endDate){
                                $q->where('vendor_id', $vendor_id)
                                  ->where('approved', 1);
                                })->where('checkout_time', '>=', $startDate)
                                  ->where('checkout_time', '<=', $endDate)
                                  ->where('paid', 1)
                                  ->pluck('id')->toArray();

              $top_total_invoices  = Invoice::where('vendor_id', $vendor_id)
                                        ->whereIn('order_id', $top_total_orders_ids)
                                        ->count();

              $top_total_sale      = Orderdetail::where('vendor_id', $vendor_id)
                                        ->where('approved', 1)
                                        ->whereIn('order_id', $top_total_orders_ids)
                                        ->sum('total');

              $top_total_products = Product::where('vendor_id', $vendor_id)
                                        ->where('created_at', '>=', $startDate)
                                        ->where('created_at', '<=', $endDate)->count(); 
              // start flow chart section
              $order_days = Order::whereHas('orderDetails', function($q) use ($vendor_id, $startDate, $endDate){
                                $q->where('vendor_id', $vendor_id)
                                  ->where('approved', 1);
                                })->where('checkout_time', '>=', $startDate)
                                  ->where('checkout_time', '<=', $endDate)
                                  ->where('paid', 1)
                                  ->pluck('created_at');

//              return $order_days;
                $unique_days   = array();
                $details_array = array();

                foreach ($order_days as $order_day) {   // start foreach unique days
                    $created_at = \Carbon\Carbon::parse($order_day)->format('Y-m-d');//->toDateString();
                   // return $created_at;
                     if (in_array($created_at, $unique_days)) {
                          continue;
                      }
                      else{
                          array_push($unique_days, $created_at);
                      }
                }   // end foreach unique days

               //return $vendor_id;

                foreach ($unique_days as $unique_day)   // start foreach day
                {  
                  // fetch report data here
                       $startDateDay = $unique_day.' 00:00:00';
                       // return $startDateDay;
                       $endDateDay   = $unique_day.' 23:59:59';

                  /*$period_total_sale    = Invoice::where('vendor_id', $vendor_id)
                                            ->where('created_at', '>=', $startDateDay)
                                            ->where('created_at', '<=', $endDateDay)
                                            ->sum('invoice_total');*/
                  $period_total_sale    = Orderdetail::where('vendor_id', $vendor_id)
                                            ->where('approved', 1)
                                            ->where('created_at', '>=', $startDateDay)
                                            ->where('created_at', '<=', $endDateDay)
                                            ->sum('total');

                   /**/
                array_push($details_array, [
                  'day'     => $unique_day,
                  'reports' => [
                      'total_sale'       => $period_total_sale,
                    ], // end reports
                ]); // end array push
              } // end foreach unique day
                    
  // end flow chart section
              //return $details_array;
              return response()->json([
                //'data'   => $data,
                'total_orders'     =>  $top_total_orders,
                'pending_orders'   =>  $pending_orders,
                'tickets'          => $tickets,
                'prod_questions'   => $prod_questions,
                'total_invoices'   =>  $top_total_invoices,
                'total_sale'       =>  $top_total_sale,
                'total_products'   =>  $top_total_products,
                // 'sales_analytics'  =>  $orders,
                'period_details'   =>  $details_array,
              ]);

      }
        /* end manager case */
        // ahmed
        /* start manager case */
        elseif (in_array('Staff', $user_roles)) 
      {
       // abort_if(Gate::denies('stores_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
       // $staff_stores = $exist_staff->stores->pluck('id')->toArray();
       // return $staff_stores;            

       $tickets = Ticket::where('vendor_id', $vendor_id)
                                ->where('created_at', '>=', $startDate)
                                ->where('created_at', '<=', $endDate)->count();

              $prod_questions = Productquestion::where('vendor_id', $vendor_id)
                                ->where('created_at', '>=', $startDate)
                                ->where('created_at', '<=', $endDate)->count();
            
             /* $top_total_orders = Order::whereHas('orderDetails', function($q) use ($vendor_id, $startDate, $endDate){
                                $q->where('vendor_id', $vendor_id)
                                  ->where('created_at', '>=', $startDate)
                                  ->where('created_at', '<=', $endDate)
                                  ->where('approved', 1);
                                })->count();

              $pending_orders = Order::whereHas('orderDetails', function($q) use ($vendor_id, $startDate, $endDate){
                                $q->where('vendor_id', $vendor_id)
                                  ->where('created_at', '>=', $startDate)
                                  ->where('created_at', '<=', $endDate)
                                  ->where('approved', '!=', 1);
                                })->where('expired', '!=', 1)->where('paid', 1)
                                  ->where('status', 'pending')->count();

              $top_total_orders_ids = Order::whereHas('orderDetails', function($q) use ($vendor_id, $startDate, $endDate){
                                $q->where('vendor_id', $vendor_id)
                                  ->where('created_at', '>=', $startDate)
                                  ->where('created_at', '<=', $endDate)
                                  ->where('approved', 1);
                                })->pluck('id')->toArray();

              /*$top_total_invoices  = Invoice::where('vendor_id', $vendor_id)
                                        ->where('created_at', '>=', $startDate)
                                        ->where('created_at', '<=', $endDate)->count();*/

             /* $top_total_invoices  = Invoice::where('vendor_id', $vendor_id)
                                        ->whereIn('order_id', $top_total_orders_ids)
                                       // ->where('created_at', '>=', $startDate)
                                       // ->where('created_at', '<=', $endDate)
                                        ->count();

              $top_total_sale      = Orderdetail::where('vendor_id', $vendor_id)
                                        ->where('approved', 1)
                                        ->where('created_at', '>=', $startDate)
                                        ->where('created_at', '<=', $endDate)
                                        ->sum('total');*/

              $top_total_products = Product::where('vendor_id', $vendor_id)
                                        ->where('created_at', '>=', $startDate)
                                        ->where('created_at', '<=', $endDate)->count(); 
              // start flow chart section
         /*     $order_days = Order::whereHas('orderDetails', function($q) use ($vendor_id, $startDate, $endDate){
                                $q->where('vendor_id', $vendor_id)
                                  ->where('created_at', '>=', $startDate)
                                  ->where('created_at', '<=', $endDate)
                                  ->where('approved', 1);
                                })->pluck('created_at');

//              return $order_days;
                $unique_days   = array();
                $details_array = array();

                foreach ($order_days as $order_day) {   // start foreach unique days
                    $created_at = \Carbon\Carbon::parse($order_day)->format('Y-m-d');//->toDateString();
                   // return $created_at;
                     if (in_array($created_at, $unique_days)) {
                          continue;
                      }
                      else{
                          array_push($unique_days, $created_at);
                      }
                }   // end foreach unique days

               //return $vendor_id;

                foreach ($unique_days as $unique_day)   // start foreach day
                {  
                  // fetch report data here
                       $startDateDay = $unique_day.' 00:00:00';
                       // return $startDateDay;
                       $endDateDay   = $unique_day.' 23:59:59';

                  /*$period_total_sale    = Invoice::where('vendor_id', $vendor_id)
                                            ->where('created_at', '>=', $startDateDay)
                                            ->where('created_at', '<=', $endDateDay)
                                            ->sum('invoice_total');*/
               /*   $period_total_sale    = Orderdetail::where('vendor_id', $vendor_id)
                                            ->where('approved', 1)
                                            ->where('created_at', '>=', $startDateDay)
                                            ->where('created_at', '<=', $endDateDay)
                                            ->sum('total');

                   /**/
             /*   array_push($details_array, [
                  'day'     => $unique_day,
                  'reports' => [
                      'total_sale'       => $period_total_sale,
                    ], // end reports
                ]); // end array push
              } // end foreach unique day 
                    
  // end flow chart section */
              //return $details_array;
              return response()->json([
                //'data'   => $data,
               // 'total_orders'     =>  $top_total_orders,
              //  'pending_orders'   =>  $pending_orders,
                'tickets'          => $tickets,
                'prod_questions'   => $prod_questions,
              //  'total_invoices'   =>  $top_total_invoices,
              //  'total_sale'       =>  $top_total_sale,
                'total_products'   =>  $top_total_products,
                // 'sales_analytics'  =>  $orders,
              //  'period_details'   =>  $details_array,
              ]);

      }
        /* end staff case */
        else{
          return response()->json([
                  'message'  => 'un authorized access page due to permissions',
                 ], 401);
        } // end if
      } // end search with days       
    }
}
