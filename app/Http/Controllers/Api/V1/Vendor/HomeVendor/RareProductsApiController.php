<?php

namespace App\Http\Controllers\Api\V1\Vendor\HomeVendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Gate;
use App\Models\AddVendor;
use Auth;
use App\Http\Resources\Admin\ProductResource;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\Website\Products\FrontProductsApiResource;

class RareProductsApiController extends Controller
{
	public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }

	public function rare_products(Request $request)
	{
		  $lang = $this->getLang();
	      $user = Auth::user();
	      $user_roles = $user->roles->pluck('title')->toArray();
	     // abort_if(Gate::denies('product_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

	      $request->page == '' ? $page = 1 : $page = $request->page;
	      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
	      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
	      $default_count = \Config::get('constants.pagination.items_per_page');
	      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
	      
	       // case logged in user role is Vendor (show only his invoices)
	      if (in_array('Vendor', $user_roles)) {
	              $vendor        = AddVendor::where('userid_id', Auth::user()->id)->first();
	              $vendor_id     = $vendor->id;

	              $products = Product::with(['tags', 'category', 'car_made', 'car_model', 'car_type', 'year_from_func', 'year_to_func', 'part_category', 'store', 'manufacturer', 'origin_country', 'product_type'])
				            	->whereColumn('quantity', '<=', 'qty_reminder')
				            	->where('producttype_id', 1)
				            	->where('lang', $lang)
				            	->where('approved', 1)
				            	->where('vendor_id', $vendor_id)
				            	->skip(($page-1)*$PAGINATION_COUNT)
				            	->take($PAGINATION_COUNT)
				            	->orderBy($ordered_by, $sort_type)
				            	->get();

				// $data = FrontProductsApiResource::collection($products);
				$data = ProductResource::collection($products);

	        return response()->json([
	            'status_code' => 200,
	            'data'  => $data,
	            'total' => Product::where('lang', $lang)
	                        ->where('producttype_id', 1)
	                        ->whereColumn('quantity', '<=', 'qty_reminder')
	                        ->where('vendor_id', $vendor_id)->where('approved', 1)->count(),
	        ], 200);
	      }
	      else{
	        return response()->json([
	        	    'status_code' => 401,
	                'message'     => 'un authorized access page due to permissions',
	               ], 401);
	      }
	  }
}
