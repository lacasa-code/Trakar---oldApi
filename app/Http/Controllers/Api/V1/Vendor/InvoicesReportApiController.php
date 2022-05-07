<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Gate;
use App\Http\Requests\ReportApiRequest;  
use App\Models\Invoice;
use App\Http\Resources\Admin\AdminInvoicesApiResource;

class InvoicesReportApiController extends Controller
{
    public function fetch_data(ReportApiRequest $request)
    {
     // abort_if(Gate::denies('show_orders_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
      $PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;

      $startDate = $request->start_date. ' '. '00:00:00';
      $endDate   = $request->end_date. ' '. '11:59:59';

      $user = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();

       // case logged in user role is Admin
       if (in_array('Admin', $user_roles)) {
   	    // case search reports in general
	       	if ($request->has('vendor_id') && $request->vendor_id != '') {
	       		$vendorId = $request->vendor_id;

	       	    $invoices = Invoice::where('vendor_id', $vendorId)->where('created_at', '>=', $startDate)
				    	->where('created_at', '<=', $endDate)
                        ->skip(($page-1)*$PAGINATION_COUNT)
                        ->take($PAGINATION_COUNT)->orderBy($ordered_by, $sort_type)->get();

                $total = Invoice::where('vendor_id', $vendorId)->where('created_at', '>=', $startDate)
				    	->where('created_at', '<=', $endDate)->count();

			    $data = AdminInvoicesApiResource::collection($invoices);
			    	return response()->json([
			    		'data'   => $data,
			    		'total'  => $total,
			    	]);
	       	} // end case search reports in general
	    // case search specific vendor reports
	       	else{
	       		$invoices = Invoice::where('created_at', '>=', $startDate)
					    	->where('created_at', '<=', $endDate)
	                        ->skip(($page-1)*$PAGINATION_COUNT)
	                        ->take($PAGINATION_COUNT)->orderBy($ordered_by, $sort_type)->get();

                $total = Invoice::where('created_at', '>=', $startDate)
					    	->where('created_at', '<=', $endDate)->count();

			    $data = AdminInvoicesApiResource::collection($invoices);
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

            $invoices = Invoice::where('vendor_id', $vendor_id)->where('created_at', '>=', $startDate)
				    	->where('created_at', '<=', $endDate)
                        ->skip(($page-1)*$PAGINATION_COUNT)
                        ->take($PAGINATION_COUNT)->orderBy($ordered_by, $sort_type)->get();

            $total = Invoice::where('vendor_id', $vendor_id)->where('created_at', '>=', $startDate)
				    	->where('created_at', '<=', $endDate)->count();

			$data = AdminInvoicesApiResource::collection($invoices);
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
    }
}
