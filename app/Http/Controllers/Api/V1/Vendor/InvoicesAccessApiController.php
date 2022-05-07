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
use App\Models\Vendorstaff;
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

class InvoicesAccessApiController extends Controller
{
    public function show_invoices(Request $request)
    {
      abort_if(Gate::denies('show_invoices_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
     // $PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
     $default_count = \Config::get('constants.pagination.items_per_page');
     $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
     
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'DESC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      $user = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();

      // case logged in user role is Admin (show all invoices)
      if (in_array('Admin', $user_roles)) {
        if ($ordered_by == 'order_number') {
          $result = Invoice::select('invoices.*')
                        ->join('orders', 'invoices.order_id', '=', 'orders.id')
                        //->join('add_vendors', 'invoices.vendor_id', '=', 'add_vendors.id')
                        ->skip(($page-1)*$PAGINATION_COUNT)
                        ->take($PAGINATION_COUNT)->orderBy('orders.order_number', $sort_type)
                        ->get();
        $data = AdminInvoicesApiResource::collection($result);
          return response()->json([
                  'status_code' => 200, 
                  'message'     => 'success',
                  'data'  => $data,
                  'total' => Invoice::count(),
          ], 200);
        }
        elseif ($ordered_by == 'vendor_name') {
          $result = Invoice::select('invoices.*')
                        //->join('orders', 'invoices.order_id', '=', 'orders.id')
                        ->join('add_vendors', 'invoices.vendor_id', '=', 'add_vendors.id')
                        ->skip(($page-1)*$PAGINATION_COUNT)
                        ->take($PAGINATION_COUNT)->orderBy('add_vendors.vendor_name', $sort_type)
                        ->get();
        $data = AdminInvoicesApiResource::collection($result);
          return response()->json([
                  'status_code' => 200, 
                  'message'     => 'success',
                  'data'  => $data,
                  'total' => Invoice::count(),
          ], 200);
        }
        else{
          $result = Invoice::skip(($page-1)*$PAGINATION_COUNT)
                        ->take($PAGINATION_COUNT)->orderBy($ordered_by, $sort_type)
                        ->get();
        $data = AdminInvoicesApiResource::collection($result);
          return response()->json([
                  'status_code' => 200, 
                  'message'     => 'success',
                  'data'  => $data,
                  'total' => Invoice::count(),
          ], 200);
        }
      } // end admin case
       // case logged in user role is Vendor (show only his invoices)
      elseif (in_array('Vendor', $user_roles)) {
        $vendor   = AddVendor::where('userid_id', Auth::user()->id)->first();
        if ($ordered_by == 'order_number') {
          $result = Invoice::select('invoices.*')->where('vendor_id', $vendor->id)
                        ->join('orders', 'invoices.order_id', '=', 'orders.id')
                        //->join('add_vendors', 'invoices.vendor_id', '=', 'add_vendors.id')
                        ->skip(($page-1)*$PAGINATION_COUNT)
                        ->take($PAGINATION_COUNT)->orderBy('orders.order_number', $sort_type)
                        ->get();
        $data = AdminInvoicesApiResource::collection($result);
          return response()->json([
                  'status_code' => 200, 
                  'message'     => 'success',
                  'data'  => $data,
                  'total' => Invoice::where('vendor_id', $vendor->id)->count(),
          ], 200);
        }
        elseif ($ordered_by == 'vendor_name') {
          $result = Invoice::select('invoices.*')->where('vendor_id', $vendor->id)
                        //->join('orders', 'invoices.order_id', '=', 'orders.id')
                        ->join('add_vendors', 'invoices.vendor_id', '=', 'add_vendors.id')
                        ->skip(($page-1)*$PAGINATION_COUNT)
                        ->take($PAGINATION_COUNT)->orderBy('add_vendors.vendor_name', $sort_type)
                        ->get();
        $data = AdminInvoicesApiResource::collection($result);
          return response()->json([
                  'status_code' => 200, 
                  'message'     => 'success',
                  'data'  => $data,
                  'total' => Invoice::where('vendor_id', $vendor->id)->count(),
          ], 200);
        }
        else{
          $result = Invoice::where('vendor_id', $vendor->id)->skip(($page-1)*$PAGINATION_COUNT)
                        ->take($PAGINATION_COUNT)->orderBy($ordered_by, $sort_type)
                        ->get();
          $data   = AdminInvoicesApiResource::collection($result);
          return response()->json([
                  'status_code' => 200, 
                  'message'     => 'success',
                  'data'  => $data,
                  'total' => Invoice::where('vendor_id', $vendor->id)->count(),
          ], 200);
        }
      } // end case vendor
      /* manager case */
      elseif (in_array('Manager', $user_roles)) 
      {
       // abort_if(Gate::denies('stores_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
        // $staff_stores = $exist_staff->stores->pluck('id')->toArray();
       // return $staff_stores;
        if ($ordered_by == 'order_number') {
          $result = Invoice::select('invoices.*')->where('vendor_id', $vendor->id)
                        ->join('orders', 'invoices.order_id', '=', 'orders.id')
                        //->join('add_vendors', 'invoices.vendor_id', '=', 'add_vendors.id')
                        ->skip(($page-1)*$PAGINATION_COUNT)
                        ->take($PAGINATION_COUNT)->orderBy('orders.order_number', $sort_type)
                        ->get();
        $data = AdminInvoicesApiResource::collection($result);
          return response()->json([
                  'status_code' => 200, 
                  'message'     => 'success',
                  'data'  => $data,
                  'total' => Invoice::where('vendor_id', $vendor->id)->count(),
          ], 200);
        }
        elseif ($ordered_by == 'vendor_name') {
          $result = Invoice::select('invoices.*')->where('vendor_id', $vendor->id)
                        //->join('orders', 'invoices.order_id', '=', 'orders.id')
                        ->join('add_vendors', 'invoices.vendor_id', '=', 'add_vendors.id')
                        ->skip(($page-1)*$PAGINATION_COUNT)
                        ->take($PAGINATION_COUNT)->orderBy('add_vendors.vendor_name', $sort_type)
                        ->get();
        $data = AdminInvoicesApiResource::collection($result);
          return response()->json([
                  'status_code' => 200, 
                  'message'     => 'success',
                  'data'  => $data,
                  'total' => Invoice::where('vendor_id', $vendor->id)->count(),
          ], 200);
        }
        else{
          $result = Invoice::where('vendor_id', $vendor->id)->skip(($page-1)*$PAGINATION_COUNT)
                        ->take($PAGINATION_COUNT)->orderBy($ordered_by, $sort_type)
                        ->get();
          $data   = AdminInvoicesApiResource::collection($result);
          return response()->json([
                  'status_code' => 200, 
                  'message'     => 'success',
                  'data'  => $data,
                  'total' => Invoice::where('vendor_id', $vendor->id)->count(),
          ], 200);
        }
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

    public function show_specific_invoice(Invoice $invoice)
    {
        abort_if(Gate::denies('show_specific_invoice'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $user = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();
     // case logged in user role is Admin 
        if (in_array('Admin', $user_roles)) {
            $invoice_data = new VendorApiSpecificInvoiceResource($invoice);
            return response()->json(['data' => $invoice_data], 200);
        } 
           // case logged in user role is Vendor 
        elseif (in_array('Vendor', $user_roles)) {
                $vendor = AddVendor::where('userid_id', Auth::user()->id)->first();
                if ($invoice->vendor_id == $vendor->id) {
                    $invoice_data = new VendorApiSpecificInvoiceResource($invoice);
                    return response()->json([
                        'status_code' => 200, 
                        'message'     => 'success',
                        'data' => $invoice_data], 200);
                }
                else{
                    return response()->json([
                        'status_code' => 401, 
                       // 'message'     => 'success',
                        'message' => 'invoice dos not match'], 401);
                }
        }
        /* manager case */
        elseif (in_array('Manager', $user_roles) || in_array('Staff', $user_roles) ) 
      {
       // abort_if(Gate::denies('stores_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
       // $staff_stores = $exist_staff->stores->pluck('id')->toArray();
       // return $staff_stores;
                if ($invoice->vendor_id == $vendor->id) 
                {
                    $invoice_data = new VendorApiSpecificInvoiceResource($invoice);
                    return response()->json([
                        'status_code' => 200, 
                        'message'     => 'success',
                        'data' => $invoice_data], 200);
                }
                else{
                    return response()->json([
                        'status_code' => 401, 
                       // 'message'     => 'success',
                        'message' => 'invoice dos not match'], 401);
                }
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

    // start search invoices with name
     public function search_with_name(SearchApisRequest $request)
     {
     // $PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
     $default_count = \Config::get('constants.pagination.items_per_page');
     $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
     
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      
        $search_index = $request->search_index;
        $user       = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

        // case admin search
        if (in_array('Admin', $user_roles)) {
             if ($ordered_by == 'order_number') {
            $get_invoices = Invoice::select('invoices.*')
                             ->join('orders', 'invoices.order_id', '=', 'orders.id')
                             //->join('add_vendors', 'invoices.vendor_id', '=', 'add_vendors.id')
                             ->where(function ($q) use ($search_index) {
                $q->where('invoice_number', 'like', "%{$search_index}%")
                  ->orWhere('invoice_total', 'like', "%{$search_index}%");
                  // ->orWhere('vendor_id', 'like', "%{$search_index}%")
                  // ->orWhere('order_id', 'like', "%{$search_index}%");
                })
                ->orWhereHas('vendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })
                ->orWhereHas('order', function($q) use ($search_index){
                                $q->where('order_number', 'like', "%{$search_index}%")
                                  ->orWhere('order_total', '=', "%{$search_index}%");
                })
                ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                ->orderBy('orders.order_number', $sort_type)->get();
        $invoices = VendorInvoicesApiResource::collection($get_invoices);

        $total = Invoice::where(function ($q) use ($search_index) {
                $q->where('invoice_number', 'like', "%{$search_index}%")
                  ->orWhere('invoice_total', 'like', "%{$search_index}%");
                  // ->orWhere('vendor_id', 'like', "%{$search_index}%")
                  // ->orWhere('order_id', 'like', "%{$search_index}%");
                })
                ->orWhereHas('vendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })
                ->orWhereHas('order', function($q) use ($search_index){
                                $q->where('order_number', 'like', "%{$search_index}%")
                                  ->orWhere('order_total', '=', "%{$search_index}%");
                })
                ->count();

            return response()->json([
                'status_code' => 200, 
                'message'     => 'success',
                'data' => $invoices,
                'total' => $total,
            ], 200);
          }
          elseif ($ordered_by == 'vendor_name') {
            $get_invoices = Invoice::select('invoices.*')
                             //->join('orders', 'invoices.order_id', '=', 'orders.id')
                             ->join('add_vendors', 'invoices.vendor_id', '=', 'add_vendors.id')
                             ->where(function ($q) use ($search_index) {
                $q->where('invoice_number', 'like', "%{$search_index}%")
                  ->orWhere('invoice_total', 'like', "%{$search_index}%");
                  // ->orWhere('vendor_id', 'like', "%{$search_index}%")
                  // ->orWhere('order_id', 'like', "%{$search_index}%");
                })
                ->orWhereHas('vendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })
                ->orWhereHas('order', function($q) use ($search_index){
                                $q->where('order_number', 'like', "%{$search_index}%")
                                  ->orWhere('order_total', '=', "%{$search_index}%");
                })
                ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                ->orderBy('add_vendors.vendor_name', $sort_type)->get();
        $invoices = VendorInvoicesApiResource::collection($get_invoices);

        $total = Invoice::where(function ($q) use ($search_index) {
                $q->where('invoice_number', 'like', "%{$search_index}%")
                  ->orWhere('invoice_total', 'like', "%{$search_index}%");
                 // ->orWhere('vendor_id', 'like', "%{$search_index}%")
                 // ->orWhere('order_id', 'like', "%{$search_index}%");
                })
                ->orWhereHas('vendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })
                ->orWhereHas('order', function($q) use ($search_index){
                                $q->where('order_number', 'like', "%{$search_index}%")
                                  ->orWhere('order_total', '=', "%{$search_index}%");
                })
                ->count();

            return response()->json([
                'status_code' => 200, 
                'message'     => 'success',
                'data' => $invoices,
                'total' => $total,
            ], 200);
          }
          else{
            $get_invoices = Invoice::where('invoice_number', 'like', "%{$search_index}%")
                  ->orWhere('invoice_total', 'like', "%{$search_index}%")
                 // ->orWhere('vendor_id', 'like', "%{$search_index}%")
                 // ->orWhere('order_id', 'like', "%{$search_index}%")
                ->orWhereHas('vendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })
                ->orWhereHas('order', function($q) use ($search_index){
                                $q->where('order_number', 'like', "%{$search_index}%")
                                  ->orWhere('order_total', '=', "%{$search_index}%");
                })
                ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                ->orderBy($ordered_by, $sort_type)->get();
        $invoices = VendorInvoicesApiResource::collection($get_invoices);

        $total = Invoice::where(function ($q) use ($search_index) {
                $q->where('invoice_number', 'like', "%{$search_index}%")
                  ->orWhere('invoice_total', 'like', "%{$search_index}%");
                  // ->orWhere('vendor_id', 'like', "%{$search_index}%")
                  // ->orWhere('order_id', 'like', "%{$search_index}%");
                })
                ->orWhereHas('vendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })
                ->orWhereHas('order', function($q) use ($search_index){
                                $q->where('order_number', 'like', "%{$search_index}%")
                                  ->orWhere('order_total', '=', "%{$search_index}%");
                })
                ->count();

            return response()->json([
                'status_code' => 200, 
                'message'     => 'success',
                'data' => $invoices,
                'total' => $total,
            ], 200);
          }  
        } // end case admin
        // case vendor search
        elseif (in_array('Vendor', $user_roles)) {
            $vendor       = AddVendor::where('userid_id', Auth::user()->id)->first();
            $vendor_id    = $vendor->id;

              if ($ordered_by == 'order_number') {
                 $get_invoices = Invoice::select('invoices.*')
                             ->join('orders', 'invoices.order_id', '=', 'orders.id')
                             //->join('add_vendors', 'invoices.vendor_id', '=', 'add_vendors.id')
                             ->where('vendor_id', $vendor_id)
                  ->where(function ($q) use ($search_index) {
                $q->where('invoice_number', 'like', "%{$search_index}%")
                  ->orWhere('invoice_total', 'like', "%{$search_index}%");
                 //  ->orWhere('vendor_id', 'like', "%{$search_index}%")
                  // ->orWhere('order_id', 'like', "%{$search_index}%");
                })
                ->orWhereHas('vendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })
                ->orWhereHas('order', function($q) use ($search_index){
                                $q->where('order_number', 'like', "%{$search_index}%")
                                  ->orWhere('order_total', '=', "%{$search_index}%");
                })
                ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                ->orderBy('orders.order_number', $sort_type)->get();
                $get_invoices = $get_invoices->where('vendor_id', $vendor_id);
                $total = count($get_invoices);
        $invoices = VendorInvoicesApiResource::collection($get_invoices);

        /*$total = Invoice::where('vendor_id', $vendor_id)
                  ->where(function ($q) use ($search_index) {
                $q->where('invoice_number', 'like', "%{$search_index}%")
                  ->orWhere('invoice_total', 'like', "%{$search_index}%");
                  // ->orWhere('vendor_id', 'like', "%{$search_index}%")
                  // ->orWhere('order_id', 'like', "%{$search_index}%");
                })
                ->orWhereHas('vendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })
                ->orWhereHas('order', function($q) use ($search_index){
                                $q->where('order_number', 'like', "%{$search_index}%")
                                  ->orWhere('order_total', '=', "%{$search_index}%");
                })
                ->count();*/

              return response()->json([
                  'status_code' => 200, 
                  'message'     => 'success',
                  'data' => $invoices,
                  'total' => $total,
              ], 200);
              }
              elseif ($ordered_by == 'vendor_name') {
                 $get_invoices = Invoice::select('invoices.*')
                             //->join('orders', 'invoices.order_id', '=', 'orders.id')
                             ->join('add_vendors', 'invoices.vendor_id', '=', 'add_vendors.id')
                             ->where('vendor_id', $vendor_id)
                  ->where(function ($q) use ($search_index) {
                $q->where('invoice_number', 'like', "%{$search_index}%")
                  ->orWhere('invoice_total', 'like', "%{$search_index}%");
                  // ->orWhere('vendor_id', 'like', "%{$search_index}%")
                  // ->orWhere('order_id', 'like', "%{$search_index}%");
                })
                ->orWhereHas('vendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })
                ->orWhereHas('order', function($q) use ($search_index){
                                $q->where('order_number', 'like', "%{$search_index}%")
                                  ->orWhere('order_total', '=', "%{$search_index}%");
                })
                ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                ->orderBy('add_vendors.vendor_name', $sort_type)->get();
                $get_invoices = $get_invoices->where('vendor_id', $vendor_id);
                $total = count($get_invoices);
        $invoices = VendorInvoicesApiResource::collection($get_invoices);

       /* $total = Invoice::where('vendor_id', $vendor_id)
                  ->where(function ($q) use ($search_index) {
                $q->where('invoice_number', 'like', "%{$search_index}%")
                  ->orWhere('invoice_total', 'like', "%{$search_index}%");
                  // ->orWhere('vendor_id', 'like', "%{$search_index}%")
                  // ->orWhere('order_id', 'like', "%{$search_index}%");
                })
                ->orWhereHas('vendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })
                ->orWhereHas('order', function($q) use ($search_index){
                                $q->where('order_number', 'like', "%{$search_index}%")
                                  ->orWhere('order_total', '=', "%{$search_index}%");
                })
                ->count();*/

              return response()->json([
                  'status_code' => 200, 
                  'message'     => 'success',
                  'data' => $invoices,
                  'total' => $total,
              ], 200);
              }
              else{
                // return $vendor_id;
                 $get_invoices = Invoice::where(function ($q) use ($search_index) {
                $q->where('invoice_number', 'like', "%{$search_index}%")
                  ->orWhere('invoice_total', 'like', "%{$search_index}%");
                  //->orWhere('vendor_id', 'like', "%{$search_index}%")
                 // ->orWhere('order_id', 'like', "%{$search_index}%");
                })
                ->orWhereHas('vendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })
                ->orWhereHas('order', function($q) use ($search_index){
                                $q->where('order_number', 'like', "%{$search_index}%")
                                  ->orWhere('order_total', '=', "%{$search_index}%");
                })
                ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                ->orderBy($ordered_by, $sort_type)->get();
        $get_invoices = $get_invoices->where('vendor_id', $vendor_id);
        $total = count($get_invoices);
        $invoices = VendorInvoicesApiResource::collection($get_invoices);

        /*$total = Invoice::where('vendor_id', $vendor_id)
                  ->where(function ($q) use ($search_index) {
                $q->where('invoice_number', 'like', "%{$search_index}%")
                  ->orWhere('invoice_total', 'like', "%{$search_index}%");
                  //->orWhere('vendor_id', 'like', "%{$search_index}%")
                 // ->orWhere('order_id', 'like', "%{$search_index}%");
                })
                ->orWhereHas('vendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })
                ->orWhereHas('order', function($q) use ($search_index){
                                $q->where('order_number', 'like', "%{$search_index}%")
                                ->orWhere('order_total', '=', "%{$search_index}%");
                })
                ->count();*/

              return response()->json([
                  'status_code' => 200, 
                  'message'     => 'success',
                  'data' => $invoices,
                  'total' => $total,
              ], 200);
              }  
        }// end case vendor
         elseif (in_array('Manager', $user_roles) ) 
      {
        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
        $staff_stores = $exist_staff->stores->pluck('id')->toArray();


              if ($ordered_by == 'order_number') {
                 $get_invoices = Invoice::select('invoices.*')
                             ->join('orders', 'invoices.order_id', '=', 'orders.id')
                             //->join('add_vendors', 'invoices.vendor_id', '=', 'add_vendors.id')
                             ->where('vendor_id', $vendor_id)
                  ->where(function ($q) use ($search_index) {
                $q->where('invoice_number', 'like', "%{$search_index}%")
                  ->orWhere('invoice_total', 'like', "%{$search_index}%");
                 //  ->orWhere('vendor_id', 'like', "%{$search_index}%")
                  // ->orWhere('order_id', 'like', "%{$search_index}%");
                })
                ->orWhereHas('vendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })
                ->orWhereHas('order', function($q) use ($search_index){
                                $q->where('order_number', 'like', "%{$search_index}%")
                                  ->orWhere('order_total', '=', "%{$search_index}%");
                })
                ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                ->orderBy('orders.order_number', $sort_type)->get();
                $get_invoices = $get_invoices->where('vendor_id', $vendor_id);
                $total = count($get_invoices);
        $invoices = VendorInvoicesApiResource::collection($get_invoices);

        /*$total = Invoice::where('vendor_id', $vendor_id)
                  ->where(function ($q) use ($search_index) {
                $q->where('invoice_number', 'like', "%{$search_index}%")
                  ->orWhere('invoice_total', 'like', "%{$search_index}%");
                  // ->orWhere('vendor_id', 'like', "%{$search_index}%")
                  // ->orWhere('order_id', 'like', "%{$search_index}%");
                })
                ->orWhereHas('vendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })
                ->orWhereHas('order', function($q) use ($search_index){
                                $q->where('order_number', 'like', "%{$search_index}%")
                                  ->orWhere('order_total', '=', "%{$search_index}%");
                })
                ->count();*/

              return response()->json([
                  'status_code' => 200, 
                  'message'     => 'success',
                  'data' => $invoices,
                  'total' => $total,
              ], 200);
              }
              elseif ($ordered_by == 'vendor_name') {
                 $get_invoices = Invoice::select('invoices.*')
                             //->join('orders', 'invoices.order_id', '=', 'orders.id')
                             ->join('add_vendors', 'invoices.vendor_id', '=', 'add_vendors.id')
                             ->where('vendor_id', $vendor_id)
                  ->where(function ($q) use ($search_index) {
                $q->where('invoice_number', 'like', "%{$search_index}%")
                  ->orWhere('invoice_total', 'like', "%{$search_index}%");
                  // ->orWhere('vendor_id', 'like', "%{$search_index}%")
                  // ->orWhere('order_id', 'like', "%{$search_index}%");
                })
                ->orWhereHas('vendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })
                ->orWhereHas('order', function($q) use ($search_index){
                                $q->where('order_number', 'like', "%{$search_index}%")
                                  ->orWhere('order_total', '=', "%{$search_index}%");
                })
                ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                ->orderBy('add_vendors.vendor_name', $sort_type)->get();
                $get_invoices = $get_invoices->where('vendor_id', $vendor_id);
                $total = count($get_invoices);
        $invoices = VendorInvoicesApiResource::collection($get_invoices);

       /* $total = Invoice::where('vendor_id', $vendor_id)
                  ->where(function ($q) use ($search_index) {
                $q->where('invoice_number', 'like', "%{$search_index}%")
                  ->orWhere('invoice_total', 'like', "%{$search_index}%");
                  // ->orWhere('vendor_id', 'like', "%{$search_index}%")
                  // ->orWhere('order_id', 'like', "%{$search_index}%");
                })
                ->orWhereHas('vendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })
                ->orWhereHas('order', function($q) use ($search_index){
                                $q->where('order_number', 'like', "%{$search_index}%")
                                  ->orWhere('order_total', '=', "%{$search_index}%");
                })
                ->count();*/

              return response()->json([
                  'status_code' => 200, 
                  'message'     => 'success',
                  'data' => $invoices,
                  'total' => $total,
              ], 200);
              }
              else{
                // return $vendor_id;
                 $get_invoices = Invoice::where(function ($q) use ($search_index) {
                $q->where('invoice_number', 'like', "%{$search_index}%")
                  ->orWhere('invoice_total', 'like', "%{$search_index}%");
                  //->orWhere('vendor_id', 'like', "%{$search_index}%")
                 // ->orWhere('order_id', 'like', "%{$search_index}%");
                })
                ->orWhereHas('vendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })
                ->orWhereHas('order', function($q) use ($search_index){
                                $q->where('order_number', 'like', "%{$search_index}%")
                                  ->orWhere('order_total', '=', "%{$search_index}%");
                })
                ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                ->orderBy($ordered_by, $sort_type)->get();
        $get_invoices = $get_invoices->where('vendor_id', $vendor_id);
        $total = count($get_invoices);
        $invoices = VendorInvoicesApiResource::collection($get_invoices);

        /*$total = Invoice::where('vendor_id', $vendor_id)
                  ->where(function ($q) use ($search_index) {
                $q->where('invoice_number', 'like', "%{$search_index}%")
                  ->orWhere('invoice_total', 'like', "%{$search_index}%");
                  //->orWhere('vendor_id', 'like', "%{$search_index}%")
                 // ->orWhere('order_id', 'like', "%{$search_index}%");
                })
                ->orWhereHas('vendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })
                ->orWhereHas('order', function($q) use ($search_index){
                                $q->where('order_number', 'like', "%{$search_index}%")
                                ->orWhere('order_total', '=', "%{$search_index}%");
                })
                ->count();*/

              return response()->json([
                  'status_code' => 200, 
                  'message'     => 'success',
                  'data' => $invoices,
                  'total' => $total,
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
    // end search invoices with name
}
