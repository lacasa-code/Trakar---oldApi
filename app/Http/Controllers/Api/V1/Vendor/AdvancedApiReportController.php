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
use App\Models\Vendorstaff;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use App\Http\Resources\Vendor\Reports\AdvancedApiReportVendorCaseResource;
use App\Http\Resources\Vendor\Reports\AdvancedApiReportStockCaseResource;
use App\Http\Resources\Vendor\Reports\AdvancedApiReportProductCaseResource;
use App\Http\Resources\Vendor\Reports\AdvancedApiReportPartCatCaseResource;
use App\Http\Resources\Vendor\Reports\AdvancedApiReportPartProdCaseResource;
use App\Http\Resources\Vendor\Reports\AdvancedApiReportVendorProdResource;
use App\Http\Resources\Vendor\Reports\AdvancedApiReportGeneralCaseResource;
use Symfony\Component\HttpFoundation\Response;

class AdvancedApiReportController extends Controller
{
    public function fetch_data_period(Request $request)
    {
      abort_if(Gate::denies('show_reports_stats'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        if (!$request->header('Authorization'))
        {
           return response()->json([
                        'status_code' => 200, 
                        'message'     => 'fail',
                        'errors' => 'No Authorization token Found'], 400);
        }

      if (!$request->has('from') && !$request->has('to') || ($request->from == '' && $request->to == ''))
      {
        $from          = Carbon::today()->subDays(7)->toDateString();
        $to            = Carbon::today()->toDateString();
      }
      else
      {
         $v = Validator::make($request->all(), [
          'from'          => 'required_with:to|date|date_format:Y-m-d|before_or_equal:to',
          'to'            => 'required_with:from|date|date_format:Y-m-d|after_or_equal:from',
        ]);
         if ($v->fails()) {
          return response()->json([
                        'status_code' => 400, 
                        'message'     => 'fail',
                        'errors' => $v->errors()], 400);
         }

        $from          = $request->from;
        $to            = $request->to;
      }

      if (!$request->has('vendor') && !$request->has('product') && !$request->has('part_category') && !$request->has('stock') && !$request->has('sale_type'))
      {
        $vendor        = null;
        $product       = null;
        $part_category = null;
        $stock         = null;
        $sale_type     = null;
      }
      else
      {
         $other_v = Validator::make($request->all(), [
          'vendor'        => 'nullable|integer|exists:add_vendors,id',
          'part_category' => 'nullable|integer|exists:part_categories,id',
          'product'       => 'nullable|integer|exists:products,id',
          'stock'         => 'nullable|integer|exists:stores,id',
          'sale_type'     => ['nullable', 'integer', Rule::in('1','2')],
        ]);
         if ($other_v->fails()) {
          return response()->json([
                       'status_code' => 400, 
                        'message'     => 'fail',
                        'errors' => $other_v->errors()], 400);
         }

        $vendor        = $request->vendor;
        $part_category = $request->part_category;
        $product       = $request->product;
        $stock         = $request->stock;
        $sale_type     = $request->sale_type;
      }

       // case 1
        if ($from != null && $to != null && $part_category == null && $product == null && $vendor == null && $stock == null && $sale_type == null) { 
          return $this->general_period_report($from, $to);
        }
        // case 2
        elseif($from != null && $to != null && $part_category == null && $product == null && $vendor != null && $stock == null && $sale_type == null) {    
              return $this->vendor_period_report($from, $to, $vendor);                   
        }
        // case 2
        elseif($from != null && $to != null && $part_category != null && $product == null && $vendor == null && $stock == null && $sale_type == null) {  
            return $this->part_category_period_report($from, $to, $part_category);                       
        }
        // case 4
        elseif($from != null && $to != null && $part_category == null && $product == null && $vendor == null && $stock != null && $sale_type == null) {     
            return $this->stock_period_report($from, $to, $stock);                    
        }
        // case 5
         elseif($from != null && $to != null && $part_category == null && $product != null && $vendor == null && $stock == null && $sale_type == null) {   
            return $this->product_period_report($from, $to, $product);                  
        }
        // case 6
        elseif($from != null && $to != null && $part_category == null && $product != null && $vendor == null && $stock != null && $sale_type == null) {   
            return $this->product_stock_period_report($from, $to, $product, $stock);                     
        }
        // case 7 
        elseif($from != null && $to != null && $part_category == null && $product != null && $vendor != null && $stock == null && $sale_type == null) {   
              return $this->vendor_product_period_report($from, $to, $vendor, $product);                      
        }
        // case 8
        elseif($from != null && $to != null && $part_category != null && $product != null && $vendor == null && $stock == null && $sale_type == null) {    
        return $this->category_product_period_report($from, $to, $product, $part_category);                        
        }
        // case 9
        elseif($from != null && $to != null && $part_category == null && $product == null && $vendor == null && $stock == null && $sale_type != null) {    
              // sale 1 // hot sale 2     
              if ($sale_type == 1) 
              {
                  return $this->sale_vendors_period_report($from, $to, $sale_type); 
              }      
              if ($sale_type == 2) 
              {
                  return $this->hotsale_vendors_period_report($from, $to, $sale_type); 
              }               
        }
        else
        {   
            return $this->general_period_report($from, $to);                   
        }    
    }  // end function ss2

  /*  public function fetch_data(Request $request) //(ReportApiRequest $request)
    {
     // abort_if(Gate::denies('show_orders_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
      $PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;

    if (!$request->has('from') && !$request->has('to'))
      {
       // $from = Carbon::today()->subMonth()->toDateString();
       $from = Carbon::today()->subDays(1)->toDateString();
       $to   = Carbon::today()->toDateString();

        $startDate = $from.' 00:00:00';
        $endDate   = $to.' 23:59:59';
      }
      else
      {
        $validator = Validator::make($request->all(), [
          'from' => 'required_with:to|date|date_format:Y-m-d|before_or_equal:to',
          'to'   => 'required_with:from|date|date_format:Y-m-d|after_or_equal:from',
        ]);

        $from = $request->from;
        $to   = $request->to;

        $startDate = $from.' 00:00:00';
        $endDate   = $to.' 23:59:59';
      }

      $user = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();

       // case logged in user role is Admin
       if (in_array('Admin', $user_roles)) {
        // case search reports in general
          if ($request->has('vendor_id') && $request->vendor_id != '') {
            $vendorId = $request->vendor_id;

            $reports   = Order::whereHas('orderDetails', function($q) use ($vendorId){
                                  $q->where('vendor_id', $vendorId);
                                })->where('created_at', '>=', $startDate)
                                ->where('created_at', '<=', $endDate)
                                ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get();
                foreach ($reports as $one) {
                    $one['orderDetails'] = $one->orderDetails->where('vendor_id', $vendorId);
                      $one['order_total']  = $one->orderDetails->where('vendor_id', $vendorId)
                                                              ->sum('total');
                }                 
                
                  $total = Order::whereHas('orderDetails', function($q) use ($vendorId){
                                $q->where('vendor_id', $vendorId);
                              })->where('created_at', '>=', $startDate)
                              ->where('created_at', '<=', $endDate)
                              ->count();

            $data = VendorOrdersApiResource::collection($reports);
            return response()->json([
              'data'   => $data,
              'total'  => $total,
            ]);
          } // end case search reports in general
      // case search specific vendor reports
          else{
              $reports = Order::with('orderDetails')->where('created_at', '>=', $startDate)
                                ->where('created_at', '<=', $endDate)
                                ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                  ->orderBy($ordered_by, $sort_type)->get();           
                
              $total = Order::with('orderDetails')->where('created_at', '>=', $startDate)
                                ->where('created_at', '<=', $endDate)
                                ->count();

            $data = VendorOrdersApiResource::collection($reports);
            return response()->json([
              'data'   => $data,
              'total'  => $total,
            ]);
      } // end case specific vendor reports
      } 
       // case logged in user role is Vendor
      elseif (in_array('Vendor', $user_roles)) {
              $vendor        = AddVendor::where('userid_id', Auth::user()->id)->first();
              $vendor_id     = $vendor->id;

            $reports   = Order::with('orderDetails', function($q) use ($vendor_id){
                            $q->where('vendor_id', $vendor_id);
                          })->where('created_at', '>=', $startDate)
                          ->where('created_at', '<=', $endDate)
                          ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                  ->orderBy($ordered_by, $sort_type)->get();
          foreach ($reports as $one) {
                $one['orderDetails'] = $one->orderDetails->where('vendor_id', $vendor_id);
                  $one['order_total']  = $one->orderDetails->where('vendor_id', $vendor_id)->sum('total');
          }                 

            $total = Order::with('orderDetails', function($q) use ($vendor_id){
                          $q->where('vendor_id', $vendor_id);
                        })->where('created_at', '>=', $startDate)
                        ->where('created_at', '<=', $endDate)
                        ->count();

      $data = VendorOrdersApiResource::collection($reports);
      return response()->json([
        'data'   => $data,
        'total'  => $total,
      ]);
      } // end case vendor
      else{
        return response()->json([
                'message'  => 'un authorized access page due to permissions',
               ], 401);
      } // end if
    }*/

    public function sale_vendors_period_report($from, $to, $sale_type)
      {
        $startDate = $from.' 00:00:00';
        $endDate   = $to.' 23:59:59';
        $user = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

            if (in_array('Admin', $user_roles)) 
            {
/*                $top_total_customers = User::whereHas('roles', function($q){
                                              $q->where('title', '!=', 'Admin');
                                            })->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();

                $top_total_vendors = AddVendor::where('created_at', '>=', $startDate)
                                             ->where('created_at', '<=', $endDate)->count();
*/
                $top_total_products = Product::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();
                
                $top_total_orders = Order::whereHas('orderDetails', function($q) use ($startDate, $endDate){
                                  $q->where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)
                                    ->where('producttype_id', 1);
                                   /* ->whereHas('vendor', function($query){
                                      $query->where('type', 1);
                                    });*/
                                  })->get();
                foreach ($top_total_orders as $one_item) {
                  
                  $one_item['orderDetails'] = Orderdetail::where('order_id', $one_item->id)
                              ->where('created_at', '>=', $startDate)
                              ->where('created_at', '<=', $endDate)
                              ->where('approved', 1)
                              ->where('producttype_id', 1);
                              /*->whereHas('vendor', function($query){
                                     $query->where('type', 1);
                                    });*/
                  $one_item['order_total']  = Orderdetail::where('order_id', $one_item->id)
                              ->where('created_at', '>=', $startDate)
                              ->where('created_at', '<=', $endDate)
                              ->where('approved', 1)
                              ->where('producttype_id', 1)
                             /* ->whereHas('vendor', function($query){
                                      $query->where('type', 1);
                                    })*/
                              ->sum('total');
                }

                $get_orders = AdvancedApiReportGeneralCaseResource::collection($top_total_orders);

                $top_total_sale  = Orderdetail::where('created_at', '>=', $startDate)
                                          ->where('created_at', '<=', $endDate)
                                          ->where('approved', 1)
                                          ->where('producttype_id', 1)
                                          ->sum('total');

                $top_total_invoices  = Invoice::whereHas('vendor', function($q){
                                            $q->where('type', 1);
                                            })->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();

    // start flow chart section
                $order_days = Order::whereHas('orderDetails', function($q) use ($startDate, $endDate){
                                    $q->where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)
                                    ->where('producttype_id', 1);
                                    /*->whereHas('vendor', function($query){
                                      $query->where('type', 1);
                                    });*/
                                  })->pluck('created_at');

                //  return $total_orders;
                  $unique_days   = array();
                  $details_array = array();

                  foreach ($order_days as $order_day) {   // start foreach unique days
                      $created_at = $order_day;//->toDateString();
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
                                              ->where('approved', 1)
                                              ->where('producttype_id', 1)
                                              /*->whereHas('vendor', function($q){
                                            $q->where('type', 1);
                                            })*/
                                            ->sum('total');

                  array_push($details_array, [
                    'day'      => $unique_day,
                    'day_name' => Carbon::parse($unique_day)->format('l'),
                    'reports'  => [
                        'total_sale'       => $period_total_sale,
                      ], // end reports
                  ]); // end array push
                } // end foreach unique day
                      
    // end flow chart section
                    
                return response()->json([
                    'status_code' => 200, 
                    'message'     => 'success',
                    'total_orders'     => $get_orders,
                    'total_invoices'   => $top_total_invoices,
                    'total_sale'       => $top_total_sale,
                    // 'total_customers'  => $top_total_customers,
                    // 'total_vendors'    => $top_total_vendors,
                    'total_products'   => $top_total_products,
                   // 'sales_nalytics'   => $orders,
                    'period_details'   => $details_array,
                ], 200);
          } // case logged in user role is Vendor
          elseif (in_array('Vendor', $user_roles)) { 
          } // end case vendor
          else{
            return response()->json([
                    'status_code' => 401, 
                    // 'message'     => 'success',
                    'message'  => 'un authorized access page due to permissions',
                   ], 401);
          } // end if
      }  // end sale vendor report

      public function hotsale_vendors_period_report($from, $to, $sale_type)
      {
        $startDate = $from.' 00:00:00';
        $endDate   = $to.' 23:59:59';
        $user = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

            if (in_array('Admin', $user_roles)) 
            {
/*                $top_total_customers = User::whereHas('roles', function($q){
                                              $q->where('title', '!=', 'Admin');
                                            })->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();

                $top_total_vendors = AddVendor::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();
*/
                $top_total_products = Product::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();
                
                $top_total_orders = Order::whereHas('orderDetails', function($q) use ($startDate, $endDate){
                                    $q->where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)
                                    ->where('producttype_id', 2);
                                   /* ->whereHas('vendor', function($query){
                                      $query->where('type', 2);
                                    });*/
                                  })->get();
                foreach ($top_total_orders as $one_item) {
                  
                  $one_item['orderDetails'] = Orderdetail::where('order_id', $one_item->id)->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)
                              ->where('approved', 1)
                              ->where('producttype_id', 2);
                             /* ->whereHas('vendor', function($query){
                                      $query->where('type', 2);
                                    });*/
                  $one_item['order_total']  = Orderdetail::where('order_id', $one_item->id)->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)
                              ->where('approved', 1)
                              ->where('producttype_id', 2)
                              /*->whereHas('vendor', function($query){
                                      $query->where('type', 2);
                                    })*/
                              ->sum('total');
                }
            $get_orders = AdvancedApiReportGeneralCaseResource::collection($top_total_orders);

            /*$top_total_sale  = Invoice::whereHas('vendor', function($q){
                                            $q->where('type', 2);
                                            })->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->sum('invoice_total');*/
              $top_total_sale  = Orderdetail::where('created_at', '>=', $startDate)
                                          ->where('created_at', '<=', $endDate)
                                          ->where('approved', 1)
                                          ->where('producttype_id', 2)
                                          ->sum('total');

                $top_total_invoices  = Invoice::whereHas('vendor', function($q){
                                            $q->where('type', 2);
                                            })->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();

    // start flow chart section
                $order_days = Order::whereHas('orderDetails', function($q) use ($startDate, $endDate){
                                    $q->where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)
                                    ->where('producttype_id', 2);
                                    /*->whereHas('vendor', function($query){
                                      $query->where('type', 2);
                                    });*/
                                  })->pluck('created_at');

                //  return $total_orders;
                  $unique_days   = array();
                  $details_array = array();

                  foreach ($order_days as $order_day) {   // start foreach unique days
                      $created_at = $order_day;//->toDateString();
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

                      $period_total_sale  = Orderdetail::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)
                                              ->where('approved', 1)
                                              ->where('producttype_id', 2)
                                             /* ->whereHas('vendor', function($q){
                                            $q->where('type', 2);
                                            })*/
                                            ->sum('total');

                  array_push($details_array, [
                    'day'      => $unique_day,
                    'day_name' => Carbon::parse($unique_day)->format('l'),
                    'reports'  => [
                        'total_sale'       => $period_total_sale,
                      ], // end reports
                  ]); // end array push
                } // end foreach unique day
                      
    // end flow chart section
                    
                return response()->json([
                    'status_code' => 200, 
                    'message'     => 'success',
                    'total_orders'     => $get_orders,
                    'total_invoices'   => $top_total_invoices,
                    'total_sale'       => $top_total_sale,
                    // 'total_customers'  => $top_total_customers,
                    // 'total_vendors'    => $top_total_vendors,
                    'total_products'   => $top_total_products,
                   // 'sales_nalytics'   => $orders,
                    'period_details'   => $details_array,
                ], 200);
          } // case logged in user role is Vendor
          elseif (in_array('Vendor', $user_roles)) { 
          } // end case vendor
          else{
            return response()->json([
                    'status_code' => 401, 
                    // 'message'     => 'success',
                    'message'  => 'un authorized access page due to permissions',
                   ], 401);
          } // end if
      } // end hot sale vendor report 

      public function general_period_report($from, $to)
      {
        $startDate = $from.' 00:00:00';
        $endDate   = $to.' 23:59:59';
        $user = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

            if (in_array('Admin', $user_roles)) 
            {
/*                $top_total_customers = User::whereHas('roles', function($q){
                                              $q->where('title', '!=', 'Admin');
                                            })->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();

                $top_total_vendors = AddVendor::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();
*/
                $top_total_products = Product::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->get();
                
                $top_total_orders = Order::whereHas('orderDetails', function($q) use ($startDate, $endDate){
                                  $q->where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1);
                })->get();

                foreach ($top_total_orders as $one_item) {
                  
                  $one_item['orderDetails'] = $one_item->orderDetails->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)
                              ->where('approved', 1);
                  $one_item['order_total']  = $one_item->orderDetails->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)
                              ->where('approved', 1)->sum('total');
                }

                $get_orders = AdvancedApiReportGeneralCaseResource::collection($top_total_orders);


                /*$top_total_sale      = Invoice::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->sum('invoice_total');*/
                $top_total_sale      = Orderdetail::where('created_at', '>=', $startDate)
                                          ->where('created_at', '<=', $endDate)
                                          ->where('approved', 1)->sum('total');

                $top_total_invoices  = Invoice::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->get();

    // start flow chart section
               /* $order_days = Order::with('orderDetails')->where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)
                                    ->pluck('created_at');*/
                $order_days = Orderdetail::where('created_at', '>=', $startDate)
                                ->where('created_at', '<=', $endDate)
                                ->where('approved', 1)
                                ->pluck('created_at');

                //  return $total_orders;
                  $unique_days   = array();
                  $details_array = array();

                  foreach ($order_days as $order_day) {   // start foreach unique days
                     // $created_at = $order_day;//->toDateString();
                      $created_at = $order_day;//->toDateString();
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

                     /* $period_total_sale      = Invoice::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->sum('invoice_total');*/
                    $period_total_sale = Orderdetail::where('approved', 1)
                                          ->where('created_at', '>=', $startDate)
                                          ->where('created_at', '<=', $endDate)->sum('total');

                  array_push($details_array, [
                    'day'      => $unique_day,
                    'day_name' => Carbon::parse($unique_day)->format('l'),
                    'reports'  => [
                        'total_sale'       => $period_total_sale,
                      ], // end reports
                  ]); // end array push
                } // end foreach unique day
                      
    // end flow chart section
                    
                return response()->json([
                    'status_code' => 200, 
                    'message'     => 'success',
                    'total_orders'     => $get_orders,
                    'total_invoices'   => $top_total_invoices,
                    'total_sale'       => $top_total_sale,
                    // 'total_customers'  => $top_total_customers,
                    // 'total_vendors'    => $top_total_vendors,
                    'total_products'   => $top_total_products,
                   // 'sales_nalytics'   => $orders,
                    'period_details'   => $details_array,
                ], 200);
          } // case logged in user role is Vendor
          elseif (in_array('Vendor', $user_roles)) { 
             $vendor        = AddVendor::where('userid_id', Auth::user()->id)->first();
             $vendorId     = $vendor->id;
            /*                $top_total_customers = User::whereHas('roles', function($q){
                                              $q->where('title', '!=', 'Admin');
                                            })->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();

                $top_total_vendors = AddVendor::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();
*/
                $top_total_products = Product::where('vendor_id', $vendorId)
                                              ->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->get();
                
                /*$top_total_orders = Order::whereHas('orderDetails', function($q)use ($vendorId){
                          $q->where('vendor_id', $vendorId)->where('approved', 1);
                        })->where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)
                                    ->get();*/
              $top_total_orders = Order::whereHas('orderDetails', function($q) use ($vendorId, $startDate, $endDate){
                          $q->where('vendor_id', $vendorId)->where('approved', 1)
                            ->where('created_at', '>=', $startDate)
                            ->where('created_at', '<=', $endDate);
                        })->get();

              foreach ($top_total_orders as $one_item) {
                  
                  $one_item['orderDetails'] = $one_item->orderDetails->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)
                              ->where('approved', 1)->where('vendor_id', $vendorId);
                  $one_item['order_total']  = $one_item->orderDetails->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)
                              ->where('approved', 1)->where('vendor_id', $vendorId)->sum('total');
                }
                $get_orders = AdvancedApiReportGeneralCaseResource::collection($top_total_orders);

                /*$top_total_sale      = Invoice::where('vendor_id', $vendorId)
                                              ->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->sum('invoice_total');*/
              $top_total_sale      = Orderdetail::where('vendor_id', $vendorId)
                                        ->where('approved', 1)
                                        ->where('created_at', '>=', $startDate)
                                        ->where('created_at', '<=', $endDate)->sum('total');

                $top_total_invoices  = Invoice::where('vendor_id', $vendorId)
                                              ->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->get();

    // start flow chart section
                $order_days = Order::whereHas('orderDetails', function($q) use ($vendorId, $startDate, $endDate){
                          $q->where('vendor_id', $vendorId)->where('approved', 1)
                            ->where('created_at', '>=', $startDate)
                            ->where('created_at', '<=', $endDate);
                        })->pluck('created_at');

                //  return $total_orders;
                  $unique_days   = array();
                  $details_array = array();

                  foreach ($order_days as $order_day) {   // start foreach unique days
                      //$created_at = $order_day;//->toDateString();
                    $created_at = $order_day;//->toDateString();
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

                      /*$period_total_sale = Invoice::where('vendor_id', $vendorId)
                                              ->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->sum('invoice_total');*/
                  $period_total_sale = Orderdetail::where('vendor_id', $vendorId)
                                        ->where('approved', 1)
                                        ->where('created_at', '>=', $startDate)
                                        ->where('created_at', '<=', $endDate)->sum('total');

                  array_push($details_array, [
                    'day'      => $unique_day,
                    'day_name' => Carbon::parse($unique_day)->format('l'),
                    'reports'  => [
                        'total_sale'  => $period_total_sale,
                      ], // end reports
                  ]); // end array push
                } // end foreach unique day
                      
    // end flow chart section
                    
                return response()->json([
                    'status_code' => 200, 
                    'message'     => 'success',
                    'total_orders'     => $get_orders,
                    'total_invoices'   => $top_total_invoices,
                    'total_sale'       => $top_total_sale,
                    // 'total_customers'  => $top_total_customers,
                    // 'total_vendors'    => $top_total_vendors,
                    'total_products'   => $top_total_products,
                   // 'sales_nalytics'   => $orders,
                    'period_details'   => $details_array,
                ], 200);
          } // end case vendor
          /* start manager */

      elseif (in_array('Manager', $user_roles)) 
      {
       // abort_if(Gate::denies('stores_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $exist_staff   = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendorId     = $vendor->id;
        // $staff_stores = $exist_staff->stores->pluck('id')->toArray();
       // return $staff_stores;

         /*                $top_total_customers = User::whereHas('roles', function($q){
                                              $q->where('title', '!=', 'Admin');
                                            })->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();

                $top_total_vendors = AddVendor::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();
*/
                $top_total_products = Product::where('vendor_id', $vendorId)
                                              ->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->get();
                
                /*$top_total_orders = Order::whereHas('orderDetails', function($q)use ($vendorId){
                          $q->where('vendor_id', $vendorId)->where('approved', 1);
                        })->where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)
                                    ->get();*/
              $top_total_orders = Order::whereHas('orderDetails', function($q) use ($vendorId, $startDate, $endDate){
                          $q->where('vendor_id', $vendorId)->where('approved', 1)
                            ->where('created_at', '>=', $startDate)
                            ->where('created_at', '<=', $endDate);
                        })->get();

              foreach ($top_total_orders as $one_item) {
                  
                  $one_item['orderDetails'] = $one_item->orderDetails->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)
                              ->where('approved', 1)->where('vendor_id', $vendorId);
                  $one_item['order_total']  = $one_item->orderDetails->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)
                              ->where('approved', 1)->where('vendor_id', $vendorId)->sum('total');
                }
                $get_orders = AdvancedApiReportGeneralCaseResource::collection($top_total_orders);

                /*$top_total_sale      = Invoice::where('vendor_id', $vendorId)
                                              ->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->sum('invoice_total');*/
              $top_total_sale      = Orderdetail::where('vendor_id', $vendorId)
                                        ->where('approved', 1)
                                        ->where('created_at', '>=', $startDate)
                                        ->where('created_at', '<=', $endDate)->sum('total');

                $top_total_invoices  = Invoice::where('vendor_id', $vendorId)
                                              ->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->get();

    // start flow chart section
                $order_days = Order::whereHas('orderDetails', function($q) use ($vendorId, $startDate, $endDate){
                          $q->where('vendor_id', $vendorId)->where('approved', 1)
                            ->where('created_at', '>=', $startDate)
                            ->where('created_at', '<=', $endDate);
                        })->pluck('created_at');

                //  return $total_orders;
                  $unique_days   = array();
                  $details_array = array();

                  foreach ($order_days as $order_day) {   // start foreach unique days
                      //$created_at = $order_day;//->toDateString();
                    $created_at = $order_day;//->toDateString();
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

                      /*$period_total_sale = Invoice::where('vendor_id', $vendorId)
                                              ->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->sum('invoice_total');*/
                  $period_total_sale = Orderdetail::where('vendor_id', $vendorId)
                                        ->where('approved', 1)
                                        ->where('created_at', '>=', $startDate)
                                        ->where('created_at', '<=', $endDate)->sum('total');

                  array_push($details_array, [
                    'day'      => $unique_day,
                    'day_name' => Carbon::parse($unique_day)->format('l'),
                    'reports'  => [
                        'total_sale'  => $period_total_sale,
                      ], // end reports
                  ]); // end array push
                } // end foreach unique day
                      
    // end flow chart section
                    
                return response()->json([
                    'status_code' => 200, 
                    'message'     => 'success',
                    'total_orders'     => $get_orders,
                    'total_invoices'   => $top_total_invoices,
                    'total_sale'       => $top_total_sale,
                    // 'total_customers'  => $top_total_customers,
                    // 'total_vendors'    => $top_total_vendors,
                    'total_products'   => $top_total_products,
                   // 'sales_nalytics'   => $orders,
                    'period_details'   => $details_array,
                ], 200);
      }
          /* end manager */
          else{
            return response()->json([
                    'status_code' => 401, 
                    // 'message'     => 'success',
                    'message'  => 'un authorized access page due to permissions',
                   ], 401);
          } // end if
      } // end general report 

      public function vendor_period_report($from, $to, $vendor)
      {
        $startDate  = $from.' 00:00:00';
        $endDate    = $to.' 23:59:59';
        $vendor_id  = $vendor;
        $user       = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

            if (in_array('Admin', $user_roles)) 
            {
/*                $top_total_customers = User::whereHas('roles', function($q){
                                              $q->where('title', '!=', 'Admin');
                                            })->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();

                $top_total_vendors = AddVendor::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();
*/
                $top_total_products = Product::where('vendor_id', $vendor_id)
                                              ->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->get();
                
                $top_total_orders = Order::whereHas('orderDetails', function($q) use ($vendor_id, $startDate, $endDate){
                                  $q->where('vendor_id', $vendor_id)
                                    ->where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1);
                                  })->get();
                foreach ($top_total_orders as $one_item) {
                  
                  $one_item['orderDetails'] = $one_item->orderDetails->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)
                              ->where('approved', 1)->where('vendor_id', $vendor_id);
                  $one_item['order_total']  = $one_item->orderDetails->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)
                              ->where('approved', 1)->where('vendor_id', $vendor_id)->sum('total');
                }
                $get_orders = AdvancedApiReportVendorCaseResource::collection($top_total_orders);

                $top_total_sale      = Orderdetail::where('vendor_id', $vendor_id)
                                              ->where('approved', 1)
                                              ->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->sum('total');

                $top_total_invoices  = Invoice::where('vendor_id', $vendor_id)
                                              ->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->get();

    // start flow chart section
                $order_days = Order::whereHas('orderDetails', function($q) use ($vendor_id, $startDate, $endDate){
                                $q->where('vendor_id', $vendor_id)
                                    ->where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1);
                                })->pluck('created_at');

                //  return $total_orders;
                  $unique_days   = array();
                  $details_array = array();

                  foreach ($order_days as $order_day) {   // start foreach unique days
                      $created_at = $order_day;//->toDateString();
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

                      $period_total_sale      = Orderdetail::where('vendor_id', $vendor_id)
                                              ->where('approved', 1)
                                              ->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->sum('total');

                  array_push($details_array, [
                    'day'      => $unique_day,
                    'day_name' => Carbon::parse($unique_day)->format('l'),
                    'reports'  => [
                        'total_sale'       => $period_total_sale,
                      ], // end reports
                  ]); // end array push
                } // end foreach unique day
                      
    // end flow chart section
                    
                return response()->json([
                    'status_code' => 200, 
                    'message'     => 'success',
                    'total_orders'     => $get_orders,
                    'total_invoices'   => $top_total_invoices,
                    'total_sale'       => $top_total_sale,
                    // 'total_customers'  => $top_total_customers,
                    // 'total_vendors'    => $top_total_vendors,
                    'total_products'   => $top_total_products,
                   // 'sales_nalytics'   => $orders,
                    'period_details'   => $details_array,
                ], 200);
          } // case logged in user role is Vendor
          elseif (in_array('Vendor', $user_roles)) { 
          } // end case vendor
          else{
            return response()->json([
                    'status_code' => 401, 
                    // 'message'     => 'success',
                    'message'  => 'un authorized access page due to permissions',
                   ], 401);
          } // end if
      }  // end function vendor report

      public function part_category_period_report($from, $to, $part_category)
      {
        $startDate  = $from.' 00:00:00';
        $endDate    = $to.' 23:59:59';
        $part_category_id  = $part_category;
        $count_partcat_invoices = array();
        $sum_partcat_invoices   = 0;
        $user       = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

            if (in_array('Admin', $user_roles)) 
            {
/*                $top_total_customers = User::whereHas('roles', function($q){
                                              $q->where('title', '!=', 'Admin');
                                            })->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();

                $top_total_vendors = AddVendor::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();
*/
                $top_total_products = Product::where('part_category_id', $part_category_id)
                                              ->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->get();
                
                $top_total_orders = Order::whereHas('orderDetails', function($q) use ($part_category_id, $startDate, $endDate){
                                      $q->where('created_at', '>=', $startDate)
                                        ->where('created_at', '<=', $endDate)
                                        ->where('approved', 1)
                                        ->whereHas('product', function($query) use($part_category_id){
                                       $query->whereHas('part_category', function($index) use($part_category_id){
                                          $index->where('id', $part_category_id);
                                        });
                                  });
                                  })->get();
                foreach ($top_total_orders as $one_item) {
                  
                  $one_item['orderDetails'] = Orderdetail::where('created_at', '>=', $startDate)
                                        ->where('created_at', '<=', $endDate)
                                        ->where('approved', 1)->where('order_id', $one_item->id)->whereHas('product', function($query) use($part_category_id){
                                       $query->whereHas('part_category', function($index) use($part_category_id){
                                          $index->where('id', $part_category_id);
                                        });
                                  })->get();
                  $one_item['order_total']  = Orderdetail::where('created_at', '>=', $startDate)
                                        ->where('created_at', '<=', $endDate)
                                        ->where('approved', 1)->where('order_id', $one_item->id)->whereHas('product', function($query) use($part_category_id){
                                       $query->whereHas('part_category', function($index) use($part_category_id){
                                          $index->where('id', $part_category_id);
                                        });
                                  })->sum('total');
                }

                $get_orders = AdvancedApiReportPartCatCaseResource::collection($top_total_orders);

                $top_total_invoices  = Invoice::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->get();

                $sum_part_invoices = Orderdetail::where('approved', 1)
                                    ->whereHas('product', function($query) use($part_category_id){
                                       $query->whereHas('part_category', function($index) use($part_category_id){
                                          $index->where('id', $part_category_id);
                                        });
                                      })->where('created_at', '>=', $startDate)
                                      ->where('created_at', '<=', $endDate)->sum('total');

     /* $countb = Invoice::whereIn('order_id', $part_invoices)->get();
      foreach ($countb as $value) {
        $found_part = Orderdetail::where('order_id', $value->order_id)
                                ->where('vendor_id', $value->vendor_id)->first()
                               ->product->part_category_id;
         if ($found_part == $part_category_id) {
          array_push($count_partcat_invoices, $value);
          $sum_partcat_invoices+= $value->invoice_total;
         }else{
          continue;
        }
      }*/

    // start flow chart section
                $order_days = Order::whereHas('orderDetails', function($q) use ($part_category_id, $startDate, $endDate){
                                  $q->where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)->whereHas('product', function($query) use($part_category_id){
                                       $query->whereHas('part_category', function($index) use($part_category_id){
                                          $index->where('id', $part_category_id);
                                        });
                                  });
                                  })->pluck('created_at');
      
                //  return $total_orders;
                  $unique_days   = array();
                  $details_array = array();

                  foreach ($order_days as $order_day) {   // start foreach unique days
                      $created_at = $order_day;//->toDateString();
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

                      $period_total_sale = Orderdetail::where('approved', 1)->whereHas('product', function($query) use($part_category_id){
                                       $query->whereHas('part_category', function($index) use($part_category_id){
                                          $index->where('id', $part_category_id);
                                        });
                                      })->where('created_at', '>=', $startDate)
                                        ->where('created_at', '<=', $endDate)->sum('total');

                  array_push($details_array, [
                    'day'      => $unique_day,
                    'day_name' => Carbon::parse($unique_day)->format('l'),
                    'reports'  => [
                        'total_sale'       => $period_total_sale,
                      ], // end reports
                  ]); // end array push
                } // end foreach unique day
                      
    // end flow chart section
                    
                return response()->json([
                    'status_code' => 200, 
                    'message'     => 'success',
                    'total_orders'     => $get_orders,
                    'total_invoices'   => $top_total_invoices,
                    'total_sale'       => $sum_part_invoices,
                    // 'total_customers'  => $top_total_customers,
                    // 'total_vendors'    => $top_total_vendors,
                    'total_products'   => $top_total_products,
                   // 'sales_nalytics'   => $orders,
                    'period_details'   => $details_array,
                ], 200);
          } // case logged in user role is Vendor
          elseif (in_array('Vendor', $user_roles)) 
          { 
             $vendor        = AddVendor::where('userid_id', Auth::user()->id)->first();
             $vendorId     = $vendor->id;
            /*                $top_total_customers = User::whereHas('roles', function($q){
                                              $q->where('title', '!=', 'Admin');
                                            })->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();

                $top_total_vendors = AddVendor::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();
*/
                $top_total_products = Product::where('vendor_id', $vendorId)
                                              ->where('part_category_id', $part_category_id)
                                              ->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->get();
                
                $top_total_orders = Order::whereHas('orderDetails', function($q) use ($vendorId, $part_category_id, $startDate, $endDate){
                                  $q->where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)->where('vendor_id', $vendorId)->whereHas('product', function($query) use($part_category_id){
                                       $query->whereHas('part_category', function($index) use($part_category_id){
                                          $index->where('id', $part_category_id);
                                        });
                                  });
                                  })->get();
                foreach ($top_total_orders as $one_item) {
                  
                  $one_item['orderDetails'] = Orderdetail::where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)->where('vendor_id', $vendorId)
                  ->where('order_id', $one_item->id)->whereHas('product', function($query) use($part_category_id){
                                       $query->whereHas('part_category', function($index) use($part_category_id){
                                          $index->where('id', $part_category_id);
                                        });
                                  })->get();
                  $one_item['order_total']  = Orderdetail::where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)->where('vendor_id', $vendorId)
                  ->where('order_id', $one_item->id)->whereHas('product', function($query) use($part_category_id){
                                       $query->whereHas('part_category', function($index) use($part_category_id){
                                          $index->where('id', $part_category_id);
                                        });
                                  })->sum('total');
                }

                $get_orders = AdvancedApiReportPartCatCaseResource::collection($top_total_orders);

                $top_total_invoices  = Invoice::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->get();

                $sum_part_invoices = Orderdetail::where('vendor_id', $vendorId)->where('approved', 1)
                                       ->whereHas('product', function($query) use($part_category_id){
                                       $query->whereHas('part_category', function($index) use($part_category_id){
                                          $index->where('id', $part_category_id);
                                        });
                                      })->where('created_at', '>=', $startDate)
                                      ->where('created_at', '<=', $endDate)->sum('total');
// return $part_invoices;
     /* $countb =  Invoice::where('vendor_id', $vendorId)->whereIn('order_id', $part_invoices)->get();
     // return $countb;
      foreach ($countb as $value) 
      {
        $found_products = Orderdetail::where('order_id', $value->order_id)
                                ->where('vendor_id', $value->vendor_id)
                                ->get();
        foreach ($found_products as $found_product) {
          $found_product_spec = Product::where('id', $found_product->product_id)->first();
        

         if ($found_product_spec->part_category_id == $part_category_id) {
          // $count_partcat_invoices++;
          $value['invoice_total'] = Orderdetail::where('order_id', $value->order_id)
                                ->where('vendor_id', $value->vendor_id)
                                ->whereHas('product', function($query) use ($part_category_id){
                                       $query->whereHas('part_category', function($index) use($part_category_id){
                                          $index->where('id', $part_category_id);
                                        });
                                      })->sum('total');
          array_push($count_partcat_invoices, $value);
          $sum_partcat_invoices+= $value->invoice_total;
         }else{
          continue;
        }
    }  // end foreach second one
      } // end foreach first one*/

    // start flow chart section
                $order_days = Order::whereHas('orderDetails', function($q) use ($vendorId, $part_category_id, $startDate, $endDate){
                                       $q->where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)->where('vendor_id', $vendorId)
                                    ->whereHas('product', function($query) use($part_category_id){
                                       $query->whereHas('part_category', function($index) use($part_category_id){
                                          $index->where('id', $part_category_id);
                                        });
                                  });
                                  })->pluck('created_at');
      
                //  return $total_orders;
                  $unique_days   = array();
                  $details_array = array();

                  foreach ($order_days as $order_day) {   // start foreach unique days
                      $created_at = $order_day;//->toDateString();
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

                      $period_total_sale = Orderdetail::where('vendor_id', $vendorId)
                                        ->where('approved', 1)
                                        ->whereHas('product', function($query) use($part_category_id){
                                       $query->whereHas('part_category', function($index) use($part_category_id){
                                          $index->where('id', $part_category_id);
                                        });
                                      })->where('created_at', '>=', $startDate)
                                        ->where('created_at', '<=', $endDate)->sum('total');

                  array_push($details_array, [
                    'day'      => $unique_day,
                    'day_name' => Carbon::parse($unique_day)->format('l'),
                    'reports'  => [
                        'total_sale'       => $period_total_sale,
                      ], // end reports
                  ]); // end array push
                } // end foreach unique day
                      
    // end flow chart section
                    
                return response()->json([
                    'status_code' => 200, 
                    'message'     => 'success',
                    'total_orders'     => $get_orders,
                    'total_invoices'   => $top_total_invoices,
                    'total_sale'       => $sum_part_invoices,
                    // 'total_customers'  => $top_total_customers,
                    // 'total_vendors'    => $top_total_vendors,
                    'total_products'   => $top_total_products,
                   // 'sales_nalytics'   => $orders,
                    'period_details'   => $details_array,
                ], 200);
          } // end case vendor
          elseif (in_array('Manager', $user_roles)) 
      {
       // abort_if(Gate::denies('stores_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendorId     = $vendor->id;
        // $staff_stores = $exist_staff->stores->pluck('id')->toArray();
       // return $staff_stores;

        /*                $top_total_customers = User::whereHas('roles', function($q){
                                              $q->where('title', '!=', 'Admin');
                                            })->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();

                $top_total_vendors = AddVendor::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();
*/
                $top_total_products = Product::where('vendor_id', $vendorId)
                                              ->where('part_category_id', $part_category_id)
                                              ->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->get();
                
                $top_total_orders = Order::whereHas('orderDetails', function($q) use ($vendorId, $part_category_id, $startDate, $endDate){
                                  $q->where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)->where('vendor_id', $vendorId)->whereHas('product', function($query) use($part_category_id){
                                       $query->whereHas('part_category', function($index) use($part_category_id){
                                          $index->where('id', $part_category_id);
                                        });
                                  });
                                  })->get();
                foreach ($top_total_orders as $one_item) {
                  
                  $one_item['orderDetails'] = Orderdetail::where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)->where('vendor_id', $vendorId)
                  ->where('order_id', $one_item->id)->whereHas('product', function($query) use($part_category_id){
                                       $query->whereHas('part_category', function($index) use($part_category_id){
                                          $index->where('id', $part_category_id);
                                        });
                                  })->get();
                  $one_item['order_total']  = Orderdetail::where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)->where('vendor_id', $vendorId)
                  ->where('order_id', $one_item->id)->whereHas('product', function($query) use($part_category_id){
                                       $query->whereHas('part_category', function($index) use($part_category_id){
                                          $index->where('id', $part_category_id);
                                        });
                                  })->sum('total');
                }

                $get_orders = AdvancedApiReportPartCatCaseResource::collection($top_total_orders);

                $top_total_invoices  = Invoice::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->get();

                $sum_part_invoices = Orderdetail::where('vendor_id', $vendorId)->where('approved', 1)
                                       ->whereHas('product', function($query) use($part_category_id){
                                       $query->whereHas('part_category', function($index) use($part_category_id){
                                          $index->where('id', $part_category_id);
                                        });
                                      })->where('created_at', '>=', $startDate)
                                      ->where('created_at', '<=', $endDate)->sum('total');
// return $part_invoices;
     /* $countb =  Invoice::where('vendor_id', $vendorId)->whereIn('order_id', $part_invoices)->get();
     // return $countb;
      foreach ($countb as $value) 
      {
        $found_products = Orderdetail::where('order_id', $value->order_id)
                                ->where('vendor_id', $value->vendor_id)
                                ->get();
        foreach ($found_products as $found_product) {
          $found_product_spec = Product::where('id', $found_product->product_id)->first();
        

         if ($found_product_spec->part_category_id == $part_category_id) {
          // $count_partcat_invoices++;
          $value['invoice_total'] = Orderdetail::where('order_id', $value->order_id)
                                ->where('vendor_id', $value->vendor_id)
                                ->whereHas('product', function($query) use ($part_category_id){
                                       $query->whereHas('part_category', function($index) use($part_category_id){
                                          $index->where('id', $part_category_id);
                                        });
                                      })->sum('total');
          array_push($count_partcat_invoices, $value);
          $sum_partcat_invoices+= $value->invoice_total;
         }else{
          continue;
        }
    }  // end foreach second one
      } // end foreach first one*/

    // start flow chart section
                $order_days = Order::whereHas('orderDetails', function($q) use ($vendorId, $part_category_id, $startDate, $endDate){
                                       $q->where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)->where('vendor_id', $vendorId)
                                    ->whereHas('product', function($query) use($part_category_id){
                                       $query->whereHas('part_category', function($index) use($part_category_id){
                                          $index->where('id', $part_category_id);
                                        });
                                  });
                                  })->pluck('created_at');
      
                //  return $total_orders;
                  $unique_days   = array();
                  $details_array = array();

                  foreach ($order_days as $order_day) {   // start foreach unique days
                      $created_at = $order_day;//->toDateString();
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

                      $period_total_sale = Orderdetail::where('vendor_id', $vendorId)
                                        ->where('approved', 1)
                                        ->whereHas('product', function($query) use($part_category_id){
                                       $query->whereHas('part_category', function($index) use($part_category_id){
                                          $index->where('id', $part_category_id);
                                        });
                                      })->where('created_at', '>=', $startDate)
                                        ->where('created_at', '<=', $endDate)->sum('total');

                  array_push($details_array, [
                    'day'      => $unique_day,
                    'day_name' => Carbon::parse($unique_day)->format('l'),
                    'reports'  => [
                        'total_sale'       => $period_total_sale,
                      ], // end reports
                  ]); // end array push
                } // end foreach unique day
                      
    // end flow chart section
                    
                return response()->json([
                    'status_code' => 200, 
                    'message'     => 'success',
                    'total_orders'     => $get_orders,
                    'total_invoices'   => $top_total_invoices,
                    'total_sale'       => $sum_part_invoices,
                    // 'total_customers'  => $top_total_customers,
                    // 'total_vendors'    => $top_total_vendors,
                    'total_products'   => $top_total_products,
                   // 'sales_nalytics'   => $orders,
                    'period_details'   => $details_array,
                ], 200);
      }
          else{
            return response()->json([
                    'status_code' => 401, 
                    // 'message'     => 'success',
                    'message'  => 'un authorized access page due to permissions',
                   ], 401);
          } // end if
      }  // end function part category report

      public function category_product_period_report($from, $to, $product, $part_category)
      {
        $startDate  = $from.' 00:00:00';
        $endDate    = $to.' 23:59:59';
        $product_id  = $product;
        $part_category_id  = $part_category;
        $count_partcat_invoices = array();
        $sum_partcat_invoices   = 0;
        $user       = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

            if (in_array('Admin', $user_roles)) 
            {
/*                $top_total_customers = User::whereHas('roles', function($q){
                                              $q->where('title', '!=', 'Admin');
                                            })->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();

                $top_total_vendors = AddVendor::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();
*/
                $top_total_products = Product::where('part_category_id', $part_category_id)
                                              ->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->get();
                
                $top_total_orders = Order::whereHas('orderDetails', function($q) use ($product_id, $part_category_id, $startDate, $endDate){
                                  $q->where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)->where('product_id', $product_id)
                                    ->whereHas('product', function($query) use($product_id, $part_category_id){
                                       $query->whereHas('part_category', function($index) use($product_id, $part_category_id){
                                          $index->where('id', $part_category_id);
                                        });
                                  });
                                  })->get();
                foreach ($top_total_orders as $one_item) {
                  
                  $one_item['orderDetails'] = Orderdetail::where('approved', 1)
                                              ->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)
                                              ->where('order_id', $one_item->id)
                                              ->where('product_id', $product_id)->whereHas('product', function($query) use($part_category_id){
                                       $query->whereHas('part_category', function($index) use($part_category_id){
                                          $index->where('id', $part_category_id);
                                        });
                                  })->get();
                  $one_item['order_total']  = Orderdetail::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)
                                              ->where('approved', 1)
                                              ->where('order_id', $one_item->id)
                                              ->where('product_id', $product_id)->whereHas('product', function($query) use($part_category_id){
                                       $query->whereHas('part_category', function($index) use($part_category_id){
                                          $index->where('id', $part_category_id);
                                        });
                                  })->sum('total');
                }

                $get_orders = AdvancedApiReportPartProdCaseResource::collection($top_total_orders);

                $top_total_invoices  = Invoice::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->get();

                $sum_part_invoices = Orderdetail::where('product_id', $product_id)
                                      ->where('approved', 1)
                                      ->whereHas('product', function($query) use($part_category_id){
                                       $query->whereHas('part_category', function($index) use($part_category_id){
                                          $index->where('id', $part_category_id);
                                        });
                                      })->where('created_at', '>=', $startDate)
                                      ->where('created_at', '<=', $endDate)->sum('total');

      /*$countb =  Invoice::whereIn('order_id', $part_invoices)->get();
      foreach ($countb as $value) {
        $found_part = Orderdetail::where('order_id', $value->order_id)
                                ->where('vendor_id', $value->vendor_id)->first();
         if ($found_part->product->part_category_id == $part_category_id && $found_part->product_id == $product_id) {
          array_push($count_partcat_invoices, $value);
          // $count_partcat_invoices++;
          $sum_partcat_invoices+= $value->invoice_total;
         }else{
          continue;
        }
      }*/
    // start flow chart section
                $order_days = Order::whereHas('orderDetails', function($q) use ($product_id, $part_category_id, $startDate, $endDate){
                                  $q->where('product_id', $product_id)
                                    ->where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)
                                    ->whereHas('product', function($query) use($product_id, $part_category_id){
                                       $query->whereHas('part_category', function($index) use($product_id, $part_category_id){
                                          $index->where('id', $part_category_id);
                                        });
                                  });
                                  })
                                    ->pluck('created_at');
      
                //  return $total_orders;
                  $unique_days   = array();
                  $details_array = array();

                  foreach ($order_days as $order_day) {   // start foreach unique days
                      $created_at = $order_day;//->toDateString();
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

                      $period_total_sale = Orderdetail::where('approved', 1)
                                      ->where('product_id', $product_id)
                                      ->whereHas('product', function($query) use($part_category_id){
                                       $query->whereHas('part_category', function($index) use($part_category_id){
                                          $index->where('id', $part_category_id);
                                        });
                                      })->where('created_at', '>=', $startDate)
                                        ->where('created_at', '<=', $endDate)->sum('total');

                  array_push($details_array, [
                    'day'      => $unique_day,
                    'day_name' => Carbon::parse($unique_day)->format('l'),
                    'reports'  => [
                        'total_sale'       => $period_total_sale,
                      ], // end reports
                  ]); // end array push
                } // end foreach unique day
                      
    // end flow chart section
                    
                return response()->json([
                    'status_code' => 200, 
                    'message'     => 'success',
                    'total_orders'     => $get_orders,
                    'total_invoices'   => $top_total_invoices,
                    'total_sale'       => $sum_part_invoices,
                    // 'total_customers'  => $top_total_customers,
                    // 'total_vendors'    => $top_total_vendors,
                    'total_products'   => $top_total_products,
                   // 'sales_nalytics'   => $orders,
                    'period_details'   => $details_array,
                ], 200);
          } // case logged in user role is Vendor
          elseif (in_array('Vendor', $user_roles)) 
          { 
            $vendor       = AddVendor::where('userid_id', Auth::user()->id)->first();
            $vendorId     = $vendor->id;
/*                $top_total_customers = User::whereHas('roles', function($q){
                                              $q->where('title', '!=', 'Admin');
                                            })->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();

                $top_total_vendors = AddVendor::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();
*/
                $top_total_products = Product::where('vendor_id', $vendorId)
                                              ->where('part_category_id', $part_category_id)
                                              ->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->get();
                
                $top_total_orders = Order::whereHas('orderDetails', function($q) use ($product_id, $part_category_id, $vendorId, $startDate, $endDate){
                                       $q->where('vendor_id', $vendorId)
                                    ->where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)
                                    ->where('product_id', $product_id)
                                       ->whereHas('product', function($query) use($product_id, $part_category_id){
                                       $query->whereHas('part_category', function($index) use($product_id, $part_category_id){
                                          $index->where('id', $part_category_id);
                                        });
                                  });
                                  })->get();
                foreach ($top_total_orders as $one_item) {
                  
                  $one_item['orderDetails'] = Orderdetail::where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)
                                    ->where('order_id', $one_item->id)
                                              ->where('vendor_id', $vendorId)
                                              ->where('approved', 1)
                                              ->where('product_id', $product_id)->whereHas('product', function($query) use($part_category_id){
                                       $query->whereHas('part_category', function($index) use($part_category_id){
                                          $index->where('id', $part_category_id);
                                        });
                                  })->get();
                  $one_item['order_total']  = Orderdetail::where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)
                                    ->where('order_id', $one_item->id)
                                              ->where('vendor_id', $vendorId)
                                              ->where('approved', 1)
                                              ->where('product_id', $product_id)->whereHas('product', function($query) use($part_category_id){
                                       $query->whereHas('part_category', function($index) use($part_category_id){
                                          $index->where('id', $part_category_id);
                                        });
                                  })->sum('total');
                }

                $get_orders = AdvancedApiReportPartProdCaseResource::collection($top_total_orders);

                $top_total_invoices  = Invoice::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->get();

                $sum_part_invoices = Orderdetail::where('vendor_id', $vendorId)
                                      ->where('product_id', $product_id)
                                      ->where('approved', 1)
                                      ->whereHas('product', function($query) use($part_category_id){
                                       $query->whereHas('part_category', function($index) use($part_category_id){
                                          $index->where('id', $part_category_id);
                                        });
                                      })->where('created_at', '>=', $startDate)
                                      ->where('created_at', '<=', $endDate)->sum('total');

      /*$countb =  Invoice::where('vendor_id', $vendorId)->whereIn('order_id', $part_invoices)->get();
      foreach ($countb as $value) {
        $found_part_cats = Orderdetail::where('order_id', $value->order_id)
                                ->where('vendor_id', $value->vendor_id)->get();

         foreach ($found_part_cats as $found_part_cat) {
          $spec_prod = Product::find($found_part_cat->product_id);
            if ($found_part_cat->product_id == $product_id && $spec_prod->part_category_id == $part_category_id) {
            // $count_partcat_invoices++;
              $value['invoice_total'] = Orderdetail::where('order_id', $value->order_id)
                                ->where('vendor_id', $value->vendor_id)->where('product_id', $product_id)
                                ->where('id', $found_part_cat->id)
                                ->sum('total');
            array_push($count_partcat_invoices, $value);
            $sum_partcat_invoices+= $value->invoice_total;
           }else{
            continue;
          }
        }
      }*/

    // start flow chart section
                $order_days = Order::whereHas('orderDetails', function($q) use ($vendorId, $product_id, $part_category_id, $startDate, $endDate){
                                  $q->where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)->where('vendor_id', $vendorId)
                                    ->where('product_id', $product_id)
                                         ->whereHas('product', function($query) use($product_id, $part_category_id){
                                       $query->whereHas('part_category', function($index) use($product_id, $part_category_id){
                                          $index->where('id', $part_category_id);
                                        });
                                  });
                                  })->pluck('created_at');
      
                //  return $total_orders;
                  $unique_days   = array();
                  $details_array = array();

                  foreach ($order_days as $order_day) {   // start foreach unique days
                      $created_at = $order_day;//->toDateString();
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

                      $period_total_sale = Orderdetail::where('vendor_id', $vendorId)
                                      ->where('approved', 1)
                                      ->where('product_id', $product_id)
                                      ->whereHas('product', function($query) use($part_category_id){
                                       $query->whereHas('part_category', function($index) use($part_category_id){
                                          $index->where('id', $part_category_id);
                                        });
                                      })->where('created_at', '>=', $startDate)
                                        ->where('created_at', '<=', $endDate)->sum('total');

                  array_push($details_array, [
                    'day'      => $unique_day,
                    'day_name' => Carbon::parse($unique_day)->format('l'),
                    'reports'  => [
                        'total_sale'       => $period_total_sale,
                      ], // end reports
                  ]); // end array push
                } // end foreach unique day
                      
    // end flow chart section
                    
                return response()->json([
                    'status_code' => 200, 
                    'message'     => 'success',
                    'total_orders'     => $get_orders,
                    'total_invoices'   => $top_total_invoices,
                    'total_sale'       => $sum_part_invoices,
                    // 'total_customers'  => $top_total_customers,
                    // 'total_vendors'    => $top_total_vendors,
                    'total_products'   => $top_total_products,
                   // 'sales_nalytics'   => $orders,
                    'period_details'   => $details_array,
                ], 200);
          } // end case vendor
          elseif (in_array('Manager', $user_roles)) 
      {
       // abort_if(Gate::denies('stores_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendorId   = $vendor->id;
        // $staff_stores = $exist_staff->stores->pluck('id')->toArray();
       // return $staff_stores;

        /*                $top_total_customers = User::whereHas('roles', function($q){
                                              $q->where('title', '!=', 'Admin');
                                            })->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();

                $top_total_vendors = AddVendor::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();
*/
                $top_total_products = Product::where('vendor_id', $vendorId)
                                              ->where('part_category_id', $part_category_id)
                                              ->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->get();
                
                $top_total_orders = Order::whereHas('orderDetails', function($q) use ($product_id, $part_category_id, $vendorId, $startDate, $endDate){
                                       $q->where('vendor_id', $vendorId)
                                    ->where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)
                                    ->where('product_id', $product_id)
                                       ->whereHas('product', function($query) use($product_id, $part_category_id){
                                       $query->whereHas('part_category', function($index) use($product_id, $part_category_id){
                                          $index->where('id', $part_category_id);
                                        });
                                  });
                                  })->get();
                foreach ($top_total_orders as $one_item) {
                  
                  $one_item['orderDetails'] = Orderdetail::where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)
                                    ->where('order_id', $one_item->id)
                                              ->where('vendor_id', $vendorId)
                                              ->where('approved', 1)
                                              ->where('product_id', $product_id)->whereHas('product', function($query) use($part_category_id){
                                       $query->whereHas('part_category', function($index) use($part_category_id){
                                          $index->where('id', $part_category_id);
                                        });
                                  })->get();
                  $one_item['order_total']  = Orderdetail::where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)
                                    ->where('order_id', $one_item->id)
                                              ->where('vendor_id', $vendorId)
                                              ->where('approved', 1)
                                              ->where('product_id', $product_id)->whereHas('product', function($query) use($part_category_id){
                                       $query->whereHas('part_category', function($index) use($part_category_id){
                                          $index->where('id', $part_category_id);
                                        });
                                  })->sum('total');
                }

                $get_orders = AdvancedApiReportPartProdCaseResource::collection($top_total_orders);

                $top_total_invoices  = Invoice::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->get();

                $sum_part_invoices = Orderdetail::where('vendor_id', $vendorId)
                                      ->where('product_id', $product_id)
                                      ->where('approved', 1)
                                      ->whereHas('product', function($query) use($part_category_id){
                                       $query->whereHas('part_category', function($index) use($part_category_id){
                                          $index->where('id', $part_category_id);
                                        });
                                      })->where('created_at', '>=', $startDate)
                                      ->where('created_at', '<=', $endDate)->sum('total');

      /*$countb =  Invoice::where('vendor_id', $vendorId)->whereIn('order_id', $part_invoices)->get();
      foreach ($countb as $value) {
        $found_part_cats = Orderdetail::where('order_id', $value->order_id)
                                ->where('vendor_id', $value->vendor_id)->get();

         foreach ($found_part_cats as $found_part_cat) {
          $spec_prod = Product::find($found_part_cat->product_id);
            if ($found_part_cat->product_id == $product_id && $spec_prod->part_category_id == $part_category_id) {
            // $count_partcat_invoices++;
              $value['invoice_total'] = Orderdetail::where('order_id', $value->order_id)
                                ->where('vendor_id', $value->vendor_id)->where('product_id', $product_id)
                                ->where('id', $found_part_cat->id)
                                ->sum('total');
            array_push($count_partcat_invoices, $value);
            $sum_partcat_invoices+= $value->invoice_total;
           }else{
            continue;
          }
        }
      }*/

    // start flow chart section
                $order_days = Order::whereHas('orderDetails', function($q) use ($vendorId, $product_id, $part_category_id, $startDate, $endDate){
                                  $q->where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)->where('vendor_id', $vendorId)
                                    ->where('product_id', $product_id)
                                         ->whereHas('product', function($query) use($product_id, $part_category_id){
                                       $query->whereHas('part_category', function($index) use($product_id, $part_category_id){
                                          $index->where('id', $part_category_id);
                                        });
                                  });
                                  })->pluck('created_at');
      
                //  return $total_orders;
                  $unique_days   = array();
                  $details_array = array();

                  foreach ($order_days as $order_day) {   // start foreach unique days
                      $created_at = $order_day;//->toDateString();
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

                      $period_total_sale = Orderdetail::where('vendor_id', $vendorId)
                                      ->where('approved', 1)
                                      ->where('product_id', $product_id)
                                      ->whereHas('product', function($query) use($part_category_id){
                                       $query->whereHas('part_category', function($index) use($part_category_id){
                                          $index->where('id', $part_category_id);
                                        });
                                      })->where('created_at', '>=', $startDate)
                                        ->where('created_at', '<=', $endDate)->sum('total');

                  array_push($details_array, [
                    'day'      => $unique_day,
                    'day_name' => Carbon::parse($unique_day)->format('l'),
                    'reports'  => [
                        'total_sale'       => $period_total_sale,
                      ], // end reports
                  ]); // end array push
                } // end foreach unique day
                      
    // end flow chart section
                    
                return response()->json([
                    'status_code' => 200, 
                    'message'     => 'success',
                    'total_orders'     => $get_orders,
                    'total_invoices'   => $top_total_invoices,
                    'total_sale'       => $sum_part_invoices,
                    // 'total_customers'  => $top_total_customers,
                    // 'total_vendors'    => $top_total_vendors,
                    'total_products'   => $top_total_products,
                   // 'sales_nalytics'   => $orders,
                    'period_details'   => $details_array,
                ], 200);
      }
          else{
            return response()->json([
                    'status_code' => 401, 
                    // 'message'     => 'success',
                    'message'  => 'un authorized access page due to permissions',
                   ], 401);
          } // end if
      }  // end function product part category report

      public function product_period_report($from, $to, $product)
      {
        $startDate  = $from.' 00:00:00';
        $endDate    = $to.' 23:59:59';
        $product_id = $product;
        $count_partcat_invoices = array();
        $sum_partcat_invoices   = 0;
        $user       = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

            if (in_array('Admin', $user_roles)) 
            {
/*                $top_total_customers = User::whereHas('roles', function($q){
                                              $q->where('title', '!=', 'Admin');
                                            })->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();

                $top_total_vendors = AddVendor::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();
*/
                $top_total_products = Product::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->get();
                
                $top_total_orders = Order::whereHas('orderDetails', function($q) use ($product_id, $startDate, $endDate){
                                       $q->where('product_id', $product_id)
                                       ->where('created_at', '>=', $startDate)
                                        ->where('created_at', '<=', $endDate)
                                        ->where('approved', 1);
                                      })->get();
                foreach ($top_total_orders as $one_item) {
                  
                  $one_item['orderDetails'] = $one_item->orderDetails->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)
                             ->where('approved', 1)->where('product_id', $product_id);
                  $one_item['order_total']  = $one_item->orderDetails->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)
                              ->where('approved', 1)->where('product_id', $product_id)->sum('total');
                }

                $get_orders = AdvancedApiReportProductCaseResource::collection($top_total_orders);

               /* $view_product_details = Order::whereHas('orderDetails', function($q) use ($product_id){
                                       $q->where('product_id', $product_id)->where('approved', 1);
                                      })->where('created_at', '>=', $startDate)
                                        ->where('created_at', '<=', $endDate)
                                        ->where('approved', 1)
                                        ->get();*/

               $top_total_invoices  = Invoice::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->get();

                $sum_part_invoices = Orderdetail::where('product_id', $product_id)
                                      ->where('approved', 1)->where('created_at', '>=', $startDate)
                                      ->where('created_at', '<=', $endDate)->sum('total');

     /* $countb =  Invoice::whereIn('order_id', $part_invoices)->get();
    //  return $countb;
      foreach ($countb as $value) {
     //   return $value->vendor_id;
        $found_part = Orderdetail::where('order_id', $value->order_id)
                                ->where('vendor_id', $value->vendor_id)->first()
                               ->product_id;
         if ($found_part == $product_id) {
          //return 'll';
          array_push($count_partcat_invoices, $value);
          // $count_partcat_invoices++;
          $sum_partcat_invoices+= $value->invoice_total;
         }else{
          continue;
        }
      }*/

    // start flow chart section
            $order_days = Order::whereHas('orderDetails', function($q) use ($product_id, $startDate, $endDate){
                                  $q->where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)->where('product_id', $product_id);
                                    })->pluck('created_at');
      
                //  return $total_orders;
                  $unique_days   = array();
                  $details_array = array();

                  foreach ($order_days as $order_day) {   // start foreach unique days
                      $created_at = $order_day;//->toDateString();
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

                      $period_total_sale = Orderdetail::where('approved', 1)
                                        ->where('product_id', $product_id)
                                        ->where('created_at', '>=', $startDate)
                                        ->where('created_at', '<=', $endDate)->sum('total');

                  array_push($details_array, [
                    'day'      => $unique_day,
                    'day_name' => Carbon::parse($unique_day)->format('l'),
                    'reports'  => [
                        'total_sale'       => $period_total_sale,
                      ], // end reports
                  ]); // end array push
                } // end foreach unique day
                      
    // end flow chart section
                    
                return response()->json([
                    'status_code' => 200, 
                    'message'     => 'success',
                    'total_orders'     => $get_orders,
                    'total_invoices'   => $top_total_invoices,
                    'total_sale'       => $sum_part_invoices,
                  //  'total_customers'  => $top_total_customers,
                   // 'total_vendors'    => $top_total_vendors,
                    'total_products'   => $top_total_products,
                   // 'sales_nalytics'   => $orders,
                    // 'view_product_details' => $view_product_details,
                    'period_details'   => $details_array,
                ], 200);
          } // case logged in user role is Vendor
          elseif (in_array('Vendor', $user_roles)) 
          { 
            $vendor        = AddVendor::where('userid_id', Auth::user()->id)->first();
            $vendorId      = $vendor->id;

/*                $top_total_customers = User::whereHas('roles', function($q){
                                              $q->where('title', '!=', 'Admin');
                                            })->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();

                $top_total_vendors = AddVendor::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();
*/
                $top_total_products = Product::where('vendor_id', $vendorId)->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)->get();
                
                $top_total_orders = Order::whereHas('orderDetails', function($q) use ($vendorId, $product_id, $startDate, $endDate){
                                       $q->where('vendor_id', $vendorId)
                                       ->where('product_id', $product_id)
                                       ->where('created_at', '>=', $startDate)
                                        ->where('created_at', '<=', $endDate)
                                        ->where('approved', 1);
                                      })->get();
               /* $view_product_details = Order::whereHas('orderDetails', function($q) use ($product_id){
                                       $q->where('product_id', $product_id)->where('approved', 1);
                                      })->where('created_at', '>=', $startDate)
                                        ->where('created_at', '<=', $endDate)
                                        ->where('approved', 1)
                                        ->get();*/
                foreach ($top_total_orders as $one_item) {
                  
                  $one_item['orderDetails'] = $one_item->orderDetails->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)
                              ->where('approved', 1)->where('vendor_id', $vendorId)->where('product_id', $product_id);
                  $one_item['order_total']  = $one_item->orderDetails->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)
                          ->where('approved', 1)->where('vendor_id', $vendorId)->where('product_id', $product_id)->sum('total');
                }

                $get_orders = AdvancedApiReportProductCaseResource::collection($top_total_orders);

                $top_total_invoices  = Invoice::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->get();

                $sum_part_invoices = Orderdetail::where('vendor_id', $vendorId)
                                      ->where('product_id', $product_id)
                                      ->where('approved', 1)->where('created_at', '>=', $startDate)
                                      ->where('created_at', '<=', $endDate)->sum('total');
/// return $part_invoices;
      /*$countb =  Invoice::where('vendor_id', $vendorId)->whereIn('order_id', $part_invoices)->get();

      foreach ($countb as $value) {
        $found_part = Orderdetail::where('order_id', $value->order_id)->where('product_id', $product_id)
                                ->where('vendor_id', $value->vendor_id)->first()->product_id;
         if ($found_part == $product_id) {
          //$count_partcat_invoices++;
          $value['invoice_total'] = Orderdetail::where('order_id', $value->order_id)
                                ->where('vendor_id', $value->vendor_id)->where('product_id', $product_id)
                                ->sum('total');
          array_push($count_partcat_invoices, $value);
            $sum_partcat_invoices+= $value->invoice_total;
         }else{
          continue;
        }
      }*/

    // start flow chart section
            $order_days = Order::whereHas('orderDetails', function($q) use ($vendorId, $product_id, $startDate, $endDate){
                                  $q->where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)->where('vendor_id', $vendorId)
                                    ->where('product_id', $product_id);
                                    })->pluck('created_at');
      
                // return $order_days;
                  $unique_days   = array();
                  $details_array = array();

                  foreach ($order_days as $order_day) {   // start foreach unique days
                      $created_at = $order_day;//->toDateString();
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

                      $period_total_sale = Orderdetail::where('vendor_id', $vendorId)
                                        ->where('approved', 1)
                                        ->where('product_id', $product_id)
                                        ->where('created_at', '>=', $startDate)
                                        ->where('created_at', '<=', $endDate)->sum('total');

                  array_push($details_array, [
                    'day'      => $unique_day,
                    'day_name' => Carbon::parse($unique_day)->format('l'),
                    'reports'  => [
                        'total_sale'       => $period_total_sale,
                      ], // end reports
                  ]); // end array push
                } // end foreach unique day
                      
    // end flow chart section
                    
                return response()->json([
                    'status_code' => 200, 
                    'message'     => 'success',
                    'total_orders'     => $get_orders,
                    'total_invoices'   => $top_total_invoices,
                    'total_sale'       => $sum_part_invoices,
                  //  'total_customers'  => $top_total_customers,
                   // 'total_vendors'    => $top_total_vendors,
                    'total_products'   => $top_total_products,
                   // 'sales_nalytics'   => $orders,
                  //  'view_product_details' => $view_product_details,
                    'period_details'   => $details_array,
                ], 200);
          } // end case vendor
          elseif (in_array('Manager', $user_roles)) 
      {
       // abort_if(Gate::denies('stores_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendorId     = $vendor->id;
        // $staff_stores = $exist_staff->stores->pluck('id')->toArray();
       // return $staff_stores;

        /*                $top_total_customers = User::whereHas('roles', function($q){
                                              $q->where('title', '!=', 'Admin');
                                            })->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();

                $top_total_vendors = AddVendor::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();
*/
                $top_total_products = Product::where('vendor_id', $vendorId)->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)->get();
                
                $top_total_orders = Order::whereHas('orderDetails', function($q) use ($vendorId, $product_id, $startDate, $endDate){
                                       $q->where('vendor_id', $vendorId)
                                       ->where('product_id', $product_id)
                                       ->where('created_at', '>=', $startDate)
                                        ->where('created_at', '<=', $endDate)
                                        ->where('approved', 1);
                                      })->get();
               /* $view_product_details = Order::whereHas('orderDetails', function($q) use ($product_id){
                                       $q->where('product_id', $product_id)->where('approved', 1);
                                      })->where('created_at', '>=', $startDate)
                                        ->where('created_at', '<=', $endDate)
                                        ->where('approved', 1)
                                        ->get();*/
                foreach ($top_total_orders as $one_item) {
                  
                  $one_item['orderDetails'] = $one_item->orderDetails->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)
                              ->where('approved', 1)->where('vendor_id', $vendorId)->where('product_id', $product_id);
                  $one_item['order_total']  = $one_item->orderDetails->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)
                          ->where('approved', 1)->where('vendor_id', $vendorId)->where('product_id', $product_id)->sum('total');
                }

                $get_orders = AdvancedApiReportProductCaseResource::collection($top_total_orders);

                $top_total_invoices  = Invoice::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->get();

                $sum_part_invoices = Orderdetail::where('vendor_id', $vendorId)
                                      ->where('product_id', $product_id)
                                      ->where('approved', 1)->where('created_at', '>=', $startDate)
                                      ->where('created_at', '<=', $endDate)->sum('total');
/// return $part_invoices;
      /*$countb =  Invoice::where('vendor_id', $vendorId)->whereIn('order_id', $part_invoices)->get();

      foreach ($countb as $value) {
        $found_part = Orderdetail::where('order_id', $value->order_id)->where('product_id', $product_id)
                                ->where('vendor_id', $value->vendor_id)->first()->product_id;
         if ($found_part == $product_id) {
          //$count_partcat_invoices++;
          $value['invoice_total'] = Orderdetail::where('order_id', $value->order_id)
                                ->where('vendor_id', $value->vendor_id)->where('product_id', $product_id)
                                ->sum('total');
          array_push($count_partcat_invoices, $value);
            $sum_partcat_invoices+= $value->invoice_total;
         }else{
          continue;
        }
      }*/

    // start flow chart section
            $order_days = Order::whereHas('orderDetails', function($q) use ($vendorId, $product_id, $startDate, $endDate){
                                  $q->where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)->where('vendor_id', $vendorId)
                                    ->where('product_id', $product_id);
                                    })->pluck('created_at');
      
                // return $order_days;
                  $unique_days   = array();
                  $details_array = array();

                  foreach ($order_days as $order_day) {   // start foreach unique days
                      $created_at = $order_day;//->toDateString();
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

                      $period_total_sale = Orderdetail::where('vendor_id', $vendorId)
                                        ->where('approved', 1)
                                        ->where('product_id', $product_id)
                                        ->where('created_at', '>=', $startDate)
                                        ->where('created_at', '<=', $endDate)->sum('total');

                  array_push($details_array, [
                    'day'      => $unique_day,
                    'day_name' => Carbon::parse($unique_day)->format('l'),
                    'reports'  => [
                        'total_sale'       => $period_total_sale,
                      ], // end reports
                  ]); // end array push
                } // end foreach unique day
                      
    // end flow chart section
                    
                return response()->json([
                    'status_code' => 200, 
                    'message'     => 'success',
                    'total_orders'     => $get_orders,
                    'total_invoices'   => $top_total_invoices,
                    'total_sale'       => $sum_part_invoices,
                  //  'total_customers'  => $top_total_customers,
                   // 'total_vendors'    => $top_total_vendors,
                    'total_products'   => $top_total_products,
                   // 'sales_nalytics'   => $orders,
                  //  'view_product_details' => $view_product_details,
                    'period_details'   => $details_array,
                ], 200);
      }
          else{
            return response()->json([
                    'status_code' => 401, 
                    // 'message'     => 'success',
                    'message'  => 'un authorized access page due to permissions',
                   ], 401);
          } // end if
      }  // end function product report 

      public function stock_period_report($from, $to, $stock)
      {
        $startDate  = $from.' 00:00:00';
        $endDate    = $to.' 23:59:59';
        $stock_id   = $stock;
        $count_partcat_invoices = array();
        $sum_partcat_invoices   = 0;
        $user       = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

            if (in_array('Admin', $user_roles)) 
            {
/*                $top_total_customers = User::whereHas('roles', function($q){
                                              $q->where('title', '!=', 'Admin');
                                            })->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();

                $top_total_vendors = AddVendor::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();
*/
                $top_total_products = Product::where('store_id', $stock_id)
                                              ->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->get();
              
                $top_total_orders = Order::whereHas('orderDetails', function($q) use ($stock_id, $startDate, $endDate){
                                  $q->where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)
                                    ->whereHas('product', function($query) use($stock_id){
                                       $query->whereHas('store', function($index) use($stock_id){
                                          $index->where('id', $stock_id);
                                        });
                                  });
                                  })->get();
                foreach ($top_total_orders as $one_item) {
                  
                  $one_item['orderDetails'] = $one_item->orderDetails->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)
                            ->where('approved', 1)->where('store_id', $stock_id);
                  $one_item['order_total']  = $one_item->orderDetails->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)
                        ->where('approved', 1)->where('store_id', $stock_id)->sum('total');
                }

                $get_orders = AdvancedApiReportStockCaseResource::collection($top_total_orders);

                $top_total_invoices  = Invoice::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->get();

                $sum_part_invoices = Orderdetail::where('approved', 1)
                                  ->whereHas('product', function($query) use($stock_id){
                                       $query->whereHas('store', function($index) use($stock_id){
                                          $index->where('id', $stock_id);
                                        });
                                      })->where('created_at', '>=', $startDate)
                                      ->where('created_at', '<=', $endDate)->sum('total');
     // return $part_invoices;
    /*  $countb =  Invoice::whereIn('order_id', $part_invoices)->get();
     // return $countb;
      foreach ($countb as $value) {
        $found_part = Orderdetail::where('order_id', $value->order_id)
                                ->where('store_id', $stock_id)
                                ->where('vendor_id', $value->vendor_id)->first();
         if ($found_part && $found_part->store_id == $stock_id) {
          // $count_partcat_invoices++;
          $value['invoice_total'] = Orderdetail::where('order_id', $value->order_id)->where('store_id', $stock_id)
                                ->where('vendor_id', $value->vendor_id)->sum('total');
          array_push($count_partcat_invoices, $value);
          $sum_partcat_invoices+= $value->invoice_total;
         }else{
          continue;
        }
      }*/

    // start flow chart section
                $order_days = Order::whereHas('orderDetails', function($q) use ($stock_id, $startDate, $endDate){
                                  $q->where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)
                                    ->whereHas('product', function($query) use($stock_id){
                                       $query->whereHas('store', function($index) use($stock_id){
                                          $index->where('id', $stock_id);
                                        });
                                  });
                                  })->pluck('created_at');
      
                //  return $total_orders;
                  $unique_days   = array();
                  $details_array = array();

                  foreach ($order_days as $order_day) {   // start foreach unique days
                      $created_at = $order_day;//->toDateString();
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

                      $period_total_sale = Orderdetail::where('approved', 1)
                                       ->whereHas('product', function($query) use($stock_id){
                                       $query->whereHas('store', function($index) use($stock_id){
                                          $index->where('id', $stock_id);
                                        });
                                      })->where('created_at', '>=', $startDate)
                                        ->where('created_at', '<=', $endDate)->sum('total');

                  array_push($details_array, [
                    'day'      => $unique_day,
                    'day_name' => Carbon::parse($unique_day)->format('l'),
                    'reports'  => [
                        'total_sale'       => $period_total_sale,
                      ], // end reports
                  ]); // end array push
                } // end foreach unique day
                      
    // end flow chart section
                    
                return response()->json([
                    'status_code' => 200, 
                    'message'     => 'success',
                    'total_orders'     => $get_orders,
                    'total_invoices'   => $top_total_invoices,
                    'total_sale'       => $sum_part_invoices,
                    // 'total_customers'  => $top_total_customers,
                    // 'total_vendors'    => $top_total_vendors,
                    'total_products'   => $top_total_products,
                   // 'sales_nalytics'   => $orders,
                    'period_details'   => $details_array,
                ], 200);
          } // case logged in user role is Vendor
          elseif (in_array('Vendor', $user_roles)) 
          { 
            $vendor       = AddVendor::where('userid_id', Auth::user()->id)->first();
            $vendorId     = $vendor->id;
/*                $top_total_customers = User::whereHas('roles', function($q){
                                              $q->where('title', '!=', 'Admin');
                                            })->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();

                $top_total_vendors = AddVendor::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();
*/
                $top_total_products = Product::where('vendor_id', $vendorId)
                                              ->where('store_id', $stock_id)
                                              ->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->get();
              
                $top_total_orders = Order::whereHas('orderDetails', function($q) use ($vendorId, $stock_id, $startDate, $endDate){
                                  $q->where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)->where('vendor_id', $vendorId)
                                        ->whereHas('product', function($query) use($stock_id){
                                       $query->whereHas('store', function($index) use($stock_id){
                                          $index->where('id', $stock_id);
                                        });
                                  });
                                  })->get();
                foreach ($top_total_orders as $one_item) {
                  
                  $one_item['orderDetails'] = $one_item->orderDetails->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)
                              ->where('approved', 1)->where('store_id', $stock_id)
                              ->where('vendor_id', $vendorId);
                  $one_item['order_total']  = $one_item->orderDetails->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)
                        ->where('approved', 1)->where('store_id', $stock_id)->where('vendor_id', $vendorId)->sum('total');
                }

                $get_orders = AdvancedApiReportStockCaseResource::collection($top_total_orders);

                $top_total_invoices  = Invoice::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->get();

                $sum_part_invoices = Orderdetail::where('vendor_id', $vendorId)->where('approved', 1)
                                       ->whereHas('product', function($query) use($stock_id){
                                       $query->whereHas('store', function($index) use($stock_id){
                                          $index->where('id', $stock_id);
                                        });
                                      })->where('created_at', '>=', $startDate)
                                      ->where('created_at', '<=', $endDate)->sum('total');

     /* $countb =  Invoice::where('vendor_id', $vendorId)->whereIn('order_id', $part_invoices)->get();
      foreach ($countb as $value) {
        $found_part = Orderdetail::where('order_id', $value->order_id)->where('store_id', $stock_id)
                                ->where('vendor_id', $value->vendor_id)->first()
                               ->store_id;
         if ($found_part == $stock_id) {
         // $count_partcat_invoices++;
         $value['invoice_total'] = Orderdetail::where('order_id', $value->order_id)->where('store_id', $stock_id)
                                ->where('vendor_id', $value->vendor_id)->sum('total');
          array_push($count_partcat_invoices, $value);
          $sum_partcat_invoices+= $value->invoice_total;
         }else{
          continue;
        }
      }*/

    // start flow chart section
                $order_days = Order::whereHas('orderDetails', function($q) use ($vendorId, $stock_id, $startDate, $endDate){
                                  $q->where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('vendor_id', $vendorId)->where('approved', 1)
                                    ->whereHas('product', function($query) use($stock_id){
                                       $query->whereHas('store', function($index) use($stock_id){
                                          $index->where('id', $stock_id);
                                        });
                                  });
                                  })->pluck('created_at');
      
                //  return $total_orders;
                  $unique_days   = array();
                  $details_array = array();

                  foreach ($order_days as $order_day) {   // start foreach unique days
                      $created_at = $order_day;//->toDateString();
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

                      $period_total_sale = Orderdetail::where('vendor_id', $vendorId)
                                       ->where('approved', 1)
                                       ->whereHas('product', function($query) use($stock_id){
                                       $query->whereHas('store', function($index) use($stock_id){
                                          $index->where('id', $stock_id);
                                        });
                                      })->where('created_at', '>=', $startDate)
                                        ->where('created_at', '<=', $endDate)->sum('total');

                  array_push($details_array, [
                    'day'      => $unique_day,
                    'day_name' => Carbon::parse($unique_day)->format('l'),
                    'reports'  => [
                        'total_sale'       => $period_total_sale,
                      ], // end reports
                  ]); // end array push
                } // end foreach unique day
                      
    // end flow chart section
                    
                return response()->json([
                    'status_code' => 200, 
                    'message'     => 'success',
                    'total_orders'     => $get_orders,
                    'total_invoices'   => $top_total_invoices,
                    'total_sale'       => $sum_part_invoices,
                    // 'total_customers'  => $top_total_customers,
                    // 'total_vendors'    => $top_total_vendors,
                    'total_products'   => $top_total_products,
                   // 'sales_nalytics'   => $orders,
                    'period_details'   => $details_array,
                ], 200);
          } // end case vendor
          elseif (in_array('Manager', $user_roles)) 
      {
       // abort_if(Gate::denies('stores_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendorId     = $vendor->id;
        // $staff_stores = $exist_staff->stores->pluck('id')->toArray();
       // return $staff_stores;

        /*                $top_total_customers = User::whereHas('roles', function($q){
                                              $q->where('title', '!=', 'Admin');
                                            })->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();

                $top_total_vendors = AddVendor::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();
*/
                $top_total_products = Product::where('vendor_id', $vendorId)
                                              ->where('store_id', $stock_id)
                                              ->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->get();
              
                $top_total_orders = Order::whereHas('orderDetails', function($q) use ($vendorId, $stock_id, $startDate, $endDate){
                                  $q->where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)->where('vendor_id', $vendorId)
                                        ->whereHas('product', function($query) use($stock_id){
                                       $query->whereHas('store', function($index) use($stock_id){
                                          $index->where('id', $stock_id);
                                        });
                                  });
                                  })->get();
                foreach ($top_total_orders as $one_item) {
                  
                  $one_item['orderDetails'] = $one_item->orderDetails->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)
                              ->where('approved', 1)->where('store_id', $stock_id)
                              ->where('vendor_id', $vendorId);
                  $one_item['order_total']  = $one_item->orderDetails->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)
                        ->where('approved', 1)->where('store_id', $stock_id)->where('vendor_id', $vendorId)->sum('total');
                }

                $get_orders = AdvancedApiReportStockCaseResource::collection($top_total_orders);

                $top_total_invoices  = Invoice::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->get();

                $sum_part_invoices = Orderdetail::where('vendor_id', $vendorId)->where('approved', 1)
                                       ->whereHas('product', function($query) use($stock_id){
                                       $query->whereHas('store', function($index) use($stock_id){
                                          $index->where('id', $stock_id);
                                        });
                                      })->where('created_at', '>=', $startDate)
                                      ->where('created_at', '<=', $endDate)->sum('total');

     /* $countb =  Invoice::where('vendor_id', $vendorId)->whereIn('order_id', $part_invoices)->get();
      foreach ($countb as $value) {
        $found_part = Orderdetail::where('order_id', $value->order_id)->where('store_id', $stock_id)
                                ->where('vendor_id', $value->vendor_id)->first()
                               ->store_id;
         if ($found_part == $stock_id) {
         // $count_partcat_invoices++;
         $value['invoice_total'] = Orderdetail::where('order_id', $value->order_id)->where('store_id', $stock_id)
                                ->where('vendor_id', $value->vendor_id)->sum('total');
          array_push($count_partcat_invoices, $value);
          $sum_partcat_invoices+= $value->invoice_total;
         }else{
          continue;
        }
      }*/

    // start flow chart section
                $order_days = Order::whereHas('orderDetails', function($q) use ($vendorId, $stock_id, $startDate, $endDate){
                                  $q->where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('vendor_id', $vendorId)->where('approved', 1)
                                    ->whereHas('product', function($query) use($stock_id){
                                       $query->whereHas('store', function($index) use($stock_id){
                                          $index->where('id', $stock_id);
                                        });
                                  });
                                  })->pluck('created_at');
      
                //  return $total_orders;
                  $unique_days   = array();
                  $details_array = array();

                  foreach ($order_days as $order_day) {   // start foreach unique days
                      $created_at = $order_day;//->toDateString();
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

                      $period_total_sale = Orderdetail::where('vendor_id', $vendorId)
                                       ->where('approved', 1)
                                       ->whereHas('product', function($query) use($stock_id){
                                       $query->whereHas('store', function($index) use($stock_id){
                                          $index->where('id', $stock_id);
                                        });
                                      })->where('created_at', '>=', $startDate)
                                        ->where('created_at', '<=', $endDate)->sum('total');

                  array_push($details_array, [
                    'day'      => $unique_day,
                    'day_name' => Carbon::parse($unique_day)->format('l'),
                    'reports'  => [
                        'total_sale'       => $period_total_sale,
                      ], // end reports
                  ]); // end array push
                } // end foreach unique day
                      
    // end flow chart section
                    
                return response()->json([
                    'status_code' => 200, 
                    'message'     => 'success',
                    'total_orders'     => $get_orders,
                    'total_invoices'   => $top_total_invoices,
                    'total_sale'       => $sum_part_invoices,
                    // 'total_customers'  => $top_total_customers,
                    // 'total_vendors'    => $top_total_vendors,
                    'total_products'   => $top_total_products,
                   // 'sales_nalytics'   => $orders,
                    'period_details'   => $details_array,
                ], 200);
      }
          else{
            return response()->json([
                    'status_code' => 401, 
                    // 'message'     => 'success',
                    'message'  => 'un authorized access page due to permissions',
                   ], 401);
          } // end if
      }  // end function stock report 

      public function product_stock_period_report($from, $to, $product, $stock)
      {
        $startDate  = $from.' 00:00:00';
        $endDate    = $to.' 23:59:59';
        $stock_id   = $stock;
        $product_id = $product;
        $count_partcat_invoices = array();
        $sum_partcat_invoices   = 0;
        $user       = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

            if (in_array('Admin', $user_roles)) 
            {
 /*               $top_total_customers = User::whereHas('roles', function($q){
                                              $q->where('title', '!=', 'Admin');
                                            })->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();

                $top_total_vendors = AddVendor::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();
*/
                $top_total_products = Product::where('store_id', $stock_id)
                                              ->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->get();
              
                $top_total_orders = Order::whereHas('orderDetails', function($q) use ($product_id, $stock_id, $startDate, $endDate){
                                  $q->where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('product_id', $product_id)
                                    ->where('store_id', $stock_id)
                                    ->where('approved', 1);
                                  })->get();
                foreach ($top_total_orders as $one_item) {
                  
                  $one_item['orderDetails'] = $one_item->orderDetails->where('product_id', $product_id)->where('store_id', $stock_id)->where('created_at', '>=', $startDate)
                               ->where('created_at', '<=', $endDate)->where('approved', 1);
                  $one_item['order_total']  = $one_item->orderDetails->where('product_id', $product_id)->where('store_id', $stock_id)->where('created_at', '>=', $startDate)
                                  ->where('created_at', '<=', $endDate)->where('approved', 1)
                                  ->sum('total');
                }

                $get_orders = AdvancedApiReportStockCaseResource::collection($top_total_orders);
                
                $top_total_invoices  = Invoice::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->get();

                $sum_part_invoices = Orderdetail::where('product_id', $product_id)->where('store_id', $stock_id)->where('approved', 1)->where('created_at', '>=', $startDate)
                                        ->where('created_at', '<=', $endDate)->sum('total');

     /* $countb =  Invoice::whereIn('order_id', $part_invoices)->get();
      foreach ($countb as $value) {
        $found_part = Orderdetail::where('order_id', $value->order_id)
                                ->where('vendor_id', $value->vendor_id)->first();
         if ($found_part->product_id == $product_id && $found_part->store_id == $stock_id) {
          // $count_partcat_invoices++;
          array_push($count_partcat_invoices, $value);
          $sum_partcat_invoices+= $value->invoice_total;
         }else{
          continue;
        }
      }*/

    // start flow chart section
                $order_days = Order::whereHas('orderDetails', function($q) use ($product_id, $stock_id, $startDate, $endDate){
                                  $q->where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)->where('product_id', $product_id)
                                    ->where('store_id', $stock_id);
                                  })->pluck('created_at');
      
                //  return $total_orders;
                  $unique_days   = array();
                  $details_array = array();

                  foreach ($order_days as $order_day) {   // start foreach unique days
                      $created_at = $order_day;//->toDateString();
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

                      $period_total_sale = Orderdetail::where('product_id', $product_id)
                                             ->where('store_id', $stock_id)
                                             ->where('approved', 1)
                                             ->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->sum('total');

                  array_push($details_array, [
                    'day'      => $unique_day,
                    'day_name' => Carbon::parse($unique_day)->format('l'),
                    'reports'  => [
                        'total_sale'       => $period_total_sale,
                      ], // end reports
                  ]); // end array push
                } // end foreach unique day
                      
    // end flow chart section
                    
                return response()->json([
                    'status_code' => 200, 
                    'message'     => 'success',
                    'total_orders'     => $get_orders,
                    'total_invoices'   => $top_total_invoices,
                    'total_sale'       => $sum_part_invoices,
                    // 'total_customers'  => $top_total_customers,
                    // 'total_vendors'    => $top_total_vendors,
                    'total_products'   => $top_total_products,
                   // 'sales_nalytics'   => $orders,
                    'period_details'   => $details_array,
                ], 200);
          } // case logged in user role is Vendor
          elseif (in_array('Vendor', $user_roles)) 
          { 
            $vendor       = AddVendor::where('userid_id', Auth::user()->id)->first();
            $vendorId     = $vendor->id;
/*               $top_total_customers = User::whereHas('roles', function($q){
                                              $q->where('title', '!=', 'Admin');
                                            })->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();

                $top_total_vendors = AddVendor::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();
*/
                $top_total_products = Product::where('vendor_id', $vendorId)->where('store_id', $stock_id)->where('created_at', '>=', $startDate)
                          ->where('created_at', '<=', $endDate)->get();
              
                $top_total_orders = Order::whereHas('orderDetails', function($q) use ($vendorId, $product_id, $stock_id, $startDate, $endDate){
                                  $q->where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)->where('vendor_id', $vendorId)
                                    ->where('product_id', $product_id)
                                    ->where('store_id', $stock_id);
                                  })->get();
                foreach ($top_total_orders as $one_item) {
                  
                  $one_item['orderDetails'] = $one_item->orderDetails->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)
                              ->where('approved', 1)->where('product_id', $product_id)
                              ->where('vendor_id', $vendorId)->where('store_id', $stock_id);
                  $one_item['order_total']  = $one_item->orderDetails->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)
                              ->where('approved', 1)->where('product_id', $product_id)
                              ->where('vendor_id', $vendorId)->where('store_id', $stock_id)
                              ->sum('total');
                }

                $get_orders = AdvancedApiReportStockCaseResource::collection($top_total_orders);

                $top_total_invoices  = Invoice::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->get();

                $sum_part_invoices = Orderdetail::where('vendor_id', $vendorId)->where('product_id', $product_id)->where('store_id', $stock_id)
                              ->where('approved', 1)->where('created_at', '>=', $startDate)
                              ->where('created_at', '<=', $endDate)->sum('total');

      /*$countb =  Invoice::where('vendor_id', $vendorId)->whereIn('order_id', $part_invoices)->get();
      foreach ($countb as $value) {
        $found_products_stocks = Orderdetail::where('order_id', $value->order_id)
                                ->where('vendor_id', $value->vendor_id)->get();
        foreach ($found_products_stocks as $found_products_stock) {
            if ($found_products_stock->product_id == $product_id && $found_products_stock->store_id == $stock_id) {
            // $count_partcat_invoices++;
              $value['invoice_total'] = Orderdetail::where('order_id', $value->order_id)
                                ->where('vendor_id', $value->vendor_id)->where('product_id', $product_id)
                                ->where('store_id', $stock_id)->sum('total');
            array_push($count_partcat_invoices, $value);
            $sum_partcat_invoices+= $value->invoice_total;
           }else{
            continue;
          }
        }
      }*/

    // start flow chart section
                $order_days = Order::whereHas('orderDetails', function($q) use ($vendorId, $product_id, $stock_id, $startDate, $endDate){
                                  $q->where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)->where('vendor_id', $vendorId)
                                    ->where('product_id', $product_id)->where('store_id', $stock_id);
                                  })->pluck('created_at');
      
                //  return $total_orders;
                  $unique_days   = array();
                  $details_array = array();

                  foreach ($order_days as $order_day) {   // start foreach unique days
                      $created_at = $order_day;//->toDateString();
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

                      $period_total_sale = Orderdetail::where('vendor_id', $vendorId)
                                             ->where('product_id', $product_id)
                                             ->where('store_id', $stock_id)
                                             ->where('approved', 1)
                                             ->where('created_at', '>=', $startDate)
                                             ->where('created_at', '<=', $endDate)->sum('total');

                  array_push($details_array, [
                    'day'      => $unique_day,
                    'day_name' => Carbon::parse($unique_day)->format('l'),
                    'reports'  => [
                        'total_sale'       => $period_total_sale,
                      ], // end reports
                  ]); // end array push
                } // end foreach unique day
                      
    // end flow chart section
                    
                return response()->json([
                    'status_code' => 200, 
                    'message'     => 'success',
                    'total_orders'     => $get_orders,
                    'total_invoices'   => $top_total_invoices,
                    'total_sale'       => $sum_part_invoices,
                    // 'total_customers'  => $top_total_customers,
                    // 'total_vendors'    => $top_total_vendors,
                    'total_products'   => $top_total_products,
                   // 'sales_nalytics'   => $orders,
                    'period_details'   => $details_array,
                ], 200);
          } // end case vendor
          elseif (in_array('Manager', $user_roles)) 
      {
       // abort_if(Gate::denies('stores_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendorId     = $vendor->id;
        // $staff_stores = $exist_staff->stores->pluck('id')->toArray();
       // return $staff_stores;

        /*               $top_total_customers = User::whereHas('roles', function($q){
                                              $q->where('title', '!=', 'Admin');
                                            })->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();

                $top_total_vendors = AddVendor::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();
*/
                $top_total_products = Product::where('vendor_id', $vendorId)->where('store_id', $stock_id)->where('created_at', '>=', $startDate)
                          ->where('created_at', '<=', $endDate)->get();
              
                $top_total_orders = Order::whereHas('orderDetails', function($q) use ($vendorId, $product_id, $stock_id, $startDate, $endDate){
                                  $q->where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)->where('vendor_id', $vendorId)
                                    ->where('product_id', $product_id)
                                    ->where('store_id', $stock_id);
                                  })->get();
                foreach ($top_total_orders as $one_item) {
                  
                  $one_item['orderDetails'] = $one_item->orderDetails->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)
                              ->where('approved', 1)->where('product_id', $product_id)
                              ->where('vendor_id', $vendorId)->where('store_id', $stock_id);
                  $one_item['order_total']  = $one_item->orderDetails->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)
                              ->where('approved', 1)->where('product_id', $product_id)
                              ->where('vendor_id', $vendorId)->where('store_id', $stock_id)
                              ->sum('total');
                }

                $get_orders = AdvancedApiReportStockCaseResource::collection($top_total_orders);

                $top_total_invoices  = Invoice::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->get();

                $sum_part_invoices = Orderdetail::where('vendor_id', $vendorId)->where('product_id', $product_id)->where('store_id', $stock_id)
                              ->where('approved', 1)->where('created_at', '>=', $startDate)
                              ->where('created_at', '<=', $endDate)->sum('total');

      /*$countb =  Invoice::where('vendor_id', $vendorId)->whereIn('order_id', $part_invoices)->get();
      foreach ($countb as $value) {
        $found_products_stocks = Orderdetail::where('order_id', $value->order_id)
                                ->where('vendor_id', $value->vendor_id)->get();
        foreach ($found_products_stocks as $found_products_stock) {
            if ($found_products_stock->product_id == $product_id && $found_products_stock->store_id == $stock_id) {
            // $count_partcat_invoices++;
              $value['invoice_total'] = Orderdetail::where('order_id', $value->order_id)
                                ->where('vendor_id', $value->vendor_id)->where('product_id', $product_id)
                                ->where('store_id', $stock_id)->sum('total');
            array_push($count_partcat_invoices, $value);
            $sum_partcat_invoices+= $value->invoice_total;
           }else{
            continue;
          }
        }
      }*/

    // start flow chart section
                $order_days = Order::whereHas('orderDetails', function($q) use ($vendorId, $product_id, $stock_id, $startDate, $endDate){
                                  $q->where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)->where('vendor_id', $vendorId)
                                    ->where('product_id', $product_id)->where('store_id', $stock_id);
                                  })->pluck('created_at');
      
                //  return $total_orders;
                  $unique_days   = array();
                  $details_array = array();

                  foreach ($order_days as $order_day) {   // start foreach unique days
                      $created_at = $order_day;//->toDateString();
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

                      $period_total_sale = Orderdetail::where('vendor_id', $vendorId)
                                             ->where('product_id', $product_id)
                                             ->where('store_id', $stock_id)
                                             ->where('approved', 1)
                                             ->where('created_at', '>=', $startDate)
                                             ->where('created_at', '<=', $endDate)->sum('total');

                  array_push($details_array, [
                    'day'      => $unique_day,
                    'day_name' => Carbon::parse($unique_day)->format('l'),
                    'reports'  => [
                        'total_sale'       => $period_total_sale,
                      ], // end reports
                  ]); // end array push
                } // end foreach unique day
                      
    // end flow chart section
                    
                return response()->json([
                    'status_code' => 200, 
                    'message'     => 'success',
                    'total_orders'     => $get_orders,
                    'total_invoices'   => $top_total_invoices,
                    'total_sale'       => $sum_part_invoices,
                    // 'total_customers'  => $top_total_customers,
                    // 'total_vendors'    => $top_total_vendors,
                    'total_products'   => $top_total_products,
                   // 'sales_nalytics'   => $orders,
                    'period_details'   => $details_array,
                ], 200);
      }
          else{
            return response()->json([
                    'status_code' => 401, 
                    //'message'     => 'success',
                    'message'  => 'un authorized access page due to permissions',
                   ], 401);
          } // end if
      }  // end function product stock report

       public function vendor_product_period_report($from, $to, $vendor, $product)
      {
        $startDate  = $from.' 00:00:00';
        $endDate    = $to.' 23:59:59';
        $vendor_id   = $vendor;
        $product_id = $product;
        $count_partcat_invoices = array();
        $sum_partcat_invoices   = 0;
        $user       = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

            if (in_array('Admin', $user_roles)) 
            {
/*                $top_total_customers = User::whereHas('roles', function($q){
                                              $q->where('title', '!=', 'Admin');
                                            })->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();

                $top_total_vendors = AddVendor::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->count();
*/
                $top_total_products = Product::where('vendor_id', $vendor_id)
                                              ->where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->get();
              
                $top_total_orders = Order::whereHas('orderDetails', function($q) use ($product_id, $vendor_id, $startDate, $endDate){
                                  $q->where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)->where('product_id', $product_id)
                                    ->where('vendor_id', $vendor_id);
                                  })
                                    ->get();
                foreach ($top_total_orders as $one_item) {
                  
                  $one_item['orderDetails'] = $one_item->orderDetails->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)
                              ->where('approved', 1)->where('product_id', $product_id)
                              ->where('vendor_id', $vendor_id);
                  $one_item['order_total']  = $one_item->orderDetails->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)
                              ->where('approved', 1)->where('product_id', $product_id)
                              ->where('vendor_id', $vendor_id)
                              ->sum('total');
                }

                $get_orders = AdvancedApiReportVendorProdResource::collection($top_total_orders);

                $top_total_invoices  = Invoice::where('created_at', '>=', $startDate)
                                              ->where('created_at', '<=', $endDate)->get();

                $sum_part_invoices = Orderdetail::where('product_id', $product_id)->where('vendor_id', $vendor_id)->where('approved', 1)->where('created_at', '>=', $startDate)
                              ->where('created_at', '<=', $endDate)->sum('total');

     /* $countb =  Invoice::whereIn('order_id', $part_invoices)->get();
      // return $countb;
      foreach ($countb as $value) {
        $found_part = Orderdetail::where('order_id', $value->order_id)
                                ->where('vendor_id', $value->vendor_id)->first();
         if ($found_part->product_id == $product_id && $found_part->vendor_id == $vendor_id) {
          // $count_partcat_invoices++;
          $value['invoice_total'] = Orderdetail::where('order_id', $value->order_id)
                                ->where('product_id', $product_id)
                                ->where('vendor_id', $value->vendor_id)->sum('total');
          array_push($count_partcat_invoices, $value);
          $sum_partcat_invoices+= $value->invoice_total;
         }else{
          continue;
        }
      }*/

    // start flow chart section
                $order_days = Order::whereHas('orderDetails', function($q) use ($product_id, $vendor_id, $startDate, $endDate){
                                  $q->where('created_at', '>=', $startDate)
                                    ->where('created_at', '<=', $endDate)
                                    ->where('approved', 1)->where('product_id', $product_id)
                                    ->where('vendor_id', $vendor_id);
                                  })->pluck('created_at');
      
                //  return $total_orders;
                  $unique_days   = array();
                  $details_array = array();

                  foreach ($order_days as $order_day) {   // start foreach unique days
                      $created_at = $order_day;//->toDateString();
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

                      $period_total_sale = Orderdetail::where('product_id', $product_id)
                                             ->where('vendor_id', $vendor_id)
                                             ->where('approved', 1)
                                             ->where('created_at', '>=', $startDate)
                                             ->where('created_at', '<=', $endDate)->sum('total');

                  array_push($details_array, [
                    'day'      => $unique_day,
                    'day_name' => Carbon::parse($unique_day)->format('l'),
                    'reports'  => [
                        'total_sale'       => $period_total_sale,
                      ], // end reports
                  ]); // end array push
                } // end foreach unique day
                      
    // end flow chart section
                    
                return response()->json([
                    'status_code' => 200, 
                    'message'     => 'success',
                    'total_orders'     => $get_orders,
                    'total_invoices'   => $top_total_invoices,
                    'total_sale'       => $sum_part_invoices,
                    // 'total_customers'  => $top_total_customers,
                    // 'total_vendors'    => $top_total_vendors,
                    'total_products'   => $top_total_products,
                   // 'sales_nalytics'   => $orders,
                    'period_details'   => $details_array,
                ], 200);
          } // case logged in user role is Vendor
          elseif (in_array('Vendor', $user_roles)) { 
          } // end case vendor
          else{
            return response()->json([
                    'status_code' => 401, 
                    // 'message'     => 'success',
                    'message'  => 'un authorized access page due to permissions',
                   ], 401);
          } // end if
      }  // end function vendor product report
}
