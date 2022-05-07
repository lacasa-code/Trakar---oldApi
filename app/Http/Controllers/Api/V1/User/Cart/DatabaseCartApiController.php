<?php

namespace App\Http\Controllers\Api\V1\User\Cart;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response as Custom;
use App\Models\Product;
use Gate;
use App\Http\Requests\SearchApisRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Schema;
use App\Http\Resources\User\Search\ProductSearchApiResource;
use App\Http\Resources\Website\Products\FrontProductsApiResource;
use App\Models\CarModel;
use App\Models\CarMade;
use App\Models\CarYear;
use Illuminate\Support\Facades\Cookie;
use App\Http\Requests\MakeOrderApiRequest;
use App\Http\Requests\Website\User\Cart\DeletefromcartApiRequest;
// 
use App\Http\Requests\Website\User\Cart\DatabaseCartAddOrderApiRequest;
use App\Http\Requests\Website\User\Cart\DatabaseCartUpdateOrderApiRequest;
use App\Http\Requests\Website\User\Cart\DatabaseCartDeleteOrderApiRequest;
use Auth;
use App\Models\Orderdetail;
use App\Models\AddVendor;
use App\Models\Order;
use App\Http\Resources\User\Cart\CartContentsApiResource;

class DatabaseCartApiController extends Controller
{
    public function addtocart(DatabaseCartAddOrderApiRequest $request)
    {
    	$user       = Auth::user();
    	$user_id    = Auth::user()->id;
    	$user_roles = $user->roles->pluck('title')->toArray();

    	if (in_array('User', $user_roles)) {
		        $product = Product::findOrFail($request->product_id);
		        $normal_price = $product->PriceAfterDiscount();
		        // check if quantity is enough or not
		        if ($product->quantity < $request->quantity) {
		            return response()->json([
		                'status_code' => 400,
		                'message' => 'sorry, quantity available right now is '. $product->quantity,
		            ], 400);
		        } // end check if quantity is enough or not

		        // get latest order number to keep sequence on
		        $latest_order = Order::withTrashed()->latest()->first();
		        if(is_null($latest_order)){
		            $sequence_order = 20000000;
		        }
		        else{
		            $sequence_order = $latest_order->order_number + 1; 
		        }
		    	$order   = Order::where('user_id', $user_id)
		    	              ->whereNull('paid')
		    	              ->whereNull('approved')
		                      ->where('status', '!=', 'cancelled') // not cancelled
                              ->where('status', '!=', 'in progress') // not in progress
		    	              ->where('expired', 0)->first();
		// not pending orders found
		    	if (!$order) {
		    		$new_order = Order::create([
		    			'user_id' => $user_id,
		                'status' => 'pending',
		                'order_number' => $sequence_order,
		                'order_total'  => 0,
		    		]);
		    		Orderdetail::create([
		                'order_id'       => $new_order->id, 
		                'product_id'     => $request->product_id, 
		                'store_id'       => $product->store_id, 
		                'vendor_id'      => $product->vendor_id,
		                'quantity'       => $request->quantity, 
		                'part_category_id' => $product->part_category_id, 
		                'vendor_type'      => $product->vendor->type, 
		                'producttype_id'  => $product->producttype_id, 
		                'price'          => $product->price, 
		                'discount'       => $product->discount == null ? 0 : $product->discount, 
		                // total after applying discount
		                'total'          => $normal_price * $request->quantity, 
		    		]);

		            $new_order->update(['order_total' => $new_order->sumTotal]);

		            return response()->json([
		                'status_code' => 200, 
		               // 'message' => 'product added to cart successfully',
		                'message' => __('site_messages.product_added_to_cart_successfully'),
		                
		               ], 200);
		    	} // end not pending orders found
		    	else{
		    		// check existence of target added product
		    		$existing_cart_contents = $order->orderDetails->pluck('product_id')->toArray();
		    		if (in_array($request->product_id, $existing_cart_contents)) {
		                // if product already in cart change qty and price
		                $orderDetail = Orderdetail::where('order_id', $order->id)
		                                        ->where('product_id', $request->product_id)->first();
		                //$orderDetail->update([
		                    $orderDetail->quantity = $request->quantity;
		                    $orderDetail->total    = $normal_price * $request->quantity;
		                    $orderDetail->save();
		               // ]);

		                $order->update(['order_total' => $orderDetail->order->sumTotal]);
		                 return response()->json([
		                    'status_code' => 200, 
		                    'message' => 'product quantity edited successfully',
		                   ], 200);
		    		} // end if
		    		else{
		    			// return 'not';
		                $orderDetail = Orderdetail::create([
		                'order_id'       => $order->id, 
		                'product_id'     => $request->product_id, 
		                'store_id'       => $product->store_id, 
		                'vendor_id'      => $product->vendor_id,
		                'part_category_id' => $product->part_category_id, 
		                'vendor_type'     => $product->vendor->type, 
		                'producttype_id'  => $product->producttype_id, 
		                'quantity'       => $request->quantity, 
		                'price'          => $product->price, 
		                'discount'       => $product->discount == null ? 0 : $product->discount, 
		                'total'          => $normal_price * $request->quantity,
		                ]);
		                $order->update(['order_total' => $orderDetail->order->sumTotal]);

		                return response()->json([
		                    'status_code' => 200, 
		                  //  'message' => 'product added to cart successfully',
		                    'message' => __('site_messages.product_added_to_cart_successfully'),
		                   ], 200);
		    		} // end else
		    	} // end first else
    } // end if user

    if (in_array('Vendor', $user_roles)) {
		        $product = Product::findOrFail($request->product_id);
		        $exist_vendor = AddVendor::where('userid_id', $user_id)->first();
		        if ($product->vendor_id == $exist_vendor->id) {
		        	return response()->json([
		        		'status_code' => 400,
		        		'errors' => __('site_messages.owner_cart_disable'),
		        	], 400);
		        }
		        // quantity check 
		        if ($product->producttype_id == 1) {
		        	if ($product->quantity < $request->quantity) {
			            return response()->json([
			                'status_code' => 400,
			                'message' => 'sorry, quantity available right now is '. $product->quantity,
			            ], 400);
		            } 
		        }
		        else{
		        	if ($exist_vendor->complete != 1 || $exist_vendor->approved != 1) {
		        		return response()->json([
			                'status_code' => 400,
			                'message' => 'can not add wholesale order to cart, not completed / approved profile',
			            ], 400);
		        	}
		        	if ($product->no_of_orders > $request->quantity) {
			            return response()->json([
			                'status_code' => 400,
			                'message' => 'sorry, min num of orders is '. $product->no_of_orders,
			            ], 400);
		            }
		        }
		        // quantity check 
		        if ($product->producttype_id == 1) {
		        	$actual_price = $product->price;
		        	$dynamic_price = $product->PriceAfterDiscount();
		        }
		        else{
		        	$actual_price = $product->holesale_price;
		        	$dynamic_price = $product->holesale_price;
		        }
		        // $wholesale_price = $product->holesale_price;

		        // get latest order number to keep sequence on
		        $latest_order = Order::withTrashed()->latest()->first();
		        if(is_null($latest_order)){
		            $sequence_order = 20000000;
		        }
		        else{
		            $sequence_order = $latest_order->order_number + 1; 
		        }
		    	$order   = Order::where('user_id', $user_id)
		    	              ->whereNull('paid')
		    	              ->whereNull('approved')
		                      ->where('status', '!=', 'cancelled') // not cancelled
                              ->where('status', '!=', 'in progress') // not in progress
		    	              ->where('expired', 0)->first();
		// not pending orders found
		    	if (!$order) {
		    		$new_order = Order::create([
		    			'user_id' => $user_id,
		                'status' => 'pending',
		                'order_number' => $sequence_order,
		                'order_total'  => 0,
		    		]);
		    		Orderdetail::create([
		                'order_id'       => $new_order->id, 
		                'product_id'     => $request->product_id, 
		                'store_id'       => $product->store_id, 
		                'vendor_id'      => $product->vendor_id,
		                'quantity'       => $request->quantity, 
		                'part_category_id' => $product->part_category_id, 
		                'vendor_type'    => $product->vendor->type, 
		                'producttype_id'  => $product->producttype_id, 
		                'price'          => $actual_price, 
		                'discount'       => $product->discount == null ? 0 : $product->discount, 
		                // total after applying discount
		                'total'          => $dynamic_price * $request->quantity, 
		    		]);

		            $new_order->update(['order_total' => $new_order->sumTotal]);

		            return response()->json([
		                'status_code' => 200, 
		              //  'message' => 'product added to cart successfully',
		                'message' => __('site_messages.product_added_to_cart_successfully'),
		               ], 200);
		    	} // end not pending orders found
		    	else{
		    		// check existence of target added product
		    		$existing_cart_contents = $order->orderDetails->pluck('product_id')->toArray();
		    		if (in_array($request->product_id, $existing_cart_contents)) {
		                // if product already in cart change qty and price
		                $orderDetail = Orderdetail::where('order_id', $order->id)
		                                        ->where('product_id', $request->product_id)->first();
		                //$orderDetail->update([
		                    $orderDetail->quantity = $request->quantity;
		                    $orderDetail->total    = $dynamic_price * $request->quantity;
		                    $orderDetail->save();
		               // ]);

		                $order->update(['order_total' => $orderDetail->order->sumTotal]);
		                 return response()->json([
		                    'status_code' => 200, 
		                    'message' => 'product quantity edited successfully',
		                   ], 200);
		    		} // end if
		    		else{
		    			//return $dynamic_price;
		                $orderDetail = Orderdetail::create([
		                'order_id'       => $order->id, 
		                'product_id'     => $request->product_id, 
		                'store_id'       => $product->store_id, 
		                'vendor_id'      => $product->vendor_id,
		                'part_category_id' => $product->part_category_id, 
		                'vendor_type'     => $product->vendor->type, 
		                'producttype_id'  => $product->producttype_id, 
		                'quantity'       => $request->quantity,
		                'price'          => $actual_price,
		                'discount'       => $product->discount == null ? 0 : $product->discount, 
		                'total'          => $dynamic_price * $request->quantity,
		                ]);
		                $order->update(['order_total' => $orderDetail->order->sumTotal]);

		                return response()->json([
		                    'status_code' => 200, 
		                   // 'message' => 'product added to cart successfully',
		                    'message' => __('site_messages.product_added_to_cart_successfully'),
		                   ], 200);
		    		} // end else
		    	} // end first else
		    } // end case vendor

      else{
        return response()->json([
                'status_code' => 401, 
                'message'  => 'un authorized access page due to permissions',
               ], 401);
      }
    } // end function

    public function updatetocart(DatabaseCartUpdateOrderApiRequest $request)
    {     
    	$user = Auth::user();
    	$user_roles = $user->roles->pluck('title')->toArray();

    	$user_id    = $user->id;
    	$product_id = $request->product_id;
    	$order_id   = $request->order_id;

    	if (in_array('User', $user_roles)) {
		        $product = Product::findOrFail($request->product_id);
		   
		    	$orderDetail   = Orderdetail::whereHas('order', function($q) use ($user_id){
								       $q->where('user_id', $user_id);
								    })
		    	                      ->where('order_id', $order_id)
		    	                      ->where('product_id', $request->product_id)
		    	                      ->where('approved', 0)->first();
		    	if (!$orderDetail) {
		    		return response()->json([
		    			'status_code' => 400,
		    			'message' => 'this order is not true',
		    		], 400);
		    	}

		    	if ($orderDetail->order->paid != null || $orderDetail->order->paid > 0) {
		    		return response()->json([
		    			'status_code' => 400,
		    			'message' => 'this order is not pending yet',
		    		], 400);
		    	}

		    	if ($orderDetail->order->approved == 1) {
		    		return response()->json([
		    			'status_code' => 400,
		    			'message' => 'this order is approved',
		    		], 400);
		    	}

		    	if ($orderDetail->order->expired != 0) {
		    		return response()->json([
		    			'status_code' => 400,
		    			'message' => 'this order is expired',
		    		], 400);
		    	}

		    	if ($orderDetail->order->status == 'cancelled') {
		    		return response()->json([
		    			'status_code' => 400,
		    			'message' => 'this order is cancelled',
		    		], 400);
		    	}

		    	 if ($product->quantity < $request->quantity) {
		            return response()->json([
		                'status_code' => 400,
		                'message' => 'sorry, quantity available right now is '. $product->quantity,
		            ], 400);
		        } // end check if quantity is enough or not
		
		    		
		                //$orderDetail->update([
		                    $orderDetail->quantity = $request->quantity;
		                    $orderDetail->total    = $product->PriceAfterDiscount() * $request->quantity;
		                    $orderDetail->save();
		               // ]);

		               $orderDetail->order->update(['order_total' => $orderDetail->order->sumTotal]);
		                 return response()->json([
		                    'status_code' => 200, 
		                    'message' => 'product quantity edited successfully',
		                   ], 200);    		
		    } // end if user

		    elseif (in_array('Vendor', $user_roles)) {
		        $product = Product::findOrFail($request->product_id);
		   
		    	$orderDetail   = Orderdetail::whereHas('order', function($q) use ($user_id){
								       $q->where('user_id', $user_id);
								    })
		    	                      ->where('order_id', $order_id)
		    	                      ->where('product_id', $request->product_id)
		    	                      ->where('approved', 0)->first();
		    	if (!$orderDetail) {
		    		return response()->json([
		    			'status_code' => 400,
		    			'message' => 'this order is not true',
		    		], 400);
		    	}

		    	if ($orderDetail->order->paid != null || $orderDetail->order->paid > 0) {
		    		return response()->json([
		    			'status_code' => 400,
		    			'message' => 'this order is not pending yet',
		    		], 400);
		    	}

		    	if ($orderDetail->order->approved == 1) {
		    		return response()->json([
		    			'status_code' => 400,
		    			'message' => 'this order is approved',
		    		], 400);
		    	}

		    	if ($orderDetail->order->expired != 0) {
		    		return response()->json([
		    			'status_code' => 400,
		    			'message' => 'this order is expired',
		    		], 400);
		    	}

		    	if ($orderDetail->order->status == 'cancelled') {
		    		return response()->json([
		    			'status_code' => 400,
		    			'message' => 'this order is cancelled',
		    		], 400);
		    	}

		    	 // quantity check 
		        if ($product->producttype_id == 1) {
		        	if ($product->quantity < $request->quantity) {
			            return response()->json([
			                'status_code' => 400,
			                'message' => 'sorry, quantity available right now is '. $product->quantity,
			            ], 400);
		            } 
		        }
		        else{
		        	if ($product->no_of_orders > $request->quantity) {
			            return response()->json([
			                'status_code' => 400,
			                'message' => 'sorry, min num of orders is '. $product->no_of_orders,
			            ], 400);
		            }
		        }
		        // quantity check 

		         if ($product->producttype_id == 1) {
		        	$dynamic_price = $product->PriceAfterDiscount();
		        }
		        else{
		        	$dynamic_price = $product->holesale_price;
		        }
		
		    		
		                //$orderDetail->update([
		                    $orderDetail->quantity = $request->quantity;
		                    $orderDetail->total    = $dynamic_price * $request->quantity;
		                    $orderDetail->save();
		               // ]);

		               $orderDetail->order->update(['order_total' => $orderDetail->order->sumTotal]);
		                 return response()->json([
		                    'status_code' => 200, 
		                    'message' => 'product quantity edited successfully',
		                   ], 200);    		
		    } // end if user
		      else{
		        return response()->json([
		                'status_code' => 401, 
		                'message'  => 'un authorized access page due to permissions',
		               ], 401);
		      }
    } // end function

    public function deletefromcart(DatabaseCartDeleteOrderApiRequest $request)
    {
    	$user = Auth::user();
    	$user_roles = $user->roles->pluck('title')->toArray();

    	$user_id    = $user->id;
    	$product_id = $request->product_id;
    	$order_id   = $request->order_id;

    	if (in_array('User', $user_roles) || in_array('Vendor', $user_roles)) {
		        $product = Product::findOrFail($request->product_id);
		        // check if quantity is enough or not
		   
		    	$orderDetail   = Orderdetail::whereHas('order', function($q) use ($user_id){
								       $q->where('user_id', $user_id);
								    })
		    	                      ->where('order_id', $order_id)
		    	                      ->where('product_id', $request->product_id)
		    	                      ->where('approved', 0)
		    	                      ->first();
		    	if (!$orderDetail) {
		    		return response()->json([
		    			'status_code' => 400,
		    			'message' => 'this order is not true',
		    		], 400);
		    	}

		    	if ($orderDetail->order->paid != null || $orderDetail->order->paid > 0) {
		    		return response()->json([
		    			'status_code' => 400,
		    			'message' => 'this order is not pending yet',
		    		], 400);
		    	}

		    	if ($orderDetail->order->approved == 1) {
		    		return response()->json([
		    			'status_code' => 400,
		    			'message' => 'this order is approved',
		    		], 400);
		    	}

		    	if ($orderDetail->order->expired != 0) {
		    		return response()->json([
		    			'status_code' => 400,
		    			'message' => 'this order is expired',
		    		], 400);
		    	}

		    	if ($orderDetail->order->status == 'cancelled') {
		    		return response()->json([
		    			'status_code' => 400,
		    			'message' => 'this order is cancelled',
		    		], 400);
		    	}		
		    		
		                    $decreased    = $product->PriceAfterDiscount() * $orderDetail->quantity;
		                    $orderID      = $orderDetail->order_id;
		                    $belong_order = Order::where('id', $orderID)->first();
		                    $orderDetail->save();
		                    $orderDetail->delete();
		                    $belong_order->update(['order_total' => $belong_order->sumTotal]);
		                  
		                 return response()->json([
		                    'status_code' => 200, 
		                  //  'message' => 'item removed from cart successfully',
		                    'message' => __('site_messages.Product_removed_from_cart_successfully'),

		                   ], 200);    		
		    } // end if user
		      else{
		        return response()->json([
		                'status_code' => 401, 
		                'message'  => 'un authorized access page due to permissions',
		               ], 401);
		      }
    } // end function

    public function clear_cart(Request $request)
    {
    	$user = Auth::user();
    	$user_id = Auth::user()->id;
    	$user_roles = $user->roles->pluck('title')->toArray();

    	if (in_array('User', $user_roles) || in_array('Vendor', $user_roles)) {
		        $order   = Order::where('user_id', $user_id)
    	              ->whereNull('paid')
    	              ->whereNull('approved')
                      ->where('status', '!=', 'cancelled')
                      ->where('status', '!=', 'in progress')
                      ->where('status', 'pending')
    	              ->where('expired', 0)->get();
		// not pending orders found
		    	if (count($order) <= 0) {
		    		return response()->json([
		                'status_code' => 400, 
		                'message'  => 'you have 0 items in your cart',
		                'data' =>  [],
		               ], 400);
		    	}else{
		    		
    	             $order_ids =  Order::where('user_id', $user_id)
    	              ->whereNull('paid')
    	              ->whereNull('approved')
                      ->where('status', '!=', 'cancelled')
    	              ->where('expired', 0)->pluck('id');
    	              Order::where('user_id', $user_id)
    	              ->whereNull('paid')
    	              ->whereNull('approved')
                      ->where('status', '!=', 'cancelled')
    	              ->where('expired', 0)->where('status', 'pending')->delete();
    	              Orderdetail::whereIn('order_id', $order_ids)->where('approved', 0)->delete();
    	              // return $order_ids;
    	              return response()->json([
		                'status_code' => 200, 
		                'message'  => 'you got you cart empty successfully',
		               ], 200);
		    	}
		} // end if user
		else{
		    return response()->json([
	            'status_code' => 401, 
	            'message'  => 'un authorized access page due to permissions',
	           ], 401);
		}
    }

    public function get_cart(Request $request)
    {
    	$user = Auth::user();
    	$user_id = Auth::user()->id;
    	$user_roles = $user->roles->pluck('title')->toArray();

    	//if (in_array('User', $user_roles)) 
    	// {
		        /*$order   = Order::where('user_id', $user_id)
    	              ->whereNull('paid')
    	              ->whereNull('approved')
                      ->where('status', '!=', 4)
    	              ->where('expired', 0)->get();*/
		// not pending orders found
    	$orders = Order::where('user_id', $user_id)
    	                        ->whereNull('paid')
    	                        ->where('status', '!=', 'cancelled')
    	                        ->where('status', 'pending')
    	                        ->where('expired', 0)
    	                        ->whereDoesntHave('orderDetails', function($q){
                                    $q->where('approved', 1);
                                  })->get();
		    	if (count($orders) <= 0) {
		    		return response()->json([
		                'status_code' => 200, 
		                'message'  => 'you have 0 items in your cart',
		                'data' =>  null,
		               ], 200);
		    	}else{
		    		$pending_orders = Order::where('user_id', $user_id)
    	                        ->whereNull('paid')
    	                        ->where('status', '!=', 'cancelled')
    	                        ->where('status', 'pending')
    	                        ->where('expired', 0)
    	                        ->whereDoesntHave('orderDetails', function($q){
                                    $q->where('approved', 1);
                                  })->get();

    	            $exist_order = $pending_orders->take(1)->first();
    	            $exist_order_details = $exist_order->orderDetails->pluck('producttype_id')->toArray();
    	            if (in_array(1, $exist_order_details) && in_array(2, $exist_order_details)) 
    	            {
    	            	$mixed = true;
    	            	$unique_type = 'both';
    	            }else
    	            {
    	            	$mixed = false;
    	            	$unique_type = 'both';
    	            }

    	            if (in_array(1, $exist_order_details) && !in_array(2, $exist_order_details)) 
    	            {
    	            	$unique_type = 'retail';
    	            }
    	            if (!in_array(1, $exist_order_details) && in_array(2, $exist_order_details)) 
    	            {
    	            	$unique_type = 'wholesale';
    	            }

    	        $data = CartContentsApiResource::collection($pending_orders);
    	        $total = count($pending_orders);
	    	        foreach ($data as $value) {
	    	        	return response()->json([
			                'status_code'    => 200, 
			                'data'           => $value,
			                'cart_total'     => $value->orderDetails->count(),
			                'mixed'          => $mixed,
			                'unique_type'    => $unique_type,
			                'message'        => 'success',
			               ], 200);
	    	        }
		    	}
		// } // end if user
		/*else{
		    return response()->json([
	            'status_code' => 401, 
	            'message'  => 'un authorized access page due to permissions',
	           ], 401);
		}*/
    }
}
