<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\MakeOrderApiRequest;
use App\Models\Order;
use App\Models\Orderdetail;
use App\Models\Product;
use App\Models\User;
use Auth;
use App\Http\Requests\VendorApproveOrderApiRequest;
use App\Http\Requests\CancelOrderApiRequest;

class OrderApiController extends Controller
{
    public function make_order(MakeOrderApiRequest $request)
    {
    	$user_id = Auth::user()->id;
    	//return $user_id;
        $product = Product::findOrFail($request->product_id);

        // check if quantity is enough or not
        if ($product->quantity < $request->quantity) {
            return response()->json([
                'status_code' => 400,
                'message' => 'sorry, quantity available right now is '. $product->quantity,
            ], 207);
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
                      ->where('status', '!=', 4) // not cancelled
                      ->where('status', '!=', 2) // not in progress
    	              ->where('expired', 0)->first();
                      // return $order;
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
                'price'          => $product->price, 
                'discount'       => $product->discount == null ? 0 : $product->discount, 
                // total after applying discount
                'total'          => $product->PriceAfterDiscount() * $request->quantity, 
    		]);

            $new_order->update(['order_total' => $new_order->sumTotal]);

            return response()->json([
                'status_code' => 200, 
                // 'message'     => 'success',
                'message' => 'product added to cart successfully',
               ], 200);
    	} // end not pending orders found

    	else{
    		// check existence of target added product
    		$existing_cart_contents = $order->orderDetails->pluck('product_id')->toArray();
    		if (in_array($request->product_id, $existing_cart_contents)) {
                // if product already in cart change qty and price
    			//return 'yes';
                // return $order->orderTotal;
                $orderDetail = Orderdetail::where('order_id', $order->id)
                                        ->where('product_id', $request->product_id)->first();
                //$orderDetail->update([
                    $orderDetail->quantity = $request->quantity;
                    $orderDetail->total    = $product->PriceAfterDiscount() * $request->quantity;
                    $orderDetail->save();
               // ]);

                $order->update(['order_total' => $orderDetail->order->sumTotal]);
                 return response()->json([
                    'status_code' => 200, 
                    // 'message'     => 'success',
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
                'vendor_type'      => $product->vendor->type, 
                'quantity'       => $request->quantity, 
                'price'          => $product->price, 
                'discount'       => $product->discount == null ? 0 : $product->discount, 
                'total'          => $product->PriceAfterDiscount() * $request->quantity,
                ]);
                $order->update(['order_total' => $orderDetail->order->sumTotal]);

                return response()->json([
                    'status_code' => 200, 
                   // 'message'     => 'success',
                    'message' => 'product added to cart successfully',
                   ], 200);
    		} // end else
    	} // end first else
    } // end function

   /* public function cancel_order(CancelOrderApiRequest $request , Order $order)
    {
        // make integers 1 pending 2 inprogress 3 delivered 4 cancelled
        if ($order->paid == 0 && $order->approved == 0 && $order->expired == 0) {
            // case cancelled
            if ($order->status == 4) {
               return response()->json([
                'message' => 'this order has already been cancelled',
               ], 400);
            }
            // case delivered
            elseif ($order->status == 3) {
                return response()->json([
                'message' => 'this order can not be cancelled',
               ], 400);
            }
            // case inprogress
            elseif ($order->status == 2) {
                return response()->json([
                'message' => 'this order can not be cancelled',
               ], 400);
            }
            // case pending
            else{
                $order->update(['status' => 4]);
                return response()->json([
                'message' => 'your order has benn cancelled successfully',
               ], 200);
            }
        } // end if
        else{
            return response()->json([
                'message' => 'this order is not pending to be cancelled',
               ], 400);
        } // end else 
    }*/

// user show cart contents
    /*public function show_orders(User $user)
    {
        $cart_orders = $user->orders->load('orderDetails');
        //->where('status', '!=', 3)->where('status', '!=', 4)->get();
        $cart_orders = $cart_orders->where('status', '!=', 3)->where('status', '!=', 4);//->get();

        return response()->json([
            'status_code' => 200, 
             'message'     => 'success',
                'data' => $cart_orders,
               ], 200);
    }*/

// user show his history
  /*  public function show_history(User $user)
    {
        $user_history = $user->orders;
        // only delivered orders (except inprogess && pending && cancelled)
        $user_history = $user_history->where('status', 3);
        return response()->json([
            'status_code' => 200, 
           'message'     => 'success',
                'data' => $user_history,
               ], 200);
    }*/

// admin show cancelled orders
    public function show_cancelled_orders()
    {
        // add permission right here
        $cancelled_orders = Order::where('status', 4)->get();
        return response()->json([
                'status_code' => 200, 
                'message'     => 'success',
                'data' => $cancelled_orders,
               ], 200);
    }

    // admin show all orders with their statuses
    public function show_all_orders()
    {
        // add permission right here
        $show_all_orders = Order::with('orderDetails')->get();
        return response()->json([
                'status_code' => 200, 
                'message'     => 'success',
                'data' => $show_all_orders,
               ], 200);
    }
}
