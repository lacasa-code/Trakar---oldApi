<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Http\Resources\Admin\AdminInvoicesApiResource;
use App\Http\Resources\Admin\AdminApiSpecificInvoiceResource;
use App\Http\Requests\SearchApisRequest;
use Gate;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\AdminAcessSpecificVendorInvoicesRequest;
use App\Models\AddVendor;
use App\Models\Orderdetail;
use App\Models\Order;
use App\Http\Resources\Vendor\VendorApiSpecificInvoiceResource;
use App\Http\Resources\Vendor\VendorInvoicesApiResource;
use App\Http\Requests\SearchVendorInvoicesApisRequest;

class AdminInvoicesApiController extends Controller
{
// not used right now
/*
    public function show_invoices(Request $request)
    {
      abort_if(Gate::denies('show_invoices_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;

      $user = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();
      if (in_array('Admin', $user_roles)) {
          $data = AdminInvoicesApiResource::collection(Invoice::skip(($page-1)*$PAGINATION_COUNT)
                        ->take($PAGINATION_COUNT)->orderBy($ordered_by, $sort_type)->get());
          return response()->json([
                  'data'  => $data,
                  'total' => Invoice::count(),
          ]);
      } 
    }

    public function show_specific_invoice(Invoice $invoice)
    {
      abort_if(Gate::denies('show_specific_invoice'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        if (in_array('Admin', $user_roles)) {
            $invoice_data = new AdminApiSpecificInvoiceResource($invoice);
            return response()->json(['data' => $invoice_data], 200);
        } 
    }
*/

// admin access specific vendor invoices
    public function access_specific_vendor_invoices(AdminAcessSpecificVendorInvoicesRequest $request)
    {
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      //$PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
      if ($ordered_by == 'order_number') {
          $got_results = Invoice::where('vendor_id', $request->vendor_id)->select('invoices.*')
                        ->join('orders', 'invoices.order_id', '=', 'orders.id')
                        //->join('add_vendors', 'invoices.vendor_id', '=', 'add_vendors.id')
                        ->skip(($page-1)*$PAGINATION_COUNT)
                        ->take($PAGINATION_COUNT)->orderBy('orders.order_number', $sort_type)
                        ->get();

          $invoices    = VendorInvoicesApiResource::collection($got_results);
          $total       = Invoice::where('vendor_id', $request->vendor_id)->count();
          return response()->json([
                'status_code' => 200,
                'message' => 'success',
                'data'  => $invoices,
                'total' => $total,
               ], 200);
        }
        elseif ($ordered_by == 'vendor_name') {
          $got_results = Invoice::where('vendor_id', $request->vendor_id)->select('invoices.*')
                        //->join('orders', 'invoices.order_id', '=', 'orders.id')
                        ->join('add_vendors', 'invoices.vendor_id', '=', 'add_vendors.id')
                        ->skip(($page-1)*$PAGINATION_COUNT)
                        ->take($PAGINATION_COUNT)->orderBy('add_vendors.vendor_name', $sort_type)
                        ->get();
          $invoices    = VendorInvoicesApiResource::collection($got_results);
          $total       = Invoice::where('vendor_id', $request->vendor_id)->count();
          return response()->json([
                'status_code' => 200,
                'message' => 'success',
                'data'  => $invoices,
                'total' => $total,
               ], 200);
        }
        else{
          $got_results = Invoice::where('vendor_id', $request->vendor_id)
                        ->skip(($page-1)*$PAGINATION_COUNT)
                        ->take($PAGINATION_COUNT)->orderBy($ordered_by, $sort_type)
                        ->get();

          $invoices    = VendorInvoicesApiResource::collection($got_results);
          $total       = Invoice::where('vendor_id', $request->vendor_id)->count();
          return response()->json([
                'status_code' => 200,
                'message' => 'success',
                'data'  => $invoices,
                'total' => $total,
               ], 200);
        }
    }

    // admin access specific vendor specific order
    public function access_specific_vendor_specific_invoice(AddVendor $vendor, Invoice $invoice)
    {
        abort_if(Gate::denies('admin_access_specific_vendor_specific_invoice'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($invoice->vendor_id == $vendor->id) {
            $invoice_data = new VendorApiSpecificInvoiceResource($invoice);
            return response()->json([
              'status_code' => 200,
              'message' => 'success',
              'data' => $invoice_data], 200);
        }
        else
        {
            return response()->json([
              'status_code' => 401,
              // 'message' => 'success',
              'message' => 'invoice dos not match'], 401);
        }
    }

    // start search invoices with name
     public function search_with_name(SearchVendorInvoicesApisRequest $request)
     {
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      //$PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
       
        $search_index = $request->search_index;
        $vendor_id    = $request->vendor_id;

        $invoices = Invoice::where('vendor_id', $vendor_id)
                          ->where(function ($q) use ($search_index, $vendor_id){
                $q->where('invoice_number', 'like', "%{$search_index}%")
                  ->orWhere('invoice_total', 'like', "%{$search_index}%")
                  ->orWhere('order_id', 'like', "%{$search_index}%");
                })
                ->orWhereHas('vendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })
                ->orWhereHas('order', function($q) use ($search_index){
                                $q->where('order_number', 'like', "%{$search_index}%")
                                ->orWhere('order_total', '>=', "%{$search_index}%");
                })
                ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                ->orderBy($ordered_by, $sort_type)->get();
        $data = AdminInvoicesApiResource::collection($invoices);

        $total = Invoice::where('vendor_id', $vendor_id)
                          ->where(function ($q) use ($search_index, $vendor_id){
                $q->where('invoice_number', 'like', "%{$search_index}%")
                  ->orWhere('invoice_total', 'like', "%{$search_index}%")
                  ->orWhere('order_id', 'like', "%{$search_index}%");
                })
                ->orWhereHas('vendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })
                ->orWhereHas('order', function($q) use ($search_index){
                                $q->where('order_number', 'like', "%{$search_index}%")
                                ->orWhere('order_total', '>=', "%{$search_index}%");
                })
                ->count();

        return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data' => $data,
            'total' => $total,
        ], 200);
     }
    // end search invoices with name
}
