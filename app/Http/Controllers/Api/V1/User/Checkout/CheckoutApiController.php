<?php

namespace App\Http\Controllers\Api\V1\User\Checkout;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\Orderdetail;
use App\Models\Order;
use App\Http\Resources\User\Cart\CartContentsApiResource;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\User\Cart\CheckoutContentsApiResource;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendCheckoutMail;
use App\Mail\SendVendorCheckoutMail;
use App\Jobs\SendCheckoutEmailJob;
use App\Models\AddVendor;
use App\Models\Product;
use App\Mail\SendCheckoutVendorMail;
use App\Models\User;
use App\Models\Shipping;
use App\Models\Userpaymentway;
use App\Http\Resources\User\Cart\CheckoutContentsToAdminApiResource;
use App\Http\Resources\User\Cart\CartOrderDetailsToAdminApiResource;
use App\Mail\SendCheckoutVendorToAdminMail;
use App\Models\Fixedshipping;
use Carbon\Carbon;

class CheckoutApiController extends Controller
{
    // user checkout
    
    public function user_checkout(Request $request)
    {
    	$user = Auth::user();
    	$user_id = Auth::user()->id;
    	$user_roles = $user->roles->pluck('title')->toArray();

        // case user 

        if (in_array('User', $user_roles)) 
        {
            $orders = Order::where('user_id', $user_id)
                                ->whereNull('paid')
                                ->where('status', '!=', 'cancelled')
                                ->where('expired', '!=', 1)
                                ->whereDoesntHave('orderDetails', function($q){
                                    $q->where('approved', 1);
                                  })->get();
        $exist_id = $orders->pluck('id');
        if (count($exist_id) <= 0) {
            return response()->json([
                    'status_code' => 400,
                    'message'  => 'you have 0 items in your cart',
                   ], 400);
        }

        $check_shipping = Order::whereIn('id', $exist_id)->take(1)->first();

        if (count($check_shipping->orderDetails) <= 0) {
            return response()->json([
                    'status_code' => 400,
                    'message'  => 'you have 0 items in your cart',
                   ], 400);
        }

        if(!$check_shipping->shipping_address_id)
        {
            $exist_default  = Shipping::where('user_id', $user_id)
                                ->where('default', 1)
                                ->first();
            if (!$exist_default) {
                
                return response()->json([
                    'status_code' => 400,
                    'message'  => 'select your default shipping first',
                   ], 400);
            }
           $check_shipping->update(['shipping_address_id' => $exist_default->id]);
        }

      Fixedshipping::create([
            'user_id'             => $check_shipping->shipping->user_id,
            'order_id'            => $check_shipping->id,
          //  'lang'                => 'ar',
            'recipient_name'      => $check_shipping->shipping->recipient_name,
            'recipient_phone'     => $check_shipping->shipping->recipient_phone,
            'last_name'           => $check_shipping->shipping->last_name,
            'district'            => $check_shipping->shipping->district, 
            'home_no'             => $check_shipping->shipping->home_no,
            'floor_no'            => $check_shipping->shipping->floor_no, 
            'apartment_no'        => $check_shipping->shipping->apartment_no, 
            'telephone_no'        => $check_shipping->shipping->telephone_no,
            'street'              => $check_shipping->shipping->street,
            'nearest_milestone'   => $check_shipping->shipping->nearest_milestone, 
            'notices'             => $check_shipping->shipping->notices,
            'city_id'             => $check_shipping->shipping->city_id,
            'area_id'             => $check_shipping->shipping->area_id,
            'country_id'          => $check_shipping->shipping->country_id,
        ]);

        $exist_default  = Shipping::where('user_id', $user_id)
                                ->where('default', 1)
                                ->first();

        if(!$check_shipping->payment_id)
        {
            $userpayment_way = Userpaymentway::where('user_id', $user_id)->first();

            if (!$userpayment_way) {
                // $shipping->update(['default' => 1]);
                return response()->json([
                    'status_code' => 400,
                    'message'  => 'select payment way first',
                   ], 400);
            }
           $check_shipping->update(['payment_id' => $userpayment_way->paymentway_id]);
        }
        
                if (count($orders) <= 0) {
                    return response()->json([
                        'status_code' => 400, 
                        'message'  => 'you have 0 items in your cart',
                       ], 400);
                }
                else
                {
                    // check for quantity before checkout 
                    $found_prods = $orders[0]->orderDetails;
                    foreach ($found_prods as $found_prod) {
                        $exist_prod = Product::where('id', $found_prod->product_id)->first();
                        if ($exist_prod->quantity < $found_prod->quantity) 
                        {
                            return response()->json([
                                'status_code' => 400,
                                // 'message' => 'sorry, quantity available right now is '. $exist_prod->quantity. ' '. $exist_prod->name;
                                'message' => __('site_messages.quantity_available'). ' ( '. $exist_prod->name. ' ) (' . $exist_prod->quantity. ' )',
                            ], 400);
                        } // end check if quantity is enough or not
                    } // end foreach

                    // check for price update 
                    foreach ($found_prods as $found_prod) {
                        $exist_prod = Product::where('id', $found_prod->product_id)->first();
                        $prod_type = $found_prod->producttype_id;
                        if ($prod_type == 1) {
                            $normal_price = $exist_prod->PriceAfterDiscount();
                            $found_prod->update([
                                'price'    => $exist_prod->price,
                                'discount' => $exist_prod->discount == null ? 0 : $exist_prod->discount, 
                                'total'    => $normal_price * $found_prod->quantity, 
                            ]);
                        }
                        if ($prod_type == 2) {
                            $wholesale_price = $exist_prod->holesale_price;
                            $found_prod->update([
                                'price'    => $wholesale_price,
                                'discount' => $exist_prod->discount == null ? 0 : $exist_prod->discount, 
                                'total'    => $wholesale_price * $found_prod->quantity, 
                            ]);
                        }
                    } // end foreach
                    // check for price update 
                    $pending_orders = Order::where('user_id', $user_id)
                                ->whereNull('paid')
                                ->where('status', '!=', 'cancelled')
                                ->where('expired', 0)
                                ->whereDoesntHave('orderDetails', function($q){
                                    $q->where('approved', 1);
                                  })->get();


                $data = CheckoutContentsApiResource::collection($pending_orders);
                $total = count($pending_orders);

                // send mail to user 
                //Mail::to($user->email)->send(new SendCheckoutMail($data));
                $details = $check_shipping->orderDetails;
                Mail::to($user->email)->send(new SendCheckoutMail($data, $check_shipping, $details, $exist_default));

                $vendors = $orders[0]->orderDetails->unique('vendor_id')->pluck('vendor_id');
                // Mail::to($user->email)->send(new SendVendorCheckoutMail($data));
                
                // send queue mail to vendors
                $vendor_emails = AddVendor::whereIn('id', $vendors)->get();
                foreach ($vendor_emails as $vendor_email) {
                    $send_mail = $vendor_email->email;
                    $vendor_details = $check_shipping->orderDetails->where('vendor_id', $vendor_email->id);
                    $vendor_total = $check_shipping->orderDetails->where('vendor_id', $vendor_email->id)->sum('total');
                    
                    dispatch(new SendCheckoutEmailJob($send_mail, $data, $check_shipping, $vendor_details, $exist_default, $vendor_total));
                }
                
                Order::whereIn('id', $exist_id)->update([
                    'paid'          => 1,
                    'checkout_time' => Carbon::now(),
                ]);

                    foreach ($data as $value) {
                        return response()->json([
                            'status_code'    => 200, 
                            'data'           => $value,
                            'cart_total'     => $value->orderDetails->count(),
                           // 'message'        => 'success checkout',
                            'message' => __('site_messages.checkout_success'),
                           ], 200);
                    }
                }
        } // end case user

        if (in_array('Vendor', $user_roles)) // case vendor
        {   
            $orders = Order::where('user_id', $user_id)
                                ->whereNull('paid')
                                ->where('status', '!=', 'cancelled')
                                ->where('expired', '!=', 1)
                                ->whereDoesntHave('orderDetails', function($q){
                                    $q->where('approved', 1);
                                  })->get();
        $exist_id = $orders->pluck('id');
        // return $exist_id;
        if (count($exist_id) <= 0) {
            return response()->json([
                    'status_code' => 400,
                    'message'  => 'you have 0 items in your cart',
                   ], 400);
        }

        $check_shipping = Order::whereIn('id', $exist_id)->take(1)->first();

        if (count($check_shipping->orderDetails) <= 0) {
            return response()->json([
                    'status_code' => 400,
                    'message'  => 'you have 0 items in your cart',
                   ], 400);
        }
        
        if(!$check_shipping->shipping_address_id)
        {
            $exist_default  = Shipping::where('user_id', $user_id)
                                ->where('default', 1)
                                ->first();
            if (!$exist_default) {
                return response()->json([
                    'status_code' => 400,
                    'message'  => 'select your default shipping first',
                   ], 400);
            }
           $check_shipping->update(['shipping_address_id' => $exist_default->id]);
        }

           Fixedshipping::create([
            'user_id'             => $check_shipping->shipping->user_id,
            'order_id'            => $check_shipping->id,
          //  'lang'                => 'ar',
            'recipient_name'      => $check_shipping->shipping->recipient_name,
            'recipient_phone'     => $check_shipping->shipping->recipient_phone,
            'last_name'           => $check_shipping->shipping->last_name,
            'district'            => $check_shipping->shipping->district, 
            'home_no'             => $check_shipping->shipping->home_no,
            'floor_no'            => $check_shipping->shipping->floor_no, 
            'apartment_no'        => $check_shipping->shipping->apartment_no, 
            'telephone_no'        => $check_shipping->shipping->telephone_no,
            'street'              => $check_shipping->shipping->street,
            'nearest_milestone'   => $check_shipping->shipping->nearest_milestone, 
            'notices'             => $check_shipping->shipping->notices,
            'city_id'             => $check_shipping->shipping->city_id,
            'area_id'             => $check_shipping->shipping->area_id,
            'country_id'          => $check_shipping->shipping->country_id,
        ]);

        $exist_default  = Shipping::where('user_id', $user_id)
                                ->where('default', 1)
                                ->first();
        if(!$check_shipping->payment_id)
        {
            $userpayment_way = Userpaymentway::where('user_id', $user_id)->first();
            if (!$userpayment_way) {
                return response()->json([
                    'status_code' => 400,
                    'message'  => 'select payment way first',
                   ], 400);
            }
           $check_shipping->update(['payment_id' => $userpayment_way->paymentway_id]);
        }
                if (count($orders) <= 0) {
                    return response()->json([
                        'status_code' => 400, 
                        'message'  => 'you have 0 items in your cart',
                       ], 400);
                }
                else
                {
                    // check for quantity before checkout 
                    $found_prods = $orders[0]->orderDetails;
                    foreach ($found_prods as $found_prod) {
                        $exist_prod = Product::where('id', $found_prod->product_id)->first();
                        if ($exist_prod->producttype_id == 1) {
                            if ($exist_prod->quantity < $found_prod->quantity) 
                            {
                                return response()->json([
                                    'status_code' => 400,
                                  //  'message' => 'sorry, quantity available right now is '. $exist_prod->quantity,
                                    'message' => __('site_messages.quantity_available'). ' ( '. $exist_prod->name. ' ) (' . $exist_prod->quantity. ' )',
                                ], 400);
                            } // end check if quantity is enough or not
                        }
                    } // end foreach
                    
                    // check for quantity before checkout 
                    
                    // check for price update 
                    foreach ($found_prods as $found_prod) {
                        $exist_prod = Product::where('id', $found_prod->product_id)->first();
                        $prod_type = $found_prod->producttype_id;
                        if ($prod_type == 1) {
                            $normal_price = $exist_prod->PriceAfterDiscount();
                            $found_prod->update([
                                'price'    => $exist_prod->price,
                                'discount' => $exist_prod->discount == null ? 0 : $exist_prod->discount, 
                                'total'    => $normal_price * $found_prod->quantity, 
                            ]);
                        }
                        if ($prod_type == 2) {
                            $wholesale_price = $exist_prod->holesale_price;
                            $found_prod->update([
                                'price'    => $wholesale_price,
                                'discount' => $exist_prod->discount == null ? 0 : $exist_prod->discount, 
                                'total'    => $wholesale_price * $found_prod->quantity, 
                            ]);
                        }
                    } // end foreach
                    // check for price update 
                $pending_orders = Order::where('user_id', $user_id)
                                ->whereNull('paid')
                                ->where('status', '!=', 'cancelled')
                                ->where('expired', 0)
                                ->whereDoesntHave('orderDetails', function($q){
                                    $q->where('approved', 1);
                                  })->get();

                $vendors = $orders[0]->orderDetails->unique('vendor_id')->pluck('vendor_id');

                $data  = CheckoutContentsApiResource::collection($pending_orders);
                //return $data;
                $total = count($pending_orders);

                 // send wholesale to admin
                if (in_array(2, $found_prods->unique('product_id')->pluck('producttype_id')->toArray())) 
                {
                    foreach ($pending_orders as $value) {
                        $value['order_wholesale_total'] = $value->orderDetails->where('producttype_id', 2)->sum('total');
                    }
                    $admin_data  = CheckoutContentsToAdminApiResource::collection($pending_orders);
                    //return $admin_data[0]->order_wholesale_total;
                    $admin       = User::findOrFail(1);
                    Mail::to($admin->email)->send(new SendCheckoutVendorToAdminMail($admin_data));
                }
                
                // send normal to admin
                if (in_array(1, $found_prods->unique('product_id')->pluck('producttype_id')->toArray())) 
                {
                    /**/
                    $vendor_emails = AddVendor::whereIn('id', $vendors)->get();
                        foreach ($vendor_emails as $vendor_email) {
                            $send_mail = $vendor_email->email;
                            $vendor_details = $check_shipping->orderDetails->where('vendor_id', $vendor_email->id)->where('producttype_id', 1);
                            $vendor_total = $check_shipping->orderDetails->where('producttype_id', 1)->where('vendor_id', $vendor_email->id)->sum('total');
                          //  return 'ahmed';
                            dispatch(new SendCheckoutEmailJob($send_mail, $data, $check_shipping, $vendor_details, $exist_default, $vendor_total));
                        }
                    /**/
                }

                // send mail to admin offline order 

                $details = $check_shipping->orderDetails;
           
             Mail::to($user->email)->send(new SendCheckoutMail($data, $check_shipping, $details, $exist_default));

        // Mail::to($user->email)->send(new SendVendorCheckoutMail($data, $check_shipping, $details, $exist_default));
                
                // send queue mail to vendors
               /* $vendor_emails = AddVendor::whereIn('id', $vendors)->get();
                foreach ($vendor_emails as $vendor_email) {
                    $send_mail = $vendor_email->email;
                    dispatch(new SendCheckoutEmailJob($send_mail, $data));
                }*/
                
               // Order::whereIn('id', $exist_id)->update(['paid' => 1]);
                Order::whereIn('id', $exist_id)->update([
                    'paid'          => 1,
                    'checkout_time' => Carbon::now(),
                ]);

                    foreach ($data as $value) {
                        return response()->json([
                            'status_code'    => 200, 
                            'data'           => $value,
                            'cart_total'     => $value->orderDetails->count(),
                           // 'message'      => 'success order done',
                            'message' => __('site_messages.checkout_success'),
                           ], 200);
                    }
                }
        } // case vendor
    }
}
