<?php

namespace App\Http\Controllers\Api\V1\User\Products;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Gate;
use App\Http\Requests\SearchApisRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Schema;
use App\Http\Resources\Website\Products\FrontProductsApiResource;
use App\Http\Resources\Website\Products\SpecificFrontProductsApiResource;
use Auth;
use App\Models\Productview;
use DB;
use App\Http\Resources\User\ProductReviews\ProductReviewsApiResource;
use App\Models\AddVendor;
use App\Models\Productreview;
use App\Models\Evaluationproduct;
use App\Http\Resources\Website\User\EvaluationProducts\UserEvaluationProductsApiResource;
use App\Http\Resources\Website\Products\MostlyViewedFrontProductsApiResource;
use App\Http\Requests\Api\V1\User\Front\SelectCarTypeApiRequest;

// use App\Http\Resources\Admin\Manufacturer\ManufacturerApiResource;
// use App\Http\Resources\Admin\OriginCountry\OriginCountryApiResource;
use App\Models\Manufacturer;
use App\Models\Prodcountry;
// use App\Http\Resources\Api\Admin\Allcategory\HomeAllcategoryApiResource;
use App\Http\Resources\Api\Admin\Allcategory\HomeAllcategoryFilterationApiResource;
use App\Models\Allcategory;
use App\Http\Resources\Admin\Manufacturer\ManufacturerFilterationApiResource;
use App\Http\Resources\Admin\OriginCountry\OriginCountryFilterationApiResource;


class HomeVendorProductsFilterationApiController extends Controller
{
    public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }

    public function home_vendor_products(SelectCarTypeApiRequest $request, $id)
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

      $vendor = AddVendor::FindOrFail($id);
      $vendor_name = $vendor->serial;
      $vendor_id = $vendor->id;

      $part_categories = $request->categories;
      $manufacturers   = $request->manufacturers;
      $origins         = $request->origins;
      $start_price     = $request->start_price;
      $end_price       = $request->end_price;
      $cartype_id      = $request->cartype_id;

      // case 1
        if ($part_categories == '' && $manufacturers == '' && $origins == '' && $start_price == '') {
          return $this->fetching_all_data($page, $ordered_by , $sort_type, $PAGINATION_COUNT, $cartype_id, $vendor_id, $vendor_name);
        }

        // case 1
        if ($part_categories != '' && $manufacturers == '' && $origins == '' && $start_price == '') {
          $part_categories = json_decode($request->categories);
        // $manufacturers   = json_decode($request->manufacturers);
        // $origins         = json_decode($request->origins);
          return $this->part_categories_search($part_categories, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $vendor_id, $vendor_name);
        }

        // case 2
        if ($part_categories == '' && $manufacturers != '' && $origins == '' && $start_price == '') {
          // $part_categories = json_decode($request->categories);
        $manufacturers   = json_decode($request->manufacturers);
        // $origins         = json_decode($request->origins);
          return $this->manufacturers_search($manufacturers, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $vendor_id, $vendor_name);
        }

        // case 3
        if ($part_categories == '' && $manufacturers == '' && $origins != '' && $start_price == '') {
          // $part_categories = json_decode($request->categories);
        // $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->origins_search($origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $vendor_id, $vendor_name);
        }

        // case 4
        if ($part_categories == '' && $manufacturers == '' && $origins == '' && $start_price != '') {
          // $part_categories = json_decode($request->categories);
        // $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->price_search($start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $vendor_id, $vendor_name);
        }

        // case 5
        if ($part_categories != '' && $manufacturers != '' && $origins == '' && $start_price == '') {
          $part_categories = json_decode($request->categories);
          // return $part_categories;
        $manufacturers   = json_decode($request->manufacturers);
        //$origins         = json_decode($request->origins);
        //return 'bbbv';
          return $this->part_categories_manufacturers_search($part_categories, $manufacturers, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $vendor_id, $vendor_name);
        }

        // case 6
        if ($part_categories != '' && $manufacturers == '' && $origins != '' && $start_price == '') {
          $part_categories = json_decode($request->categories);
        //$manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->part_categories_origins_search($part_categories, $origins, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $vendor_id, $vendor_name);
        }

        // case 7
        if ($part_categories != '' && $manufacturers == '' && $origins == '' && $start_price != '') {
          $part_categories = json_decode($request->categories);
        //$manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->part_categories_price_search($part_categories, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $vendor_id, $vendor_name);
        }

        // case 8
        if ($part_categories != '' && $manufacturers != '' && $origins != '' && $start_price == '') {
          $part_categories = json_decode($request->categories);
        $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->part_manufacturers_origins_search($part_categories, $manufacturers, $origins, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $vendor_id, $vendor_name);
        }

        // case 9
        if ($part_categories != '' && $manufacturers != '' && $origins == '' && $start_price != '') {
          $part_categories = json_decode($request->categories);
        $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->part_manufacturers_price_search($part_categories, $manufacturers, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $vendor_id, $vendor_name);
        }

        // case 10
        if ($part_categories != '' && $manufacturers == '' && $origins != '' && $start_price != '') {
          $part_categories = json_decode($request->categories);
        $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->part_origins_price_search($part_categories, $origins, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $vendor_id, $vendor_name);
        }

        // case 11
        if ($part_categories != '' && $manufacturers != '' && $origins != '' && $start_price != '') {
          $part_categories = json_decode($request->categories);
        $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->four_hand_search($part_categories, $manufacturers, $origins, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $vendor_id, $vendor_name);
        }

        // case 12
        if ($part_categories == '' && $manufacturers != '' && $origins != '' && $start_price == '') {
          //$part_categories = json_decode($request->categories);
        $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->manufacturers_origins_search($manufacturers, $origins, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $vendor_id, $vendor_name);
        }

        // case 13
        if ($part_categories == '' && $manufacturers != '' && $origins == '' && $start_price != '') {
            //$part_categories = json_decode($request->categories);
            $manufacturers   = json_decode($request->manufacturers);
            $origins         = json_decode($request->origins);
          return $this->manufacturers_price_search($manufacturers, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $vendor_id, $vendor_name);
        }

        // case 14
        if ($part_categories == '' && $manufacturers != '' && $origins != '' && $start_price != '') {
            //$part_categories = json_decode($request->categories);
            $manufacturers   = json_decode($request->manufacturers);
            $origins         = json_decode($request->origins);
          return $this->manufacturers_origins_price_search($manufacturers, $origins, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $vendor_id, $vendor_name);
        }


        // case 15
        if ($part_categories == '' && $manufacturers == '' && $origins != '' && $start_price != '') {
            //$part_categories = json_decode($request->categories);
            // $manufacturers   = json_decode($request->manufacturers);
            $origins         = json_decode($request->origins);
          return $this->origins_price_search($origins, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $vendor_id, $vendor_name);
        }
    }

    public function fetching_all_data($page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $vendor_id, $vendor_name)
    {
        $lang = $this->getLang();
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
                                       ->where('cartype_id', $cartype_id)
                                       ->where('vendor_id', $vendor_id)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get();

            $products_arr = Product::where('approved', 1)
                                       ->where('producttype_id', 1)
                                       ->where('cartype_id', $cartype_id)
                                       ->where('vendor_id', $vendor_id)
                                        ->pluck('id')->toArray();

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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
                $new_products = Product::where('approved', 1)
                                ->whereIn('producttype_id', [1, 2, 3])
                                ->where('cartype_id', $cartype_id)
                                ->where('vendor_id', $vendor_id)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                ->orderBy($ordered_by, $sort_type)->get(); 

            $products_arr = Product::where('approved', 1)
                                ->whereIn('producttype_id', [1, 2, 3])
                                ->where('cartype_id', $cartype_id)
                                ->where('vendor_id', $vendor_id)
                                ->pluck('id')->toArray();

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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
            $new_products = Product::where('approved', 1)
                                      ->where('producttype_id', 1)
                                      ->where('cartype_id', $cartype_id)
                                      ->where('vendor_id', $vendor_id)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $products_arr = Product::where('approved', 1)
                                      ->where('producttype_id', 1)
                                      ->where('cartype_id', $cartype_id)
                                      ->where('vendor_id', $vendor_id)
                                        ->pluck('id')->toArray();

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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);          
        } // end guest case
    }


    public function part_categories_search($part_categories, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $vendor_id, $vendor_name)
    {
        $lang = $this->getLang();
      //return $part_categories;
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
                $new_products = Product::where('approved', 1)
                                       ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
                                       ->where('vendor_id', $vendor_id)
                                       ->whereIn('allcategory_id', $part_categories)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $products_arr = Product::where('approved', 1)
                                       ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
                                       ->where('vendor_id', $vendor_id)
                                       ->whereIn('allcategory_id', $part_categories)
                                ->pluck('id')->toArray();

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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
                $new_products = Product::where('approved', 1)
                                ->whereIn('producttype_id', [1, 2, 3])->where('cartype_id', $cartype_id)
                                ->where('vendor_id', $vendor_id)
                                ->whereIn('allcategory_id', $part_categories)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                ->orderBy($ordered_by, $sort_type)->get(); 

        $products_arr = Product::where('approved', 1)
                                ->whereIn('producttype_id', [1, 2, 3])->where('cartype_id', $cartype_id)
                                ->where('vendor_id', $vendor_id)
                                ->whereIn('allcategory_id', $part_categories)
                                ->pluck('id')->toArray();

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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
            $new_products = Product::where('approved', 1)
            ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
            ->where('vendor_id', $vendor_id)
                                   ->whereIn('allcategory_id', $part_categories)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $products_arr = Product::where('approved', 1)
            ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
            ->where('vendor_id', $vendor_id)
                                   ->whereIn('allcategory_id', $part_categories)
                                ->pluck('id')->toArray();

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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);          
        } // end guest case
    }

    public function part_manufacturers_origins_search($part_categories, $manufacturers, $origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $vendor_id, $vendor_name)
    {
        $lang = $this->getLang();
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
                $new_products = Product::where('approved', 1)
                ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
                ->where('vendor_id', $vendor_id)
                                        ->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->whereIn('prodcountry_id', $origins)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
       $products_arr = Product::where('approved', 1)
                ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
                ->where('vendor_id', $vendor_id)
                                        ->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->pluck('id')->toArray(); 

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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
              $new_products = Product::where('approved', 1)
              ->whereIn('producttype_id', [1, 2, 3])->where('cartype_id', $cartype_id)
              ->where('vendor_id', $vendor_id)
                                        ->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->whereIn('prodcountry_id', $origins)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $products_arr = Product::where('approved', 1)
              ->whereIn('producttype_id', [1, 2, 3])->where('cartype_id', $cartype_id)
              ->where('vendor_id', $vendor_id)
                                        ->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->pluck('id')->toArray(); 

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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
              $new_products = Product::where('approved', 1)
              ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
              ->where('vendor_id', $vendor_id)
                                        ->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->whereIn('prodcountry_id', $origins)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $products_arr = Product::where('approved', 1)
              ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
              ->where('vendor_id', $vendor_id)
                                        ->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->pluck('id')->toArray(); 

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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);         
        } // end guest case
    }

    public function part_manufacturers_price_search($part_categories, $manufacturers, $start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $vendor_id, $vendor_name)
    {
        $lang = $this->getLang();
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $new_products = Product::where('approved', 1)
              ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
              ->where('vendor_id', $vendor_id)
                                        ->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $products_arr = Product::where('approved', 1)
              ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
              ->where('vendor_id', $vendor_id)
                                        ->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)
                                        ->pluck('id')->toArray(); 

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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $new_products = Product::where('approved', 1)
            ->whereIn('producttype_id', [1, 2, 3])->where('cartype_id', $cartype_id)
            ->where('vendor_id', $vendor_id)
                                        ->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $products_arr = Product::where('approved', 1)
            ->whereIn('producttype_id', [1, 2, 3])->where('cartype_id', $cartype_id)
            ->where('vendor_id', $vendor_id)
                                        ->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)
                                        ->pluck('id')->toArray(); 

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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
             $new_products = Product::where('approved', 1)
             ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
             ->where('vendor_id', $vendor_id)
                                        ->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get();

        $products_arr = Product::where('approved', 1)
             ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
             ->where('vendor_id', $vendor_id)
                                        ->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)
                                        ->pluck('id')->toArray();

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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);        
        } // end guest case
    }

    public function origins_price_search($origins, $start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $vendor_id, $vendor_name)
    {
        $lang = $this->getLang();
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
               $new_products = Product::where('approved', 1)
               ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
               ->where('vendor_id', $vendor_id)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $products_arr = Product::where('approved', 1)
               ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
               ->where('vendor_id', $vendor_id)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)
                                        ->pluck('id')->toArray(); 

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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
             $new_products = Product::where('approved', 1)
             ->whereIn('producttype_id', [1, 2, 3])->where('cartype_id', $cartype_id)
             ->where('vendor_id', $vendor_id)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $products_arr = Product::where('approved', 1)
             ->whereIn('producttype_id', [1, 2, 3])->where('cartype_id', $cartype_id)
             ->where('vendor_id', $vendor_id)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)
                                        ->pluck('id')->toArray(); 

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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
             $new_products = Product::where('approved', 1)
             ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
             ->where('vendor_id', $vendor_id)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $products_arr = Product::where('approved', 1)
             ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
             ->where('vendor_id', $vendor_id)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)
                                        ->pluck('id')->toArray(); 

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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);         
        } // end guest case
    }

    public function manufacturers_origins_price_search($manufacturers, $origins, $start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $vendor_id, $vendor_name)
    {
        $lang = $this->getLang();
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
               $new_products = Product::where('approved', 1)
               ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
               ->where('vendor_id', $vendor_id)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $products_arr = Product::where('approved', 1)
               ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
               ->where('vendor_id', $vendor_id)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)
                                        ->pluck('id')->toArray(); 

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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
             $new_products = Product::where('approved', 1)
             ->whereIn('producttype_id', [1, 2, 3])->where('cartype_id', $cartype_id)
             ->where('vendor_id', $vendor_id)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        
        $products_arr = Product::where('approved', 1)
             ->whereIn('producttype_id', [1, 2, 3])->where('cartype_id', $cartype_id)
             ->where('vendor_id', $vendor_id)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)
                                        ->pluck('id')->toArray();

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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
             $new_products = Product::where('approved', 1)
             ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
             ->where('vendor_id', $vendor_id)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $products_arr = Product::where('approved', 1)
             ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
             ->where('vendor_id', $vendor_id)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)
                                        ->pluck('id')->toArray(); 

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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);         
        } // end guest case
    }

    public function four_hand_search($part_categories, $manufacturers, $origins, $start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $vendor_id, $vendor_name)
    {
        $lang = $this->getLang();
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
                $new_products = Product::where('approved', 1)
                ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
                ->where('vendor_id', $vendor_id)
                                        ->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $products_arr = Product::where('approved', 1)
                ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
                ->where('vendor_id', $vendor_id)
                                        ->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)
                                        ->pluck('id')->toArray(); 

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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200); 
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
               $new_products = Product::where('approved', 1)
               ->whereIn('producttype_id', [1, 2, 3])->where('cartype_id', $cartype_id)
               ->where('vendor_id', $vendor_id)
                                        ->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $products_arr = Product::where('approved', 1)
               ->whereIn('producttype_id', [1, 2, 3])->where('cartype_id', $cartype_id)
               ->where('vendor_id', $vendor_id)
                                        ->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)
                                        ->pluck('id')->toArray(); 

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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
               $new_products = Product::where('approved', 1)
               ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
               ->where('vendor_id', $vendor_id)
                                        ->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $products_arr = Product::where('approved', 1)
               ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
               ->where('vendor_id', $vendor_id)
                                        ->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)
                                        ->pluck('id')->toArray(); 

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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);         
        } // end guest case
    }

    public function part_origins_price_search($part_categories, $origins, $start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $vendor_id, $vendor_name)
    {
        $lang = $this->getLang();
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
               $new_products = Product::where('approved', 1)
               ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
               ->where('vendor_id', $vendor_id)
                                        ->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $products_arr = Product::where('approved', 1)
               ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
               ->where('vendor_id', $vendor_id)
                                        ->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)
                                        ->pluck('id')->toArray();

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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
             $new_products = Product::where('approved', 1)
             ->whereIn('producttype_id', [1, 2, 3])->where('cartype_id', $cartype_id)
             ->where('vendor_id', $vendor_id)
                                        ->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $products_arr = Product::where('approved', 1)
             ->whereIn('producttype_id', [1, 2, 3])->where('cartype_id', $cartype_id)
             ->where('vendor_id', $vendor_id)
                                        ->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)
                                        ->pluck('id')->toArray(); 

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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
             $new_products = Product::where('approved', 1)
             ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
             ->where('vendor_id', $vendor_id)
                                        ->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)
                                       // ->skip(($page-1)*$PAGINATION_COUNT)
                                       // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $products_arr = Product::where('approved', 1)
             ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
             ->where('vendor_id', $vendor_id)
                                        ->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->where('actual_price', '>=', $start_price)
                                        ->where('actual_price', '<=', $end_price)
                                        ->pluck('id')->toArray();

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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);         
        } // end guest case
    }

public function price_search($start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $vendor_id, $vendor_name)
    {
        //return $part_categories;
        $lang = $this->getLang();
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
               $new_products = Product::where('approved', 1)
               ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
               ->where('vendor_id', $vendor_id)
                                ->where('actual_price', '>=', $start_price)
                                ->where('actual_price', '<=', $end_price)
                                        //->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $products_arr = Product::where('approved', 1)
               ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
               ->where('vendor_id', $vendor_id)
                                ->where('actual_price', '>=', $start_price)
                                ->where('actual_price', '<=', $end_price)
                                        ->pluck('id')->toArray(); 

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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
             $new_products = Product::where('approved', 1)
             ->whereIn('producttype_id', [1, 2, 3])->where('cartype_id', $cartype_id)
             ->where('vendor_id', $vendor_id)
                             ->where('actual_price', '>=', $start_price)
                                ->where('actual_price', '<=', $end_price)
                                        //->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $products_arr = Product::where('approved', 1)
             ->whereIn('producttype_id', [1, 2, 3])->where('cartype_id', $cartype_id)
             ->where('vendor_id', $vendor_id)
                             ->where('actual_price', '>=', $start_price)
                                ->where('actual_price', '<=', $end_price)
                                        ->pluck('id')->toArray();

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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
            $new_products = Product::where('approved', 1)
            ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
            ->where('vendor_id', $vendor_id)
                                ->where('actual_price', '>=', $start_price)
                                ->where('actual_price', '<=', $end_price)
                                        //->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $products_arr = Product::where('approved', 1)
            ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
            ->where('vendor_id', $vendor_id)
                                ->where('actual_price', '>=', $start_price)
                                ->where('actual_price', '<=', $end_price)
                                        ->pluck('id')->toArray(); 

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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);          
        } // end guest case
    }

    public function part_categories_price_search($part_categories, $start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $vendor_id, $vendor_name)
    {
        $lang = $this->getLang();
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $new_products = Product::where('approved', 1)
              ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
              ->where('vendor_id', $vendor_id)
                                ->where('actual_price', '>=', $start_price)
                                ->where('actual_price', '<=', $end_price)
                                ->whereIn('allcategory_id', $part_categories)
                                        //->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $products_arr = Product::where('approved', 1)
              ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
              ->where('vendor_id', $vendor_id)
                                ->where('actual_price', '>=', $start_price)
                                ->where('actual_price', '<=', $end_price)
                                ->whereIn('allcategory_id', $part_categories)
                                ->pluck('id')->toArray();

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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200); 
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
             $new_products = Product::where('approved', 1)
             ->whereIn('producttype_id', [1, 2, 3])->where('cartype_id', $cartype_id)
             ->where('vendor_id', $vendor_id)
                                ->where('actual_price', '>=', $start_price)
                                ->where('actual_price', '<=', $end_price)
                                ->whereIn('allcategory_id', $part_categories)
                                        //->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $products_arr = Product::where('approved', 1)
             ->whereIn('producttype_id', [1, 2, 3])->where('cartype_id', $cartype_id)
             ->where('vendor_id', $vendor_id)
                                ->where('actual_price', '>=', $start_price)
                                ->where('actual_price', '<=', $end_price)
                                ->whereIn('allcategory_id', $part_categories)
                                ->pluck('id')->toArray(); 

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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
             $new_products = Product::where('approved', 1)
             ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
             ->where('vendor_id', $vendor_id)
                                ->where('actual_price', '>=', $start_price)
                                ->where('actual_price', '<=', $end_price)
                                ->whereIn('allcategory_id', $part_categories)
                                        //->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $products_arr = Product::where('approved', 1)
             ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
             ->where('vendor_id', $vendor_id)
                                ->where('actual_price', '>=', $start_price)
                                ->where('actual_price', '<=', $end_price)
                                ->whereIn('allcategory_id', $part_categories)
                                ->pluck('id')->toArray();

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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);         
        } // end guest case
    }

    public function manufacturers_price_search($manufacturers, $start_price, $end_price, $page, $ordered_by , $sort_type, $PAGINATION_COUNT, $cartype_id, $vendor_id, $vendor_name)
    {
        $lang = $this->getLang();
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
                $new_products = Product::where('approved', 1)
                ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
                ->where('vendor_id', $vendor_id)
                                ->where('actual_price', '>=', $start_price)
                                ->where('actual_price', '<=', $end_price)
                                ->whereIn('manufacturer_id', $manufacturers)
                                        //->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $products_arr = Product::where('approved', 1)
                ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
                ->where('vendor_id', $vendor_id)
                                ->where('actual_price', '>=', $start_price)
                                ->where('actual_price', '<=', $end_price)
                                ->whereIn('manufacturer_id', $manufacturers)
                                        ->pluck('id')->toArray();

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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
              $new_products = Product::where('approved', 1)
              ->whereIn('producttype_id', [1, 2, 3])->where('cartype_id', $cartype_id)
              ->where('vendor_id', $vendor_id)
                                ->where('actual_price', '>=', $start_price)
                                ->where('actual_price', '<=', $end_price)
                                ->whereIn('manufacturer_id', $manufacturers)
                                        //->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $products_arr = Product::where('approved', 1)
              ->whereIn('producttype_id', [1, 2, 3])->where('cartype_id', $cartype_id)
              ->where('vendor_id', $vendor_id)
                                ->where('actual_price', '>=', $start_price)
                                ->where('actual_price', '<=', $end_price)
                                ->whereIn('manufacturer_id', $manufacturers)
                                        ->pluck('id')->toArray();

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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
              $new_products = Product::where('approved', 1)->where('producttype_id', 1)->where('cartype_id', $cartype_id)
              ->where('vendor_id', $vendor_id)
                               ->where('actual_price', '>=', $start_price)
                                ->where('actual_price', '<=', $end_price)
                                ->whereIn('manufacturer_id', $manufacturers)
                                        //->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $products_arr = Product::where('approved', 1)->where('producttype_id', 1)->where('cartype_id', $cartype_id)
              ->where('vendor_id', $vendor_id)
                               ->where('actual_price', '>=', $start_price)
                                ->where('actual_price', '<=', $end_price)
                                ->whereIn('manufacturer_id', $manufacturers)
                                        ->pluck('id')->toArray();

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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);         
        } // end guest case
    }

    public function manufacturers_search($manufacturers, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $vendor_id, $vendor_name)
    {
        $lang = $this->getLang();
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $new_products = Product::where('approved', 1)->where('producttype_id', 1)->where('cartype_id', $cartype_id)
              ->where('vendor_id', $vendor_id)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $products_arr = Product::where('approved', 1)->where('producttype_id', 1)->where('cartype_id', $cartype_id)
              ->where('vendor_id', $vendor_id)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->pluck('id')->toArray();

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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $new_products = Product::where('approved', 1)->whereIn('producttype_id', [1, 2, 3])->where('cartype_id', $cartype_id)
            ->where('vendor_id', $vendor_id)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $products_arr = Product::where('approved', 1)->whereIn('producttype_id', [1, 2, 3])->where('cartype_id', $cartype_id)
            ->where('vendor_id', $vendor_id)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->pluck('id')->toArray();

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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
            $new_products = Product::where('approved', 1)
            ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
            ->where('vendor_id', $vendor_id)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
         $products_arr = Product::where('approved', 1)
            ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
            ->where('vendor_id', $vendor_id)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->pluck('id')->toArray();

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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);         
        } // end guest case
    }

    public function origins_search($origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $vendor_id, $vendor_name)
    {
        $lang = $this->getLang();
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $new_products = Product::where('approved', 1)
              ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
              ->where('vendor_id', $vendor_id)
                                        ->whereIn('prodcountry_id', $origins)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $products_arr = Product::where('approved', 1)
              ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
              ->where('vendor_id', $vendor_id)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->pluck('id')->toArray(); 

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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $new_products = Product::where('approved', 1)
            ->whereIn('producttype_id', [1, 2, 3])->where('cartype_id', $cartype_id)
            ->where('vendor_id', $vendor_id)
                                          ->whereIn('prodcountry_id', $origins)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        
        $products_arr = Product::where('approved', 1)
            ->whereIn('producttype_id', [1, 2, 3])->where('cartype_id', $cartype_id)
            ->where('vendor_id', $vendor_id)
                                          ->whereIn('prodcountry_id', $origins)
                                        ->pluck('id')->toArray(); 
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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
            $new_products = Product::where('approved', 1)
            ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
            ->where('vendor_id', $vendor_id)
                                        ->whereIn('prodcountry_id', $origins)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $products_arr = Product::where('approved', 1)
            ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
            ->where('vendor_id', $vendor_id)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->pluck('id')->toArray(); 

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
                'vendor_name' => $vendor_name,
                'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
            ], 200);         
        } // end guest case
    }

    public function part_categories_manufacturers_search($part_categories, $manufacturers, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $vendor_id, $vendor_name)
    {
        $lang = $this->getLang();
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
             $new_products = Product::where('approved', 1)
             ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
             ->where('vendor_id', $vendor_id)
                                        ->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
            $products_arr = Product::where('approved', 1)
             ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
             ->where('vendor_id', $vendor_id)
                                        ->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->pluck('id')->toArray();

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
                    'vendor_name' => $vendor_name,
                    'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                ], 200); 
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $new_products = Product::where('approved', 1)
            ->whereIn('producttype_id', [1, 2, 3])->where('cartype_id', $cartype_id)
            ->where('vendor_id', $vendor_id)
                                        ->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
            $products_arr = Product::where('approved', 1)
            ->whereIn('producttype_id', [1, 2, 3])->where('cartype_id', $cartype_id)
            ->where('vendor_id', $vendor_id)
                                        ->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->pluck('id')->toArray();

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
                    'vendor_name' => $vendor_name,
                    'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
          // return $part_categories;
           $new_products = Product::where('approved', 1)
           ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
           ->where('vendor_id', $vendor_id)
                                        ->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
            $products_arr = Product::where('approved', 1)
           ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
           ->where('vendor_id', $vendor_id)
                                        ->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        ->pluck('id')->toArray();

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
                    'vendor_name' => $vendor_name,
                    'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                ], 200);          
        } // end guest case
    }

    public function part_categories_origins_search($part_categories, $origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $vendor_id, $vendor_name)
    {
        $lang = $this->getLang();
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $new_products = Product::where('approved', 1)
              ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
              ->where('vendor_id', $vendor_id)
                                        ->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
                $products_arr = Product::where('approved', 1)
              ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
              ->where('vendor_id', $vendor_id)
                                        ->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->pluck('id')->toArray(); 

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
                        'vendor_name' => $vendor_name,
                        'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                    ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $new_products = Product::where('approved', 1)
            ->whereIn('producttype_id', [1, 2, 3])->where('cartype_id', $cartype_id)
            ->where('vendor_id', $vendor_id)
                                        ->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
            $products_arr = Product::where('approved', 1)
            ->whereIn('producttype_id', [1, 2, 3])->where('cartype_id', $cartype_id)
            ->where('vendor_id', $vendor_id)
                                        ->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->pluck('id')->toArray();

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
                    'vendor_name' => $vendor_name,
                    'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
            $new_products = Product::where('approved', 1)
            ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
            ->where('vendor_id', $vendor_id)
                                        ->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
            $products_arr = Product::where('approved', 1)
            ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
            ->where('vendor_id', $vendor_id)
                                        ->whereIn('allcategory_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        ->pluck('id')->toArray();

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
                    'vendor_name' => $vendor_name,
                    'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                ], 200);         
        } // end guest case
    }

      public function manufacturers_origins_search($manufacturers, $origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $vendor_id, $vendor_name)
      {
          $lang = $this->getLang();
          // alraedy user logged in
          if (Auth::guard('api')->check() && Auth::user()) 
          {
              $user = Auth::user();
              $user_roles = $user->roles->pluck('title')->toArray();
            
            // case logged in user role is User 
            if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $new_products = Product::where('approved', 1)
              ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
              ->where('vendor_id', $vendor_id)
                                          ->whereIn('manufacturer_id', $manufacturers)
                                          ->whereIn('prodcountry_id', $origins)
                                          // ->skip(($page-1)*$PAGINATION_COUNT)
                                          // ->take($PAGINATION_COUNT)
                                          ->orderBy($ordered_by, $sort_type)->get(); 
              $products_arr = Product::where('approved', 1)
              ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
              ->where('vendor_id', $vendor_id)
                                          ->whereIn('manufacturer_id', $manufacturers)
                                          ->whereIn('prodcountry_id', $origins)
                                          ->pluck('id')->toArray();

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
                      'vendor_name' => $vendor_name,
                      'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                  ], 200);
            }  // end user case

            if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
              $new_products = Product::where('approved', 1)
              ->whereIn('producttype_id', [1, 2, 3])->where('cartype_id', $cartype_id)
              ->where('vendor_id', $vendor_id)
                                          ->whereIn('manufacturer_id', $manufacturers)
                                          ->whereIn('prodcountry_id', $origins)
                                          // ->skip(($page-1)*$PAGINATION_COUNT)
                                          // ->take($PAGINATION_COUNT)
                                          ->orderBy($ordered_by, $sort_type)->get(); 
              $products_arr = Product::where('approved', 1)
              ->whereIn('producttype_id', [1, 2, 3])->where('cartype_id', $cartype_id)
              ->where('vendor_id', $vendor_id)
                                          ->whereIn('manufacturer_id', $manufacturers)
                                          ->whereIn('prodcountry_id', $origins)
                                          ->pluck('id')->toArray();

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
                      'vendor_name' => $vendor_name,
                      'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                  ], 200);
            } // end vendor case
          }  // end case logged in 
          else{ // guest case
              $new_products = Product::where('approved', 1)
              ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
              ->where('vendor_id', $vendor_id)
                                          ->whereIn('manufacturer_id', $manufacturers)
                                          ->whereIn('prodcountry_id', $origins)
                                          // ->skip(($page-1)*$PAGINATION_COUNT)
                                          // ->take($PAGINATION_COUNT)
                                          ->orderBy($ordered_by, $sort_type)->get(); 
              $products_arr = Product::where('approved', 1)
              ->where('producttype_id', 1)->where('cartype_id', $cartype_id)
              ->where('vendor_id', $vendor_id)
                                          ->whereIn('manufacturer_id', $manufacturers)
                                          ->whereIn('prodcountry_id', $origins)
                                          ->pluck('id')->toArray();

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
                      'vendor_name' => $vendor_name,
                      'manufacturers_data' => $manufacturers_data,
                    'origins_data'       => $origins_data,
                    'cats_data'       => $cats_data,
                  ], 200);         
          } // end guest case
      }  // end probs
}
