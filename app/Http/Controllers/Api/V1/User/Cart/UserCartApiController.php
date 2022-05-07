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

class UserCartApiController extends Controller
{
    public function addtocart(MakeOrderApiRequest $request)
    {
    	$prod_id  = $request->product_id;
        $quantity = $request->quantity;
    	
        if(Cookie::get('shopping_cart')){
        	//return 'get';
            $cookie_data = stripslashes(Cookie::get('shopping_cart'));
            $cart_data   = json_decode($cookie_data, true);
        }
        else{
        	//return 'not get cart';
            $cart_data = array();
        }
    		$item_id_list = array_column($cart_data, 'item_id');
            $prod_id_is_there = $prod_id;
            //return $item_id_list;

        if(in_array($prod_id_is_there, $item_id_list))
        {
        	// return 'exists';
            foreach($cart_data as $keys => $values)
            {
                if($cart_data[$keys]["item_id"] == $prod_id)
                {
                	$current_price = Product::find($prod_id);
                    $cart_data[$keys]["item_quantity"] = $quantity;
                    $cart_data[$keys]["item_total"]    = $current_price->PriceAfterDiscount() * $quantity; 

                    $item_data = json_encode($cart_data);
                    $minutes  = time() + 3600;
                    $response = new Custom(['status_code' => 200, 'message' => 'Already in your cart'], 200);
                    $response->withCookie('shopping_cart', $item_data, $minutes);
                    return $response; 
                }
            }
        }
        else
        {
        	// return 'lessa';
            $product      = Product::find($prod_id);
            $prod_name    = $product->name;
            $prod_image   = $product->photo->toArray()[0]['image'];
            $priceval     = $product->price;
            $item_total   = $product->PriceAfterDiscount() * $quantity;

            if($product){
                $item_array = array(
                    'item_id' => $prod_id,
                    'item_name' => $prod_name,
                    'item_quantity' => $quantity,
                    'item_price' => $priceval,
                    'item_image' => $prod_image,
                    'item_total' => $item_total,
                );

                $cart_data[] = $item_array;
                $item_data = json_encode($cart_data);
                $minutes  = time() + 3600;
                $response = new Custom(['status_code' => 200, 'message' => 'successfully add'], 200);
                $response->withCookie('shopping_cart', $item_data, $minutes);
                return $response; 
            }
        }
    }
     // add to cart 

    public function updatetocart(MakeOrderApiRequest $request)
    {
        $prod_id  = $request->product_id;
        $quantity = $request->quantity;

        if(Cookie::get('shopping_cart'))
        {
            $cookie_data = stripslashes(Cookie::get('shopping_cart'));
            $cart_data = json_decode($cookie_data, true);

            $item_id_list = array_column($cart_data, 'item_id');
            $prod_id_is_there = $prod_id;

            if(in_array($prod_id_is_there, $item_id_list))
            {
                foreach($cart_data as $keys => $values)
                {
                    if($cart_data[$keys]["item_id"] == $prod_id)
                    {
                    	$current_price = Product::find($prod_id);
                        $cart_data[$keys]["item_quantity"] =  $quantity;
                        $cart_data[$keys]["item_total"]    = $current_price->PriceAfterDiscount() * $quantity;

                        $item_data = json_encode($cart_data);
                        $minutes  = time() + 3600;
                        $response = new Custom(['status_code' => 200, 'message' => 'quantity successfully updated'], 200);
                        $response->withCookie('shopping_cart', $item_data, $minutes);
                        return $response; 
                    }
                }
            }
        }
    }

    public function deletefromcart(DeletefromcartApiRequest $request)
    {
    	//$current_price = Product::find(6);
    	//return $current_price->photo->toArray()[0]['image'];
        $prod_id     = $request->product_id;
        $cookie_data = stripslashes(Cookie::get('shopping_cart'));
        $cart_data   = json_decode($cookie_data, true);

        $item_id_list = array_column($cart_data, 'item_id');
        $prod_id_is_there = $prod_id;

        if(in_array($prod_id_is_there, $item_id_list))
        {
            foreach($cart_data as $keys => $values)
            {
                if($cart_data[$keys]["item_id"] == $prod_id)
                {
                    unset($cart_data[$keys]);
                    $item_data = json_encode($cart_data);
                    $minutes  = time() + 3600;
                    $response = new Custom(['status_code' => 200, 'message' => 'Item Removed from Cart'], 200);
                    $response->withCookie('shopping_cart', $item_data, $minutes);
                    return $response; 
                }
            }
        }
    }

    public function clear_cart(Request $request)
    {
    	$cookie = Cookie::forget('shopping_cart');
        return response()->json(['status'=>'Your Cart is Cleared'])->withCookie($cookie);
                        // ->withCookie(Cookie::forget('XSRF-TOKEN'));
    }

    public function get_cart(Request $request)
    {
    	// return $request->cookie('shopping_cart');
    	$cookie_data = stripslashes(Cookie::get('shopping_cart'));
	    $cart_data   = json_decode($cookie_data, true);
	    $sum_total   = 0;
	    if ($cart_data != null) {
	    	foreach ($cart_data as $cart_data_item) {
	    	$sum_total += $cart_data_item['item_total'];
	        }
	    }
	   
	    return response()->json(['data'=> $cart_data, 'sum_total' => $sum_total]);//->withCookie('shopping_cart');
    }
}
