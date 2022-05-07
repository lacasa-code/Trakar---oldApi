<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\Vendor\VendorOrdersApiResource;
use App\Models\Order;
use App\Models\Orderdetail;
use Auth;
use Gate;
use App\Http\Requests\ReportApiRequest;  
use Validator;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Product;
use App\Models\AddVendor;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Ticket;
use App\Models\Productquestion;
use Artisan;

class BasicApiReportController extends Controller
{
    public function fetch_data_period(Request $request) //(ReportApiRequest $request)
    {
      Artisan::call('order:expire');
      abort_if(Gate::denies('show_reports_stats'), Response::HTTP_FORBIDDEN, '403 Forbidden');
      // $PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
        
        if (!$request->header('Authorization'))
        {
           return response()->json([
            'status_code' => 400, 
            'message'     => 'fail',
            'errors' => 'No Authorization token Found'], 400);
        }

        // if not sent date for filter (default is one day from now)
      if (!$request->has('from') && !$request->has('to') || ($request->from == '' && $request->to == ''))
      {
       // $from = Carbon::today()->subMonth()->toDateString();
       $from = Carbon::today()->subDays(7)->toDateString();
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

      $user = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();

       // case logged in user role is Admin
       if (in_array('Admin', $user_roles)) {
        // case search specific vendor reports
          if ($request->has('vendor_id') && $request->vendor_id != '') {
            
          } // end case specific vendor reports
          else{  // case search reports in general

            $top_total_customers = User::whereHas('roles', function($q){
                                          $q->where('title', '!=', 'Admin');
                                        })->where('created_at', '>=', $startDate)
                                          ->where('created_at', '<=', $endDate)->count();

            $top_total_vendors = AddVendor::where('created_at', '>=', $startDate)
                                          ->where('created_at', '<=', $endDate)->count();

            $top_total_products = Product::where('created_at', '>=', $startDate)
                                          ->where('created_at', '<=', $endDate)->count();

            $pending_vendors = AddVendor::where('approved', '!=', 1)
                                          ->where('complete', 1)
                                          ->where('declined', 0)
                                          ->where('rejected', 0)
                                          ->where('created_at', '>=', $startDate)
                                          ->where('created_at', '<=', $endDate)
                                          ->count();
   
          $top_total_orders = Order::whereHas('orderDetails', function($q) use ($startDate, $endDate)
              {
                $q->where('created_at', '>=', $startDate)
                                ->where('created_at', '<=', $endDate)
                                ->where('approved', 1);
              })->count();

          $top_pending_orders = Order::whereHas('orderDetails')->where('paid', 1)
                                ->where('status', 'pending')
                                ->where('created_at', '>=', $startDate)
                                ->where('created_at', '<=', $endDate)
                              //  ->pluck('id');
                                ->count();

          $tickets = Ticket::count();
          $prod_questions = Productquestion::count();

            /*$top_total_sale      = Invoice::where('created_at', '>=', $startDate)
                                          ->where('created_at', '<=', $endDate)->sum('invoice_total');*/
            $top_total_sale      = Orderdetail::where('created_at', '>=', $startDate)
                                          ->where('created_at', '<=', $endDate)
                                          ->where('approved', 1)->sum('total');

            $top_total_invoices  = Invoice::where('created_at', '>=', $startDate)
                                          ->where('created_at', '<=', $endDate)
                                          ->count();

// start flow chart section
            $order_days = Orderdetail::where('created_at', '>=', $startDate)
                                ->where('created_at', '<=', $endDate)
                                ->where('approved', 1)
                                ->pluck('created_at');

            //  return $total_orders;
              $unique_days   = array();
              $details_array = array();

              foreach ($order_days as $order_day) {   // start foreach unique days
                  //$created_at = $order_day->toDateString();
                $created_at = $order_day;//->;//toDateString();
                   if (in_array($created_at, $unique_days)) {
                        continue;
                    }
                    else{
                        array_push($unique_days, $created_at);
                    }
              }   // end foreach unique days

              foreach ($unique_days as $unique_day)   // start foreach day
              {  
                // fetch report data here
                     $startDate = $unique_day.' 00:00:00';
                     $endDate   = $unique_day.' 23:59:59';

                  $period_total_sale      = Orderdetail::where('created_at', '>=', $startDate)
                                          ->where('created_at', '<=', $endDate)
                                          ->where('approved', 1)->sum('total');

                 /**/

              array_push($details_array, [
                'day'      => $unique_day,
                'day_name' => Carbon::parse($unique_day)->format('l'),
                'reports'  => [
                    // 'total_orders'     => $top_total_orders,
                    // 'total_invoices'   => $top_total_invoices,
                    'total_sale'       => $period_total_sale,
                    // 'total_customers'  => $top_total_customers,
                    // 'total_products'   => $top_total_products,
                  ], // end reports
              ]); // end array push
            } // end foreach unique day
                  
// end flow chart section
                
            return response()->json([
                'total_orders'       => $top_total_orders,
                'top_pending_orders' => $top_pending_orders,
                'pending_vendors'    => $pending_vendors,
                'total_invoices'     => $top_total_invoices,
                'total_sale'       => $top_total_sale,
                'total_customers'  => $top_total_customers,
                'tickets'          => $tickets,
                'prod_questions'   => $prod_questions,
                'total_vendors'    => $top_total_vendors,
                'total_products'   => $top_total_products,
               // 'sales_nalytics'   => $orders,
                'period_details'   => $details_array,
            ]);
        }  // end case search reports in general
      } // case logged in user role is Vendor
      elseif (in_array('Vendor', $user_roles)) {
            $vendor        = AddVendor::where('userid_id', Auth::user()->id)->first();
            $vendor_id     = $vendor->id;
          
            $top_total_orders = Order::whereHas('orderDetails', function($q) use ($vendor_id, $startDate, $endDate){
                $q->where('vendor_id', $vendor_id)
                                ->where('created_at', '>=', $startDate)
                                ->where('created_at', '<=', $endDate)
                                ->where('approved', 1);
              })->count();

            $top_pending_orders = Order::whereHas('orderDetails', function($q) use ($vendor_id, $startDate, $endDate){
                $q->where('vendor_id', $vendor_id)
                                ->where('created_at', '>=', $startDate)
                                ->where('created_at', '<=', $endDate);
                })->where('paid', 1)->where('status', 'pending')
                                ->where('created_at', '>=', $startDate)
                                ->where('created_at', '<=', $endDate)
                                //->pluck('id');
                                ->count();

            $top_total_invoices  = Invoice::where('vendor_id', $vendor_id)
                                      ->where('created_at', '>=', $startDate)
                                      ->where('created_at', '<=', $endDate)->count();

            $tickets = Ticket::where('vendor_id', $vendor_id)->count();
            $prod_questions = Productquestion::where('vendor_id', $vendor_id)->count();

            /*$top_total_sale      = Invoice::where('vendor_id', $vendor_id)
                                      ->where('created_at', '>=', $startDate)
                                      ->where('created_at', '<=', $endDate)->sum('invoice_total');*/
            
            $top_total_sale      = Orderdetail::where('vendor_id', $vendor_id)
                                        ->where('created_at', '>=', $startDate)
                                        ->where('approved', 1)
                                        ->where('created_at', '<=', $endDate)->sum('total');

            $top_total_products = Product::where('vendor_id', $vendor_id)
                                      ->where('created_at', '>=', $startDate)
                                      ->where('created_at', '<=', $endDate)->count(); 
            // start flow chart section
            /*$order_days = Order::whereHas('orderDetails', function($q) use ($vendor_id){
                              $q->where('vendor_id', $vendor_id);
                              })->where('created_at', '>=', $startDate)
                                ->where('created_at', '<=', $endDate)
                                ->where('approved', 1)
                                ->pluck('created_at');*/
            $order_days = Orderdetail::where('vendor_id', $vendor_id)
                                ->where('created_at', '>=', $startDate)
                                ->where('created_at', '<=', $endDate)
                                ->where('approved', 1)
                                ->pluck('created_at');
            //  return $total_orders;
              $unique_days   = array();
              $details_array = array();

              foreach ($order_days as $order_day) {   // start foreach unique days
                  $created_at = $order_day;//toDateString();
                   if (in_array($created_at, $unique_days)) {
                        continue;
                    }
                    else{
                        array_push($unique_days, $created_at);
                    }
              }   // end foreach unique days

              foreach ($unique_days as $unique_day)   // start foreach day
              {  
                // fetch report data here
                     $startDate = $unique_day.' 00:00:00';
                     $endDate   = $unique_day.' 23:59:59';

                  /*$period_total_sale      = Invoice::where('vendor_id', $vendor_id)
                                          ->where('created_at', '>=', $startDate)
                                          ->where('created_at', '<=', $endDate)->sum('invoice_total');*/

                $period_total_sale      = Orderdetail::where('vendor_id', $vendor_id)
                            ->where('created_at', '>=', $startDate)
                            ->where('created_at', '<=', $endDate)
                            ->where('approved', 1)
                            ->where('approved', 1)->sum('total');
                 /**/

              array_push($details_array, [
                'day'     => $unique_day,
                'reports' => [
                    // 'total_orders'     => $top_total_orders,
                    // 'total_invoices'   => $top_total_invoices,
                    'total_sale'       => $period_total_sale,
                    // 'total_customers'  => $top_total_customers,
                    // 'total_products'   => $top_total_products,
                  ], // end reports
              ]); // end array push
            } // end foreach unique day
                  
// end flow chart section
            //return $details_array;
            return response()->json([
              'status_code' => 200, 
              'message'     => 'success',
              //'data'   => $data,
              'top_pending_orders' => $top_pending_orders,
              'tickets'            => $tickets,
              'prod_questions'     => $prod_questions,
              'total_orders'     =>  $top_total_orders,
              'total_invoices'   =>  $top_total_invoices,
              'total_sale'       =>  $top_total_sale,
              'total_products'   =>  $top_total_products,
              // 'sales_analytics'  =>  $orders,
              'period_details'   =>  $details_array,
            ], 200);
      } // end case vendor
      else{
        return response()->json([
                'status_code' => 200, 
                // 'message'     => 'success',
                'message'  => 'un authorized access page due to permissions',
               ], 401);
      } // end if
    }
}
