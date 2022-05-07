<?php

namespace App\Http\Controllers\Api\V1\User\Wishlist;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\User\Wishlist\UserSingleWishlistApiResource;
use App\Http\Resources\User\Wishlist\UserWishlistApiResource;
use Auth;
use App\Models\Wishlist;
use App\Models\User;
use App\Models\Product;
use App\Http\Requests\Website\User\Wishlist\UserRemoveItemWishlistApiRequest;
use App\Http\Requests\Website\User\Wishlist\UserAddWishlistApiRequest;
use App\Models\AddVendor;

class UserWishlistApiController extends Controller
{
    public function user_get_wishlist()
    {
    	$user = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();

     if (in_array('User', $user_roles)) {
      	$user_id   = Auth::user()->id;
      	$shippings = Wishlist::where('user_id', $user_id)->get();
      	$total     = Wishlist::where('user_id', $user_id)->count();
        $data      = UserWishlistApiResource::collection($shippings);
      
        return response()->json([
        	          'status_code'  => 200,
	        	        'message'      => 'success',
                    'data'         => $data,
                    'total'        => $total,
            ], 200);
     } 
     elseif (in_array('Vendor', $user_roles)) {
        $user_id   = Auth::user()->id;
        $shippings = Wishlist::where('user_id', $user_id)->get();
        $total     = Wishlist::where('user_id', $user_id)->count();
        $data      = UserWishlistApiResource::collection($shippings);
      
        return response()->json([
                    'status_code'  => 200,
                    'message'      => 'success',
                    'data'         => $data,
                    'total'        => $total,
            ], 200);
     } 
      else{
        return response()->json([
                'status_code' => 401,
                'message'  => 'un authorized access page due to permissions',
               ], 401);
      }
    }

    public function user_add_wishlist(UserAddWishlistApiRequest $request)
    {
    	$user = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();

      if (in_array('User', $user_roles)) 
      {
            $user_id   = Auth::user()->id;
            $product = Product::findOrFail($request->product_id);
            $normal_price = $product->PriceAfterDiscount();
            $exist      = Wishlist::where('user_id', $user_id)
                            ->where('product_id', $product->id)->first();
          if ($exist != null) {
            return response()->json([
              'status_code'  => 400,
              'message'      => 'fail',
                  'data'  => 'you have already this product in wishlist',
                 ], 400);

           /* $exist->delete();
            return response()->json([
              'status_code'  => 200,
              'message'      => __('site_messages.Product_removed_from_wishlist_successfully'),
                  'data'  => __('site_messages.Product_removed_from_wishlist_successfully'),
                 ], 400);*/
          }
          $wishlist = Wishlist::create([
                'user_id'         => $user_id, 
                'product_id'     => $request->product_id,  
        ]);
        $data      = new UserSingleWishlistApiResource($wishlist);
        return response()->json([
                  'status_code'  => 200,
                   'message' => __('site_messages.Product_added_to_your_wishlist_successfully'),
                  'data'         => $data,
            ], 200);
      } // end user wishlist
      elseif (in_array('Vendor', $user_roles)) 
      {
              $user_id   = Auth::user()->id;
              $product = Product::findOrFail($request->product_id);
              $exist      = Wishlist::where('user_id', $user_id)
                            ->where('product_id', $product->id)->first();
              $exist_vendor = AddVendor::where('userid_id', $user_id)->first();
              // check vendor id of product
              if ($product->vendor_id == $exist_vendor->id) {
                return response()->json([
                  'status_code' => 400,
                  'errors' =>  __('site_messages.owner_wishlist_disable'),
                ], 400);
              }
               if ($exist != null) {
                return response()->json([
                  'status_code'  => 400,
                  'message'      => __('site_messages.Product_already_wishlist'),
                     // 'data'  => 'you have already this product in wishlist',
                      'data' => __('site_messages.Product_already_wishlist'),
                     ], 400);
              }
              $wishlist = Wishlist::create([
                'user_id'         => $user_id, 
                'product_id'     => $request->product_id,  
             ]);
              $data      = new UserSingleWishlistApiResource($wishlist);
              return response()->json([
                        'status_code'  => 200,
                     //   'message'      => 'success',
                        'message' => __('site_messages.Product_added_to_your_wishlist_successfully'),
                        'data'         => $data,
                  ], 200);
        }       
      else{
        return response()->json([
                'status_code'  => 401,
                'message'  => 'un authorized access page due to permissions',
               ], 401);
      }		
    }

    public function user_remove_item_wishlist(UserRemoveItemWishlistApiRequest $request)
    {
	    	$user = Auth::user();
	      $user_roles = $user->roles->pluck('title')->toArray();

	      //if (in_array('User', $user_roles)) {
	      	$user_id    = Auth::user()->id;
	      	$product_id = $request->product_id;
	
	      	$exist = Wishlist::where('product_id', $product_id)->where('user_id', $user->id)
                          ->first();
          if ($exist == null) {
              return response()->json([
                            'status_code'  => 400,
                            'message'      => 'wrong product id wishlist',
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
      		        	        // 'message'      => 'success',
                            'message' => __('site_messages.Product_removed_from_wishlist_successfully'),
      	                    'data'         => null,
      	            ], 200);
              }
	     /* } 
	      else{
	        return response()->json([
	                'message'  => 'un authorized access page due to permissions',
	               ], 401);
	      }		*/
    }

    public function user_empty_wishlist()
    {
    	$user = Auth::user();
	    $user_roles = $user->roles->pluck('title')->toArray();

	      if (in_array('User', $user_roles)) {
	      	$user_id   = Auth::user()->id;
	      	// $arr       = json_decode('$user_id');
	      	// Wishlist::whereIn('user_id', $user_id)->delete();
	      	$user->wishlists()->delete();
	        return response()->json([
	        	       'status_code'  => 200,
		        	     'message'      => 'success',
	                 'data'         => null,
	            ], 200);
	      } 
	      else{
	        return response()->json([
                  'status_code'  => 401,
	                'message'  => 'un authorized access page due to permissions',
	               ], 401);
	      }		
    }
}
