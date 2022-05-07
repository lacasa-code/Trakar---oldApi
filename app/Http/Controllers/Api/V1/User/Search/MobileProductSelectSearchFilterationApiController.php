<?php

namespace App\Http\Controllers\Api\V1\User\Search;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Gate;
use App\Http\Requests\SearchApisRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Schema;
use App\Http\Resources\User\Search\ProductSearchApiResource;
use App\Http\Resources\Website\Products\FrontProductsApiResource;
// use App\Http\Resources\Website\Products\FrontProductsApiResource;
use App\Http\Requests\Website\User\Products\AddFavouriteCarMadeApiRequest;
use App\Http\Resources\Website\User\Products\UserFavouriteCarsApiResource;
use App\Models\Favouritecar;
use Auth;
use App\Http\Requests\Website\HomePage\DisplaySearchResultsApiRequest;
use App\Models\AddVendor;
use App\Models\CarYear;
use App\Models\CarModel;
// use App\Http\Resources\Admin\Manufacturer\ManufacturerApiResource;
// use App\Http\Resources\Admin\OriginCountry\OriginCountryApiResource;
use App\Models\Manufacturer;
use App\Models\Prodcountry;
// use App\Http\Resources\Api\Admin\Allcategory\HomeAllcategoryApiResource;
use App\Http\Resources\Api\Admin\Allcategory\HomeAllcategoryFilterationApiResource;
use App\Http\Resources\Admin\Manufacturer\ManufacturerFilterationApiResource;
use App\Http\Resources\Admin\OriginCountry\OriginCountryFilterationApiResource;
use App\Models\Allcategory;

class MobileProductSelectSearchFilterationApiController extends Controller
{
    public function getLang()
  {
      return $lang = \Config::get('app.locale');
  } 

    // start search products with name
     public function display_search_results(DisplaySearchResultsApiRequest $request)
     {
      $lang = $this->getLang();
        //$PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
        $default_count = \Config::get('constants.pagination.items_per_page');
        $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
        
        $request->page == '' ? $page = 1 : $page = $request->page;
        $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
        $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
        $request->column_name == '' ? $column_name = '' : $column_name = $request->column_name;

                $part_categories = $request->categories;
                $manufacturers   = $request->manufacturers;
                $origins         = $request->origins;
                $start_price     = $request->start_price;
                $end_price       = $request->end_price;

        // $search_index = $request->search_index;

      // return $column_name;
      if ($ordered_by != '') {
        if (!Schema::hasColumn('products', $ordered_by)) {
          return response()->json(['message'  =>'order column not found',], 400);
        }
        if ($ordered_by == 'tags' || $ordered_by == 'categories') {
          $ordered_by = 'id';
        }
            if ($ordered_by == 'price') {
            $ordered_by = 'actual_price';
          }
      } // end if

      $car_type_id       = $request->car_type_id;
      $car_made_id       = $request->car_made_id;
      $car_model_id      = $request->car_model_id;
      $car_year_id       = $request->car_year_id;
      $transmission_id   = $request->transmission_id;

      // case 1
        if ($car_made_id == '' && $car_model_id == '' && $car_year_id == '' && $transmission_id == '') {
          return response()->json([
            'status_code' => 400,
            'message'  =>'select at least one item',], 400);
        }
        // case 2
        if($car_made_id != '' && $car_model_id == '' && $car_year_id == '' && $transmission_id == '') {
          $search_index = $car_made_id;
          $filtered_products_arr =  $this->search_with_car_made($car_type_id, $search_index, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 6
        if($car_made_id != '' && $car_model_id != '' && $car_year_id == '' && $transmission_id == '') {
           $search_index = $car_year_id;
           $filtered_products_arr =  $this->search_with_made_model($car_type_id, $car_made_id, $car_model_id, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 12
        if($car_made_id != '' && $car_model_id != '' && $car_year_id != '' && $transmission_id == '') {
          $filtered_products_arr =  $this->made_model_year($car_type_id, $car_made_id, $car_model_id, $car_year_id, $page, $ordered_by, $sort_type, $PAGINATION_COUNT);
        }
    
        // case 14
        if($car_made_id != '' && $car_model_id != '' && $car_year_id != '' && $transmission_id != '') {
          $filtered_products_arr =  $this->all_search($car_type_id, $car_made_id, $car_model_id, $car_year_id, $transmission_id, $page, $ordered_by, $sort_type, $PAGINATION_COUNT);
        }

        // case 1
        if ($part_categories == '' && $manufacturers == '' && $origins == '' && $start_price == '') {
          return $this->fetching_all_data($page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $filtered_products_arr);
        }

        // case 1
        if ($part_categories != '' && $manufacturers == '' && $origins == '' && $start_price == '') {
          $part_categories = json_decode($request->categories);
        // $manufacturers   = json_decode($request->manufacturers);
        // $origins         = json_decode($request->origins);
          return $this->part_categories_search($part_categories, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $filtered_products_arr);
        }

        // case 2
        if ($part_categories == '' && $manufacturers != '' && $origins == '' && $start_price == '') {
          // $part_categories = json_decode($request->categories);
        $manufacturers   = json_decode($request->manufacturers);
        // $origins         = json_decode($request->origins);
          return $this->manufacturers_search($manufacturers, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $filtered_products_arr);
        }

        // case 3
        if ($part_categories == '' && $manufacturers == '' && $origins != '' && $start_price == '') {
          // $part_categories = json_decode($request->categories);
        // $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->origins_search($origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $filtered_products_arr);
        }

        // case 4
        if ($part_categories == '' && $manufacturers == '' && $origins == '' && $start_price != '') {
          // $part_categories = json_decode($request->categories);
        // $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->price_search($start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $filtered_products_arr);
        }

        // case 5
        if ($part_categories != '' && $manufacturers != '' && $origins == '' && $start_price == '') {
          $part_categories = json_decode($request->categories);
          // return $part_categories;
        $manufacturers   = json_decode($request->manufacturers);
        //$origins         = json_decode($request->origins);
        //return 'bbbv';
          return $this->part_categories_manufacturers_search($part_categories, $manufacturers, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $filtered_products_arr);
        }

        // case 6
        if ($part_categories != '' && $manufacturers == '' && $origins != '' && $start_price == '') {
          $part_categories = json_decode($request->categories);
        //$manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->part_categories_origins_search($part_categories, $origins, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $filtered_products_arr);
        }

        // case 7
        if ($part_categories != '' && $manufacturers == '' && $origins == '' && $start_price != '') {
          $part_categories = json_decode($request->categories);
        //$manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->part_categories_price_search($part_categories, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $filtered_products_arr);
        }

        // case 8
        if ($part_categories != '' && $manufacturers != '' && $origins != '' && $start_price == '') {
          $part_categories = json_decode($request->categories);
        $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->part_manufacturers_origins_search($part_categories, $manufacturers, $origins, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $filtered_products_arr);
        }

        // case 9
        if ($part_categories != '' && $manufacturers != '' && $origins == '' && $start_price != '') {
          $part_categories = json_decode($request->categories);
        $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->part_manufacturers_price_search($part_categories, $manufacturers, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $filtered_products_arr);
        }

        // case 10
        if ($part_categories != '' && $manufacturers == '' && $origins != '' && $start_price != '') {
          $part_categories = json_decode($request->categories);
        $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->part_origins_price_search($part_categories, $origins, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $filtered_products_arr);
        }

        // case 11
        if ($part_categories != '' && $manufacturers != '' && $origins != '' && $start_price != '') {
          $part_categories = json_decode($request->categories);
        $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->four_hand_search($part_categories, $manufacturers, $origins, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $filtered_products_arr);
        }

        // case 12
        if ($part_categories == '' && $manufacturers != '' && $origins != '' && $start_price == '') {
          //$part_categories = json_decode($request->categories);
        $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->manufacturers_origins_search($manufacturers, $origins, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $filtered_products_arr);
        }

        // case 13
        if ($part_categories == '' && $manufacturers != '' && $origins == '' && $start_price != '') {
            //$part_categories = json_decode($request->categories);
            $manufacturers   = json_decode($request->manufacturers);
            $origins         = json_decode($request->origins);
          return $this->manufacturers_price_search($manufacturers, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $filtered_products_arr);
        }

        // case 14
        if ($part_categories == '' && $manufacturers != '' && $origins != '' && $start_price != '') {
            //$part_categories = json_decode($request->categories);
            $manufacturers   = json_decode($request->manufacturers);
            $origins         = json_decode($request->origins);
          return $this->manufacturers_origins_price_search($manufacturers, $origins, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $filtered_products_arr);
        }


        // case 15
        if ($part_categories == '' && $manufacturers == '' && $origins != '' && $start_price != '') {
            //$part_categories = json_decode($request->categories);
            // $manufacturers   = json_decode($request->manufacturers);
            $origins         = json_decode($request->origins);
          return $this->origins_price_search($origins, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $filtered_products_arr);
        }

    } // end search products with name 

    public function fetching_all_data($page, $ordered_by, $sort_type, $PAGINATION_COUNT, $filtered_products_arr)
    {
        $lang = $this->getLang();
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {

            $new_products = Product::whereIn('id', $filtered_products_arr)->orderBy($ordered_by, $sort_type)->get(); 
            $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();

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

		        $total = count($new_products);
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
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200); 
          }  // end user case

        //  if (in_array('Vendor', $user_roles)) {
        if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {

                 $new_products = Product::whereIn('id', $filtered_products_arr)->orderBy($ordered_by, $sort_type)->get(); 
                 $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();

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

		        $total = count($new_products);
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
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
             $new_products = Product::whereIn('id', $filtered_products_arr)->orderBy($ordered_by, $sort_type)->get(); 
             $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();

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

		        $total = count($new_products);
		        foreach ($new_products as $new_product) {
		                $new_product['in_cart']       = 0;//$user->revise_cart($new_product->id);
		                $new_product['in_wishlist']   = 0;//$user->revise_wishlist($new_product->id);
		                $new_product['in_favourites'] = 0;//$user->revise_favourites($new_product->id);
		              }
        $data = FrontProductsApiResource::collection($new_products);        
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


    public function part_categories_search($part_categories, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $filtered_products_arr)
    {
        $lang = $this->getLang();
         /// $common_types = [$cartype_id, 7];
      //return $part_categories;
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {

        $new_products = Product::whereIn('id', $filtered_products_arr)->whereIn('allcategory_id', $part_categories)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray(); 

        $total = count($new_products);

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
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          }  // end user case

           
         // if (in_array('Vendor', $user_roles)) {
            if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
                $new_products = Product::whereIn('id', $filtered_products_arr)->whereIn('allcategory_id', $part_categories)
                ->orderBy($ordered_by, $sort_type)->get(); 
                $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();

        $total = count($new_products);

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
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
            $new_products = Product::whereIn('id', $filtered_products_arr)->whereIn('allcategory_id', $part_categories)
            ->orderBy($ordered_by, $sort_type)->get(); 
            $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();

        $total = count($new_products);

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
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);          
        } // end guest case
    }

    public function part_manufacturers_origins_search($part_categories, $manufacturers, $origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $filtered_products_arr)
    {
        $lang = $this->getLang();
         /// $common_types = [$cartype_id, 7];
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {

                $new_products = Product::whereIn('id', $filtered_products_arr)->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->whereIn('prodcountry_id', $origins)->orderBy($ordered_by, $sort_type)->get(); 
                $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();
        $total = count($new_products);

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
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          }  // end user case

           
         // if (in_array('Vendor', $user_roles)) {
             if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
              $new_products = Product::whereIn('id', $filtered_products_arr)->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->whereIn('prodcountry_id', $origins)->orderBy($ordered_by, $sort_type)->get(); 
              $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray(); 
        $total = count($new_products);

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
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
              $new_products = Product::whereIn('id', $filtered_products_arr)->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->whereIn('prodcountry_id', $origins)->orderBy($ordered_by, $sort_type)->get(); 
              $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();
        $total = count($new_products);

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
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);         
        } // end guest case
    }

    public function part_manufacturers_price_search($part_categories, $manufacturers, $start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $filtered_products_arr)
    {
        $lang = $this->getLang();
         /// $common_types = [$cartype_id, 7];
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {

              $new_products = Product::whereIn('id', $filtered_products_arr)->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)->orderBy($ordered_by, $sort_type)->get(); 
              $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray(); 
        $total = count($new_products);

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
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          }  // end user case

           

          //if (in_array('Vendor', $user_roles)) {
             if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $new_products = Product::whereIn('id', $filtered_products_arr)->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)->orderBy($ordered_by, $sort_type)->get(); 
            $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();
        $total = count($new_products);

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
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
             $new_products = Product::whereIn('id', $filtered_products_arr)->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)->orderBy($ordered_by, $sort_type)->get(); 
             $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray(); 
        $total = count($new_products);

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
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);        
        } // end guest case
    }

    public function origins_price_search($origins, $start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $filtered_products_arr)
    {
        $lang = $this->getLang();
         /// $common_types = [$cartype_id, 7];
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {

               $new_products = Product::whereIn('id', $filtered_products_arr)->whereIn('prodcountry_id', $origins)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)->orderBy($ordered_by, $sort_type)->get(); 
               $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray(); 
        $total = count($new_products);

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
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          }  // end user case

           
          //if (in_array('Vendor', $user_roles)) {
             if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
             $new_products = Product::whereIn('id', $filtered_products_arr)->whereIn('prodcountry_id', $origins)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)->orderBy($ordered_by, $sort_type)->get(); 
             $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray(); 
        $total = count($new_products);

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
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
             $new_products = Product::whereIn('id', $filtered_products_arr)->whereIn('prodcountry_id', $origins)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)->orderBy($ordered_by, $sort_type)->get(); 
             $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();
        $total = count($new_products);

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
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);         
        } // end guest case
    }

    public function manufacturers_origins_price_search($manufacturers, $origins, $start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $filtered_products_arr)
    {
        $lang = $this->getLang();
         /// $common_types = [$cartype_id, 7];
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {

               $new_products = Product::whereIn('id', $filtered_products_arr)->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)->orderBy($ordered_by, $sort_type)->get(); 
               $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();
        $total = count($new_products);

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
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          }  // end user case

           
          //if (in_array('Vendor', $user_roles)) {
             if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
             $new_products = Product::whereIn('id', $filtered_products_arr)->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)->orderBy($ordered_by, $sort_type)->get(); 
             $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();
        $total = count($new_products);

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
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
             $new_products = Product::whereIn('id', $filtered_products_arr)->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)->orderBy($ordered_by, $sort_type)->get(); 
             $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();
        $total = count($new_products);

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
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);         
        } // end guest case
    }

    public function four_hand_search($part_categories, $manufacturers, $origins, $start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $filtered_products_arr)
    {
        $lang = $this->getLang();
         /// $common_types = [$cartype_id, 7];
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {

                $new_products = Product::whereIn('id', $filtered_products_arr)->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)->orderBy($ordered_by, $sort_type)->get(); 
                $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();
        $total = count($new_products);

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
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200); 
          }  // end user case

          
          //if (in_array('Vendor', $user_roles)) {
             if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
               $new_products = Product::whereIn('id', $filtered_products_arr)->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)->orderBy($ordered_by, $sort_type)->get(); 
               $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();
        $total = count($new_products);

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
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
               $new_products = Product::whereIn('id', $filtered_products_arr)->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)->orderBy($ordered_by, $sort_type)->get(); 
               $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();
        $total = count($new_products);

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
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);         
        } // end guest case
    }

    public function part_origins_price_search($part_categories, $origins, $start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $filtered_products_arr)
    {
        $lang = $this->getLang();
         /// $common_types = [$cartype_id, 7];
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {

               $new_products = Product::whereIn('id', $filtered_products_arr)->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)->orderBy($ordered_by, $sort_type)->get(); 
               $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();
        $total = count($new_products);

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
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          }  // end user case

           
          //if (in_array('Vendor', $user_roles)) {
             if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
             $new_products = Product::whereIn('id', $filtered_products_arr)->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)->orderBy($ordered_by, $sort_type)->get(); 
             $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();
        $total = count($new_products);

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
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
             $new_products = Product::whereIn('id', $filtered_products_arr)->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)->orderBy($ordered_by, $sort_type)->get(); 
             $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray(); 
        $total = count($new_products);

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
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);         
        } // end guest case
    }

public function price_search($start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $filtered_products_arr)
    {
        //return $part_categories;
        $lang = $this->getLang();
         /// $common_types = [$cartype_id, 7];
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {

               $new_products = Product::whereIn('id', $filtered_products_arr)->where('actual_price', '>=', $start_price)
                                ->where('actual_price', '<=', $end_price)->orderBy($ordered_by, $sort_type)->get(); 
               $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();

        $total = count($new_products);

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
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          }  // end user case

          
          //if (in_array('Vendor', $user_roles)) {
             if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
             $new_products = Product::whereIn('id', $filtered_products_arr)->where('actual_price', '>=', $start_price)
                                ->where('actual_price', '<=', $end_price)->orderBy($ordered_by, $sort_type)->get(); 
             $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();

        $total = count($new_products);

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
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
            $new_products = Product::whereIn('id', $filtered_products_arr)->where('actual_price', '>=', $start_price)
                                ->where('actual_price', '<=', $end_price)->orderBy($ordered_by, $sort_type)->get(); 
            $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();

        $total = count($new_products);

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
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);          
        } // end guest case
    }

    public function part_categories_price_search($part_categories, $start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $filtered_products_arr)
    {
        $lang = $this->getLang();
         /// $common_types = [$cartype_id, 7];
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {

              $new_products = Product::whereIn('id', $filtered_products_arr)->where('actual_price', '>=', $start_price)
                                ->where('actual_price', '<=', $end_price)
                                ->whereIn('allcategory_id', $part_categories)->orderBy($ordered_by, $sort_type)->get(); 
              $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray(); 

        $total = count($new_products);

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
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200); 
          }  // end user case

          //if (in_array('Vendor', $user_roles)) {
             if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
             $new_products = Product::whereIn('id', $filtered_products_arr)->where('actual_price', '>=', $start_price)
                                ->where('actual_price', '<=', $end_price)
                                ->whereIn('allcategory_id', $part_categories)->orderBy($ordered_by, $sort_type)->get(); 
             $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();

        $total = count($new_products);

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
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
             $new_products = Product::whereIn('id', $filtered_products_arr)->where('actual_price', '>=', $start_price)
                                ->where('actual_price', '<=', $end_price)
                                ->whereIn('allcategory_id', $part_categories)->orderBy($ordered_by, $sort_type)->get(); 
             $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();

        $total = count($new_products);

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
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);         
        } // end guest case
    }

    public function manufacturers_price_search($manufacturers, $start_price, $end_price, $page, $ordered_by , $sort_type, $PAGINATION_COUNT, $filtered_products_arr)
    {
        $lang = $this->getLang();
         /// $common_types = [$cartype_id, 7];
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {

                $new_products = Product::whereIn('id', $filtered_products_arr)->where('actual_price', '>=', $start_price)
                                ->where('actual_price', '<=', $end_price)
                                ->whereIn('manufacturer_id', $manufacturers)->orderBy($ordered_by, $sort_type)->get(); 
                $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray(); 

        $total = count($new_products);

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
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          }  // end user case

          //if (in_array('Vendor', $user_roles)) {
             if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
              $new_products = Product::whereIn('id', $filtered_products_arr)->where('actual_price', '>=', $start_price)
                                ->where('actual_price', '<=', $end_price)
                                ->whereIn('manufacturer_id', $manufacturers)->orderBy($ordered_by, $sort_type)->get(); 
              $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();

        $total = count($new_products);

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
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
              $new_products = Product::whereIn('id', $filtered_products_arr)->where('actual_price', '>=', $start_price)
                                ->where('actual_price', '<=', $end_price)
                                ->whereIn('manufacturer_id', $manufacturers)->orderBy($ordered_by, $sort_type)->get(); 
              $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();

        $total = count($new_products);

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
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);         
        } // end guest case
    }

    public function manufacturers_search($manufacturers, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $filtered_products_arr)
    {
        $lang = $this->getLang();
         /// $common_types = [$cartype_id, 7];
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {

              $new_products = Product::whereIn('id', $filtered_products_arr)->whereIn('manufacturer_id', $manufacturers)->orderBy($ordered_by, $sort_type)->get(); 
              $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();
        $total = count($new_products);

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
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          }  // end user case

          
          //if (in_array('Vendor', $user_roles)) {
             if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $new_products = Product::whereIn('id', $filtered_products_arr)->whereIn('manufacturer_id', $manufacturers)->orderBy($ordered_by, $sort_type)->get(); 
            $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();
        $total = count($new_products);

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
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
            $new_products = Product::whereIn('id', $filtered_products_arr)->whereIn('manufacturer_id', $manufacturers)->orderBy($ordered_by, $sort_type)->get(); 
            $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();
        $total = count($new_products);

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
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);         
        } // end guest case
    }

    public function origins_search($origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $filtered_products_arr)
    {
        $lang = $this->getLang();
         /// $common_types = [$cartype_id, 7];
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {

              $new_products = Product::whereIn('id', $filtered_products_arr)->whereIn('prodcountry_id', $origins)->orderBy($ordered_by, $sort_type)->get(); 
              $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();
        $total = count($new_products);

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
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          }  // end user case

          //if (in_array('Vendor', $user_roles)) {
             if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $new_products = Product::whereIn('id', $filtered_products_arr)->whereIn('prodcountry_id', $origins)->orderBy($ordered_by, $sort_type)->get(); 
            $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();
        $total = count($new_products);

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
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
            $new_products = Product::whereIn('id', $filtered_products_arr)->whereIn('prodcountry_id', $origins)->orderBy($ordered_by, $sort_type)->get(); 
            $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();
        $total = count($new_products);

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
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);         
        } // end guest case
    }

    public function part_categories_manufacturers_search($part_categories, $manufacturers, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $filtered_products_arr)
    {
        $lang = $this->getLang();
         /// $common_types = [$cartype_id, 7];
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {

             $new_products = Product::whereIn('id', $filtered_products_arr)->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)->orderBy($ordered_by, $sort_type)->get(); 
             $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();
            $total = count($new_products);

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
                    'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                ], 200); 
          }  // end user case

          //if (in_array('Vendor', $user_roles)) {
             if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $new_products = Product::whereIn('id', $filtered_products_arr)->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)->orderBy($ordered_by, $sort_type)->get(); 
            $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();
            $total = count($new_products);

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
                    'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
          // return $part_categories;
           $new_products = Product::whereIn('id', $filtered_products_arr)->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)->orderBy($ordered_by, $sort_type)->get(); 
           $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();
            $total = count($new_products);

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
                    'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                ], 200);          
        } // end guest case
    }

    public function part_categories_origins_search($part_categories, $origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $filtered_products_arr)
    {
        $lang = $this->getLang();
         /// $common_types = [$cartype_id, 7];
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {

              $new_products = Product::whereIn('id', $filtered_products_arr)->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)->orderBy($ordered_by, $sort_type)->get(); 
              $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();
                $total = count($new_products);

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
                        'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                    ], 200);
          }  // end user case

          //if (in_array('Vendor', $user_roles)) {
             if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $new_products = Product::whereIn('id', $filtered_products_arr)->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)->orderBy($ordered_by, $sort_type)->get(); 
            $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();
            $total = count($new_products);

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
                    'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
            $new_products = Product::whereIn('id', $filtered_products_arr)->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)->orderBy($ordered_by, $sort_type)->get(); 
            $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();
            $total = count($new_products);

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
                    'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                ], 200);         
        } // end guest case
    }

      public function manufacturers_origins_search($manufacturers, $origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $filtered_products_arr)
      {
          $lang = $this->getLang();
           /// $common_types = [$cartype_id, 7];
          // alraedy user logged in
          if (Auth::guard('api')->check() && Auth::user()) 
          {
              $user = Auth::user();
              $user_roles = $user->roles->pluck('title')->toArray();
            
            // case logged in user role is User 
            if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {

              $new_products = Product::whereIn('id', $filtered_products_arr)->whereIn('manufacturer_id', $manufacturers)
                                          ->whereIn('prodcountry_id', $origins)->orderBy($ordered_by, $sort_type)->get(); 
              $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();
              $total = count($new_products);

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
                      'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                  ], 200);
            }  // end user case


          //  if (in_array('Vendor', $user_roles)) {
               if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
              $new_products = Product::whereIn('id', $filtered_products_arr)->whereIn('manufacturer_id', $manufacturers)
                                          ->whereIn('prodcountry_id', $origins)->orderBy($ordered_by, $sort_type)->get(); 
              $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();
              $total = count($new_products);

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
                      'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                  ], 200);
            } // end vendor case
          }  // end case logged in 
          else{ // guest case
              $new_products = Product::whereIn('id', $filtered_products_arr)->whereIn('manufacturer_id', $manufacturers)
                                          ->whereIn('prodcountry_id', $origins)->orderBy($ordered_by, $sort_type)->get(); 
              $products_arr = $new_products->where('approved', 1)->pluck('id')->toArray();
              $total = count($new_products);

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
                      'manufacturers_data' => $manufacturers_data,
                      'origins_data'       => $origins_data,
                      'cats_data'       => $cats_data,
                  ], 200);         
          } // end guest case
      }  // end probs

    // start search products with car made 
     public function search_with_car_made($car_type_id, $search_index, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
     {   
      $lang = $this->getLang();
      $carTypes = [$car_type_id, 7];
      // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
          	$existTypes = [1];
              $products = Product::where('approved', 1)
                             // ->where('cartype_id', $car_type_id)
                              ->whereIn('cartype_id', $carTypes)
                              ->where('car_made_id', $search_index)
                              ->where('producttype_id', 1)
                               ->orderBy($ordered_by, $sort_type)->get();

            return $filtered_products_arr = $products->whereIn('producttype_id', $existTypes)->where('approved', 1)
                                            ->pluck('id')->toArray();
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
          	$existTypes = [1, 2, 3];
            $products = Product::where('approved', 1)
                             // ->where('cartype_id', $car_type_id)
                               ->whereIn('cartype_id', $carTypes)
                              ->where('car_made_id', $search_index)
                              ->whereIn('producttype_id', [1, 2, 3])
                               ->orderBy($ordered_by, $sort_type)->get();

            return $filtered_products_arr = $products->whereIn('producttype_id', $existTypes)->where('approved', 1)
                                            ->pluck('id')->toArray();
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
        	$existTypes = [1];
            $products = Product::where('approved', 1)
                             // ->where('cartype_id', $car_type_id)
                              ->whereIn('cartype_id', $carTypes)
                              ->where('car_made_id', $search_index)
                              ->where('producttype_id', 1)
                               ->orderBy($ordered_by, $sort_type)->get();

            return $filtered_products_arr = $products->whereIn('producttype_id', $existTypes)->where('approved', 1)
                                            ->pluck('id')->toArray(); 
        } // end guest case
        // case logged in user role is User 
     }
    // end search products with car made 

     // start search products with made model 
     public function search_with_made_model($car_type_id, $car_made_id, $car_model_id, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
     {
      $lang = $this->getLang();
       $carTypes = [$car_type_id, 7];
        // case logged in user role is user
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
         if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
         	$existTypes = [1];
           // $year = CarYear::where('id', $car_year_id)->first()->year;
            $model = CarModel::where('id', $car_model_id)->first()->carmodel;
              $products = Product::where('approved', 1)
                            //->where('cartype_id', $car_type_id)
                            ->whereIn('cartype_id', $carTypes)
                            ->where('car_made_id', $car_made_id)
                           // ->where('car_model_id', $car_model_id)
                             ->whereHas('car_model', function($q) use ($model){
                              $q->where('carmodel', $model);
                            })  
                            ->where('producttype_id', 1)
                            ->orderBy($ordered_by, $sort_type)->get();

          return $filtered_products_arr = $products->whereIn('producttype_id', $existTypes)->where('approved', 1)
                                            ->pluck('id')->toArray();
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
          	$existTypes = [1, 2, 3];
          //  $year = CarYear::where('id', $car_year_id)->first()->year;
          $model = CarModel::where('id', $car_model_id)->first()->carmodel;
            $products = Product::where('approved', 1)
                            //->where('cartype_id', $car_type_id)
                            ->whereIn('cartype_id', $carTypes)
                            ->where('car_made_id', $car_made_id)
                            //->where('car_model_id', $car_model_id)
                             ->whereHas('car_model', function($q) use ($model){
                              $q->where('carmodel', $model);
                            })   
                            ->whereIn('producttype_id', [1, 2, 3])
                            ->orderBy($ordered_by, $sort_type)->get();

          return $filtered_products_arr = $products->whereIn('producttype_id', $existTypes)->where('approved', 1)
                                            ->pluck('id')->toArray();
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
        	$existTypes = [1];
         // $year = CarYear::where('id', $car_year_id)->first()->year;
          $model = CarModel::where('id', $car_model_id)->first()->carmodel;
            $products = Product::where('approved', 1)
                           // ->where('cartype_id', $car_type_id)
                            ->whereIn('cartype_id', $carTypes)
                            ->where('car_made_id', $car_made_id)
                            //->where('car_model_id', $car_model_id)
                             ->whereHas('car_model', function($q) use ($model){
                              $q->where('carmodel', $model);
                            })  
                            ->where('producttype_id', 1)
                            ->orderBy($ordered_by, $sort_type)->get();

          return $filtered_products_arr = $products->whereIn('producttype_id', $existTypes)->where('approved', 1)
                                            ->pluck('id')->toArray();
        } // end guest case 
     }
    // end search products with made model

       // start search products with made model year 
     public function made_model_year($car_type_id, $car_made_id, $car_model_id, $car_year_id, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
     {
      $lang = $this->getLang();
       $carTypes = [$car_type_id, 7];
        // case logged in user role is user 
      // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
         if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
         	$existTypes = [1];
            $year = CarYear::where('id', $car_year_id)->first()->year;
          $model = CarModel::where('id', $car_model_id)->first()->carmodel;
              $products = Product::where('approved', 1)->whereIn('cartype_id', $carTypes)
                            ->where('car_made_id', $car_made_id)
                            //->where('car_model_id', $car_model_id)
                            //->where('year_id', $car_year_id)
                             ->whereHas('car_model', function($q) use ($model){
                              $q->where('carmodel', $model);
                            })  
                            ->whereHas('year_from_func', function($q) use ($year){
                              $q->where('year', '<=', $year);
                            }) 
                            ->whereHas('year_to_func', function($q) use ($year){
                              $q->where('year', '>=', $year);
                            }) 
                            ->where('producttype_id', 1)
                            //->skip(($page-1)*$PAGINATION_COUNT)
                            //->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)->get();

          return $filtered_products_arr = $products->whereIn('producttype_id', $existTypes)->where('approved', 1)
                                            ->pluck('id')->toArray();
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
          	$existTypes = [1, 2, 3];
            $year = CarYear::where('id', $car_year_id)->first()->year;
          $model = CarModel::where('id', $car_model_id)->first()->carmodel;
            $products = Product::where('approved', 1)->whereIn('cartype_id', $carTypes)
                            ->where('car_made_id', $car_made_id)
                            //->where('car_model_id', $car_model_id)
                            //->where('year_id', $car_year_id)
                             ->whereHas('car_model', function($q) use ($model){
                              $q->where('carmodel', $model);
                            })  
                            ->whereHas('year_from_func', function($q) use ($year){
                              $q->where('year', '<=', $year);
                            }) 
                            ->whereHas('year_to_func', function($q) use ($year){
                              $q->where('year', '>=', $year);
                            }) 
                            ->whereIn('producttype_id', [1, 2, 3])
                            //->skip(($page-1)*$PAGINATION_COUNT)
                            //->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)->get();

          return $filtered_products_arr = $products->whereIn('producttype_id', $existTypes)->where('approved', 1)
                                            ->pluck('id')->toArray();
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
        	$existTypes = [1];
          $year = CarYear::where('id', $car_year_id)->first()->year;
          $model = CarModel::where('id', $car_model_id)->first()->carmodel;
             $products = Product::where('approved', 1)->whereIn('cartype_id', $carTypes)
                            ->where('car_made_id', $car_made_id)
                            //->where('car_model_id', $car_model_id)
                            //->where('year_id', $car_year_id)
                             ->whereHas('car_model', function($q) use ($model){
                              $q->where('carmodel', $model);
                            })  
                            ->whereHas('year_from_func', function($q) use ($year){
                              $q->where('year', '<=', $year);
                            }) 
                            ->whereHas('year_to_func', function($q) use ($year){
                              $q->where('year', '>=', $year);
                            }) 
                            ->where('producttype_id', 1)
                            //->skip(($page-1)*$PAGINATION_COUNT)
                            //->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)->get();

          return $filtered_products_arr = $products->whereIn('producttype_id', $existTypes)->where('approved', 1)
                                            ->pluck('id')->toArray();
        } // end guest case
     }
    // end search products with made model year 

     // start search products all_search 
     public function all_search($car_type_id, $car_made_id, $car_model_id, $car_year_id, $transmission_id, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
     {
      $lang = $this->getLang();
       $carTypes = [$car_type_id, 7];

      if ($transmission_id == 1) {
        $transmission_id = [1, 3];
      }
      if ($transmission_id == 2) {
        $transmission_id = [2, 3];
      }
      if ($transmission_id == 3) {
        $transmission_id = [1, 2, 3];
      }
        // case logged in user role is user 
      // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
         if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
         	$existTypes = [1];
              $year = CarYear::where('id', $car_year_id)->first()->year;
              $model = CarModel::where('id', $car_model_id)->first()->carmodel;
              $products = Product::where('approved', 1)
                            ->whereIn('cartype_id', $carTypes)
                            ->where('car_made_id', $car_made_id)
                            ->whereIn('transmission_id', $transmission_id)
                            ->where('producttype_id', 1)
                            ->whereHas('car_model', function($q) use ($model){
                              $q->where('carmodel', $model);
                            })  
                            ->whereHas('year_from_func', function($q) use ($year){
                              $q->where('year', '<=', $year);
                            }) 
                            ->whereHas('year_to_func', function($q) use ($year){
                              $q->where('year', '>=', $year);
                            })  
                            ->orderBy($ordered_by, $sort_type)->get();

          return $filtered_products_arr = $products->whereIn('producttype_id', $existTypes)->where('approved', 1)
                                            ->pluck('id')->toArray();
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) 
          {
          	$existTypes = [1, 2, 3];
            $year = CarYear::where('id', $car_year_id)->first()->year;
            $model = CarModel::where('id', $car_model_id)->first()->carmodel;
            $products = Product::where('approved', 1)
                            ->whereIn('cartype_id', $carTypes)
                            ->where('car_made_id', $car_made_id)
                            ->whereIn('transmission_id', $transmission_id)
                            ->whereIn('producttype_id', [1, 2, 3])
                            ->whereHas('car_model', function($q) use ($model){
                              $q->where('carmodel', $model);
                            })  
                            ->whereHas('year_from_func', function($q) use ($year){
                              $q->where('year', '<=', $year);
                            }) 
                            ->whereHas('year_to_func', function($q) use ($year){
                              $q->where('year', '>=', $year);
                            })  
                            ->orderBy($ordered_by, $sort_type)->get();

          return $filtered_products_arr = $products->whereIn('producttype_id', $existTypes)->where('approved', 1)
                                            ->pluck('id')->toArray();
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
        	$existTypes = [1];
          $year = CarYear::where('id', $car_year_id)->first()->year;
          $model = CarModel::where('id', $car_model_id)->first()->carmodel;
            $products = Product::where('approved', 1)
                            ->whereIn('cartype_id', $carTypes)
                            ->where('car_made_id', $car_made_id)
                            ->whereIn('transmission_id', $transmission_id)
                            ->where('producttype_id', 1)
                            ->whereHas('car_model', function($q) use ($model){
                              $q->where('carmodel', $model);
                            })  
                            ->whereHas('year_from_func', function($q) use ($year){
                              $q->where('year', '<=', $year);
                            }) 
                            ->whereHas('year_to_func', function($q) use ($year){
                              $q->where('year', '>=', $year);
                            })  
                            ->orderBy($ordered_by, $sort_type)->get();

          return $filtered_products_arr = $products->whereIn('producttype_id', $existTypes)->where('approved', 1)
                                            ->pluck('id')->toArray();
        } // end guest case
     }
    // end search products all_search  
} // end class
