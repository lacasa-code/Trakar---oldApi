<?php

namespace App\Http\Controllers\Api\V1\User\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\Website\Products\FrontProductsApiResource;
use App\Http\Resources\Website\Products\SpecificFrontProductsApiResource;
use Gate;
use Auth;
use DB;
use App\Models\Product;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\Website\User\CheckboxFilter\FetchCheckboxFilterApiRequest;
use App\Models\ProductCategory;
use App\Models\PartCategory;
use App\Models\AddVendor;

class FetchMultipleCheckboxSearchApiController extends Controller
{
    public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }

    public function fetch_multiple_checkbox(FetchCheckboxFilterApiRequest $request)
    {
        $lang = $this->getLang();
    	$part_categories = $request->categories;
    	$manufacturers   = $request->manufacturers;
    	$origins         = $request->origins;
        $start_price     =  $request->start_price;
        $end_price       =  $request->end_price;

    	$default_count = \Config::get('constants.pagination.items_per_page');
        $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
        
        $request->page == '' ? $page = 1 : $page = $request->page;
        $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
        $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
        $request->column_name == '' ? $column_name = '' : $column_name = $request->column_name;
        if ($ordered_by == 'price') {
        $ordered_by = 'actual_price';
      }

        // return $column_name;
      if ($ordered_by != '') {
        if (!Schema::hasColumn('products', $ordered_by)) {
          return response()->json(['message'  =>'order column not found',], 400);
        }
        if ($ordered_by == 'tags' || $ordered_by == 'categories') {
          $ordered_by = 'id';
        }
      }

    	// case 1
        if ($part_categories == '' && $manufacturers == '' && $origins == '' && $start_price == '') {
          return response()->json(['errors'  =>'select at least one item',], 400);
        }

        // case 1
        if ($part_categories != '' && $manufacturers == '' && $origins == '' && $start_price == '') {
        	$part_categories = json_decode($request->part_categories);
	    	// $manufacturers   = json_decode($request->manufacturers);
	    	// $origins         = json_decode($request->origins);
          return $this->part_categories_search($part_categories, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 2
        if ($part_categories == '' && $manufacturers != '' && $origins == '' && $start_price == '') {
        	// $part_categories = json_decode($request->part_categories);
	    	$manufacturers   = json_decode($request->manufacturers);
	    	// $origins         = json_decode($request->origins);
          return $this->manufacturers_search($manufacturers, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 3
        if ($part_categories == '' && $manufacturers == '' && $origins != '' && $start_price == '') {
        	// $part_categories = json_decode($request->part_categories);
	    	// $manufacturers   = json_decode($request->manufacturers);
	    	$origins         = json_decode($request->origins);
          return $this->origins_search($origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT);
        }

        // case 4
        if ($part_categories == '' && $manufacturers == '' && $origins == '' && $start_price != '') {
        	// $part_categories = json_decode($request->part_categories);
	    	// $manufacturers   = json_decode($request->manufacturers);
	    	$origins         = json_decode($request->origins);
          return $this->price_search($start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 5
        if ($part_categories != '' && $manufacturers != '' && $origins == '' && $start_price == '') {
        	$part_categories = json_decode($request->part_categories);
	    	$manufacturers   = json_decode($request->manufacturers);
	    	//$origins         = json_decode($request->origins);
          return $this->part_categories_manufacturers_search($part_categories, $manufacturers, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 6
        if ($part_categories != '' && $manufacturers == '' && $origins != '' && $start_price == '') {
        	$part_categories = json_decode($request->part_categories);
	    	//$manufacturers   = json_decode($request->manufacturers);
	    	$origins         = json_decode($request->origins);
          return $this->part_categories_origins_search($part_categories, $origins, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 7
        if ($part_categories != '' && $manufacturers == '' && $origins == '' && $start_price != '') {
        	$part_categories = json_decode($request->part_categories);
	    	//$manufacturers   = json_decode($request->manufacturers);
	    	$origins         = json_decode($request->origins);
          return $this->part_categories_price_search($part_categories, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 8
        if ($part_categories != '' && $manufacturers != '' && $origins != '' && $start_price == '') {
        	$part_categories = json_decode($request->part_categories);
	    	$manufacturers   = json_decode($request->manufacturers);
	    	$origins         = json_decode($request->origins);
          return $this->part_manufacturers_origins_search($part_categories, $manufacturers, $origins, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 9
        if ($part_categories != '' && $manufacturers != '' && $origins == '' && $start_price != '') {
        	$part_categories = json_decode($request->part_categories);
	    	$manufacturers   = json_decode($request->manufacturers);
	    	$origins         = json_decode($request->origins);
          return $this->part_manufacturers_price_search($part_categories, $manufacturers, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 10
        if ($part_categories != '' && $manufacturers == '' && $origins != '' && $start_price != '') {
        	$part_categories = json_decode($request->part_categories);
	    	$manufacturers   = json_decode($request->manufacturers);
	    	$origins         = json_decode($request->origins);
          return $this->part_origins_price_search($part_categories, $origins, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 11
        if ($part_categories != '' && $manufacturers != '' && $origins != '' && $start_price != '') {
        	$part_categories = json_decode($request->part_categories);
	    	$manufacturers   = json_decode($request->manufacturers);
	    	$origins         = json_decode($request->origins);
          return $this->four_hand_search($part_categories, $manufacturers, $origins, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 12
        if ($part_categories == '' && $manufacturers != '' && $origins != '' && $start_price == '') {
        	//$part_categories = json_decode($request->part_categories);
	    	$manufacturers   = json_decode($request->manufacturers);
	    	$origins         = json_decode($request->origins);
          return $this->manufacturers_origins_search($manufacturers, $origins, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 13
        if ($part_categories == '' && $manufacturers != '' && $origins == '' && $start_price != '') {
            //$part_categories = json_decode($request->part_categories);
            $manufacturers   = json_decode($request->manufacturers);
            $origins         = json_decode($request->origins);
          return $this->manufacturers_price_search($manufacturers, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 14
        if ($part_categories == '' && $manufacturers != '' && $origins != '' && $start_price != '') {
            //$part_categories = json_decode($request->part_categories);
            $manufacturers   = json_decode($request->manufacturers);
            $origins         = json_decode($request->origins);
          return $this->manufacturers_origins_price_search($manufacturers, $origins, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }


        // case 15
        if ($part_categories == '' && $manufacturers == '' && $origins != '' && $start_price != '') {
            //$part_categories = json_decode($request->part_categories);
            // $manufacturers   = json_decode($request->manufacturers);
            $origins         = json_decode($request->origins);
          return $this->origins_price_search($origins, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }
    }

    public function part_categories_search($part_categories, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
    {
        $lang = $this->getLang();
    	//return $part_categories;
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if (in_array('User', $user_roles)) {
                $new_products = Product::where('lang', $lang)->where('approved', 1)
                                       ->where('producttype_id', 1)
                                       ->whereIn('category_id', $part_categories)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                        ->whereIn('category_id', $part_categories)->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
          }  // end user casec

          // case logged in user role is User 
         if (in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ){
                $new_products = Product::where('lang', $lang)->where('approved', 1)
                                       ->where('producttype_id', 1)
                                       ->whereIn('category_id', $part_categories)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                        ->whereIn('category_id', $part_categories)->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
                $new_products = Product::where('lang', $lang)->where('approved', 1)->whereIn('producttype_id', [1, 2, 3])
                                ->whereIn('category_id', $part_categories)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $total = Product::where('lang', $lang)->where('approved', 1)->whereIn('producttype_id', [1, 2, 3])
                        ->whereIn('category_id', $part_categories)->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
            $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                   ->whereIn('category_id', $part_categories)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                               ->whereIn('category_id', $part_categories)->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }

        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);          
        } // end guest case
    }

    public function part_manufacturers_origins_search($part_categories, $manufacturers, $origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
    {
        $lang = $this->getLang();
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if (in_array('User', $user_roles)) {
                $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->whereIn('prodcountry_id', $origins)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }

        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
          }  // end user case

          // case logged in user role is User 
         if (in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ){
                $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->whereIn('prodcountry_id', $origins)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }

        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
              $new_products = Product::where('lang', $lang)->where('approved', 1)->whereIn('producttype_id', [1, 2, 3])
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->whereIn('prodcountry_id', $origins)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = Product::where('lang', $lang)->where('approved', 1)->whereIn('producttype_id', [1, 2, 3])
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
              $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->whereIn('prodcountry_id', $origins)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }

        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);         
        } // end guest case
    }

    public function part_manufacturers_price_search($part_categories, $manufacturers, $start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
    {
        $lang = $this->getLang();
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if (in_array('User', $user_roles)) {
              $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                        ->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
          }  // end user case

           // case logged in user role is User 
          if (in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ){
              $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                        ->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $new_products = Product::where('lang', $lang)->where('approved', 1)->whereIn('producttype_id', [1, 2, 3])
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = Product::where('lang', $lang)->where('approved', 1)->whereIn('producttype_id', [1, 2, 3])
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                        ->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
             $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                        ->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }

        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);        
        } // end guest case
    }

    public function origins_price_search($origins, $start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
    {
        $lang = $this->getLang();
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if (in_array('User', $user_roles)) {
               $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                        ->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
          }  // end user case

           // case logged in user role is User 
          if (in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ){
               $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                        ->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
             $new_products = Product::where('lang', $lang)->where('approved', 1)->whereIn('producttype_id', [1, 2, 3])
                                        ->whereIn('prodcountry_id', $origins)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = Product::where('lang', $lang)->where('approved', 1)->whereIn('producttype_id', [1, 2, 3])
                                        ->whereIn('prodcountry_id', $origins)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                        ->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
             $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                        ->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }

        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);         
        } // end guest case
    }

    public function manufacturers_origins_price_search($manufacturers, $origins, $start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
    {
        $lang = $this->getLang();
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if (in_array('User', $user_roles)) {
               $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                        ->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
          }  // end user case

          // case logged in user role is User 
         if (in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ){
               $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                        ->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
             $new_products = Product::where('lang', $lang)->where('approved', 1)->whereIn('producttype_id', [1, 2, 3])
                                        ->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = Product::where('lang', $lang)->where('approved', 1)->whereIn('producttype_id', [1, 2, 3])
                                        ->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                        ->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
             $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                        ->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
        
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);         
        } // end guest case
    }

    public function four_hand_search($part_categories, $manufacturers, $origins, $start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
    {
        $lang = $this->getLang();
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if (in_array('User', $user_roles)) {
                $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                        ->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200); 
          }  // end user case

          // case logged in user role is User 
          if (in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ){
                $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                        ->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200); 
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
               $new_products = Product::where('lang', $lang)->where('approved', 1)->whereIn('producttype_id', [1, 2, 3])
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = Product::where('lang', $lang)->where('approved', 1)->whereIn('producttype_id', [1, 2, 3])
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                        ->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
               $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                        ->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }

        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);         
        } // end guest case
    }

    public function part_origins_price_search($part_categories, $origins, $start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
    {
        $lang = $this->getLang();
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if (in_array('User', $user_roles)) {
               $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                        ->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
          }  // end user case

          // case logged in user role is User 
          if (in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ){
               $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                        ->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
             $new_products = Product::where('lang', $lang)->where('approved', 1)->whereIn('producttype_id', [1, 2, 3])
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = Product::where('lang', $lang)->where('approved', 1)->whereIn('producttype_id', [1, 2, 3])
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                        ->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
             $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                        ->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }

        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);         
        } // end guest case
    }

public function price_search($start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
    {
        //return $part_categories;
        $lang = $this->getLang();
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if (in_array('User', $user_roles)) {
               $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                ->where('price', '>=', $start_price)
                                ->where('price', '<=', $end_price)
                                        //->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                        ->where('price', '>=', $start_price)
                        ->where('price', '<=', $end_price)->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
          }  // end user case

          // case logged in user role is User 
          if (in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ){
               $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                ->where('price', '>=', $start_price)
                                ->where('price', '<=', $end_price)
                                        //->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                        ->where('price', '>=', $start_price)
                        ->where('price', '<=', $end_price)->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
             $new_products = Product::where('lang', $lang)->where('approved', 1)->whereIn('producttype_id', [1, 2, 3])
                             ->where('price', '>=', $start_price)
                                ->where('price', '<=', $end_price)
                                        //->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $total = Product::where('lang', $lang)->where('approved', 1)->whereIn('producttype_id', [1, 2, 3])
                        ->where('price', '>=', $start_price)
                        ->where('price', '<=', $end_price)->count();
        
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }

        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
            $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                ->where('price', '>=', $start_price)
                                ->where('price', '<=', $end_price)
                                        //->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                        ->where('price', '>=', $start_price)
                        ->where('price', '<=', $end_price)->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }

        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);          
        } // end guest case
    }

    public function part_categories_price_search($part_categories, $start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
    {
        $lang = $this->getLang();
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if (in_array('User', $user_roles)) {
              $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                ->where('price', '>=', $start_price)
                                ->where('price', '<=', $end_price)
                                ->whereIn('category_id', $part_categories)
                                        //->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                        ->where('price', '>=', $start_price)
                        ->where('price', '<=', $end_price)
                        ->whereIn('category_id', $part_categories)->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200); 
          }  // end user case

          // case logged in user role is User 
          if (in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ){
              $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                ->where('price', '>=', $start_price)
                                ->where('price', '<=', $end_price)
                                ->whereIn('category_id', $part_categories)
                                        //->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                        ->where('price', '>=', $start_price)
                        ->where('price', '<=', $end_price)
                        ->whereIn('category_id', $part_categories)->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200); 
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
             $new_products = Product::where('lang', $lang)->where('approved', 1)->whereIn('producttype_id', [1, 2, 3])
                                ->where('price', '>=', $start_price)
                                ->where('price', '<=', $end_price)
                                ->whereIn('category_id', $part_categories)
                                        //->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $total = Product::where('lang', $lang)->where('approved', 1)->whereIn('producttype_id', [1, 2, 3])
                           ->where('price', '>=', $start_price)
                        ->where('price', '<=', $end_price)
                        ->whereIn('category_id', $part_categories)->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
             $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                ->where('price', '>=', $start_price)
                                ->where('price', '<=', $end_price)
                                ->whereIn('category_id', $part_categories)
                                        //->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                        ->where('price', '>=', $start_price)
                        ->where('price', '<=', $end_price)
                        ->whereIn('category_id', $part_categories)->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }

        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);         
        } // end guest case
    }

    public function manufacturers_price_search($manufacturers, $start_price, $end_price, $page, $ordered_by , $sort_type, $PAGINATION_COUNT)
    {
        $lang = $this->getLang();
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if (in_array('User', $user_roles)) {
                $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                ->where('price', '>=', $start_price)
                                ->where('price', '<=', $end_price)
                                ->whereIn('manufacturer_id', $manufacturers)
                                        //->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                        ->where('price', '>=', $start_price)
                        ->where('price', '<=', $end_price)
                        ->whereIn('manufacturer_id', $manufacturers)->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
          }  // end user case

          // case logged in user role is User 
          if (in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ){
                $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                ->where('price', '>=', $start_price)
                                ->where('price', '<=', $end_price)
                                ->whereIn('manufacturer_id', $manufacturers)
                                        //->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                        ->where('price', '>=', $start_price)
                        ->where('price', '<=', $end_price)
                        ->whereIn('manufacturer_id', $manufacturers)->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
              $new_products = Product::where('lang', $lang)->where('approved', 1)->whereIn('producttype_id', [1, 2, 3])
                                ->where('price', '>=', $start_price)
                                ->where('price', '<=', $end_price)
                                ->whereIn('manufacturer_id', $manufacturers)
                                        //->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $total = Product::where('lang', $lang)->where('approved', 1)->whereIn('producttype_id', [1, 2, 3])
                        ->where('price', '>=', $start_price)
                        ->where('price', '<=', $end_price)
                        ->whereIn('manufacturer_id', $manufacturers)->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
              $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                               ->where('price', '>=', $start_price)
                                ->where('price', '<=', $end_price)
                                ->whereIn('manufacturer_id', $manufacturers)
                                        //->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                        ->where('price', '>=', $start_price)
                        ->where('price', '<=', $end_price)
                        ->whereIn('manufacturer_id', $manufacturers)->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
        
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);         
        } // end guest case
    }

    public function manufacturers_search($manufacturers, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
    {
        $lang = $this->getLang();
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if (in_array('User', $user_roles)) {
              $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                       ->whereIn('manufacturer_id', $manufacturers)->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
          }  // end user case

          // case logged in user role is User 
          if (in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ){
              $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                       ->whereIn('manufacturer_id', $manufacturers)->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $new_products = Product::where('lang', $lang)->where('approved', 1)->whereIn('producttype_id', [1, 2, 3])
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = Product::where('lang', $lang)->where('approved', 1)->whereIn('producttype_id', [1, 2, 3])
                                    ->whereIn('manufacturer_id', $manufacturers)->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
            $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                        ->whereIn('manufacturer_id', $manufacturers)->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }

        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);         
        } // end guest case
    }

    public function origins_search($origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
    {
        $lang = $this->getLang();
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if (in_array('User', $user_roles)) {
              $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('prodcountry_id', $origins)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                          ->whereIn('prodcountry_id', $origins)->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
          }  // end user case

          // case logged in user role is User 
          if (in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ){
              $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('prodcountry_id', $origins)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                          ->whereIn('prodcountry_id', $origins)->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $new_products = Product::where('lang', $lang)->where('approved', 1)->whereIn('producttype_id', [1, 2, 3])
                                          ->whereIn('prodcountry_id', $origins)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = Product::where('lang', $lang)->where('approved', 1)->whereIn('producttype_id', [1, 2, 3])
                                      ->whereIn('prodcountry_id', $origins)->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
            $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('prodcountry_id', $origins)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                        ->whereIn('prodcountry_id', $origins)->count();
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }

        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);         
        } // end guest case
    }

    public function part_categories_manufacturers_search($part_categories, $manufacturers, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
    {
        $lang = $this->getLang();
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if (in_array('User', $user_roles)) {
             $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
            $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                            ->whereIn('category_id', $part_categories)
                                            ->whereIn('manufacturer_id', $manufacturers)
                                            ->count();
            foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
            $data = FrontProductsApiResource::collection($new_products);        
                return response()->json([
                    'status_code' => 200,
                    'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200); 
          }  // end user case

          // case logged in user role is User 
          if (in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ){
             $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
            $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                            ->whereIn('category_id', $part_categories)
                                            ->whereIn('manufacturer_id', $manufacturers)
                                            ->count();
            foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
            $data = FrontProductsApiResource::collection($new_products);        
                return response()->json([
                    'status_code' => 200,
                    'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200); 
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $new_products = Product::where('lang', $lang)->where('approved', 1)->whereIn('producttype_id', [1, 2, 3])
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
            $total = Product::where('lang', $lang)->where('approved', 1)->whereIn('producttype_id', [1, 2, 3])
                                        ->whereIn('category_id', $part_categories)
                                            ->whereIn('manufacturer_id', $manufacturers)
                                            ->count();
            foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
            $data = FrontProductsApiResource::collection($new_products);        
                return response()->json([
                    'status_code' => 200,
                    'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
           $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
            $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                         ->whereIn('category_id', $part_categories)
                                            ->whereIn('manufacturer_id', $manufacturers)
                                            ->count();
            foreach ($new_products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }

            $data = FrontProductsApiResource::collection($new_products);        
                return response()->json([
                    'status_code' => 200,
                    'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200);          
        } // end guest case
    }

    public function part_categories_origins_search($part_categories, $origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
    {
        $lang = $this->getLang();
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if (in_array('User', $user_roles)) {
              $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
                $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                                ->whereIn('category_id', $part_categories)
                                                ->whereIn('prodcountry_id', $origins)
                                                ->count();
                foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
                $data = FrontProductsApiResource::collection($new_products);        
                    return response()->json([
                        'status_code' => 200,
                        'message'     => 'success',
                        'data'        => $data,
                        'total'       => $total,
                    ], 200);
          }  // end user case

          // case logged in user role is User 
          if (in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ){
              $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
                $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                                ->whereIn('category_id', $part_categories)
                                                ->whereIn('prodcountry_id', $origins)
                                                ->count();
                foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
                $data = FrontProductsApiResource::collection($new_products);        
                    return response()->json([
                        'status_code' => 200,
                        'message'     => 'success',
                        'data'        => $data,
                        'total'       => $total,
                    ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $new_products = Product::where('lang', $lang)->where('approved', 1)->whereIn('producttype_id', [1, 2, 3])
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
            $total = Product::where('lang', $lang)->where('approved', 1)->whereIn('producttype_id', [1, 2, 3])
                                        ->whereIn('category_id', $part_categories)
                                            ->whereIn('prodcountry_id', $origins)
                                            ->count();
            foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
            $data = FrontProductsApiResource::collection($new_products);        
                return response()->json([
                    'status_code' => 200,
                    'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
            $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
            $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                            ->whereIn('category_id', $part_categories)
                                            ->whereIn('prodcountry_id', $origins)
                                            ->count();
            foreach ($new_products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }

            $data = FrontProductsApiResource::collection($new_products);        
                return response()->json([
                    'status_code' => 200,
                    'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200);         
        } // end guest case
    }

    public function manufacturers_origins_search($manufacturers, $origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
    {
        $lang = $this->getLang();
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if (in_array('User', $user_roles)) {
            $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->whereIn('prodcountry_id', $origins)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
            $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                            ->whereIn('manufacturer_id', $manufacturers)
                                            ->whereIn('prodcountry_id', $origins)
                                            ->count();
            foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
            $data = FrontProductsApiResource::collection($new_products);        
                return response()->json([
                    'status_code' => 200,
                    'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200);
          }  // end user case

          // case logged in user role is User 
          if (in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ){
            $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->whereIn('prodcountry_id', $origins)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
            $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                            ->whereIn('manufacturer_id', $manufacturers)
                                            ->whereIn('prodcountry_id', $origins)
                                            ->count();
            foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
            $data = FrontProductsApiResource::collection($new_products);        
                return response()->json([
                    'status_code' => 200,
                    'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $new_products = Product::where('lang', $lang)->where('approved', 1)->whereIn('producttype_id', [1, 2, 3])
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->whereIn('prodcountry_id', $origins)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
            $total = Product::where('lang', $lang)->where('approved', 1)->whereIn('producttype_id', [1, 2, 3])
                                            ->whereIn('manufacturer_id', $manufacturers)
                                            ->whereIn('prodcountry_id', $origins)
                                            ->count();
            foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
            $data = FrontProductsApiResource::collection($new_products);        
                return response()->json([
                    'status_code' => 200,
                    'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
            $new_products = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->whereIn('prodcountry_id', $origins)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
            $total = Product::where('lang', $lang)->where('approved', 1)->where('producttype_id', 1)
                                            ->whereIn('manufacturer_id', $manufacturers)
                                            ->whereIn('prodcountry_id', $origins)
                                            ->count();
            foreach ($new_products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
            $data = FrontProductsApiResource::collection($new_products);        
                return response()->json([
                    'status_code' => 200,
                    'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200);         
        } // end guest case
    }
}
