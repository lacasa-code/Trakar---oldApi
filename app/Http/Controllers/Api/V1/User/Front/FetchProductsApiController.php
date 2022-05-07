<?php

namespace App\Http\Controllers\Api\V1\User\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Resources\Website\Products\FrontProductsApiResource;
use Gate;
use Symfony\Component\HttpFoundation\Response;
// use Illuminate\Support\Facades\Schema;
// use Auth;
use Carbon\Carbon;
use App\Models\Orderdetail;
use App\Http\Requests\Website\HomePage\NewlyAddedProductsApiRequest;
use Auth; // to fetch roles
use App\Http\Requests\Api\V1\User\Front\SelectCarTypeApiRequest;
use App\Models\AddVendor;

class FetchProductsApiController extends Controller
{
   public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }

  public function best_seller_products(SelectCarTypeApiRequest $request) // fetch top 6
    {
      $lang = $this->getLang();
     // $default_count = \Config::get('constants.pagination.items_per_page');
    //  $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;

      $default_count = 20;
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      if ($ordered_by == 'price') {
        $ordered_by = 'actual_price';
      }

      $part_categories = $request->categories;
     // return $part_categories;
      $manufacturers   = $request->manufacturers;
      $origins         = $request->origins;
      $start_price     = $request->start_price;
      $end_price       = $request->end_price;
      $cartype_id      = $request->cartype_id;

      // case 1
        if ($part_categories == '' && $manufacturers == '' && $origins == '' && $start_price == '') {
          return $this->fetching_all_data($page, $ordered_by , $sort_type, $PAGINATION_COUNT, $cartype_id);
        }

        // case 1
        if ($part_categories != '' && $manufacturers == '' && $origins == '' && $start_price == '') {
          $part_categories = json_decode($request->categories);
        // $manufacturers   = json_decode($request->manufacturers);
        // $origins         = json_decode($request->origins);
          return $this->part_categories_search($part_categories, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id);
        }

        // case 2
        if ($part_categories == '' && $manufacturers != '' && $origins == '' && $start_price == '') {
          // $part_categories = json_decode($request->categories);
        $manufacturers   = json_decode($request->manufacturers);
        // $origins         = json_decode($request->origins);
          return $this->manufacturers_search($manufacturers, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id);
        }

        // case 3
        if ($part_categories == '' && $manufacturers == '' && $origins != '' && $start_price == '') {
          // $part_categories = json_decode($request->categories);
        // $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->origins_search($origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id);
        }

        // case 4
        if ($part_categories == '' && $manufacturers == '' && $origins == '' && $start_price != '') {
          // $part_categories = json_decode($request->categories);
        // $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->price_search($start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id);
        }

        // case 5
        if ($part_categories != '' && $manufacturers != '' && $origins == '' && $start_price == '') {
          $part_categories = json_decode($request->categories);
          // return $part_categories;
        $manufacturers   = json_decode($request->manufacturers);
        //$origins         = json_decode($request->origins);
        //return 'bbbv';
          return $this->part_categories_manufacturers_search($part_categories, $manufacturers, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id);
        }

        // case 6
        if ($part_categories != '' && $manufacturers == '' && $origins != '' && $start_price == '') {
          $part_categories = json_decode($request->categories);
        //$manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->part_categories_origins_search($part_categories, $origins, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id);
        }

        // case 7
        if ($part_categories != '' && $manufacturers == '' && $origins == '' && $start_price != '') {
          $part_categories = json_decode($request->categories);
        //$manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->part_categories_price_search($part_categories, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id);
        }

        // case 8
        if ($part_categories != '' && $manufacturers != '' && $origins != '' && $start_price == '') {
          $part_categories = json_decode($request->categories);
        $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->part_manufacturers_origins_search($part_categories, $manufacturers, $origins, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id);
        }

        // case 9
        if ($part_categories != '' && $manufacturers != '' && $origins == '' && $start_price != '') {
          $part_categories = json_decode($request->categories);
        $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->part_manufacturers_price_search($part_categories, $manufacturers, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id);
        }

        // case 10
        if ($part_categories != '' && $manufacturers == '' && $origins != '' && $start_price != '') {
          $part_categories = json_decode($request->categories);
        $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->part_origins_price_search($part_categories, $origins, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id);
        }

        // case 11
        if ($part_categories != '' && $manufacturers != '' && $origins != '' && $start_price != '') {
          $part_categories = json_decode($request->categories);
        $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->four_hand_search($part_categories, $manufacturers, $origins, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id);
        }

        // case 12
        if ($part_categories == '' && $manufacturers != '' && $origins != '' && $start_price == '') {
          //$part_categories = json_decode($request->categories);
        $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->manufacturers_origins_search($manufacturers, $origins, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id);
        }

        // case 13
        if ($part_categories == '' && $manufacturers != '' && $origins == '' && $start_price != '') {
            //$part_categories = json_decode($request->categories);
            $manufacturers   = json_decode($request->manufacturers);
            $origins         = json_decode($request->origins);
          return $this->manufacturers_price_search($manufacturers, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id);
        }

        // case 14
        if ($part_categories == '' && $manufacturers != '' && $origins != '' && $start_price != '') {
            //$part_categories = json_decode($request->categories);
            $manufacturers   = json_decode($request->manufacturers);
            $origins         = json_decode($request->origins);
          return $this->manufacturers_origins_price_search($manufacturers, $origins, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id);
        }


        // case 15
        if ($part_categories == '' && $manufacturers == '' && $origins != '' && $start_price != '') {
            //$part_categories = json_decode($request->categories);
            // $manufacturers   = json_decode($request->manufacturers);
            $origins         = json_decode($request->origins);
          return $this->origins_price_search($origins, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id);
        }
    }

    public function fetching_all_data($page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id)
    {
        $lang = $this->getLang();
        $common_types = [$cartype_id, 7];
        
      //return $part_categories;
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
               
              $indexes = Orderdetail::where('approved', 1)
                     ->where('producttype_id', 1)
                     ->whereHas('product', function($q) use ($cartype_id, $common_types){
                      $q->whereIn('cartype_id', $common_types); // added june 29 2021);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

              $sorter = static function ($produto) use ($indexes) {
                      return array_search($produto->id, $indexes);
                   };

              $products = Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereIn('id', $indexes)
                                  ->get()
                                  ->sortBy($sorter);

              if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
              $data = FrontProductsApiResource::collection($products); 
              return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    // 'total'       => $total,
                ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $indexes = Orderdetail::where('approved', 1)
                     ->whereIn('producttype_id', [1, 2, 3])
                     ->whereHas('product', function($q) use ($cartype_id, $common_types){
                      $q->whereIn('cartype_id', $common_types); // added june 29 2021);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->whereIn('producttype_id', [1, 2, 3])
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            //->orderBy($ordered_by, $sort_type)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->whereIn('producttype_id', [1, 2, 3])
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
              $indexes = Orderdetail::where('approved', 1)->where('producttype_id', 1)
                     ->whereHas('product', function($q) use ($cartype_id, $common_types){
                      $q->whereIn('cartype_id', $common_types); // added june 29 2021);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();
                    // return $indexes;

              $sorter = static function ($produto) use ($indexes) {
                      return array_search($produto->id, $indexes);
                   };

              $products = Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereIn('id', $indexes)
                                  // ->orderBy($ordered_by, $sort_type)
                                  ->get()
                                 ->sortBy($sorter);
              if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
           
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
              $data = FrontProductsApiResource::collection($products); 
              return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    // 'total'       => $total,
                ], 200);       
        } // end guest case
    }


    public function part_categories_search($part_categories, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id)
    {
        $lang = $this->getLang();
        $common_types = [$cartype_id, 7];
        $part_categories = $part_categories;
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
                $indexes = Orderdetail::where('approved', 1)
                     ->where('producttype_id', 1)
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $part_categories){
                      $q->whereIn('cartype_id', $common_types)
                        ->whereHas('allcategory', function($q) use ($part_categories){
                                $q->whereIn('allcategories.id', $part_categories);
                       });
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->where('producttype_id', 1)
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->whereHas('allcategory', function($q) use ($part_categories){
                                $q->whereIn('allcategories.id', $part_categories);
                            })
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereHas('allcategory', function($q) use ($part_categories){
                                  $q->whereIn('allcategories.id', $part_categories);
                                  })
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200); 
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
              $indexes = Orderdetail::where('approved', 1)
                     ->whereIn('producttype_id', [1, 2, 3])
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $part_categories){
                      $q->whereIn('cartype_id', $common_types)
                        ->whereHas('allcategory', function($q) use ($part_categories){
                                $q->whereIn('allcategories.id', $part_categories);
                       });
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->whereIn('producttype_id', [1, 2, 3])
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->whereHas('allcategory', function($q) use ($part_categories){
                                $q->whereIn('allcategories.id', $part_categories);
                            })
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->whereIn('producttype_id', [1, 2, 3])
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereHas('allcategory', function($q) use ($part_categories){
                                  $q->whereIn('allcategories.id', $part_categories);
                                  })
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
          $part_categories = $part_categories;
         // return $part_categories;
            $indexes = Orderdetail::where('approved', 1)
                     ->where('producttype_id', 1)
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $part_categories){
                      $q->whereIn('cartype_id', $common_types)
                      ->whereHas('allcategory', function($q) use ($part_categories){
                                $q->whereIn('allcategories.id', $part_categories);
                       });
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

                   //  return $indexes;

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->where('producttype_id', 1)
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->whereHas('allcategory', function($q) use ($part_categories){
                                $q->whereIn('allcategories.id', $part_categories);
                            })
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereHas('allcategory', function($q) use ($part_categories){
                                  $q->whereIn('allcategories.id', $part_categories);
                                  })
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);        
        } // end guest case
    }

    public function part_manufacturers_origins_search($part_categories, $manufacturers, $origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id)
    {
        $lang = $this->getLang();
        $common_types = [$cartype_id, 7];
        
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $indexes = Orderdetail::where('approved', 1)
                     ->where('producttype_id', 1)
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $part_categories, $manufacturers, $origins){
                      $q->whereIn('cartype_id', $common_types)
                        ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                       })
                        ->whereIn('manufacturer_id', $manufacturers)
                        ->whereIn('prodcountry_id', $origins);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->where('producttype_id', 1)
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                           })->whereIn('manufacturer_id', $manufacturers)
                            ->whereIn('prodcountry_id', $origins)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereHas('allcategory', function($q) use ($part_categories){
                                    $q->whereIn('allcategories.id', $part_categories);
                                 })->whereIn('manufacturer_id', $manufacturers)
                                  ->whereIn('prodcountry_id', $origins)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);
          } // end user case
          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $indexes = Orderdetail::where('approved', 1)
                     ->whereIn('producttype_id', [1, 2, 3])
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $part_categories, $manufacturers, $origins){
                      $q->whereIn('cartype_id', $common_types)
                        ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                       })
                        ->whereIn('manufacturer_id', $manufacturers)
                        ->whereIn('prodcountry_id', $origins);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->whereIn('producttype_id', [1, 2, 3])
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                           })->whereIn('manufacturer_id', $manufacturers)
                            ->whereIn('prodcountry_id', $origins)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->whereIn('producttype_id', [1, 2, 3])
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereHas('allcategory', function($q) use ($part_categories){
                                    $q->whereIn('allcategories.id', $part_categories);
                                 })->whereIn('manufacturer_id', $manufacturers)
                                  ->whereIn('prodcountry_id', $origins)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
               $indexes = Orderdetail::where('approved', 1)
                     ->where('producttype_id', 1)
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $part_categories, $manufacturers, $origins){
                      $q->whereIn('cartype_id', $common_types)
                        ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                       })
                        ->whereIn('manufacturer_id', $manufacturers)
                        ->whereIn('prodcountry_id', $origins);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->where('producttype_id', 1)
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                           })->whereIn('manufacturer_id', $manufacturers)
                            ->whereIn('prodcountry_id', $origins)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereHas('allcategory', function($q) use ($part_categories){
                                    $q->whereIn('allcategories.id', $part_categories);
                                 })->whereIn('manufacturer_id', $manufacturers)
                                  ->whereIn('prodcountry_id', $origins)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);      
        } // end guest case
    }

    public function part_manufacturers_price_search($part_categories, $manufacturers, $start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id)
    {
        $lang = $this->getLang();
        $common_types = [$cartype_id, 7];   
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $indexes = Orderdetail::where('approved', 1)
                     ->where('producttype_id', 1)
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $part_categories, $manufacturers, $start_price, $end_price){
                      $q->whereIn('cartype_id', $common_types)
                        ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                       })
                        ->whereIn('manufacturer_id', $manufacturers)
                        ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->where('producttype_id', 1)
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                           })->whereIn('manufacturer_id', $manufacturers)
                            ->where('actual_price', '>=', $start_price)
                            ->where('actual_price', '<=', $end_price)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereHas('allcategory', function($q) use ($part_categories){
                                    $q->whereIn('allcategories.id', $part_categories);
                                 })->whereIn('manufacturer_id', $manufacturers)
                                  ->where('actual_price', '>=', $start_price)
                                  ->where('actual_price', '<=', $end_price)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);
          } // end user case
          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $indexes = Orderdetail::where('approved', 1)
                     ->whereIn('producttype_id', [1, 2, 3])
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $part_categories, $manufacturers, $start_price, $end_price){
                      $q->whereIn('cartype_id', $common_types)
                        ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                       })
                        ->whereIn('manufacturer_id', $manufacturers)
                        ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->whereIn('producttype_id', [1, 2, 3])
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                           })->whereIn('manufacturer_id', $manufacturers)
                            ->where('actual_price', '>=', $start_price)
                            ->where('actual_price', '<=', $end_price)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->whereIn('producttype_id', [1, 2, 3])
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereHas('allcategory', function($q) use ($part_categories){
                                    $q->whereIn('allcategories.id', $part_categories);
                                 })->whereIn('manufacturer_id', $manufacturers)
                                  ->where('actual_price', '>=', $start_price)
                                  ->where('actual_price', '<=', $end_price)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
               $indexes = Orderdetail::where('approved', 1)
                     ->where('producttype_id', 1)
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $part_categories, $manufacturers, $start_price, $end_price){
                      $q->whereIn('cartype_id', $common_types)
                        ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                       })
                        ->whereIn('manufacturer_id', $manufacturers)
                        ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->where('producttype_id', 1)
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                           })->whereIn('manufacturer_id', $manufacturers)
                            ->where('actual_price', '>=', $start_price)
                            ->where('actual_price', '<=', $end_price)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereHas('allcategory', function($q) use ($part_categories){
                                    $q->whereIn('allcategories.id', $part_categories);
                                 })->whereIn('manufacturer_id', $manufacturers)
                                  ->where('actual_price', '>=', $start_price)
                                  ->where('actual_price', '<=', $end_price)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);      
        } // end guest case     
    }

    public function origins_price_search($origins, $start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id)
    {
        $lang = $this->getLang();
        $common_types = [$cartype_id, 7];
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $indexes = Orderdetail::where('approved', 1)
                     ->where('producttype_id', 1)
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $origins, $start_price, $end_price){
                      $q->whereIn('cartype_id', $common_types)
                        ->whereIn('prodcountry_id', $origins)
                        ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->where('producttype_id', 1)
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->whereIn('prodcountry_id', $origins)
                            ->where('actual_price', '>=', $start_price)
                            ->where('actual_price', '<=', $end_price)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereIn('prodcountry_id', $origins)
                                  ->where('actual_price', '>=', $start_price)
                                  ->where('actual_price', '<=', $end_price)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);
          } // end user case
          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $indexes = Orderdetail::where('approved', 1)
                     ->whereIn('producttype_id', [1, 2, 3])
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $origins, $start_price, $end_price){
                      $q->whereIn('cartype_id', $common_types)
                        ->whereIn('prodcountry_id', $origins)
                        ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->whereIn('producttype_id', [1, 2, 3])
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->whereIn('prodcountry_id', $origins)
                            ->where('actual_price', '>=', $start_price)
                            ->where('actual_price', '<=', $end_price)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->whereIn('producttype_id', [1, 2, 3])
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereIn('prodcountry_id', $origins)
                                  ->where('actual_price', '>=', $start_price)
                                  ->where('actual_price', '<=', $end_price)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
               $indexes = Orderdetail::where('approved', 1)
                     ->where('producttype_id', 1)
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $origins, $start_price, $end_price){
                      $q->whereIn('cartype_id', $common_types)
                        ->whereIn('prodcountry_id', $origins)
                        ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->where('producttype_id', 1)
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->whereIn('prodcountry_id', $origins)
                            ->where('actual_price', '>=', $start_price)
                            ->where('actual_price', '<=', $end_price)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)
                                 ->whereIn('prodcountry_id', $origins)
                                  ->where('actual_price', '>=', $start_price)
                                  ->where('actual_price', '<=', $end_price)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);      
        } // end guest case   
    }

    public function manufacturers_origins_price_search($manufacturers, $origins, $start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id)
    {
        $lang = $this->getLang();
        $common_types = [$cartype_id, 7];
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $indexes = Orderdetail::where('approved', 1)
                     ->where('producttype_id', 1)
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $manufacturers, $origins, $start_price, $end_price){
                      $q->whereIn('cartype_id', $common_types)
                        ->whereIn('prodcountry_id', $origins)

                        ->whereIn('manufacturer_id', $manufacturers)
                        ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->where('producttype_id', 1)
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->whereIn('prodcountry_id', $origins)
                            ->whereIn('manufacturer_id', $manufacturers)
                            ->where('actual_price', '>=', $start_price)
                            ->where('actual_price', '<=', $end_price)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereIn('prodcountry_id', $origins)
                                  ->whereIn('manufacturer_id', $manufacturers)
                                  ->where('actual_price', '>=', $start_price)
                                  ->where('actual_price', '<=', $end_price)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);
          } // end user case
          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $indexes = Orderdetail::where('approved', 1)
                     ->whereIn('producttype_id', [1, 2, 3])
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $manufacturers, $origins, $start_price, $end_price){
                      $q->whereIn('cartype_id', $common_types)
                        ->whereIn('prodcountry_id', $origins)

                        ->whereIn('manufacturer_id', $manufacturers)
                        ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->whereIn('producttype_id', [1, 2, 3])
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->whereIn('prodcountry_id', $origins)
                            ->whereIn('manufacturer_id', $manufacturers)
                            ->where('actual_price', '>=', $start_price)
                            ->where('actual_price', '<=', $end_price)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->whereIn('producttype_id', [1, 2, 3])
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereIn('prodcountry_id', $origins)
                                  ->whereIn('manufacturer_id', $manufacturers)
                                  ->where('actual_price', '>=', $start_price)
                                  ->where('actual_price', '<=', $end_price)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
               $indexes = Orderdetail::where('approved', 1)
                     ->where('producttype_id', 1)
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $manufacturers, $origins, $start_price, $end_price){
                      $q->whereIn('cartype_id', $common_types)
                        ->whereIn('prodcountry_id', $origins)

                        ->whereIn('manufacturer_id', $manufacturers)
                        ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->where('producttype_id', 1)
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->whereIn('prodcountry_id', $origins)
                            ->whereIn('manufacturer_id', $manufacturers)
                            ->where('actual_price', '>=', $start_price)
                            ->where('actual_price', '<=', $end_price)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereIn('prodcountry_id', $origins)
                                  ->whereIn('manufacturer_id', $manufacturers)
                                  ->where('actual_price', '>=', $start_price)
                                  ->where('actual_price', '<=', $end_price)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);      
        } // end guest case     
        
    }

    public function four_hand_search($part_categories, $manufacturers, $origins, $start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id)
    {
        $lang = $this->getLang();
        $common_types = [$cartype_id, 7];
         // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $indexes = Orderdetail::where('approved', 1)
                     ->where('producttype_id', 1)
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $part_categories, $manufacturers, $origins, $start_price, $end_price){
                      $q->whereIn('cartype_id', $common_types)
                        ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                       })
                        ->whereIn('manufacturer_id', $manufacturers)
                        ->whereIn('prodcountry_id', $origins)
                        ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->where('producttype_id', 1)
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                           })->whereIn('manufacturer_id', $manufacturers)
                            ->whereIn('prodcountry_id', $origins)

                            ->where('actual_price', '>=', $start_price)
                            ->where('actual_price', '<=', $end_price)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereHas('allcategory', function($q) use ($part_categories){
                                    $q->whereIn('allcategories.id', $part_categories);
                                 })->whereIn('manufacturer_id', $manufacturers)
                                  ->whereIn('prodcountry_id', $origins)

                                  ->where('actual_price', '>=', $start_price)
                                  ->where('actual_price', '<=', $end_price)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);
          } // end user case
          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $indexes = Orderdetail::where('approved', 1)
                     ->whereIn('producttype_id', [1, 2, 3])
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $part_categories, $manufacturers, $origins, $start_price, $end_price){
                      $q->whereIn('cartype_id', $common_types)
                        ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                       })
                        ->whereIn('manufacturer_id', $manufacturers)
                        ->whereIn('prodcountry_id', $origins)

                        ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->whereIn('producttype_id', [1, 2, 3])
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                           })->whereIn('manufacturer_id', $manufacturers)
                            ->whereIn('prodcountry_id', $origins)

                            ->where('actual_price', '>=', $start_price)
                            ->where('actual_price', '<=', $end_price)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->whereIn('producttype_id', [1, 2, 3])
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereHas('allcategory', function($q) use ($part_categories){
                                    $q->whereIn('allcategories.id', $part_categories);
                                 })->whereIn('manufacturer_id', $manufacturers)
                                  ->whereIn('prodcountry_id', $origins)

                                  ->where('actual_price', '>=', $start_price)
                                  ->where('actual_price', '<=', $end_price)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
               $indexes = Orderdetail::where('approved', 1)
                     ->where('producttype_id', 1)
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $part_categories, $manufacturers, $origins, $start_price, $end_price){
                      $q->whereIn('cartype_id', $common_types)
                        ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                       })
                        ->whereIn('manufacturer_id', $manufacturers)
                        ->whereIn('prodcountry_id', $origins)

                        ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->where('producttype_id', 1)
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                           })->whereIn('manufacturer_id', $manufacturers)
                            ->whereIn('prodcountry_id', $origins)

                            ->where('actual_price', '>=', $start_price)
                            ->where('actual_price', '<=', $end_price)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereHas('allcategory', function($q) use ($part_categories){
                                    $q->whereIn('allcategories.id', $part_categories);
                                 })->whereIn('manufacturer_id', $manufacturers)
                                  ->whereIn('prodcountry_id', $origins)

                                  ->where('actual_price', '>=', $start_price)
                                  ->where('actual_price', '<=', $end_price)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);      
        } // end guest case  
        
    }

    public function part_origins_price_search($part_categories, $origins, $start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id)
    {
        $lang = $this->getLang();
        $common_types = [$cartype_id, 7];
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $indexes = Orderdetail::where('approved', 1)
                     ->where('producttype_id', 1)
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $part_categories, $origins, $start_price, $end_price){
                      $q->whereIn('cartype_id', $common_types)
                        ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                       })
                        ->whereIn('prodcountry_id', $origins)

                        ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->where('producttype_id', 1)
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                           })
                            ->whereIn('prodcountry_id', $origins)

                            ->where('actual_price', '>=', $start_price)
                            ->where('actual_price', '<=', $end_price)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereHas('allcategory', function($q) use ($part_categories){
                                    $q->whereIn('allcategories.id', $part_categories);
                                 })
                                  ->whereIn('prodcountry_id', $origins)

                                  ->where('actual_price', '>=', $start_price)
                                  ->where('actual_price', '<=', $end_price)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);
          } // end user case
          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $indexes = Orderdetail::where('approved', 1)
                     ->whereIn('producttype_id', [1, 2, 3])
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $part_categories, $origins, $start_price, $end_price){
                      $q->whereIn('cartype_id', $common_types)
                        ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                       })
                        ->whereIn('prodcountry_id', $origins)

                        ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->whereIn('producttype_id', [1, 2, 3])
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                           })
                            ->whereIn('prodcountry_id', $origins)

                            ->where('actual_price', '>=', $start_price)
                            ->where('actual_price', '<=', $end_price)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->whereIn('producttype_id', [1, 2, 3])
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereHas('allcategory', function($q) use ($part_categories){
                                    $q->whereIn('allcategories.id', $part_categories);
                                 })
                                  ->whereIn('prodcountry_id', $origins)

                                  ->where('actual_price', '>=', $start_price)
                                  ->where('actual_price', '<=', $end_price)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
               $indexes = Orderdetail::where('approved', 1)
                     ->where('producttype_id', 1)
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $part_categories, $origins, $start_price, $end_price){
                      $q->whereIn('cartype_id', $common_types)
                        ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                       })
                        ->whereIn('prodcountry_id', $origins)

                        ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->where('producttype_id', 1)
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                           })
                            ->whereIn('prodcountry_id', $origins)

                            ->where('actual_price', '>=', $start_price)
                            ->where('actual_price', '<=', $end_price)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereHas('allcategory', function($q) use ($part_categories){
                                    $q->whereIn('allcategories.id', $part_categories);
                                 })
                                  ->whereIn('prodcountry_id', $origins)

                                  ->where('actual_price', '>=', $start_price)
                                  ->where('actual_price', '<=', $end_price)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);      
        } // end guest case      
    }

public function price_search($start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id)
    {
        $lang = $this->getLang();
        $common_types = [$cartype_id, 7];
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $indexes = Orderdetail::where('approved', 1)
                     ->where('producttype_id', 1)
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $start_price, $end_price){
                      $q->whereIn('cartype_id', $common_types)
                        ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->where('producttype_id', 1)
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->where('actual_price', '>=', $start_price)
                            ->where('actual_price', '<=', $end_price)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)
                                  ->where('actual_price', '>=', $start_price)
                                  ->where('actual_price', '<=', $end_price)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);
          } // end user case
          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $indexes = Orderdetail::where('approved', 1)
                     ->whereIn('producttype_id', [1, 2, 3])
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $start_price, $end_price){
                      $q->whereIn('cartype_id', $common_types)
                        ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->whereIn('producttype_id', [1, 2, 3])
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->where('actual_price', '>=', $start_price)
                            ->where('actual_price', '<=', $end_price)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->whereIn('producttype_id', [1, 2, 3])
                                  ->whereIn('cartype_id', $common_types)
                                  ->where('actual_price', '>=', $start_price)
                                  ->where('actual_price', '<=', $end_price)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
               $indexes = Orderdetail::where('approved', 1)
                     ->where('producttype_id', 1)
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $start_price, $end_price){
                      $q->whereIn('cartype_id', $common_types)
                        ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->where('producttype_id', 1)
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->where('actual_price', '>=', $start_price)
                            ->where('actual_price', '<=', $end_price)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)
                                  ->where('actual_price', '>=', $start_price)
                                  ->where('actual_price', '<=', $end_price)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);      
        } // end guest case  
        
    }

    public function part_categories_price_search($part_categories, $start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id)
    {
        $lang = $this->getLang();
        $common_types = [$cartype_id, 7];
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $indexes = Orderdetail::where('approved', 1)
                     ->where('producttype_id', 1)
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $part_categories, $start_price, $end_price){
                      $q->whereIn('cartype_id', $common_types)
                        ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                       })
                        ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->where('producttype_id', 1)
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                           })
                            ->where('actual_price', '>=', $start_price)
                            ->where('actual_price', '<=', $end_price)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereHas('allcategory', function($q) use ($part_categories){
                                    $q->whereIn('allcategories.id', $part_categories);
                                 })
                                  ->where('actual_price', '>=', $start_price)
                                  ->where('actual_price', '<=', $end_price)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);
          } // end user case
          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $indexes = Orderdetail::where('approved', 1)
                     ->whereIn('producttype_id', [1, 2, 3])
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $part_categories, $start_price, $end_price){
                      $q->whereIn('cartype_id', $common_types)
                        ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                       })
                        ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->whereIn('producttype_id', [1, 2, 3])
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                           })
                            ->where('actual_price', '>=', $start_price)
                            ->where('actual_price', '<=', $end_price)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->whereIn('producttype_id', [1, 2, 3])
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereHas('allcategory', function($q) use ($part_categories){
                                    $q->whereIn('allcategories.id', $part_categories);
                                 })
                                  ->where('actual_price', '>=', $start_price)
                                  ->where('actual_price', '<=', $end_price)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
               $indexes = Orderdetail::where('approved', 1)
                     ->where('producttype_id', 1)
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $part_categories, $start_price, $end_price){
                      $q->whereIn('cartype_id', $common_types)
                        ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                       })
                        ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->where('producttype_id', 1)
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                           })
                            ->where('actual_price', '>=', $start_price)
                            ->where('actual_price', '<=', $end_price)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereHas('allcategory', function($q) use ($part_categories){
                                    $q->whereIn('allcategories.id', $part_categories);
                                 })
                                  ->where('actual_price', '>=', $start_price)
                                  ->where('actual_price', '<=', $end_price)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);      
        } // end guest case     
        
    }

    public function manufacturers_price_search($manufacturers, $start_price, $end_price, $page, $ordered_by , $sort_type, $PAGINATION_COUNT, $cartype_id)
    {
        $lang = $this->getLang();
        $common_types = [$cartype_id, 7];
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $indexes = Orderdetail::where('approved', 1)
                     ->where('producttype_id', 1)
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $manufacturers, $start_price, $end_price){
                      $q->whereIn('cartype_id', $common_types)
                        ->whereIn('manufacturer_id', $manufacturers)
                        ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->where('producttype_id', 1)
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)->whereIn('manufacturer_id', $manufacturers)
                            ->where('actual_price', '>=', $start_price)
                            ->where('actual_price', '<=', $end_price)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)->whereIn('manufacturer_id', $manufacturers)
                                  ->where('actual_price', '>=', $start_price)
                                  ->where('actual_price', '<=', $end_price)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);
          } // end user case
          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $indexes = Orderdetail::where('approved', 1)
                     ->whereIn('producttype_id', [1, 2, 3])
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $manufacturers, $start_price, $end_price){
                      $q->whereIn('cartype_id', $common_types)
                        ->whereIn('manufacturer_id', $manufacturers)
                        ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->whereIn('producttype_id', [1, 2, 3])
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)->whereIn('manufacturer_id', $manufacturers)
                            ->where('actual_price', '>=', $start_price)
                            ->where('actual_price', '<=', $end_price)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->whereIn('producttype_id', [1, 2, 3])
                                  ->whereIn('cartype_id', $common_types)->whereIn('manufacturer_id', $manufacturers)
                                  ->where('actual_price', '>=', $start_price)
                                  ->where('actual_price', '<=', $end_price)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
               $indexes = Orderdetail::where('approved', 1)
                     ->where('producttype_id', 1)
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $manufacturers, $start_price, $end_price){
                      $q->whereIn('cartype_id', $common_types)
                        ->whereIn('manufacturer_id', $manufacturers)
                        ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->where('producttype_id', 1)
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)->whereIn('manufacturer_id', $manufacturers)
                            ->where('actual_price', '>=', $start_price)
                            ->where('actual_price', '<=', $end_price)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)->whereIn('manufacturer_id', $manufacturers)
                                  ->where('actual_price', '>=', $start_price)
                                  ->where('actual_price', '<=', $end_price)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);      
        } // end guest case     
        
    }

    public function manufacturers_search($manufacturers, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id)
    {
        $lang = $this->getLang();
        $common_types = [$cartype_id, 7];
         // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $indexes = Orderdetail::where('approved', 1)
                     ->where('producttype_id', 1)
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $manufacturers){
                      $q->whereIn('cartype_id', $common_types)->whereIn('manufacturer_id', $manufacturers);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->where('producttype_id', 1)
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)->whereIn('manufacturer_id', $manufacturers)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)->whereIn('manufacturer_id', $manufacturers)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);
          } // end user case
          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $indexes = Orderdetail::where('approved', 1)
                     ->whereIn('producttype_id', [1, 2, 3])
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $manufacturers){
                      $q->whereIn('cartype_id', $common_types)->whereIn('manufacturer_id', $manufacturers);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->whereIn('producttype_id', [1, 2, 3])
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)->whereIn('manufacturer_id', $manufacturers)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->whereIn('producttype_id', [1, 2, 3])
                                  ->whereIn('cartype_id', $common_types)->whereIn('manufacturer_id', $manufacturers)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
               $indexes = Orderdetail::where('approved', 1)
                     ->where('producttype_id', 1)
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $manufacturers){
                      $q->whereIn('cartype_id', $common_types)->whereIn('manufacturer_id', $manufacturers);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->where('producttype_id', 1)
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)->whereIn('manufacturer_id', $manufacturers)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)->whereIn('manufacturer_id', $manufacturers)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);      
        } // end guest case
        
    }

    public function origins_search($origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id)
    {
        $lang = $this->getLang();
        $common_types = [$cartype_id, 7];
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $indexes = Orderdetail::where('approved', 1)
                     ->where('producttype_id', 1)
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $origins){
                      $q->whereIn('cartype_id', $common_types)
                        ->whereIn('prodcountry_id', $origins);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->where('producttype_id', 1)
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->whereIn('prodcountry_id', $origins)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereIn('prodcountry_id', $origins)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);
          } // end user case
          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $indexes = Orderdetail::where('approved', 1)
                     ->whereIn('producttype_id', [1, 2, 3])
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $origins){
                      $q->whereIn('cartype_id', $common_types)
                        ->whereIn('prodcountry_id', $origins);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->whereIn('producttype_id', [1, 2, 3])
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->whereIn('prodcountry_id', $origins)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->whereIn('producttype_id', [1, 2, 3])
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereIn('prodcountry_id', $origins)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
               $indexes = Orderdetail::where('approved', 1)
                     ->where('producttype_id', 1)
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $origins){
                      $q->whereIn('cartype_id', $common_types)
                        ->whereIn('prodcountry_id', $origins);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->where('producttype_id', 1)
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->whereIn('prodcountry_id', $origins)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)
                                 ->whereIn('prodcountry_id', $origins)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);      
        } // end guest case   
        
    }

    public function part_categories_manufacturers_search($part_categories, $manufacturers, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id)
    {
        $lang = $this->getLang();
        $common_types = [$cartype_id, 7];
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $indexes = Orderdetail::where('approved', 1)
                     ->where('producttype_id', 1)
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $part_categories, $manufacturers){
                      $q->whereIn('cartype_id', $common_types)
                        ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                       })->whereIn('manufacturer_id', $manufacturers);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->where('producttype_id', 1)
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                           })->whereIn('manufacturer_id', $manufacturers)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereHas('allcategory', function($q) use ($part_categories){
                                    $q->whereIn('allcategories.id', $part_categories);
                                 })->whereIn('manufacturer_id', $manufacturers)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);
          } // end user case
          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $indexes = Orderdetail::where('approved', 1)
                     ->whereIn('producttype_id', [1, 2, 3])
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $part_categories, $manufacturers){
                      $q->whereIn('cartype_id', $common_types)
                        ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                       })->whereIn('manufacturer_id', $manufacturers);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->whereIn('producttype_id', [1, 2, 3])
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                           })->whereIn('manufacturer_id', $manufacturers)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->whereIn('producttype_id', [1, 2, 3])
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereHas('allcategory', function($q) use ($part_categories){
                                    $q->whereIn('allcategories.id', $part_categories);
                                 })->whereIn('manufacturer_id', $manufacturers)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
               $indexes = Orderdetail::where('approved', 1)
                     ->where('producttype_id', 1)
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $part_categories, $manufacturers){
                      $q->whereIn('cartype_id', $common_types)
                        ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                       })->whereIn('manufacturer_id', $manufacturers);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->where('producttype_id', 1)
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                           })->whereIn('manufacturer_id', $manufacturers)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereHas('allcategory', function($q) use ($part_categories){
                                    $q->whereIn('allcategories.id', $part_categories);
                                 })->whereIn('manufacturer_id', $manufacturers)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);      
        } // end guest case     
        
    }

    public function part_categories_origins_search($part_categories, $origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id)
    {
        $lang = $this->getLang();
        $common_types = [$cartype_id, 7];
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $indexes = Orderdetail::where('approved', 1)
                     ->where('producttype_id', 1)
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $part_categories, $origins){
                      $q->whereIn('cartype_id', $common_types)
                        ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                       })
                        ->whereIn('prodcountry_id', $origins)
                        ;
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->where('producttype_id', 1)
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                           })
                            ->whereIn('prodcountry_id', $origins)

                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereHas('allcategory', function($q) use ($part_categories){
                                    $q->whereIn('allcategories.id', $part_categories);
                                 })
                                  ->whereIn('prodcountry_id', $origins)

                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);
          } // end user case
          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $indexes = Orderdetail::where('approved', 1)
                     ->whereIn('producttype_id', [1, 2, 3])
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $part_categories, $origins){
                      $q->whereIn('cartype_id', $common_types)
                        ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                       })
                        ->whereIn('prodcountry_id', $origins)
                        ;
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->whereIn('producttype_id', [1, 2, 3])
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                           })
                            ->whereIn('prodcountry_id', $origins)

                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->whereIn('producttype_id', [1, 2, 3])
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereHas('allcategory', function($q) use ($part_categories){
                                    $q->whereIn('allcategories.id', $part_categories);
                                 })
                                  ->whereIn('prodcountry_id', $origins)

                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
               $indexes = Orderdetail::where('approved', 1)
                     ->where('producttype_id', 1)
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $part_categories, $origins){
                      $q->whereIn('cartype_id', $common_types)
                        ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                       })
                        ->whereIn('prodcountry_id', $origins)
                        ;
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->where('producttype_id', 1)
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->whereHas('allcategory', function($q) use ($part_categories){
                          $q->whereIn('allcategories.id', $part_categories);
                           })
                            ->whereIn('prodcountry_id', $origins)

                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereHas('allcategory', function($q) use ($part_categories){
                                    $q->whereIn('allcategories.id', $part_categories);
                                 })
                                  ->whereIn('prodcountry_id', $origins)

                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);      
        } // end guest case    
    }

      public function manufacturers_origins_search($manufacturers, $origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id)
      {
          $lang = $this->getLang();
          $common_types = [$cartype_id, 7];
          // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $indexes = Orderdetail::where('approved', 1)
                     ->where('producttype_id', 1)
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $manufacturers, $origins){
                      $q->whereIn('cartype_id', $common_types)
                        ->whereIn('prodcountry_id', $origins)
                        ->whereIn('manufacturer_id', $manufacturers);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->where('producttype_id', 1)
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->whereIn('prodcountry_id', $origins)
                            ->whereIn('manufacturer_id', $manufacturers)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereIn('prodcountry_id', $origins)
                                  ->whereIn('manufacturer_id', $manufacturers)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);
          } // end user case
          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $indexes = Orderdetail::where('approved', 1)
                     ->whereIn('producttype_id', [1, 2, 3])
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $manufacturers, $origins){
                      $q->whereIn('cartype_id', $common_types)
                        ->whereIn('prodcountry_id', $origins)
                        ->whereIn('manufacturer_id', $manufacturers);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->whereIn('producttype_id', [1, 2, 3])
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->whereIn('prodcountry_id', $origins)
                            ->whereIn('manufacturer_id', $manufacturers)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->whereIn('producttype_id', [1, 2, 3])
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereIn('prodcountry_id', $origins)
                                  ->whereIn('manufacturer_id', $manufacturers)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
               $indexes = Orderdetail::where('approved', 1)
                     ->where('producttype_id', 1)
                     ->whereHas('product', function($q) use ($cartype_id, $common_types, $manufacturers, $origins){
                      $q->whereIn('cartype_id', $common_types)
                        ->whereIn('prodcountry_id', $origins)
                        ->whereIn('manufacturer_id', $manufacturers);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->where('producttype_id', 1)
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            ->whereIn('prodcountry_id', $origins)
                            ->whereIn('manufacturer_id', $manufacturers)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereIn('prodcountry_id', $origins)
                                  ->whereIn('manufacturer_id', $manufacturers)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);      
        } // end guest case     
          
      }  // end probs


      /* public function best_seller_products(SelectCarTypeApiRequest $request) // fetch top 6
      {
        $lang = $this->getLang();
        $common_types = [$cartype_id, 7];
        $cartype_id = $request->cartype_id;
        
        
        $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
        $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
        if ($ordered_by == 'price') {
          $ordered_by = 'actual_price';
        }
          // start case sum quantity
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if (in_array('User', $user_roles)) {
              $indexes = Orderdetail::where('approved', 1)
                     ->where('producttype_id', 1)
                     ->whereHas('product', function($q) use ($cartype_id, $common_types){
                      // $q->where('cartype_id', $cartype_id); // added june 29 2021);
                      $q->whereIn('cartype_id', $common_types); // added june 29 2021);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

              $sorter = static function ($produto) use ($indexes) {
                      return array_search($produto->id, $indexes);
                   };

              $products = Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereIn('id', $indexes)
                                  //->orderBy($ordered_by, $sort_type)
                                  ->get()
                                  ->sortBy($sorter);

              if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
              $data = FrontProductsApiResource::collection($products); 
              return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    // 'total'       => $total,
                ], 200);
          }  // end user case

          // case logged in vendor role
          if ( ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $indexes = Orderdetail::where('approved', 1)
                     ->where('producttype_id', 1)
                     ->whereHas('product', function($q) use ($cartype_id, $common_types){
                      // $q->where('cartype_id', $cartype_id); // added june 29 2021);
                      $q->whereIn('cartype_id', $common_types); // added june 29 2021);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

              $sorter = static function ($produto) use ($indexes) {
                      return array_search($produto->id, $indexes);
                   };

              $products = Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereIn('id', $indexes)
                                  //->orderBy($ordered_by, $sort_type)
                                  ->get()
                                  ->sortBy($sorter);

              if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
              $data = FrontProductsApiResource::collection($products); 
              return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    // 'total'       => $total,
                ], 200);
          }  // end user case

         if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
          // case logged in user role is User 
            $indexes = Orderdetail::where('approved', 1)
                     ->whereIn('producttype_id', [1, 2, 3])
                     ->whereHas('product', function($q) use ($cartype_id, $common_types){
                      // $q->where('cartype_id', $cartype_id); // added june 29 2021);
                      $q->whereIn('cartype_id', $common_types); // added june 29 2021);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();

          $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

          $products = Product::where('approved', 1)
                            ->whereIn('producttype_id', [1, 2, 3])
                            ->whereIn('cartype_id', $common_types)
                            ->whereIn('id', $indexes)
                            //->orderBy($ordered_by, $sort_type)
                            ->get()->sortBy($sorter);
          if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->whereIn('producttype_id', [1, 2, 3])
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products); 
          return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'        => $data,
                // 'total'       => $total,
            ], 200);
            
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
              $indexes = Orderdetail::where('approved', 1)->where('producttype_id', 1)
                     ->whereHas('product', function($q) use ($cartype_id, $common_types){
                      // $q->where('cartype_id', $cartype_id); // added june 29 2021);
                      $q->whereIn('cartype_id', $common_types); // added june 29 2021);
                     })
                     ->groupBy('product_id')
                     ->orderByRaw('SUM(quantity) DESC')
                     ->select('product_id')
                     ->limit($PAGINATION_COUNT)
                     ->pluck('product_id')->toArray();
                    // return $indexes;

              $sorter = static function ($produto) use ($indexes) {
                      return array_search($produto->id, $indexes);
                   };

              $products = Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereIn('id', $indexes)
                                  // ->orderBy($ordered_by, $sort_type)
                                  ->get()
                                 ->sortBy($sorter);
              if ($ordered_by == 'actual_price') {
                $products= Product::where('approved', 1)
                                  ->where('producttype_id', 1)
                                  ->whereIn('cartype_id', $common_types)
                                  ->whereIn('id', $products->pluck('id'))
                                  ->orderBy($ordered_by, $sort_type)
                                  ->get();
              }
           
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
              $data = FrontProductsApiResource::collection($products); 
              return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    // 'total'       => $total,
                ], 200);       
        } // end guest case
      } */
          // end case sum quantity
}
