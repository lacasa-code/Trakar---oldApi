<?php

namespace App\Http\Controllers\Api\V1\User\Products;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Gate;
use App\Http\Requests\SearchApisRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Schema;
use App\Http\Resources\Website\Products\FrontProductsApiResource;
use App\Http\Resources\Website\Products\SpecificFrontProductsApiResource;
use Auth;
use App\Models\Productuserview;
// use DB;
use App\Http\Requests\Api\V1\Website\User\Views\LoggedUserViewProductApiRequest;

class UserMostViewedProductsApiController extends Controller
{
    public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }

  public function user_viewed_prods(Request $request)
    {
        $lang = $this->getLang();

        $user     = Auth::user();
        $user_id  = Auth::user()->id;
        $user_roles = $user->roles->pluck('title')->toArray();
        // return $user_roles;

	      // case logged in user role is Admin 
	      if (in_array('User', $user_roles)) 
        {
	      	$indexes = Productuserview::where('user_id', $user_id)
                 //  ->select('product_id', DB::raw('count(*) as count'))
                   // ->groupBy('product_id')
                   // ->limit(6)
                   ->orderBy('count_view', 'desc')
                   ->pluck('product_id')->toArray();

	        $sorter = static function ($produto) use ($indexes) {
	                  return array_search($produto->id, $indexes);
	               };

	        $products = Product::where('approved', 1)->whereIn('id', $indexes)->get()->sortBy($sorter);
          foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
	        $data = FrontProductsApiResource::collection($products);        
	            return response()->json([
	              'status_code' => 200,
	              'message'     => 'success',
	                'data'      => $data,
	                // 'total'       => $total,
	            ], 200);
	      }// end admin user
	      else{
	      	return response()->json([
                'status_code' => 401, 
                'message'  => 'un authorized access page due to permissions',
               ], 401);
	      } // end else
      }  // end function

    public function user_view_product(LoggedUserViewProductApiRequest $request)
    {
    	$user = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

      if (in_array('User', $user_roles)) {
      	$user_id   = Auth::user()->id;
      	$product   = Product::findOrFail($request->product_id);
      	$exist     = Productuserview::where('user_id', $user_id)
      	                    ->where('product_id', $product->id)->first();
      	   if ($exist != null) {
	      		$exist->update([ 
	                'count_view' => $exist->count_view + 1,
	            ]);
	            $data = new SpecificFrontProductsApiResource($product);   
		        return response()->json([
		        	      'status_code'  => 200,
			        	  'message'      => 'success',
		                  'data'         => $data,
		            ], 200);
		    }
            else{
            	Productuserview::create([
            		'user_id'    => $user_id,
            		'product_id' => $product->id,
            		'count_view' => 1,
            	]);
            	$data = new SpecificFrontProductsApiResource($product);   
		        return response()->json([
		        	      'status_code'  => 200,
			        	  'message'      => 'success',
		                  'data'         => $data,
		            ], 200);
            }   
      } 
      else{
        return response()->json([
                'status_code'  => 401,
                'message'  => 'un authorized access page due to permissions',
               ], 401);
      }		
    }
}
