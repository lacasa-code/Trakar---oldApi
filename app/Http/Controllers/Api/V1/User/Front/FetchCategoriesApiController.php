<?php

namespace App\Http\Controllers\Api\V1\User\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductCategory;
use App\Models\PartCategory;
use App\Http\Resources\Website\Products\FrontProductsApiResource;
use Gate;
use Symfony\Component\HttpFoundation\Response;
// use Illuminate\Support\Facades\Schema;
 use Auth;
use App\Http\Resources\Website\Products\FrontCategoriesApiResource;
use App\Http\Resources\Website\Products\FrontPartCategoriesApiResource;
use Carbon\Carbon;
use App\Models\Product;
use App\Http\Requests\Api\V1\User\Front\SelectCarTypeApiRequest;
use App\Models\AddVendor;

class FetchCategoriesApiController extends Controller
{
  public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }
  
    /* public function fetch_categories(Request $request)
    {
      $PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
        
     
        /* $product_categories = ProductCategory::skip(($page-1)*$PAGINATION_COUNT)
                                ->take($PAGINATION_COUNT)
                                ->orderBy($ordered_by, $sort_type)->get(); */
       
        /*$product_categories = ProductCategory::orderBy('created_at', 'desc')->get();

        $total = ProductCategory::count();
        $data = FrontCategoriesApiResource::collection($product_categories);        
            return response()->json([
            	'status_code'      => 200,
            	'message'          => 'success',
                'data'             => $data,
                'total'            => $total,
            ], 200);
      } */

    public function fetch_specific_categories(SelectCarTypeApiRequest $request, $id)
    {
       $lang = $this->getLang();
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      if ($ordered_by == 'price') {
        $ordered_by = 'actual_price';
      }
        $part_one = ProductCategory::where('id', $id)->first();
        //return $part_one;
      $part_categories = $request->categories;
      $manufacturers   = $request->manufacturers;
      $origins         = $request->origins;
      $start_price     = $request->start_price;
      $end_price       = $request->end_price;
      $cartype_id      = $request->cartype_id;
      $category_id     = $part_one->id;

      // case 1
        if ($part_categories == '' && $manufacturers == '' && $origins == '' && $start_price == '') {

          return $this->fetching_all_data($page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $category_id);
        }

        // case 1
        if ($part_categories != '' && $manufacturers == '' && $origins == '' && $start_price == '') {
          $part_categories = json_decode($request->categories);
        // $manufacturers   = json_decode($request->manufacturers);
        // $origins         = json_decode($request->origins);
          return $this->part_categories_search($part_categories, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $category_id);
        }

        // case 2
        if ($part_categories == '' && $manufacturers != '' && $origins == '' && $start_price == '') {
          // $part_categories = json_decode($request->categories);
        $manufacturers   = json_decode($request->manufacturers);
        // $origins         = json_decode($request->origins);
          return $this->manufacturers_search($manufacturers, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $category_id);
        }

        // case 3
        if ($part_categories == '' && $manufacturers == '' && $origins != '' && $start_price == '') {
          // $part_categories = json_decode($request->categories);
        // $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->origins_search($origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $category_id);
        }

        // case 4
        if ($part_categories == '' && $manufacturers == '' && $origins == '' && $start_price != '') {
          // $part_categories = json_decode($request->categories);
        // $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->price_search($start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $category_id);
        }

        // case 5
        if ($part_categories != '' && $manufacturers != '' && $origins == '' && $start_price == '') {
          $part_categories = json_decode($request->categories);
          // return $part_categories;
        $manufacturers   = json_decode($request->manufacturers);
        //$origins         = json_decode($request->origins);
        //return 'bbbv';
          return $this->part_categories_manufacturers_search($part_categories, $manufacturers, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $category_id);
        }

        // case 6
        if ($part_categories != '' && $manufacturers == '' && $origins != '' && $start_price == '') {
          $part_categories = json_decode($request->categories);
        //$manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->part_categories_origins_search($part_categories, $origins, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $category_id);
        }

        // case 7
        if ($part_categories != '' && $manufacturers == '' && $origins == '' && $start_price != '') {
          $part_categories = json_decode($request->categories);
        //$manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->part_categories_price_search($part_categories, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $category_id);
        }

        // case 8
        if ($part_categories != '' && $manufacturers != '' && $origins != '' && $start_price == '') {
          $part_categories = json_decode($request->categories);
        $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->part_manufacturers_origins_search($part_categories, $manufacturers, $origins, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $category_id);
        }

        // case 9
        if ($part_categories != '' && $manufacturers != '' && $origins == '' && $start_price != '') {
          $part_categories = json_decode($request->categories);
        $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->part_manufacturers_price_search($part_categories, $manufacturers, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $category_id);
        }

        // case 10
        if ($part_categories != '' && $manufacturers == '' && $origins != '' && $start_price != '') {
          $part_categories = json_decode($request->categories);
        $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->part_origins_price_search($part_categories, $origins, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $category_id);
        }

        // case 11
        if ($part_categories != '' && $manufacturers != '' && $origins != '' && $start_price != '') {
          $part_categories = json_decode($request->categories);
        $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->four_hand_search($part_categories, $manufacturers, $origins, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $category_id);
        }

        // case 12
        if ($part_categories == '' && $manufacturers != '' && $origins != '' && $start_price == '') {
          //$part_categories = json_decode($request->categories);
        $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->manufacturers_origins_search($manufacturers, $origins, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $category_id);
        }

        // case 13
        if ($part_categories == '' && $manufacturers != '' && $origins == '' && $start_price != '') {
            //$part_categories = json_decode($request->categories);
            $manufacturers   = json_decode($request->manufacturers);
            $origins         = json_decode($request->origins);
          return $this->manufacturers_price_search($manufacturers, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $category_id);
        }

        // case 14
        if ($part_categories == '' && $manufacturers != '' && $origins != '' && $start_price != '') {
            //$part_categories = json_decode($request->categories);
            $manufacturers   = json_decode($request->manufacturers);
            $origins         = json_decode($request->origins);
          return $this->manufacturers_origins_price_search($manufacturers, $origins, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $category_id);
        }


        // case 15
        if ($part_categories == '' && $manufacturers == '' && $origins != '' && $start_price != '') {
            //$part_categories = json_decode($request->categories);
            // $manufacturers   = json_decode($request->manufacturers);
            $origins         = json_decode($request->origins);
          return $this->origins_price_search($origins, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $category_id);
        }
    }

    public function fetching_all_data($page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $category_id)
    {
        $lang = $this->getLang();
        $cat_name = ProductCategory::findOrFail($category_id);
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
          // return 'bbbu';
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
                $new_products = Product::where('approved', 1)
                                       ->where('producttype_id', 1)
                                       ->where('category_id', $category_id)
                                       ->where('cartype_id', $cartype_id) // added june 29 2021
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
                //$new_product['cat_name']      = $cat_name;
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name'    => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
           // return 'bbbv';
                $new_products = Product::where('approved', 1)
                                ->whereIn('producttype_id', [1, 2, 3])
                                ->where('category_id', $category_id)
                                ->where('cartype_id', $cartype_id)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                ->orderBy($ordered_by, $sort_type)->get(); 

        $total = count($new_products);
                        
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
                //$new_product['cat_name']      = $cat_name;
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name' => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
         // return $ordered_by;
            $new_products = Product::where('approved', 1)
                                      ->where('producttype_id', 1)
                                      ->where('category_id', $category_id)
                                      ->where('cartype_id', $cartype_id) // added june 29 2021
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
                //$new_product['cat_name']      = $cat_name;
              }

        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name' => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200);          
        } // end guest case
    }


    public function part_categories_search($part_categories, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $category_id)
    {
        $lang = $this->getLang();
        $cat_name = ProductCategory::findOrFail($category_id);
      //return $part_categories;
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
                $new_products = Product::where('approved', 1)
                                       ->where('producttype_id', 1)
                                       ->where('category_id', $category_id)
                                       ->where('cartype_id', $cartype_id) // added june 29 2021
                                       // //->whereIn('category_id', $part_categories)
                                       ->whereIn('part_category_id', $part_categories)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
                //$new_product['cat_name']      = $cat_name;
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name' => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
                $new_products = Product::where('approved', 1)
                                ->whereIn('producttype_id', [1, 2, 3])
                                ->where('category_id', $category_id)
                                ->where('cartype_id', $cartype_id)
                                //->whereIn('category_id', $part_categories)
                                ->whereIn('part_category_id', $part_categories)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                ->orderBy($ordered_by, $sort_type)->get(); 

        $total = count($new_products);
                        
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
                //$new_product['cat_name']      = $cat_name;
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name' => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
            $new_products = Product::where('approved', 1)
                                   ->where('producttype_id', 1)
                                   ->where('category_id', $category_id)
                                   ->where('cartype_id', $cartype_id) // added june 29 2021
                                   //->whereIn('category_id', $part_categories)
                                   ->whereIn('part_category_id', $part_categories)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                    ->orderBy($ordered_by, $sort_type)->get(); 

        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
                //$new_product['cat_name']      = $cat_name;
              }

        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name' => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200);          
        } // end guest case
    }

    public function part_manufacturers_origins_search($part_categories, $manufacturers, $origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $category_id)
    {
        $lang = $this->getLang();
        $cat_name = ProductCategory::findOrFail($category_id);
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
                $new_products = Product::where('approved', 1)
                                      ->where('producttype_id', 1)
                                      ->where('category_id', $category_id)
                                     ->where('cartype_id', $cartype_id) // added june 29 2021
                                        //->whereIn('category_id', $part_categories)
                                     ->whereIn('part_category_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->whereIn('prodcountry_id', $origins)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
                //$new_product['cat_name']      = $cat_name;
              }

        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name' => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
              $new_products = Product::where('approved', 1)
                                        ->whereIn('producttype_id', [1, 2, 3])
                                        ->where('category_id', $category_id)
                                        ->where('cartype_id', $cartype_id)
                                        //->whereIn('category_id', $part_categories)
                                        ->whereIn('part_category_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->whereIn('prodcountry_id', $origins)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
                //$new_product['cat_name']      = $cat_name;
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name' => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
              $new_products = Product::where('approved', 1)
                                        ->where('producttype_id', 1)
                                        ->where('category_id', $category_id)
                                        ->where('cartype_id', $cartype_id) // added june 29 2021
                                        //->whereIn('category_id', $part_categories)
                                        ->whereIn('part_category_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->whereIn('prodcountry_id', $origins)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
                //$new_product['cat_name']      = $cat_name;
              }

        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name' => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200);         
        } // end guest case
    }

    public function part_manufacturers_price_search($part_categories, $manufacturers, $start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $category_id)
    {
        $lang = $this->getLang();
        $cat_name = ProductCategory::findOrFail($category_id);
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $new_products = Product::where('approved', 1)
                                      ->where('producttype_id', 1)
                                      ->where('category_id', $category_id)
                                      ->where('cartype_id', $cartype_id) // added june 29 2021
                                        //->whereIn('category_id', $part_categories)
                                      ->whereIn('part_category_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
                //$new_product['cat_name']      = $cat_name;
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name' => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $new_products = Product::where('approved', 1)
                                        ->whereIn('producttype_id', [1, 2, 3])
                                        ->where('category_id', $category_id)
                                        ->where('cartype_id', $cartype_id)
                                        //->whereIn('category_id', $part_categories)
                                        ->whereIn('part_category_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
                //$new_product['cat_name']      = $cat_name;
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name' => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
             $new_products = Product::where('approved', 1)
                                       ->where('producttype_id', 1)
                                       ->where('category_id', $category_id)
                                       ->where('cartype_id', $cartype_id) // added june 29 2021
                                        //->whereIn('category_id', $part_categories)
                                       ->whereIn('part_category_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
                //$new_product['cat_name']      = $cat_name;
              }

        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name' => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200);        
        } // end guest case
    }

    public function origins_price_search($origins, $start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $category_id)
    {
        $lang = $this->getLang();
        $cat_name = ProductCategory::findOrFail($category_id);
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
               $new_products = Product::where('approved', 1)
                                        ->where('producttype_id', 1)
                                        ->where('category_id', $category_id)
                                        ->where('cartype_id', $cartype_id) // added june 29 2021
                                        ->whereIn('prodcountry_id', $origins)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
                //$new_product['cat_name']      = $cat_name;
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name' => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
             $new_products = Product::where('approved', 1)
             ->whereIn('producttype_id', [1, 2, 3])
             ->where('category_id', $category_id)
             ->where('cartype_id', $cartype_id)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
                //$new_product['cat_name']      = $cat_name;
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name' => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
             $new_products = Product::where('approved', 1)
             ->where('producttype_id', 1)
             ->where('category_id', $category_id)
             ->where('cartype_id', $cartype_id) // added june 29 2021

                                        ->whereIn('prodcountry_id', $origins)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
                //$new_product['cat_name']      = $cat_name;
              }

        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name' => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200);         
        } // end guest case
    }

    public function manufacturers_origins_price_search($manufacturers, $origins, $start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $category_id)
    {
        $lang = $this->getLang();
        $cat_name = ProductCategory::findOrFail($category_id);
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
               $new_products = Product::where('approved', 1)
               ->where('producttype_id', 1)
               ->where('category_id', $category_id)
               ->where('cartype_id', $cartype_id) // added june 29 2021

                                        ->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
                //$new_product['cat_name']      = $cat_name;
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name' => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
             $new_products = Product::where('approved', 1)
             ->whereIn('producttype_id', [1, 2, 3])
             ->where('category_id', $category_id)
             ->where('cartype_id', $cartype_id)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
                //$new_product['cat_name']      = $cat_name;
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name' => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
             $new_products = Product::where('approved', 1)
             ->where('producttype_id', 1)
             ->where('category_id', $category_id)
             ->where('cartype_id', $cartype_id) // added june 29 2021

                                        ->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
                //$new_product['cat_name']      = $cat_name;
              }
        
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name' => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200);         
        } // end guest case
    }

    public function four_hand_search($part_categories, $manufacturers, $origins, $start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $category_id)
    {
        $lang = $this->getLang();
        $cat_name = ProductCategory::findOrFail($category_id);
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
                $new_products = Product::where('approved', 1)
                ->where('producttype_id', 1)
                ->where('category_id', $category_id)
                ->where('cartype_id', $cartype_id) // added june 29 2021

                                        //->whereIn('category_id', $part_categories)
                ->whereIn('part_category_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
                //$new_product['cat_name']      = $cat_name;
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name' => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200); 
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
               $new_products = Product::where('approved', 1)
               ->whereIn('producttype_id', [1, 2, 3])
               ->where('category_id', $category_id)
               ->where('cartype_id', $cartype_id)
                                        //->whereIn('category_id', $part_categories)
               ->whereIn('part_category_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
                //$new_product['cat_name']      = $cat_name;
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name' => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
               $new_products = Product::where('approved', 1)
               ->where('producttype_id', 1)
               ->where('category_id', $category_id)
               ->where('cartype_id', $cartype_id) // added june 29 2021

                                        //->whereIn('category_id', $part_categories)
               ->whereIn('part_category_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
                //$new_product['cat_name']      = $cat_name;
              }

        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name' => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200);         
        } // end guest case
    }

    public function part_origins_price_search($part_categories, $origins, $start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $category_id)
    {
        $lang = $this->getLang();
        $cat_name = ProductCategory::findOrFail($category_id);
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
               $new_products = Product::where('approved', 1)
               ->where('producttype_id', 1)
               ->where('category_id', $category_id)
               ->where('cartype_id', $cartype_id) // added june 29 2021

                                        //->whereIn('category_id', $part_categories)
               ->whereIn('part_category_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
                //$new_product['cat_name']      = $cat_name;
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name' => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
             $new_products = Product::where('approved', 1)
             ->whereIn('producttype_id', [1, 2, 3])
             ->where('category_id', $category_id)
             ->where('cartype_id', $cartype_id)
                                        //->whereIn('category_id', $part_categories)
             ->whereIn('part_category_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
                //$new_product['cat_name']      = $cat_name;
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name' => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
             $new_products = Product::where('approved', 1)
             ->where('producttype_id', 1)
             ->where('category_id', $category_id)
             ->where('cartype_id', $cartype_id) // added june 29 2021

                                        //->whereIn('category_id', $part_categories)
             ->whereIn('part_category_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->where('price', '>=', $start_price)
                                        ->where('price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
                //$new_product['cat_name']      = $cat_name;
              }

        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name' => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200);         
        } // end guest case
    }

public function price_search($start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $category_id)
    {
        //return $part_categories;
        $lang = $this->getLang();
        $cat_name = ProductCategory::findOrFail($category_id);
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
               $new_products = Product::where('approved', 1)
               ->where('producttype_id', 1)
               ->where('category_id', $category_id)
               ->where('cartype_id', $cartype_id) // added june 29 2021

                                ->where('price', '>=', $start_price)
                                ->where('price', '<=', $end_price)
                                        //->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
                //$new_product['cat_name']      = $cat_name;
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name' => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
             $new_products = Product::where('approved', 1)
             ->whereIn('producttype_id', [1, 2, 3])
             ->where('category_id', $category_id)
             ->where('cartype_id', $cartype_id)
                             ->where('price', '>=', $start_price)
                                ->where('price', '<=', $end_price)
                                        //->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $total = count($new_products);
        
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
                //$new_product['cat_name']      = $cat_name;
              }

        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name' => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
            $new_products = Product::where('approved', 1)
            ->where('producttype_id', 1)
            ->where('category_id', $category_id)
            ->where('cartype_id', $cartype_id) // added june 29 2021

                                ->where('price', '>=', $start_price)
                                ->where('price', '<=', $end_price)
                                        //->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
                //$new_product['cat_name']      = $cat_name;
              }

        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name' => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200);          
        } // end guest case
    }

    public function part_categories_price_search($part_categories, $start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $category_id)
    {
        $lang = $this->getLang();
        $cat_name = ProductCategory::findOrFail($category_id);
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $new_products = Product::where('approved', 1)
              ->where('producttype_id', 1)
              ->where('category_id', $category_id)
              ->where('cartype_id', $cartype_id) // added june 29 2021

                                ->where('price', '>=', $start_price)
                                ->where('price', '<=', $end_price)
                                //->whereIn('category_id', $part_categories)
                                ->whereIn('part_category_id', $part_categories)
                                        //->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
                //$new_product['cat_name']      = $cat_name;
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name' => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200); 
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
             $new_products = Product::where('approved', 1)
             ->whereIn('producttype_id', [1, 2, 3])
             ->where('category_id', $category_id)
             ->where('cartype_id', $cartype_id)
                                ->where('price', '>=', $start_price)
                                ->where('price', '<=', $end_price)
                                //->whereIn('category_id', $part_categories)
                                ->whereIn('part_category_id', $part_categories)
                                        //->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
                //$new_product['cat_name']      = $cat_name;
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name' => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
             $new_products = Product::where('approved', 1)
             ->where('producttype_id', 1)
             ->where('category_id', $category_id)
             ->where('cartype_id', $cartype_id) // added june 29 2021

                                ->where('price', '>=', $start_price)
                                ->where('price', '<=', $end_price)
                                //->whereIn('category_id', $part_categories)
                                ->whereIn('part_category_id', $part_categories)
                                        //->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
                //$new_product['cat_name']      = $cat_name;
              }

        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name' => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200);         
        } // end guest case
    }

    public function manufacturers_price_search($manufacturers, $start_price, $end_price, $page, $ordered_by , $sort_type, $PAGINATION_COUNT, $cartype_id, $category_id)
    {
        $lang = $this->getLang();
        $cat_name = ProductCategory::findOrFail($category_id);
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
                $new_products = Product::where('approved', 1)
                ->where('producttype_id', 1)
                ->where('category_id', $category_id)
                ->where('cartype_id', $cartype_id) // added june 29 2021

                                ->where('price', '>=', $start_price)
                                ->where('price', '<=', $end_price)
                                ->whereIn('manufacturer_id', $manufacturers)
                                        //->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
                //$new_product['cat_name']      = $cat_name;
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name' => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
              $new_products = Product::where('approved', 1)
              ->whereIn('producttype_id', [1, 2, 3])
              ->where('category_id', $category_id)
              ->where('cartype_id', $cartype_id)
                                ->where('price', '>=', $start_price)
                                ->where('price', '<=', $end_price)
                                ->whereIn('manufacturer_id', $manufacturers)
                                        //->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
                //$new_product['cat_name']      = $cat_name;
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name' => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
              $new_products = Product::where('approved', 1)
              ->where('producttype_id', 1)
              ->where('category_id', $category_id)
              ->where('cartype_id', $cartype_id) // added june 29 2021

                               ->where('price', '>=', $start_price)
                                ->where('price', '<=', $end_price)
                                ->whereIn('manufacturer_id', $manufacturers)
                                        //->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
                //$new_product['cat_name']      = $cat_name;
              }
        
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name' => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200);         
        } // end guest case
    }

    public function manufacturers_search($manufacturers, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $category_id)
    {
        $lang = $this->getLang();
        $cat_name = ProductCategory::findOrFail($category_id);
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $new_products = Product::where('approved', 1)
              ->where('producttype_id', 1)
              ->where('category_id', $category_id)
              ->where('cartype_id', $cartype_id) // added june 29 2021

                                        ->whereIn('manufacturer_id', $manufacturers)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
                //$new_product['cat_name']      = $cat_name;
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name' => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $new_products = Product::where('approved', 1)
            ->whereIn('producttype_id', [1, 2, 3])
            ->where('category_id', $category_id)
            ->where('cartype_id', $cartype_id)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
                //$new_product['cat_name']      = $cat_name;
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name' => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
            $new_products = Product::where('approved', 1)
            ->where('producttype_id', 1)
            ->where('category_id', $category_id)
            ->where('cartype_id', $cartype_id) // added june 29 2021

                                        ->whereIn('manufacturer_id', $manufacturers)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
                //$new_product['cat_name']      = $cat_name;
              }

        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name' => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200);         
        } // end guest case
    }

    public function origins_search($origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $category_id)
    {
        $lang = $this->getLang();
        $cat_name = ProductCategory::findOrFail($category_id);
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $new_products = Product::where('approved', 1)
              ->where('producttype_id', 1)
              ->where('category_id', $category_id)
              ->where('cartype_id', $cartype_id) // added june 29 2021

                                        ->whereIn('prodcountry_id', $origins)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
                //$new_product['cat_name']      = $cat_name;
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name' => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $new_products = Product::where('approved', 1)
            ->whereIn('producttype_id', [1, 2, 3])
            ->where('category_id', $category_id)
            ->where('cartype_id', $cartype_id)
                                          ->whereIn('prodcountry_id', $origins)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
                //$new_product['cat_name']      = $cat_name;
              }
        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name' => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
            $new_products = Product::where('approved', 1)
            ->where('producttype_id', 1)
            ->where('category_id', $category_id)
            ->where('cartype_id', $cartype_id) // added june 29 2021

                                        ->whereIn('prodcountry_id', $origins)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
                //$new_product['cat_name']      = $cat_name;
              }

        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'cat_name' => $cat_name->name,
                'cat_name_en' => $cat_name->name_en,
                'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
            ], 200);         
        } // end guest case
    }

    public function part_categories_manufacturers_search($part_categories, $manufacturers, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $category_id)
    {
        $lang = $this->getLang();
        $cat_name = ProductCategory::findOrFail($category_id);
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
             $new_products = Product::where('approved', 1)
             ->where('producttype_id', 1)
             ->where('category_id', $category_id)
             ->where('cartype_id', $cartype_id) // added june 29 2021

                                        //->whereIn('category_id', $part_categories)
             ->whereIn('part_category_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
            $total = count($new_products);
            foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
                //$new_product['cat_name']      = $cat_name;
              }
            $data = FrontProductsApiResource::collection($new_products);        
                return response()->json([
                    'status_code' => 200,
                    'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                    'cat_name' => $cat_name->name,
                    'cat_name_en' => $cat_name->name_en,
                    'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
                ], 200); 
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $new_products = Product::where('approved', 1)
            ->whereIn('producttype_id', [1, 2, 3])
            ->where('category_id', $category_id)
            ->where('cartype_id', $cartype_id)
                                        //->whereIn('category_id', $part_categories)
            ->whereIn('part_category_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
            $total = count($new_products);
            foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
                //$new_product['cat_name']      = $cat_name;
              }
            $data = FrontProductsApiResource::collection($new_products);        
                return response()->json([
                    'status_code' => 200,
                    'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                    'cat_name' => $cat_name->name,
                    'cat_name_en' => $cat_name->name_en,
                    'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
                ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
          // return $part_categories;
           $new_products = Product::where('approved', 1)
           ->where('producttype_id', 1)
           ->where('category_id', $category_id)
           ->where('cartype_id', $cartype_id) // added june 29 2021

                                        //->whereIn('category_id', $part_categories)
           ->whereIn('part_category_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
            $total = count($new_products);
            foreach ($new_products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
                //$new_product['cat_name']      = $cat_name;
              }

            $data = FrontProductsApiResource::collection($new_products);        
                return response()->json([
                    'status_code' => 200,
                    'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                    'cat_name' => $cat_name->name,
                    'cat_name_en' => $cat_name->name_en,
                    'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
                ], 200);          
        } // end guest case
    }

    public function part_categories_origins_search($part_categories, $origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $category_id)
    {
        $lang = $this->getLang();
        $cat_name = ProductCategory::findOrFail($category_id);
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $new_products = Product::where('approved', 1)
              ->where('producttype_id', 1)
              ->where('category_id', $category_id)
              ->where('cartype_id', $cartype_id) // added june 29 2021

                                        //->whereIn('category_id', $part_categories)
              ->whereIn('part_category_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
                $total = count($new_products);
                foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
                //$new_product['cat_name']      = $cat_name;
              }
                $data = FrontProductsApiResource::collection($new_products);        
                    return response()->json([
                        'status_code' => 200,
                        'message'     => 'success',
                        'data'        => $data,
                        'total'       => $total,
                        'cat_name' => $cat_name->name,
                        'cat_name_en' => $cat_name->name_en,
                        'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
                    ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $new_products = Product::where('approved', 1)
            ->whereIn('producttype_id', [1, 2, 3])
            ->where('category_id', $category_id)
            ->where('cartype_id', $cartype_id)
                                        //->whereIn('category_id', $part_categories)
            ->whereIn('part_category_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
            $total = count($new_products);
            foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
                //$new_product['cat_name']      = $cat_name;
              }
            $data = FrontProductsApiResource::collection($new_products);        
                return response()->json([
                    'status_code' => 200,
                    'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                    'cat_name' => $cat_name->name,
                    'cat_name_en' => $cat_name->name_en,
                    'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
                ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
            $new_products = Product::where('approved', 1)
            ->where('producttype_id', 1)
            ->where('category_id', $category_id)
            ->where('cartype_id', $cartype_id) // added june 29 2021

                                        //->whereIn('category_id', $part_categories)
                                        ->whereIn('part_category_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
            $total = count($new_products);
            foreach ($new_products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
                //$new_product['cat_name']      = $cat_name;
              }

            $data = FrontProductsApiResource::collection($new_products);        
                return response()->json([
                    'status_code' => 200,
                    'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                    'cat_name' => $cat_name->name,
                    'cat_name_en' => $cat_name->name_en,
                    'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
                ], 200);         
        } // end guest case
    }

      public function manufacturers_origins_search($manufacturers, $origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $category_id)
      {
          $lang = $this->getLang();
          $cat_name = ProductCategory::findOrFail($category_id);
          // alraedy user logged in
          if (Auth::guard('api')->check() && Auth::user()) 
          {
              $user = Auth::user();
              $user_roles = $user->roles->pluck('title')->toArray();
            
            // case logged in user role is User 
            if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $new_products = Product::where('approved', 1)
              ->where('producttype_id', 1)
              ->where('category_id', $category_id)
              ->where('cartype_id', $cartype_id) // added june 29 2021

                                          ->whereIn('manufacturer_id', $manufacturers)
                                          ->whereIn('prodcountry_id', $origins)
                                          // ->skip(($page-1)*$PAGINATION_COUNT)
                                          // ->take($PAGINATION_COUNT)
                                          ->orderBy($ordered_by, $sort_type)->get(); 
              $total = count($new_products);
              foreach ($new_products as $new_product) {
                  $new_product['in_cart']       = $user->revise_cart($new_product->id);
                  $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                  $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
                  //$new_product['cat_name']      = $cat_name;
                }
              $data = FrontProductsApiResource::collection($new_products);        
                  return response()->json([
                      'status_code' => 200,
                      'message'     => 'success',
                      'data'        => $data,
                      'total'       => $total,
                      'cat_name' => $cat_name->name,
                      'cat_name_en' => $cat_name->name_en,
                      'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
                  ], 200);
            }  // end user case

            if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
              $new_products = Product::where('approved', 1)
              ->whereIn('producttype_id', [1, 2, 3])
              ->where('category_id', $category_id)
              ->where('cartype_id', $cartype_id)
                                          ->whereIn('manufacturer_id', $manufacturers)
                                          ->whereIn('prodcountry_id', $origins)
                                          // ->skip(($page-1)*$PAGINATION_COUNT)
                                          // ->take($PAGINATION_COUNT)
                                          ->orderBy($ordered_by, $sort_type)->get(); 
              $total = count($new_products);
              foreach ($new_products as $new_product) {
                  $new_product['in_cart']       = $user->revise_cart($new_product->id);
                  $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                  $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
                  //$new_product['cat_name']      = $cat_name;
                }
              $data = FrontProductsApiResource::collection($new_products);        
                  return response()->json([
                      'status_code' => 200,
                      'message'     => 'success',
                      'data'        => $data,
                      'total'       => $total,
                      'cat_name' => $cat_name->name,
                      'cat_name_en' => $cat_name->name_en,
                      'maincat_name'    => $cat_name->main_category->main_category_name,
                'maincat_name_en' => $cat_name->main_category->name_en,
                'maincat_id'      => $cat_name->main_category->id,
                  ], 200);
            } // end vendor case
          }  // end case logged in 
          else{ // guest case
              $new_products = Product::where('approved', 1)
              ->where('producttype_id', 1)
              ->where('category_id', $category_id)
              ->where('cartype_id', $cartype_id) // added june 29 2021

                                          ->whereIn('manufacturer_id', $manufacturers)
                                          ->whereIn('prodcountry_id', $origins)
                                          // ->skip(($page-1)*$PAGINATION_COUNT)
                                          // ->take($PAGINATION_COUNT)
                                          ->orderBy($ordered_by, $sort_type)->get(); 
              $total = count($new_products);
              foreach ($new_products as $new_product) {
                  $new_product['in_cart']       = 0;
                  $new_product['in_wishlist']   = 0;
                  $new_product['in_favourites'] = 0;
                  //$new_product['cat_name']      = $cat_name;
                }
              $data = FrontProductsApiResource::collection($new_products);        
                  return response()->json([
                      'status_code' => 200,
                      'message'     => 'success',
                      'data'        => $data,
                      'total'       => $total,
                      'cat_name' => $cat_name->name,
                      'cat_name_en' => $cat_name->name_en,
                      'maincat_name'    => $cat_name->main_category->main_category_name,
                      'maincat_name_en' => $cat_name->main_category->name_en,
                      'maincat_id'      => $cat_name->main_category->id,
                  ], 200);         
          } // end guest case
      }  // end probs

    public function fetch_part_categories(Request $request)
    {
      $lang = $this->getLang();
     // $cat_name = ProductCategory::findOrFail($category_id);
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      //$PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
        
     
        $part_categories = PartCategory::skip(($page-1)*$PAGINATION_COUNT)
                                ->take($PAGINATION_COUNT)
                                ->orderBy($ordered_by, $sort_type)->get(); 

       /* $part_categories = PartCategory::orderBy('created_at', 'desc')->get(); */

        $total = PartCategory::count();
        $data = FrontPartCategoriesApiResource::collection($part_categories);        
            return response()->json([
            	'status_code'      => 200,
            	'message'          => 'success',
                'data'             => $data,
                'total'            => $total,
            ], 200);
      }   
}