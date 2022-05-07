<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\AddVendor;
use Auth;
use App\Http\Resources\Vendor\VendorInvoicesApiResource;
use App\Http\Resources\Vendor\VendorApiSpecificInvoiceResource;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class VendorInvoicesApiController extends Controller
{
    public function show_invoices(Request $request)
    {
        abort_if(Gate::denies('show_invoices_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;

      $user = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();
      // case logged in user role is Admin (show all invoices)
      if (in_array('Admin', $user_roles)) {
        $data = AdminInvoicesApiResource::collection(Invoice::skip(($page-1)*PAGINATION_COUNT)
                        ->take(PAGINATION_COUNT)->orderBy($ordered_by, $sort_type)->get());
          return response()->json([
                  'data'  => $data,
                  'total' => Invoice::count(),
          ]);
      } 
       // case logged in user role is Vendor (show only his invoices)
      elseif (in_array('Vendor', $user_roles)) {
        $vendor   = AddVendor::where('userid_id', Auth::user()->id)->first();
        $mine     = Invoice::where('vendor_id', $vendor->id)
                                ->skip(($page-1)*PAGINATION_COUNT)
                                ->take(PAGINATION_COUNT)->orderBy($ordered_by, $sort_type)->get();
        $total    = Invoice::where('vendor_id', $vendor->id)
                                ->count();
        $invoices = VendorInvoicesApiResource::collection($mine);
        return response()->json([
                'data'  => $invoices,
                'total' => $total,
               ], 200);
      }
      else{
        return response()->json([
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
                    return response()->json(['data' => $invoice_data], 200);
                }
                else{
                    return response()->json(['message' => 'invoice dos not match'], 401);
                }
        }
        else{
            return response()->json([
                    'message'  => 'un authorized access page due to permissions',
                   ], 401);
          }
    }
}
