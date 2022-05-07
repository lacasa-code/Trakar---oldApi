<?php

namespace App\Http\Controllers\Api\V1\User\Products;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\Website\User\FavouriteProducts\UserSingleFavouriteProductApiResource;
use App\Http\Resources\Website\User\FavouriteProducts\UserFavouriteProductsApiResource;
use Auth;
use App\Models\Favouriteproduct;
use App\Models\User;
use App\Models\Product;
use App\Http\Requests\Website\User\FavouriteProducts\UserRemoveItemFavouriteProductsApiRequest;
use App\Http\Requests\Website\User\FavouriteProducts\UserAddFavouriteProductApiRequest;

class UserFavouriteProductsApiController extends Controller
{
    public function user_get_favourite_products()
    {
    	$user = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

    //  if (in_array('User', $user_roles)) {
      	$user_id    = Auth::user()->id;
      	$favourites = Favouriteproduct::where('user_id', $user_id)->get();
      	$total      = Favouriteproduct::where('user_id', $user_id)->count();
        $data       = UserFavouriteProductsApiResource::collection($favourites);
      
        return response()->json([
        	          'status_code'  => 200,
	        	        'message'      => 'success',
                    'data'         => $data,
                    'total'        => $total,
            ], 200);
     /* } 
      else{
        return response()->json([
                'status_code' => 401,
                'message'  => 'un authorized access page due to permissions',
               ], 401);
      }*/
    }

    public function user_add_favourite(UserAddFavouriteProductApiRequest $request)
    {
    	$user = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

      //if (in_array('User', $user_roles)) {
      	$user_id   = Auth::user()->id;
      	$product   = Product::findOrFail($request->product_id);
      	$exist      = Favouriteproduct::where('user_id', $user_id)
      	                    ->where('product_id', $product->id)->first();
      	if ($exist != null) {
      		return response()->json([
      			'status_code'  => 400,
	        	'message'      => 'fail',
                'data'  => 'you have already this product in favourites',
               ], 400);
      	}
      	$favourite = Favouriteproduct::create([
                'user_id'         => $user_id, 
                'product_id'      => $request->product_id,  
    		]);
        $data      = new UserSingleFavouriteProductApiResource($favourite);
      
        return response()->json([
        	      'status_code'  => 200,
	        	  'message'      => 'success',
                  'data'         => $data,
            ], 200);
    /*  } 
      else{
        return response()->json([
                'status_code'  => 401,
                'message'  => 'un authorized access page due to permissions',
               ], 401);
      }		*/
    }

    public function user_remove_favourite(UserRemoveItemFavouriteProductsApiRequest $request)
    {
	    	$user = Auth::user();
	      $user_roles = $user->roles->pluck('title')->toArray();

	     // if (in_array('User', $user_roles)) {
	      	$user_id   = Auth::user()->id;
	      	$product_id      = $request->product_id;

          $exist = Favouriteproduct::where('product_id', $product_id)
                                  ->where('user_id', $user->id)
                          ->first();
          if ($exist == null) {
              return response()->json([
                            'status_code'  => 400,
                            'message'      => 'wrong product id favourite',
                            //'data'         => null,
                    ], 400);
            }
            

            if ($exist->user_id != $user_id) {
              return response()->json([
                            'status_code'  => 401,
                            'message'      => 'this does not belong to you',
                            //'data'         => null,
                    ], 401);
            }else{
                $exist->delete();
                return response()->json([
                            'status_code'  => 200,
                            'message'      => 'success',
                            'data'         => null,
                    ], 200);
              }
	     // }  // end if
	      /* else{
	        return response()->json([
	                'message'  => 'un authorized access page due to permissions',
	               ], 401);
	      }		*/
    }

    public function user_empty_favourite()
    {
    	$user = Auth::user();
	    $user_roles = $user->roles->pluck('title')->toArray();

	     // if (in_array('User', $user_roles)) {
	      	$user_id   = Auth::user()->id;
	      	// $arr       = json_decode('$user_id');
	      	// Wishlist::whereIn('user_id', $user_id)->delete();
	      	$user->favourites()->delete();
	        return response()->json([
	        	       'status_code'  => 200,
		        	     'message'      => 'success',
	                 'data'         => null,
	            ], 200);
	    /*  } 
	      else{
	        return response()->json([
                  'status_code'  => 401,
	                'message'  => 'un authorized access page due to permissions',
	               ], 401);
	      }		*/
    }
}
