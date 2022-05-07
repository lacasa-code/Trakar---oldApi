<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Gate;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AddVendor;
use Auth;
use App\Http\Resources\Admin\SpecificProductApiResource;
use Validator;
use App\Http\Resources\Website\Products\FrontProductsApiResource;
// use App\Models\Sanctum\PersonalAccessToken;
use App\Http\Resources\Admin\AllProductsApiResource;
use App\Http\Requests\Api\V1\Admin\Products\AdminApproveProductApiRequest ;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotifyVendorWithApproveProductApiMail;
use App\Mail\ProductApprovalNotificationMail;

class ApprovedProductsApiController extends Controller
{
	public function getLang()
    {
      return $lang = \Config::get('app.locale');
    }

    public function approve_product(AdminApproveProductApiRequest  $request)
    {
    	$user = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

        if (in_array('Admin', $user_roles)) 
        {
	    	$product = Product::findOrFail($request->product_id);
	    	if ($product->approved == 1) {
	    		return response()->json([
	              'status_code' => 400,
	              'errors' => 'fail, already approved',
	              // 'data'  => $data,
	            ], 400);
	    	}else{
	    		$product->update(['approved' => 1]);
	    		$vendor_name  = $product->vendor->vendor_name;
	    		$user_id      = $product->vendor->userid_id;
	    		$vendor_email = User::where('id', $user_id)->first()->email;
	    		$prod_name    = $product->name;
	    		$prod_serial  = $product->serial_id;
	    		$prod_type  = $product->product_type->producttype;
	    		Mail::to($vendor_email)->send(new ProductApprovalNotificationMail($vendor_name, $prod_name, $prod_serial, $prod_type));
         
	    		return response()->json([
	              'status_code' => 200,
	              'message' => 'product approved successfully',
	              // 'data'  => $data,
	            ], 200);
	    	}
	    }else{
	    	return response()->json([
	              'status_code' => 400,
	              'errors' => 'Unauthorized access',
	              // 'data'  => $data,
	            ], 400);
	    }
    }

    public function products_need_approval(Request $request)
    {
    	$lang = $this->getLang();
       // abort_if(Gate::denies('product_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
      
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;

      $user = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();

       if (in_array('Admin', $user_roles)) 
        {
	    	$prods = Product::where('lang', $lang)->where('approved', '!=', 1)
	    	          ->skip(($page-1)*$PAGINATION_COUNT)
                      ->take($PAGINATION_COUNT)->orderBy($ordered_by, $sort_type)
                      ->get();
	        $data = AllProductsApiResource::collection($prods);
	        $total = Product::where('lang', $lang)->where('approved', '!=', 1)->count();
	        return response()->json([
              'status_code' => 200,
              'message' => 'success',
              'data'  => $data,
              'total' => $total,
            ], 200);
	    }else{
	    	return response()->json([
	              'status_code' => 400,
	              'errors' => 'Unauthorized access',
	              // 'data'  => $data,
	            ], 400);
	    }
    }
}
