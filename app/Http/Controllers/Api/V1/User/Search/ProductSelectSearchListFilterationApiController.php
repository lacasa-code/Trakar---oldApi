<?php

namespace App\Http\Controllers\Api\V1\User\Search;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response as Custom;
use App\Models\Product;
use Gate;
use Auth;
use App\Http\Requests\SearchApisRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Schema;
use App\Http\Resources\User\Search\ProductSearchApiResource;
use App\Http\Resources\Website\Products\FrontProductsApiResource;
use App\Models\Transmission;
use App\Models\Cartype;
use App\Models\Adpositions;
use App\Models\AddVendor;
use App\Http\Requests\Api\V1\User\Front\SelectCategoryApiEditRequest;
use App\Models\Allcategory;
use App\Models\Manufacturer;
use App\Models\Prodcountry;
// use App\Http\Resources\Api\Admin\Allcategory\HomeAllcategoryApiResource;
use App\Http\Resources\Api\Admin\Allcategory\HomeAllcategoryFilterationApiResource;
use App\Http\Resources\Admin\Manufacturer\ManufacturerFilterationApiResource;
use App\Http\Resources\Admin\OriginCountry\OriginCountryFilterationApiResource;

class ProductSelectSearchListFilterationApiController extends Controller
{
     public function getLang()
     {
        return $lang = \Config::get('app.locale');
     }
     
     public function search_home_categories_parts(SelectCategoryApiEditRequest $request)
     {
        $lang  = $this->getLang();
         
         $width  = $request->width;
         $height = $request->height;
         $size   = $request->size;
       
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

        // case 1
        if ($manufacturers == '' && $origins == '' && $start_price == '') {
          $part_categories = json_decode($request->part_categories);
          return $this->attribute_only_search($width, $height, $size, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 2
        if ($manufacturers != '' && $origins == '' && $start_price == '') {
        $manufacturers   = json_decode($request->manufacturers);
          return $this->attribute_manufacturers_search($width, $height, $size, $manufacturers, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 3
        if ($manufacturers == '' && $origins != '' && $start_price == '') {
          $origins         = json_decode($request->origins);
          return $this->attribute_origins_search($width, $height, $size, $origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT);
        }

        // case 4
        if ($manufacturers == '' && $origins == '' && $start_price != '') {
          $origins         = json_decode($request->origins);
          return $this->attribute_price_search($width, $height, $size, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 4
        if ($manufacturers != '' && $origins != '' && $start_price == '') {
          $origins         = json_decode($request->origins);
          $manufacturers   = json_decode($request->manufacturers);
          return $this->attribute_manufacturers_origins_search($width, $height, $size, $origins, $manufacturers, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 4
        if ($manufacturers != '' && $origins == '' && $start_price != '') {
          $manufacturers         = json_decode($request->manufacturers);
          return $this->attribute_manufacturers_price_search($width, $height, $size, $manufacturers, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 4
        if ($manufacturers == '' && $origins != '' && $start_price != '') {
          $origins         = json_decode($request->origins);
          return $this->attribute_origins_price_search($width, $height, $size, $origins, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 4
        if ($manufacturers != '' && $origins != '' && $start_price != '') {
          $origins         = json_decode($request->origins);
          $manufacturers   = json_decode($request->manufacturers);
          return $this->all_search($width, $height, $size, $origins, $manufacturers, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }
    }

     public function attribute_only_search($width, $height, $size, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT)
    {
       if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {

            $products = Product::where('approved', 1)
                          ->where('producttype_id', 1)
                          ->where('width', $width)
                          ->where('height', $height)
                          ->where('size', $size)
                         ->orderBy($ordered_by, $sort_type)->get();

        $products_arr = $products->pluck('id')->toArray();

        $manufacturers_arr = Product::whereIn('id', $products_arr)->groupBy('manufacturer_id')
                                      ->pluck('manufacturer_id')->toArray();

            $origins_arr = Product::whereIn('id', $products_arr)->groupBy('prodcountry_id')
                                    ->pluck('prodcountry_id')->toArray();

              $manufacturers  = Manufacturer::whereIn('id', $manufacturers_arr)->get();
              $origins        = Prodcountry::whereIn('id', $origins_arr)->get();

              $cats_arr = Product::whereIn('id', $products_arr)->groupBy('allcategory_id')
                                ->pluck('allcategory_id')->toArray();
              $cats        = Allcategory::whereIn('id', $cats_arr)->get();

              foreach ($cats as $cat) {
                $count_cats = Product::whereIn('id', $products_arr)->where('allcategory_id', $cat->id)->count();
                $cat['count_cats'] = $count_cats;
              }
              foreach ($origins as $origin) {
                $count_origins = Product::whereIn('id', $products_arr)->where('prodcountry_id', $origin->id)->count();
                $origin['count_origins'] = $count_origins;
              }
              foreach ($manufacturers as $manufacturer) {
                $count_manufacturers = Product::whereIn('id', $products_arr)->where('manufacturer_id', $manufacturer->id)->count();
                $manufacturer['count_manufacturers'] = $count_manufacturers;
              }
      
              $cats_data = HomeAllcategoryFilterationApiResource::collection($cats);

              $manufacturers_data = ManufacturerFilterationApiResource::collection($manufacturers);
              $origins_data = OriginCountryFilterationApiResource::collection($origins);
                                                        
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
              //$data = ProductSearchApiResource::collection($products->where('lang', $lang));
              $data = ProductSearchApiResource::collection($products);
                $total = count($products);
                              
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                    'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                ], 200);         
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {

            $products = Product::where('approved', 1)
                          ->where('width', $width)
                          ->where('height', $height)
                          ->where('size', $size)

                         ->orderBy($ordered_by, $sort_type)->get();

        $products_arr = $products->pluck('id')->toArray();

        $manufacturers_arr = Product::whereIn('id', $products_arr)->groupBy('manufacturer_id')
                                      ->pluck('manufacturer_id')->toArray();

            $origins_arr = Product::whereIn('id', $products_arr)->groupBy('prodcountry_id')
                                    ->pluck('prodcountry_id')->toArray();

              $manufacturers  = Manufacturer::whereIn('id', $manufacturers_arr)->get();
              $origins        = Prodcountry::whereIn('id', $origins_arr)->get();

              $cats_arr = Product::whereIn('id', $products_arr)->groupBy('allcategory_id')
                                ->pluck('allcategory_id')->toArray();
              $cats        = Allcategory::whereIn('id', $cats_arr)->get();

              foreach ($cats as $cat) {
                $count_cats = Product::whereIn('id', $products_arr)->where('allcategory_id', $cat->id)->count();
                $cat['count_cats'] = $count_cats;
              }
              foreach ($origins as $origin) {
                $count_origins = Product::whereIn('id', $products_arr)->where('prodcountry_id', $origin->id)->count();
                $origin['count_origins'] = $count_origins;
              }
              foreach ($manufacturers as $manufacturer) {
                $count_manufacturers = Product::whereIn('id', $products_arr)->where('manufacturer_id', $manufacturer->id)->count();
                $manufacturer['count_manufacturers'] = $count_manufacturers;
              }
      
              $cats_data = HomeAllcategoryFilterationApiResource::collection($cats);

              $manufacturers_data = ManufacturerFilterationApiResource::collection($manufacturers);
              $origins_data = OriginCountryFilterationApiResource::collection($origins);
                                                        
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
                $data = ProductSearchApiResource::collection($products);
                $total = count($products);
                              
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                    'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                ], 200);  
              
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
          $products = Product::where('approved', 1)->where('producttype_id', 1)
                          ->where('width', $width)
                          ->where('height', $height)
                          ->where('size', $size)
                         ->orderBy($ordered_by, $sort_type)->get();

        $products_arr = $products->pluck('id')->toArray();

        $manufacturers_arr = Product::whereIn('id', $products_arr)->groupBy('manufacturer_id')
                                      ->pluck('manufacturer_id')->toArray();

            $origins_arr = Product::whereIn('id', $products_arr)->groupBy('prodcountry_id')
                                    ->pluck('prodcountry_id')->toArray();

              $manufacturers  = Manufacturer::whereIn('id', $manufacturers_arr)->get();
              $origins        = Prodcountry::whereIn('id', $origins_arr)->get();

              $cats_arr = Product::whereIn('id', $products_arr)->groupBy('allcategory_id')
                                ->pluck('allcategory_id')->toArray();
              $cats        = Allcategory::whereIn('id', $cats_arr)->get();

              foreach ($cats as $cat) {
                $count_cats = Product::whereIn('id', $products_arr)->where('allcategory_id', $cat->id)->count();
                $cat['count_cats'] = $count_cats;
              }
              foreach ($origins as $origin) {
                $count_origins = Product::whereIn('id', $products_arr)->where('prodcountry_id', $origin->id)->count();
                $origin['count_origins'] = $count_origins;
              }
              foreach ($manufacturers as $manufacturer) {
                $count_manufacturers = Product::whereIn('id', $products_arr)->where('manufacturer_id', $manufacturer->id)->count();
                $manufacturer['count_manufacturers'] = $count_manufacturers;
              }
      
              $cats_data = HomeAllcategoryFilterationApiResource::collection($cats);

              $manufacturers_data = ManufacturerFilterationApiResource::collection($manufacturers);
              $origins_data = OriginCountryFilterationApiResource::collection($origins);
                                                        
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
                $data = ProductSearchApiResource::collection($products);
                $total = count($products);
                              
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                    'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                ], 200);  
                    
        } // end guest case
    }

     public function attribute_manufacturers_search($width, $height, $size, $manufacturers, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT)
    {
       if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {

            $products = Product::where('approved', 1)
                          ->where('producttype_id', 1)
                          ->where('width', $width)
                          ->where('height', $height)
                          ->where('size', $size)
                          ->whereIn('manufacturer_id', $manufacturers)

                         ->orderBy($ordered_by, $sort_type)->get();

        $products_arr = $products->pluck('id')->toArray();

        $manufacturers_arr = Product::whereIn('id', $products_arr)->groupBy('manufacturer_id')
                                      ->pluck('manufacturer_id')->toArray();

            $origins_arr = Product::whereIn('id', $products_arr)->groupBy('prodcountry_id')
                                    ->pluck('prodcountry_id')->toArray();

              $manufacturers  = Manufacturer::whereIn('id', $manufacturers_arr)->get();
              $origins        = Prodcountry::whereIn('id', $origins_arr)->get();

              $cats_arr = Product::whereIn('id', $products_arr)->groupBy('allcategory_id')
                                ->pluck('allcategory_id')->toArray();
              $cats        = Allcategory::whereIn('id', $cats_arr)->get();

              foreach ($cats as $cat) {
                $count_cats = Product::whereIn('id', $products_arr)->where('allcategory_id', $cat->id)->count();
                $cat['count_cats'] = $count_cats;
              }
              foreach ($origins as $origin) {
                $count_origins = Product::whereIn('id', $products_arr)->where('prodcountry_id', $origin->id)->count();
                $origin['count_origins'] = $count_origins;
              }
              foreach ($manufacturers as $manufacturer) {
                $count_manufacturers = Product::whereIn('id', $products_arr)->where('manufacturer_id', $manufacturer->id)->count();
                $manufacturer['count_manufacturers'] = $count_manufacturers;
              }
      
              $cats_data = HomeAllcategoryFilterationApiResource::collection($cats);

              $manufacturers_data = ManufacturerFilterationApiResource::collection($manufacturers);
              $origins_data = OriginCountryFilterationApiResource::collection($origins);
                                                        
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
              //$data = ProductSearchApiResource::collection($products->where('lang', $lang));
              $data = ProductSearchApiResource::collection($products);
                $total = count($products);
                              
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                    'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                ], 200);         
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {

            $products = Product::where('approved', 1)
                          ->where('width', $width)
                          ->where('height', $height)
                          ->where('size', $size)
                          ->whereIn('manufacturer_id', $manufacturers)

                         ->orderBy($ordered_by, $sort_type)->get();

        $products_arr = $products->pluck('id')->toArray();

        $manufacturers_arr = Product::whereIn('id', $products_arr)->groupBy('manufacturer_id')
                                      ->pluck('manufacturer_id')->toArray();

            $origins_arr = Product::whereIn('id', $products_arr)->groupBy('prodcountry_id')
                                    ->pluck('prodcountry_id')->toArray();

              $manufacturers  = Manufacturer::whereIn('id', $manufacturers_arr)->get();
              $origins        = Prodcountry::whereIn('id', $origins_arr)->get();

              $cats_arr = Product::whereIn('id', $products_arr)->groupBy('allcategory_id')
                                ->pluck('allcategory_id')->toArray();
              $cats        = Allcategory::whereIn('id', $cats_arr)->get();

              foreach ($cats as $cat) {
                $count_cats = Product::whereIn('id', $products_arr)->where('allcategory_id', $cat->id)->count();
                $cat['count_cats'] = $count_cats;
              }
              foreach ($origins as $origin) {
                $count_origins = Product::whereIn('id', $products_arr)->where('prodcountry_id', $origin->id)->count();
                $origin['count_origins'] = $count_origins;
              }
              foreach ($manufacturers as $manufacturer) {
                $count_manufacturers = Product::whereIn('id', $products_arr)->where('manufacturer_id', $manufacturer->id)->count();
                $manufacturer['count_manufacturers'] = $count_manufacturers;
              }
      
              $cats_data = HomeAllcategoryFilterationApiResource::collection($cats);

              $manufacturers_data = ManufacturerFilterationApiResource::collection($manufacturers);
              $origins_data = OriginCountryFilterationApiResource::collection($origins);
                                                        
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
                $data = ProductSearchApiResource::collection($products);
                $total = count($products);
                              
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                    'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                ], 200);  
              
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
          $products = Product::where('approved', 1)->where('producttype_id', 1)
                          ->where('width', $width)
                          ->where('height', $height)
                          ->where('size', $size)
                          ->whereIn('manufacturer_id', $manufacturers)
                         ->orderBy($ordered_by, $sort_type)->get();

        $products_arr = $products->pluck('id')->toArray();

        $manufacturers_arr = Product::whereIn('id', $products_arr)->groupBy('manufacturer_id')
                                      ->pluck('manufacturer_id')->toArray();

            $origins_arr = Product::whereIn('id', $products_arr)->groupBy('prodcountry_id')
                                    ->pluck('prodcountry_id')->toArray();

              $manufacturers  = Manufacturer::whereIn('id', $manufacturers_arr)->get();
              $origins        = Prodcountry::whereIn('id', $origins_arr)->get();

              $cats_arr = Product::whereIn('id', $products_arr)->groupBy('allcategory_id')
                                ->pluck('allcategory_id')->toArray();
              $cats        = Allcategory::whereIn('id', $cats_arr)->get();

              foreach ($cats as $cat) {
                $count_cats = Product::whereIn('id', $products_arr)->where('allcategory_id', $cat->id)->count();
                $cat['count_cats'] = $count_cats;
              }
              foreach ($origins as $origin) {
                $count_origins = Product::whereIn('id', $products_arr)->where('prodcountry_id', $origin->id)->count();
                $origin['count_origins'] = $count_origins;
              }
              foreach ($manufacturers as $manufacturer) {
                $count_manufacturers = Product::whereIn('id', $products_arr)->where('manufacturer_id', $manufacturer->id)->count();
                $manufacturer['count_manufacturers'] = $count_manufacturers;
              }
      
              $cats_data = HomeAllcategoryFilterationApiResource::collection($cats);

              $manufacturers_data = ManufacturerFilterationApiResource::collection($manufacturers);
              $origins_data = OriginCountryFilterationApiResource::collection($origins);
                                                        
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
                $data = ProductSearchApiResource::collection($products);
                $total = count($products);
                              
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                    'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                ], 200);  
                    
        } // end guest case
    }

     public function attribute_origins_search($width, $height, $size, $origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
    {
       if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {

            $products = Product::where('approved', 1)
                          ->where('producttype_id', 1)
                          ->where('width', $width)
                          ->where('height', $height)
                          ->where('size', $size)
                          ->whereIn('prodcountry_id', $origins)

                         ->orderBy($ordered_by, $sort_type)->get();

        $products_arr = $products->pluck('id')->toArray();

        $manufacturers_arr = Product::whereIn('id', $products_arr)->groupBy('manufacturer_id')
                                      ->pluck('manufacturer_id')->toArray();

            $origins_arr = Product::whereIn('id', $products_arr)->groupBy('prodcountry_id')
                                    ->pluck('prodcountry_id')->toArray();

              $manufacturers  = Manufacturer::whereIn('id', $manufacturers_arr)->get();
              $origins        = Prodcountry::whereIn('id', $origins_arr)->get();

              $cats_arr = Product::whereIn('id', $products_arr)->groupBy('allcategory_id')
                                ->pluck('allcategory_id')->toArray();
              $cats        = Allcategory::whereIn('id', $cats_arr)->get();

              foreach ($cats as $cat) {
                $count_cats = Product::whereIn('id', $products_arr)->where('allcategory_id', $cat->id)->count();
                $cat['count_cats'] = $count_cats;
              }
              foreach ($origins as $origin) {
                $count_origins = Product::whereIn('id', $products_arr)->where('prodcountry_id', $origin->id)->count();
                $origin['count_origins'] = $count_origins;
              }
              foreach ($manufacturers as $manufacturer) {
                $count_manufacturers = Product::whereIn('id', $products_arr)->where('manufacturer_id', $manufacturer->id)->count();
                $manufacturer['count_manufacturers'] = $count_manufacturers;
              }
      
              $cats_data = HomeAllcategoryFilterationApiResource::collection($cats);

              $manufacturers_data = ManufacturerFilterationApiResource::collection($manufacturers);
              $origins_data = OriginCountryFilterationApiResource::collection($origins);
                                                        
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
              //$data = ProductSearchApiResource::collection($products->where('lang', $lang));
              $data = ProductSearchApiResource::collection($products);
                $total = count($products);
                              
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                    'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                ], 200);         
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {

            $products = Product::where('approved', 1)
                          ->where('width', $width)
                          ->where('height', $height)
                          ->where('size', $size)
                          ->whereIn('prodcountry_id', $origins)

                         ->orderBy($ordered_by, $sort_type)->get();

        $products_arr = $products->pluck('id')->toArray();

        $manufacturers_arr = Product::whereIn('id', $products_arr)->groupBy('manufacturer_id')
                                      ->pluck('manufacturer_id')->toArray();

            $origins_arr = Product::whereIn('id', $products_arr)->groupBy('prodcountry_id')
                                    ->pluck('prodcountry_id')->toArray();

              $manufacturers  = Manufacturer::whereIn('id', $manufacturers_arr)->get();
              $origins        = Prodcountry::whereIn('id', $origins_arr)->get();

              $cats_arr = Product::whereIn('id', $products_arr)->groupBy('allcategory_id')
                                ->pluck('allcategory_id')->toArray();
              $cats        = Allcategory::whereIn('id', $cats_arr)->get();

              foreach ($cats as $cat) {
                $count_cats = Product::whereIn('id', $products_arr)->where('allcategory_id', $cat->id)->count();
                $cat['count_cats'] = $count_cats;
              }
              foreach ($origins as $origin) {
                $count_origins = Product::whereIn('id', $products_arr)->where('prodcountry_id', $origin->id)->count();
                $origin['count_origins'] = $count_origins;
              }
              foreach ($manufacturers as $manufacturer) {
                $count_manufacturers = Product::whereIn('id', $products_arr)->where('manufacturer_id', $manufacturer->id)->count();
                $manufacturer['count_manufacturers'] = $count_manufacturers;
              }
      
              $cats_data = HomeAllcategoryFilterationApiResource::collection($cats);

              $manufacturers_data = ManufacturerFilterationApiResource::collection($manufacturers);
              $origins_data = OriginCountryFilterationApiResource::collection($origins);
                                                        
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
                $data = ProductSearchApiResource::collection($products);
                $total = count($products);
                              
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                    'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                ], 200);  
              
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
          $products = Product::where('approved', 1)
                          ->where('producttype_id', 1)
                          ->where('width', $width)
                          ->where('height', $height)
                          ->where('size', $size)
                          ->whereIn('prodcountry_id', $origins)
                         ->orderBy($ordered_by, $sort_type)->get();

        $products_arr = $products->pluck('id')->toArray();

        $manufacturers_arr = Product::whereIn('id', $products_arr)->groupBy('manufacturer_id')
                                      ->pluck('manufacturer_id')->toArray();

            $origins_arr = Product::whereIn('id', $products_arr)->groupBy('prodcountry_id')
                                    ->pluck('prodcountry_id')->toArray();

              $manufacturers  = Manufacturer::whereIn('id', $manufacturers_arr)->get();
              $origins        = Prodcountry::whereIn('id', $origins_arr)->get();

              $cats_arr = Product::whereIn('id', $products_arr)->groupBy('allcategory_id')
                                ->pluck('allcategory_id')->toArray();
              $cats        = Allcategory::whereIn('id', $cats_arr)->get();

              foreach ($cats as $cat) {
                $count_cats = Product::whereIn('id', $products_arr)->where('allcategory_id', $cat->id)->count();
                $cat['count_cats'] = $count_cats;
              }
              foreach ($origins as $origin) {
                $count_origins = Product::whereIn('id', $products_arr)->where('prodcountry_id', $origin->id)->count();
                $origin['count_origins'] = $count_origins;
              }
              foreach ($manufacturers as $manufacturer) {
                $count_manufacturers = Product::whereIn('id', $products_arr)->where('manufacturer_id', $manufacturer->id)->count();
                $manufacturer['count_manufacturers'] = $count_manufacturers;
              }
      
              $cats_data = HomeAllcategoryFilterationApiResource::collection($cats);

              $manufacturers_data = ManufacturerFilterationApiResource::collection($manufacturers);
              $origins_data = OriginCountryFilterationApiResource::collection($origins);
                                                        
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
                $data = ProductSearchApiResource::collection($products);
                $total = count($products);
                              
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                    'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                ], 200);  
                    
        } // end guest case
    }

     public function attribute_price_search($width, $height, $size, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT)
    {
       if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {

            $products = Product::where('approved', 1)
                          ->where('producttype_id', 1)
                          ->where('width', $width)
                          ->where('height', $height)
                          ->where('size', $size)
                          ->where('actual_price', '>=', $start_price)
                          ->where('actual_price', '<=', $end_price)

                         ->orderBy($ordered_by, $sort_type)->get();

        $products_arr = $products->pluck('id')->toArray();

        $manufacturers_arr = Product::whereIn('id', $products_arr)->groupBy('manufacturer_id')
                                      ->pluck('manufacturer_id')->toArray();

            $origins_arr = Product::whereIn('id', $products_arr)->groupBy('prodcountry_id')
                                    ->pluck('prodcountry_id')->toArray();

              $manufacturers  = Manufacturer::whereIn('id', $manufacturers_arr)->get();
              $origins        = Prodcountry::whereIn('id', $origins_arr)->get();

              $cats_arr = Product::whereIn('id', $products_arr)->groupBy('allcategory_id')
                                ->pluck('allcategory_id')->toArray();
              $cats        = Allcategory::whereIn('id', $cats_arr)->get();

              foreach ($cats as $cat) {
                $count_cats = Product::whereIn('id', $products_arr)->where('allcategory_id', $cat->id)->count();
                $cat['count_cats'] = $count_cats;
              }
              foreach ($origins as $origin) {
                $count_origins = Product::whereIn('id', $products_arr)->where('prodcountry_id', $origin->id)->count();
                $origin['count_origins'] = $count_origins;
              }
              foreach ($manufacturers as $manufacturer) {
                $count_manufacturers = Product::whereIn('id', $products_arr)->where('manufacturer_id', $manufacturer->id)->count();
                $manufacturer['count_manufacturers'] = $count_manufacturers;
              }
      
              $cats_data = HomeAllcategoryFilterationApiResource::collection($cats);

              $manufacturers_data = ManufacturerFilterationApiResource::collection($manufacturers);
              $origins_data = OriginCountryFilterationApiResource::collection($origins);
                                                        
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
              //$data = ProductSearchApiResource::collection($products->where('lang', $lang));
              $data = ProductSearchApiResource::collection($products);
                $total = count($products);
                              
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                    'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                ], 200);         
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {

            $products = Product::where('approved', 1)
                          ->where('width', $width)
                          ->where('height', $height)
                          ->where('size', $size)
                          ->where('actual_price', '>=', $start_price)
                          ->where('actual_price', '<=', $end_price)

                         ->orderBy($ordered_by, $sort_type)->get();

        $products_arr = $products->pluck('id')->toArray();

        $manufacturers_arr = Product::whereIn('id', $products_arr)->groupBy('manufacturer_id')
                                      ->pluck('manufacturer_id')->toArray();

            $origins_arr = Product::whereIn('id', $products_arr)->groupBy('prodcountry_id')
                                    ->pluck('prodcountry_id')->toArray();

              $manufacturers  = Manufacturer::whereIn('id', $manufacturers_arr)->get();
              $origins        = Prodcountry::whereIn('id', $origins_arr)->get();

              $cats_arr = Product::whereIn('id', $products_arr)->groupBy('allcategory_id')
                                ->pluck('allcategory_id')->toArray();
              $cats        = Allcategory::whereIn('id', $cats_arr)->get();

              foreach ($cats as $cat) {
                $count_cats = Product::whereIn('id', $products_arr)->where('allcategory_id', $cat->id)->count();
                $cat['count_cats'] = $count_cats;
              }
              foreach ($origins as $origin) {
                $count_origins = Product::whereIn('id', $products_arr)->where('prodcountry_id', $origin->id)->count();
                $origin['count_origins'] = $count_origins;
              }
              foreach ($manufacturers as $manufacturer) {
                $count_manufacturers = Product::whereIn('id', $products_arr)->where('manufacturer_id', $manufacturer->id)->count();
                $manufacturer['count_manufacturers'] = $count_manufacturers;
              }
      
              $cats_data = HomeAllcategoryFilterationApiResource::collection($cats);

              $manufacturers_data = ManufacturerFilterationApiResource::collection($manufacturers);
              $origins_data = OriginCountryFilterationApiResource::collection($origins);
                                                        
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
                $data = ProductSearchApiResource::collection($products);
                $total = count($products);
                              
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                    'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                ], 200);  
              
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
          $products = Product::where('approved', 1)->where('producttype_id', 1)
                          ->where('width', $width)
                          ->where('height', $height)
                          ->where('size', $size)
                          ->where('actual_price', '>=', $start_price)
                          ->where('actual_price', '<=', $end_price)
                         ->orderBy($ordered_by, $sort_type)->get();

        $products_arr = $products->pluck('id')->toArray();

        $manufacturers_arr = Product::whereIn('id', $products_arr)->groupBy('manufacturer_id')
                                      ->pluck('manufacturer_id')->toArray();

            $origins_arr = Product::whereIn('id', $products_arr)->groupBy('prodcountry_id')
                                    ->pluck('prodcountry_id')->toArray();

              $manufacturers  = Manufacturer::whereIn('id', $manufacturers_arr)->get();
              $origins        = Prodcountry::whereIn('id', $origins_arr)->get();

              $cats_arr = Product::whereIn('id', $products_arr)->groupBy('allcategory_id')
                                ->pluck('allcategory_id')->toArray();
              $cats        = Allcategory::whereIn('id', $cats_arr)->get();

              foreach ($cats as $cat) {
                $count_cats = Product::whereIn('id', $products_arr)->where('allcategory_id', $cat->id)->count();
                $cat['count_cats'] = $count_cats;
              }
              foreach ($origins as $origin) {
                $count_origins = Product::whereIn('id', $products_arr)->where('prodcountry_id', $origin->id)->count();
                $origin['count_origins'] = $count_origins;
              }
              foreach ($manufacturers as $manufacturer) {
                $count_manufacturers = Product::whereIn('id', $products_arr)->where('manufacturer_id', $manufacturer->id)->count();
                $manufacturer['count_manufacturers'] = $count_manufacturers;
              }
      
              $cats_data = HomeAllcategoryFilterationApiResource::collection($cats);

              $manufacturers_data = ManufacturerFilterationApiResource::collection($manufacturers);
              $origins_data = OriginCountryFilterationApiResource::collection($origins);
                                                        
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
                $data = ProductSearchApiResource::collection($products);
                $total = count($products);
                              
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                    'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                ], 200);  
                    
        } // end guest case
    }

     public function attribute_manufacturers_origins_search($width, $height, $size, $origins, $manufacturers, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT)
    {
       if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {

            $products = Product::where('approved', 1)
                          ->where('producttype_id', 1)
                          ->where('width', $width)
                          ->where('height', $height)
                          ->where('size', $size)
                          ->whereIn('prodcountry_id', $origins)
                          ->whereIn('manufacturer_id', $manufacturers)

                         ->orderBy($ordered_by, $sort_type)->get();

        $products_arr = $products->pluck('id')->toArray();

        $manufacturers_arr = Product::whereIn('id', $products_arr)->groupBy('manufacturer_id')
                                      ->pluck('manufacturer_id')->toArray();

            $origins_arr = Product::whereIn('id', $products_arr)->groupBy('prodcountry_id')
                                    ->pluck('prodcountry_id')->toArray();

              $manufacturers  = Manufacturer::whereIn('id', $manufacturers_arr)->get();
              $origins        = Prodcountry::whereIn('id', $origins_arr)->get();

              $cats_arr = Product::whereIn('id', $products_arr)->groupBy('allcategory_id')
                                ->pluck('allcategory_id')->toArray();
              $cats        = Allcategory::whereIn('id', $cats_arr)->get();

              foreach ($cats as $cat) {
                $count_cats = Product::whereIn('id', $products_arr)->where('allcategory_id', $cat->id)->count();
                $cat['count_cats'] = $count_cats;
              }
              foreach ($origins as $origin) {
                $count_origins = Product::whereIn('id', $products_arr)->where('prodcountry_id', $origin->id)->count();
                $origin['count_origins'] = $count_origins;
              }
              foreach ($manufacturers as $manufacturer) {
                $count_manufacturers = Product::whereIn('id', $products_arr)->where('manufacturer_id', $manufacturer->id)->count();
                $manufacturer['count_manufacturers'] = $count_manufacturers;
              }
      
              $cats_data = HomeAllcategoryFilterationApiResource::collection($cats);

              $manufacturers_data = ManufacturerFilterationApiResource::collection($manufacturers);
              $origins_data = OriginCountryFilterationApiResource::collection($origins);
                                                        
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
              //$data = ProductSearchApiResource::collection($products->where('lang', $lang));
              $data = ProductSearchApiResource::collection($products);
                $total = count($products);
                              
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                    'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                ], 200);         
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {

            $products = Product::where('approved', 1)
                          ->where('width', $width)
                          ->where('height', $height)
                          ->where('size', $size)
                          ->whereIn('prodcountry_id', $origins)
                          ->whereIn('manufacturer_id', $manufacturers)

                         ->orderBy($ordered_by, $sort_type)->get();

        $products_arr = $products->pluck('id')->toArray();

        $manufacturers_arr = Product::whereIn('id', $products_arr)->groupBy('manufacturer_id')
                                      ->pluck('manufacturer_id')->toArray();

            $origins_arr = Product::whereIn('id', $products_arr)->groupBy('prodcountry_id')
                                    ->pluck('prodcountry_id')->toArray();

              $manufacturers  = Manufacturer::whereIn('id', $manufacturers_arr)->get();
              $origins        = Prodcountry::whereIn('id', $origins_arr)->get();

              $cats_arr = Product::whereIn('id', $products_arr)->groupBy('allcategory_id')
                                ->pluck('allcategory_id')->toArray();
              $cats        = Allcategory::whereIn('id', $cats_arr)->get();

              foreach ($cats as $cat) {
                $count_cats = Product::whereIn('id', $products_arr)->where('allcategory_id', $cat->id)->count();
                $cat['count_cats'] = $count_cats;
              }
              foreach ($origins as $origin) {
                $count_origins = Product::whereIn('id', $products_arr)->where('prodcountry_id', $origin->id)->count();
                $origin['count_origins'] = $count_origins;
              }
              foreach ($manufacturers as $manufacturer) {
                $count_manufacturers = Product::whereIn('id', $products_arr)->where('manufacturer_id', $manufacturer->id)->count();
                $manufacturer['count_manufacturers'] = $count_manufacturers;
              }
      
              $cats_data = HomeAllcategoryFilterationApiResource::collection($cats);

              $manufacturers_data = ManufacturerFilterationApiResource::collection($manufacturers);
              $origins_data = OriginCountryFilterationApiResource::collection($origins);
                                                        
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
                $data = ProductSearchApiResource::collection($products);
                $total = count($products);
                              
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                    'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                ], 200);  
              
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
          $products = Product::where('approved', 1)->where('producttype_id', 1)
                          ->where('width', $width)
                          ->where('height', $height)
                          ->where('size', $size)
                          ->whereIn('prodcountry_id', $origins)
                          ->whereIn('manufacturer_id', $manufacturers)
                         ->orderBy($ordered_by, $sort_type)->get();

        $products_arr = $products->pluck('id')->toArray();

        $manufacturers_arr = Product::whereIn('id', $products_arr)->groupBy('manufacturer_id')
                                      ->pluck('manufacturer_id')->toArray();

            $origins_arr = Product::whereIn('id', $products_arr)->groupBy('prodcountry_id')
                                    ->pluck('prodcountry_id')->toArray();

              $manufacturers  = Manufacturer::whereIn('id', $manufacturers_arr)->get();
              $origins        = Prodcountry::whereIn('id', $origins_arr)->get();

              $cats_arr = Product::whereIn('id', $products_arr)->groupBy('allcategory_id')
                                ->pluck('allcategory_id')->toArray();
              $cats        = Allcategory::whereIn('id', $cats_arr)->get();

              foreach ($cats as $cat) {
                $count_cats = Product::whereIn('id', $products_arr)->where('allcategory_id', $cat->id)->count();
                $cat['count_cats'] = $count_cats;
              }
              foreach ($origins as $origin) {
                $count_origins = Product::whereIn('id', $products_arr)->where('prodcountry_id', $origin->id)->count();
                $origin['count_origins'] = $count_origins;
              }
              foreach ($manufacturers as $manufacturer) {
                $count_manufacturers = Product::whereIn('id', $products_arr)->where('manufacturer_id', $manufacturer->id)->count();
                $manufacturer['count_manufacturers'] = $count_manufacturers;
              }
      
              $cats_data = HomeAllcategoryFilterationApiResource::collection($cats);

              $manufacturers_data = ManufacturerFilterationApiResource::collection($manufacturers);
              $origins_data = OriginCountryFilterationApiResource::collection($origins);
                                                        
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
                $data = ProductSearchApiResource::collection($products);
                $total = count($products);
                              
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                    'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                ], 200);  
                    
        } // end guest case
    }

    public function attribute_manufacturers_price_search($width, $height, $size, $manufacturers, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT)
    {
       if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {

            $products = Product::where('approved', 1)
                          ->where('producttype_id', 1)
                          ->where('width', $width)
                          ->where('height', $height)
                          ->where('size', $size)
                          ->whereIn('manufacturer_id', $manufacturers)
                          ->where('actual_price', '>=', $start_price)
                          ->where('actual_price', '<=', $end_price)

                         ->orderBy($ordered_by, $sort_type)->get();

        $products_arr = $products->pluck('id')->toArray();

        $manufacturers_arr = Product::whereIn('id', $products_arr)->groupBy('manufacturer_id')
                                      ->pluck('manufacturer_id')->toArray();

            $origins_arr = Product::whereIn('id', $products_arr)->groupBy('prodcountry_id')
                                    ->pluck('prodcountry_id')->toArray();

              $manufacturers  = Manufacturer::whereIn('id', $manufacturers_arr)->get();
              $origins        = Prodcountry::whereIn('id', $origins_arr)->get();

              $cats_arr = Product::whereIn('id', $products_arr)->groupBy('allcategory_id')
                                ->pluck('allcategory_id')->toArray();
              $cats        = Allcategory::whereIn('id', $cats_arr)->get();

              foreach ($cats as $cat) {
                $count_cats = Product::whereIn('id', $products_arr)->where('allcategory_id', $cat->id)->count();
                $cat['count_cats'] = $count_cats;
              }
              foreach ($origins as $origin) {
                $count_origins = Product::whereIn('id', $products_arr)->where('prodcountry_id', $origin->id)->count();
                $origin['count_origins'] = $count_origins;
              }
              foreach ($manufacturers as $manufacturer) {
                $count_manufacturers = Product::whereIn('id', $products_arr)->where('manufacturer_id', $manufacturer->id)->count();
                $manufacturer['count_manufacturers'] = $count_manufacturers;
              }
      
              $cats_data = HomeAllcategoryFilterationApiResource::collection($cats);

              $manufacturers_data = ManufacturerFilterationApiResource::collection($manufacturers);
              $origins_data = OriginCountryFilterationApiResource::collection($origins);
                                                        
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
              //$data = ProductSearchApiResource::collection($products->where('lang', $lang));
              $data = ProductSearchApiResource::collection($products);
                $total = count($products);
                              
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                    'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                ], 200);         
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {

            $products = Product::where('approved', 1)
                          ->where('width', $width)
                          ->where('height', $height)
                          ->where('size', $size)
                          ->whereIn('manufacturer_id', $manufacturers)
                          ->where('actual_price', '>=', $start_price)
                          ->where('actual_price', '<=', $end_price)

                         ->orderBy($ordered_by, $sort_type)->get();

        $products_arr = $products->pluck('id')->toArray();

        $manufacturers_arr = Product::whereIn('id', $products_arr)->groupBy('manufacturer_id')
                                      ->pluck('manufacturer_id')->toArray();

            $origins_arr = Product::whereIn('id', $products_arr)->groupBy('prodcountry_id')
                                    ->pluck('prodcountry_id')->toArray();

              $manufacturers  = Manufacturer::whereIn('id', $manufacturers_arr)->get();
              $origins        = Prodcountry::whereIn('id', $origins_arr)->get();

              $cats_arr = Product::whereIn('id', $products_arr)->groupBy('allcategory_id')
                                ->pluck('allcategory_id')->toArray();
              $cats        = Allcategory::whereIn('id', $cats_arr)->get();

              foreach ($cats as $cat) {
                $count_cats = Product::whereIn('id', $products_arr)->where('allcategory_id', $cat->id)->count();
                $cat['count_cats'] = $count_cats;
              }
              foreach ($origins as $origin) {
                $count_origins = Product::whereIn('id', $products_arr)->where('prodcountry_id', $origin->id)->count();
                $origin['count_origins'] = $count_origins;
              }
              foreach ($manufacturers as $manufacturer) {
                $count_manufacturers = Product::whereIn('id', $products_arr)->where('manufacturer_id', $manufacturer->id)->count();
                $manufacturer['count_manufacturers'] = $count_manufacturers;
              }
      
              $cats_data = HomeAllcategoryFilterationApiResource::collection($cats);

              $manufacturers_data = ManufacturerFilterationApiResource::collection($manufacturers);
              $origins_data = OriginCountryFilterationApiResource::collection($origins);
                                                        
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
                $data = ProductSearchApiResource::collection($products);
                $total = count($products);
                              
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                    'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                ], 200);  
              
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
          $products = Product::where('approved', 1)->where('producttype_id', 1)
                          ->where('width', $width)
                          ->where('height', $height)
                          ->where('size', $size)
                          ->whereIn('manufacturer_id', $manufacturers)
                          ->where('actual_price', '>=', $start_price)
                          ->where('actual_price', '<=', $end_price)
                         ->orderBy($ordered_by, $sort_type)->get();

        $products_arr = $products->pluck('id')->toArray();

        $manufacturers_arr = Product::whereIn('id', $products_arr)->groupBy('manufacturer_id')
                                      ->pluck('manufacturer_id')->toArray();

            $origins_arr = Product::whereIn('id', $products_arr)->groupBy('prodcountry_id')
                                    ->pluck('prodcountry_id')->toArray();

              $manufacturers  = Manufacturer::whereIn('id', $manufacturers_arr)->get();
              $origins        = Prodcountry::whereIn('id', $origins_arr)->get();

              $cats_arr = Product::whereIn('id', $products_arr)->groupBy('allcategory_id')
                                ->pluck('allcategory_id')->toArray();
              $cats        = Allcategory::whereIn('id', $cats_arr)->get();

              foreach ($cats as $cat) {
                $count_cats = Product::whereIn('id', $products_arr)->where('allcategory_id', $cat->id)->count();
                $cat['count_cats'] = $count_cats;
              }
              foreach ($origins as $origin) {
                $count_origins = Product::whereIn('id', $products_arr)->where('prodcountry_id', $origin->id)->count();
                $origin['count_origins'] = $count_origins;
              }
              foreach ($manufacturers as $manufacturer) {
                $count_manufacturers = Product::whereIn('id', $products_arr)->where('manufacturer_id', $manufacturer->id)->count();
                $manufacturer['count_manufacturers'] = $count_manufacturers;
              }
      
              $cats_data = HomeAllcategoryFilterationApiResource::collection($cats);

              $manufacturers_data = ManufacturerFilterationApiResource::collection($manufacturers);
              $origins_data = OriginCountryFilterationApiResource::collection($origins);
                                                        
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
                $data = ProductSearchApiResource::collection($products);
                $total = count($products);
                              
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                    'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                ], 200);  
                    
        } // end guest case
    }

    public function attribute_origins_price_search($width, $height, $size, $origins, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT)
    {
       if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {

            $products = Product::where('approved', 1)
                          ->where('producttype_id', 1)
                          ->where('width', $width)
                          ->where('height', $height)
                          ->where('size', $size)
                          ->whereIn('prodcountry_id', $origins)
                          ->where('actual_price', '>=', $start_price)
                          ->where('actual_price', '<=', $end_price)

                         ->orderBy($ordered_by, $sort_type)->get();

        $products_arr = $products->pluck('id')->toArray();

        $manufacturers_arr = Product::whereIn('id', $products_arr)->groupBy('manufacturer_id')
                                      ->pluck('manufacturer_id')->toArray();

            $origins_arr = Product::whereIn('id', $products_arr)->groupBy('prodcountry_id')
                                    ->pluck('prodcountry_id')->toArray();

              $manufacturers  = Manufacturer::whereIn('id', $manufacturers_arr)->get();
              $origins        = Prodcountry::whereIn('id', $origins_arr)->get();

              $cats_arr = Product::whereIn('id', $products_arr)->groupBy('allcategory_id')
                                ->pluck('allcategory_id')->toArray();
              $cats        = Allcategory::whereIn('id', $cats_arr)->get();

              foreach ($cats as $cat) {
                $count_cats = Product::whereIn('id', $products_arr)->where('allcategory_id', $cat->id)->count();
                $cat['count_cats'] = $count_cats;
              }
              foreach ($origins as $origin) {
                $count_origins = Product::whereIn('id', $products_arr)->where('prodcountry_id', $origin->id)->count();
                $origin['count_origins'] = $count_origins;
              }
              foreach ($manufacturers as $manufacturer) {
                $count_manufacturers = Product::whereIn('id', $products_arr)->where('manufacturer_id', $manufacturer->id)->count();
                $manufacturer['count_manufacturers'] = $count_manufacturers;
              }
      
              $cats_data = HomeAllcategoryFilterationApiResource::collection($cats);

              $manufacturers_data = ManufacturerFilterationApiResource::collection($manufacturers);
              $origins_data = OriginCountryFilterationApiResource::collection($origins);
                                                        
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
              //$data = ProductSearchApiResource::collection($products->where('lang', $lang));
              $data = ProductSearchApiResource::collection($products);
                $total = count($products);
                              
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                    'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                ], 200);         
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {

            $products = Product::where('approved', 1)
                          ->where('width', $width)
                          ->where('height', $height)
                          ->where('size', $size)
                          ->whereIn('prodcountry_id', $origins)
                          ->where('actual_price', '>=', $start_price)
                          ->where('actual_price', '<=', $end_price)

                         ->orderBy($ordered_by, $sort_type)->get();

        $products_arr = $products->pluck('id')->toArray();

        $manufacturers_arr = Product::whereIn('id', $products_arr)->groupBy('manufacturer_id')
                                      ->pluck('manufacturer_id')->toArray();

            $origins_arr = Product::whereIn('id', $products_arr)->groupBy('prodcountry_id')
                                    ->pluck('prodcountry_id')->toArray();

              $manufacturers  = Manufacturer::whereIn('id', $manufacturers_arr)->get();
              $origins        = Prodcountry::whereIn('id', $origins_arr)->get();

              $cats_arr = Product::whereIn('id', $products_arr)->groupBy('allcategory_id')
                                ->pluck('allcategory_id')->toArray();
              $cats        = Allcategory::whereIn('id', $cats_arr)->get();

              foreach ($cats as $cat) {
                $count_cats = Product::whereIn('id', $products_arr)->where('allcategory_id', $cat->id)->count();
                $cat['count_cats'] = $count_cats;
              }
              foreach ($origins as $origin) {
                $count_origins = Product::whereIn('id', $products_arr)->where('prodcountry_id', $origin->id)->count();
                $origin['count_origins'] = $count_origins;
              }
              foreach ($manufacturers as $manufacturer) {
                $count_manufacturers = Product::whereIn('id', $products_arr)->where('manufacturer_id', $manufacturer->id)->count();
                $manufacturer['count_manufacturers'] = $count_manufacturers;
              }
      
              $cats_data = HomeAllcategoryFilterationApiResource::collection($cats);

              $manufacturers_data = ManufacturerFilterationApiResource::collection($manufacturers);
              $origins_data = OriginCountryFilterationApiResource::collection($origins);
                                                        
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
                $data = ProductSearchApiResource::collection($products);
                $total = count($products);
                              
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                    'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                ], 200);  
              
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
          $products = Product::where('approved', 1)->where('producttype_id', 1)
                          ->where('width', $width)
                          ->where('height', $height)
                          ->where('size', $size)
                          ->whereIn('prodcountry_id', $origins)
                          ->where('actual_price', '>=', $start_price)
                          ->where('actual_price', '<=', $end_price)
                         ->orderBy($ordered_by, $sort_type)->get();

        $products_arr = $products->pluck('id')->toArray();

        $manufacturers_arr = Product::whereIn('id', $products_arr)->groupBy('manufacturer_id')
                                      ->pluck('manufacturer_id')->toArray();

            $origins_arr = Product::whereIn('id', $products_arr)->groupBy('prodcountry_id')
                                    ->pluck('prodcountry_id')->toArray();

              $manufacturers  = Manufacturer::whereIn('id', $manufacturers_arr)->get();
              $origins        = Prodcountry::whereIn('id', $origins_arr)->get();

              $cats_arr = Product::whereIn('id', $products_arr)->groupBy('allcategory_id')
                                ->pluck('allcategory_id')->toArray();
              $cats        = Allcategory::whereIn('id', $cats_arr)->get();

              foreach ($cats as $cat) {
                $count_cats = Product::whereIn('id', $products_arr)->where('allcategory_id', $cat->id)->count();
                $cat['count_cats'] = $count_cats;
              }
              foreach ($origins as $origin) {
                $count_origins = Product::whereIn('id', $products_arr)->where('prodcountry_id', $origin->id)->count();
                $origin['count_origins'] = $count_origins;
              }
              foreach ($manufacturers as $manufacturer) {
                $count_manufacturers = Product::whereIn('id', $products_arr)->where('manufacturer_id', $manufacturer->id)->count();
                $manufacturer['count_manufacturers'] = $count_manufacturers;
              }
      
              $cats_data = HomeAllcategoryFilterationApiResource::collection($cats);

              $manufacturers_data = ManufacturerFilterationApiResource::collection($manufacturers);
              $origins_data = OriginCountryFilterationApiResource::collection($origins);
                                                        
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
                $data = ProductSearchApiResource::collection($products);
                $total = count($products);
                              
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                    'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                ], 200);  
                    
        } // end guest case
    }

    public function all_search($width, $height, $size, $origins, $manufacturers, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT)
    {
       if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {

            $products = Product::where('approved', 1)
                          ->where('producttype_id', 1)
                          ->where('width', $width)
                          ->where('height', $height)
                          ->where('size', $size)
                          ->whereIn('prodcountry_id', $origins)
                          ->whereIn('manufacturer_id', $manufacturers)
                          ->where('actual_price', '>=', $start_price)
                          ->where('actual_price', '<=', $end_price)

                         ->orderBy($ordered_by, $sort_type)->get();

        $products_arr = $products->pluck('id')->toArray();

        $manufacturers_arr = Product::whereIn('id', $products_arr)->groupBy('manufacturer_id')
                                      ->pluck('manufacturer_id')->toArray();

            $origins_arr = Product::whereIn('id', $products_arr)->groupBy('prodcountry_id')
                                    ->pluck('prodcountry_id')->toArray();

              $manufacturers  = Manufacturer::whereIn('id', $manufacturers_arr)->get();
              $origins        = Prodcountry::whereIn('id', $origins_arr)->get();

              $cats_arr = Product::whereIn('id', $products_arr)->groupBy('allcategory_id')
                                ->pluck('allcategory_id')->toArray();
              $cats        = Allcategory::whereIn('id', $cats_arr)->get();

              foreach ($cats as $cat) {
                $count_cats = Product::whereIn('id', $products_arr)->where('allcategory_id', $cat->id)->count();
                $cat['count_cats'] = $count_cats;
              }
              foreach ($origins as $origin) {
                $count_origins = Product::whereIn('id', $products_arr)->where('prodcountry_id', $origin->id)->count();
                $origin['count_origins'] = $count_origins;
              }
              foreach ($manufacturers as $manufacturer) {
                $count_manufacturers = Product::whereIn('id', $products_arr)->where('manufacturer_id', $manufacturer->id)->count();
                $manufacturer['count_manufacturers'] = $count_manufacturers;
              }
      
              $cats_data = HomeAllcategoryFilterationApiResource::collection($cats);

              $manufacturers_data = ManufacturerFilterationApiResource::collection($manufacturers);
              $origins_data = OriginCountryFilterationApiResource::collection($origins);
                                                        
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
              //$data = ProductSearchApiResource::collection($products->where('lang', $lang));
              $data = ProductSearchApiResource::collection($products);
                $total = count($products);
                              
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                    'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                ], 200);         
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {

            $products = Product::where('approved', 1)
                          ->where('width', $width)
                          ->where('height', $height)
                          ->where('size', $size)
                          ->whereIn('prodcountry_id', $origins)
                          ->whereIn('manufacturer_id', $manufacturers)
                          ->where('actual_price', '>=', $start_price)
                          ->where('actual_price', '<=', $end_price)

                         ->orderBy($ordered_by, $sort_type)->get();

        $products_arr = $products->pluck('id')->toArray();

        $manufacturers_arr = Product::whereIn('id', $products_arr)->groupBy('manufacturer_id')
                                      ->pluck('manufacturer_id')->toArray();

            $origins_arr = Product::whereIn('id', $products_arr)->groupBy('prodcountry_id')
                                    ->pluck('prodcountry_id')->toArray();

              $manufacturers  = Manufacturer::whereIn('id', $manufacturers_arr)->get();
              $origins        = Prodcountry::whereIn('id', $origins_arr)->get();

              $cats_arr = Product::whereIn('id', $products_arr)->groupBy('allcategory_id')
                                ->pluck('allcategory_id')->toArray();
              $cats        = Allcategory::whereIn('id', $cats_arr)->get();

              foreach ($cats as $cat) {
                $count_cats = Product::whereIn('id', $products_arr)->where('allcategory_id', $cat->id)->count();
                $cat['count_cats'] = $count_cats;
              }
              foreach ($origins as $origin) {
                $count_origins = Product::whereIn('id', $products_arr)->where('prodcountry_id', $origin->id)->count();
                $origin['count_origins'] = $count_origins;
              }
              foreach ($manufacturers as $manufacturer) {
                $count_manufacturers = Product::whereIn('id', $products_arr)->where('manufacturer_id', $manufacturer->id)->count();
                $manufacturer['count_manufacturers'] = $count_manufacturers;
              }
      
              $cats_data = HomeAllcategoryFilterationApiResource::collection($cats);

              $manufacturers_data = ManufacturerFilterationApiResource::collection($manufacturers);
              $origins_data = OriginCountryFilterationApiResource::collection($origins);
                                                        
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
                $data = ProductSearchApiResource::collection($products);
                $total = count($products);
                              
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                    'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                ], 200);  
              
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
          $products = Product::where('approved', 1)->where('producttype_id', 1)
                          ->where('width', $width)
                          ->where('height', $height)
                          ->where('size', $size)
                          ->whereIn('prodcountry_id', $origins)
                          ->whereIn('manufacturer_id', $manufacturers)
                          ->where('actual_price', '>=', $start_price)
                          ->where('actual_price', '<=', $end_price)
                         ->orderBy($ordered_by, $sort_type)->get();

        $products_arr = $products->pluck('id')->toArray();

        $manufacturers_arr = Product::whereIn('id', $products_arr)->groupBy('manufacturer_id')
                                      ->pluck('manufacturer_id')->toArray();

            $origins_arr = Product::whereIn('id', $products_arr)->groupBy('prodcountry_id')
                                    ->pluck('prodcountry_id')->toArray();

              $manufacturers  = Manufacturer::whereIn('id', $manufacturers_arr)->get();
              $origins        = Prodcountry::whereIn('id', $origins_arr)->get();

              $cats_arr = Product::whereIn('id', $products_arr)->groupBy('allcategory_id')
                                ->pluck('allcategory_id')->toArray();
              $cats        = Allcategory::whereIn('id', $cats_arr)->get();

              foreach ($cats as $cat) {
                $count_cats = Product::whereIn('id', $products_arr)->where('allcategory_id', $cat->id)->count();
                $cat['count_cats'] = $count_cats;
              }
              foreach ($origins as $origin) {
                $count_origins = Product::whereIn('id', $products_arr)->where('prodcountry_id', $origin->id)->count();
                $origin['count_origins'] = $count_origins;
              }
              foreach ($manufacturers as $manufacturer) {
                $count_manufacturers = Product::whereIn('id', $products_arr)->where('manufacturer_id', $manufacturer->id)->count();
                $manufacturer['count_manufacturers'] = $count_manufacturers;
              }
      
              $cats_data = HomeAllcategoryFilterationApiResource::collection($cats);

              $manufacturers_data = ManufacturerFilterationApiResource::collection($manufacturers);
              $origins_data = OriginCountryFilterationApiResource::collection($origins);
                                                        
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
                $data = ProductSearchApiResource::collection($products);
                $total = count($products);
                              
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                    'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                ], 200);  
                    
        } // end guest case
    }
}
