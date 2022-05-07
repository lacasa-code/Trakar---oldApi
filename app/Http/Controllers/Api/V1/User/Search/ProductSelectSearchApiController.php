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

class ProductSelectSearchApiController extends Controller
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

        // $search_index = $request->search_index;

      // return $column_name;
      if ($ordered_by != '') {
        if (!Schema::hasColumn('products', $ordered_by)) {
          return response()->json(['message'  =>'order column not found',], 400);
        }
        if ($ordered_by == 'tags' || $ordered_by == 'categories') {
          $ordered_by = 'id';
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
          return $this->search_with_car_made($car_type_id, $search_index, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }
        // case 3
        if($car_made_id == '' && $car_model_id != '' && $car_year_id == '' && $transmission_id == '') {
          $search_index = $car_model_id;
          return $this->search_with_car_model($car_type_id, $search_index, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }
        // case 4
        if($car_made_id == '' && $car_model_id == '' && $car_year_id != '' && $transmission_id == '') {
           $search_index = $car_year_id;
           return $this->search_with_car_year($car_type_id, $search_index, $page, $ordered_by, $sort_type, $PAGINATION_COUNT);
        }
        // case 5
        if($car_made_id == '' && $car_model_id == '' && $car_year_id == '' && $transmission_id != '') {
          $search_index = $transmission_id;
          return $this->search_with_transmission($car_type_id, $search_index, $page, $ordered_by, $sort_type, $PAGINATION_COUNT);
        }
        // case 6
        if($car_made_id != '' && $car_model_id != '' && $car_year_id == '' && $transmission_id == '') {
           $search_index = $car_year_id;
           return $this->search_with_made_model($car_type_id, $car_made_id, $car_model_id, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }
        // case 7
        if($car_made_id != '' && $car_model_id == '' && $car_year_id != '' && $transmission_id == '') {
           $search_index = $car_year_id;
           return $this->search_with_made_year($car_type_id, $car_made_id, $car_year_id, $page, $ordered_by, $sort_type, $PAGINATION_COUNT);
        }
        // case 8
        if($car_made_id != '' && $car_model_id == '' && $car_year_id == '' && $transmission_id != '') {
           $search_index = $car_year_id;
           return $this->search_with_made_transmission($car_type_id, $car_made_id, $transmission_id, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }
        // case 9
        if($car_made_id == '' && $car_model_id != '' && $car_year_id != '' && $transmission_id == '') {
          return $this->search_with_model_year($car_type_id, $car_model_id, $car_year_id, $page, $ordered_by, $sort_type, $PAGINATION_COUNT);
        }
        // case 10
        if($car_made_id == '' && $car_model_id != '' && $car_year_id == '' && $transmission_id != '') {
          return $this->search_with_model_transmission($car_type_id, $car_model_id, $transmission_id, $page, $ordered_by, $sort_type, $PAGINATION_COUNT);
        }
        // case 11
        if($car_made_id == '' && $car_model_id == '' && $car_year_id != '' && $transmission_id != '') {
          return $this->search_with_year_transmission($car_type_id, $car_year_id, $transmission_id, $page, $ordered_by, $sort_type, $PAGINATION_COUNT);
        }
        // case 12
        if($car_made_id != '' && $car_model_id != '' && $car_year_id != '' && $transmission_id == '') {
          return $this->made_model_year($car_type_id, $car_made_id, $car_model_id, $car_year_id, $page, $ordered_by, $sort_type, $PAGINATION_COUNT);
        }
        // case 12
        if($car_made_id != '' && $car_model_id != '' && $car_year_id == '' && $transmission_id != '') {
          return $this->made_model_transmission($car_type_id, $car_made_id, $car_model_id, $transmission_id, $page, $ordered_by, $sort_type, $PAGINATION_COUNT);
        }
        // case 13
        if($car_made_id == '' && $car_model_id != '' && $car_year_id != '' && $transmission_id != '') {
          return $this->model_year_transmission($car_type_id, $car_model_id, $car_year_id, $transmission_id, $page, $ordered_by, $sort_type, $PAGINATION_COUNT);
        }
        // case 14
        if($car_made_id != '' && $car_model_id == '' && $car_year_id != '' && $transmission_id != '') {
          return $this->made_year_transmission($car_type_id, $car_made_id, $car_year_id, $transmission_id, $page, $ordered_by, $sort_type, $PAGINATION_COUNT);
        }
        // case 14
        if($car_made_id != '' && $car_model_id != '' && $car_year_id != '' && $transmission_id != '') {
          return $this->all_search($car_type_id, $car_made_id, $car_model_id, $car_year_id, $transmission_id, $page, $ordered_by, $sort_type, $PAGINATION_COUNT);
        }
    } // end search products with name 

    // start search products with car made 
     public function search_with_car_made($car_type_id, $search_index, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
     {   
      $lang = $this->getLang();
      // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $products = Product::where('approved', 1)
                              ->where('cartype_id', $car_type_id)
                              ->where('car_made_id', $search_index)
                              ->where('producttype_id', 1)
                               //->skip(($page-1)*$PAGINATION_COUNT)
                               //->take($PAGINATION_COUNT)
                               ->orderBy($ordered_by, $sort_type)->get();

            $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                                                ->where('car_made_id', $search_index)
                                                ->where('producttype_id', 1)->count();
            foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
            $data = FrontProductsApiResource::collection($products);

            return response()->json([
              'status_code' => 200,
              'message' => 'success',
              'data'  => $data,
              'total' => $total,
            ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $products = Product::where('approved', 1)
                              ->where('cartype_id', $car_type_id)
                              ->where('car_made_id', $search_index)
                              ->whereIn('producttype_id', [1, 2, 3])
                               //->skip(($page-1)*$PAGINATION_COUNT)
                               //->take($PAGINATION_COUNT)
                               ->orderBy($ordered_by, $sort_type)->get();

            $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                                                ->where('car_made_id', $search_index)
                                                ->whereIn('producttype_id', [1, 2, 3])->count();
            foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
            $data = FrontProductsApiResource::collection($products);

            return response()->json([
              'status_code' => 200,
              'message' => 'success',
              'data'  => $data,
              'total' => $total,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
            $products = Product::where('approved', 1)
                              ->where('cartype_id', $car_type_id)
                              ->where('car_made_id', $search_index)
                              ->where('producttype_id', 1)
                               //->skip(($page-1)*$PAGINATION_COUNT)
                               //->take($PAGINATION_COUNT)
                               ->orderBy($ordered_by, $sort_type)->get();

            $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                                                ->where('car_made_id', $search_index)
                                                ->where('producttype_id', 1)->count();
            foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
            $data = FrontProductsApiResource::collection($products);

            return response()->json([
              'status_code' => 200,
              'message' => 'success',
              'data'  => $data,
              'total' => $total,
            ], 200);   
        } // end guest case
        // case logged in user role is User 
     }
    // end search products with car made 

      // start search products with car model 
     public function search_with_car_model($car_type_id, $search_index, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
     { 
      $lang = $this->getLang();
      // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $products = Product::where('approved', 1)
                              ->where('cartype_id', $car_type_id)
                              ->where('car_model_id', $search_index)
                              ->where('producttype_id', 1)
                               // ->skip(($page-1)*$PAGINATION_COUNT)
                               // ->take($PAGINATION_COUNT)
                               ->orderBy($ordered_by, $sort_type)->get();

            $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_model_id', $search_index)->where('producttype_id', 1)->count();
            foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
            $data = FrontProductsApiResource::collection($products);

            return response()->json([
              'status_code' => 200,
              'message' => 'success',
              'data'  => $data,
              'total' => $total,
            ], 200);
          }  // end user case

           if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
                $products = Product::where('approved', 1)
                              ->where('cartype_id', $car_type_id)
                              ->where('car_model_id', $search_index)
                              ->whereIn('producttype_id', [1, 2, 3])
                               // ->skip(($page-1)*$PAGINATION_COUNT)
                               // ->take($PAGINATION_COUNT)
                               ->orderBy($ordered_by, $sort_type)->get();

            $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_model_id', $search_index)
                            ->whereIn('producttype_id', [1, 2, 3])->count();
            foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
            $data = FrontProductsApiResource::collection($products);

            return response()->json([
              'status_code' => 200,
              'message' => 'success',
              'data'  => $data,
              'total' => $total,
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
               $products = Product::where('approved', 1)
                              ->where('cartype_id', $car_type_id)
                              ->where('car_model_id', $search_index)
                              ->where('producttype_id', 1)
                               // ->skip(($page-1)*$PAGINATION_COUNT)
                               // ->take($PAGINATION_COUNT)
                               ->orderBy($ordered_by, $sort_type)->get();

            $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_model_id', $search_index)
                            ->where('producttype_id', 1)->count();
            foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
            $data = FrontProductsApiResource::collection($products);

            return response()->json([
              'status_code' => 200,
              'message' => 'success',
              'data'  => $data,
              'total' => $total,
            ], 200);      
        } // end guest case
        // case logged in user role is user 
     }
    // end search products with car model 

      // start search products with car year 
     public function search_with_car_year($car_type_id, $search_index, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
     {
      $lang = $this->getLang();
        // case logged in user role is user 
      // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $products = Product::where('approved', 1)
                              ->where('cartype_id', $car_type_id)
                              ->where('year_id', $search_index)
                              ->where('producttype_id', 1)
                               //->skip(($page-1)*$PAGINATION_COUNT)
                               //->take($PAGINATION_COUNT)
                               ->orderBy($ordered_by, $sort_type)->get();

          $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                        ->where('year_id', $search_index)
                        ->where('producttype_id', 1)->count();
          foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products);

          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);
          }  // end user case

           if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $products = Product::where('approved', 1)
                              ->where('cartype_id', $car_type_id)
                              ->where('year_id', $search_index)
                              ->whereIn('producttype_id', [1, 2, 3])
                               //->skip(($page-1)*$PAGINATION_COUNT)
                               //->take($PAGINATION_COUNT)
                               ->orderBy($ordered_by, $sort_type)->get();

          $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                        ->where('year_id', $search_index)
                        ->whereIn('producttype_id', [1, 2, 3])->count();
          foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products);

          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
               $products = Product::where('approved', 1)
                              ->where('cartype_id', $car_type_id)
                              ->where('year_id', $search_index)
                              ->where('producttype_id', 1)
                               //->skip(($page-1)*$PAGINATION_COUNT)
                               //->take($PAGINATION_COUNT)
                               ->orderBy($ordered_by, $sort_type)->get();

          $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                        ->where('year_id', $search_index)
                        ->where('producttype_id', 1)->count();
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
          $data = FrontProductsApiResource::collection($products);

          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);      
        } // end guest case
     }
    // end search products with car year 

     // start search products with transmission 
     public function search_with_transmission($car_type_id, $search_index, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
     {
      $lang = $this->getLang();
        // case logged in user role is user 
      // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $products = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                              ->where('transmission_id', $search_index)
                              ->where('producttype_id', 1)
                               //->skip(($page-1)*$PAGINATION_COUNT)
                               //->take($PAGINATION_COUNT)
                               ->orderBy($ordered_by, $sort_type)->get();

          $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                          ->where('transmission_id', $search_index)
                          ->where('producttype_id', 1)->count();
          foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products);

          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);
          }  // end user case

           if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
               $products = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                              ->where('transmission_id', $search_index)
                              ->whereIn('producttype_id', [1, 2, 3])
                               //->skip(($page-1)*$PAGINATION_COUNT)
                               //->take($PAGINATION_COUNT)
                               ->orderBy($ordered_by, $sort_type)->get();

          $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                          ->where('transmission_id', $search_index)
                          ->whereIn('producttype_id', [1, 2, 3])->count();
          foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products);

          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
                $products = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                              ->where('transmission_id', $search_index)
                              ->where('producttype_id', 1)
                               //->skip(($page-1)*$PAGINATION_COUNT)
                               //->take($PAGINATION_COUNT)
                               ->orderBy($ordered_by, $sort_type)->get();

          $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                          ->where('transmission_id', $search_index)
                          ->where('producttype_id', 1)->count();
          foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
          $data = FrontProductsApiResource::collection($products);

          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);     
        } // end guest case
     }
    // end search products with transmission 

     // start search products with made model year 
     public function made_model_year($car_type_id, $car_made_id, $car_model_id, $car_year_id, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
     {
      $lang = $this->getLang();
        // case logged in user role is user 
      // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
            $year = CarYear::where('id', $car_year_id)->first()->year;
          $model = CarModel::where('id', $car_model_id)->first()->carmodel;
              $products = Product::where('approved', 1)->where('cartype_id', $car_type_id)
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

          $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
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
                            ->count();
          foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products);
          
          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);
          }  // end user case

           if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) 
           {
            $year = CarYear::where('id', $car_year_id)->first()->year;
          $model = CarModel::where('id', $car_model_id)->first()->carmodel;
            $products = Product::where('approved', 1)->where('cartype_id', $car_type_id)
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

          $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_made_id', $car_made_id)
                           // ->where('car_model_id', $car_model_id)
                          //  ->where('year_id', $car_year_id)
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
                            ->count();
          foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products);
          
          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
          $year = CarYear::where('id', $car_year_id)->first()->year;
          $model = CarModel::where('id', $car_model_id)->first()->carmodel;
             $products = Product::where('approved', 1)->where('cartype_id', $car_type_id)
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

          $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
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
                            ->count();
          foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
          $data = FrontProductsApiResource::collection($products);
          
          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);        
        } // end guest case
     }
    // end search products with made model year 

     // start search products with made model 
     public function search_with_made_model($car_type_id, $car_made_id, $car_model_id, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
     {
      $lang = $this->getLang();
        // case logged in user role is user
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
           // $year = CarYear::where('id', $car_year_id)->first()->year;
            $model = CarModel::where('id', $car_model_id)->first()->carmodel;
              $products = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_made_id', $car_made_id)
                           // ->where('car_model_id', $car_model_id)
                             ->whereHas('car_model', function($q) use ($model){
                              $q->where('carmodel', $model);
                            })  
                            ->where('producttype_id', 1)
                            // ->skip(($page-1)*$PAGINATION_COUNT)
                            // ->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)->get();

          $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_made_id', $car_made_id)
                            //->where('car_model_id', $car_model_id)
                             ->whereHas('car_model', function($q) use ($model){
                              $q->where('carmodel', $model);
                            })  
                            ->where('producttype_id', 1)
                            ->count();
          foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products);
          
          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);
          }  // end user case

           if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
          //  $year = CarYear::where('id', $car_year_id)->first()->year;
          $model = CarModel::where('id', $car_model_id)->first()->carmodel;
            $products = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_made_id', $car_made_id)
                            //->where('car_model_id', $car_model_id)
                             ->whereHas('car_model', function($q) use ($model){
                              $q->where('carmodel', $model);
                            })   
                            ->whereIn('producttype_id', [1, 2, 3])
                            // ->skip(($page-1)*$PAGINATION_COUNT)
                            // ->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)->get();

          $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_made_id', $car_made_id)
                             ->whereHas('car_model', function($q) use ($model){
                              $q->where('carmodel', $model);
                            })  
                            ->whereIn('producttype_id', [1, 2, 3])
                            ->count();
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products);
          
          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
         // $year = CarYear::where('id', $car_year_id)->first()->year;
          $model = CarModel::where('id', $car_model_id)->first()->carmodel;
            $products = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_made_id', $car_made_id)
                            //->where('car_model_id', $car_model_id)
                             ->whereHas('car_model', function($q) use ($model){
                              $q->where('carmodel', $model);
                            })  
                            ->where('producttype_id', 1)
                            // ->skip(($page-1)*$PAGINATION_COUNT)
                            // ->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)->get();

          $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_made_id', $car_made_id)
                           // ->where('car_model_id', $car_model_id)
                             ->whereHas('car_model', function($q) use ($model){
                              $q->where('carmodel', $model);
                            })  
                            ->where('producttype_id', 1)
                            ->count();
          foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
          $data = FrontProductsApiResource::collection($products);
          
          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);         
        } // end guest case 
     }
    // end search products with made model

     // start search products with made year 
     public function search_with_made_year($car_type_id, $car_made_id, $car_year_id, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
     {
      $lang = $this->getLang();
        // case logged in user role is user 
      // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $products = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_made_id', $car_made_id)
                            ->where('year_id', $car_year_id)
                            ->where('producttype_id', 1)
                            // ->skip(($page-1)*$PAGINATION_COUNT)
                            // ->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)->get();

          $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_made_id', $car_made_id)
                            ->where('year_id', $car_year_id)
                            ->where('producttype_id', 1)
                            ->count();
          foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products);
          
          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);
          }  // end user case

           if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $products = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_made_id', $car_made_id)
                            ->where('year_id', $car_year_id)
                            ->whereIn('producttype_id', [1, 2, 3])
                            // ->skip(($page-1)*$PAGINATION_COUNT)
                            // ->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)->get();

          $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_made_id', $car_made_id)
                            ->where('year_id', $car_year_id)
                            ->whereIn('producttype_id', [1, 2, 3])
                            ->count();
          foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products);
          
          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
             $products = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_made_id', $car_made_id)
                            ->where('year_id', $car_year_id)
                            ->where('producttype_id', 1)
                            // ->skip(($page-1)*$PAGINATION_COUNT)
                            // ->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)->get();

          $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_made_id', $car_made_id)
                            ->where('year_id', $car_year_id)
                            ->where('producttype_id', 1)
                            ->count();
          foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
          $data = FrontProductsApiResource::collection($products);
          
          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);        
        } // end guest case
     }
    // end search products with made year  

     // start search products with made transmission
     public function search_with_made_transmission($car_type_id, $car_made_id, $transmission_id, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
     {
      $lang = $this->getLang();
        // case logged in user role is user 
      // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $products = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_made_id', $car_made_id)
                            ->where('transmission_id', $transmission_id)
                            ->where('producttype_id', 1)
                            // ->skip(($page-1)*$PAGINATION_COUNT)
                            // ->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)->get();

          $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_made_id', $car_made_id)
                            ->where('transmission_id', $transmission_id)
                            ->where('producttype_id', 1)
                            ->count();
          foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products);
          
          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);
          }  // end user case

           if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $products = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_made_id', $car_made_id)
                            ->where('transmission_id', $transmission_id)
                            ->whereIn('producttype_id', [1, 2, 3])
                            // ->skip(($page-1)*$PAGINATION_COUNT)
                            // ->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)->get();

          $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_made_id', $car_made_id)
                            ->where('transmission_id', $transmission_id)
                            ->whereIn('producttype_id', [1, 2, 3])
                            ->count();
          foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products);
          
          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
              $products = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_made_id', $car_made_id)
                            ->where('transmission_id', $transmission_id)
                            ->where('producttype_id', 1)
                            // ->skip(($page-1)*$PAGINATION_COUNT)
                            // ->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)->get();

          $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_made_id', $car_made_id)
                            ->where('transmission_id', $transmission_id)
                            ->where('producttype_id', 1)
                            ->count();
          foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
          $data = FrontProductsApiResource::collection($products);
          
          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);       
        } // end guest case
     }
    // end search products with made transmission 

     // start search products with model year 
     public function search_with_model_year($car_type_id, $car_model_id, $car_year_id, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
     {
      $lang = $this->getLang();
        // case logged in user role is user 
      // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
             $products = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_model_id', $car_model_id)
                            ->where('year_id', $car_year_id)
                            ->where('producttype_id', 1)
                            // ->skip(($page-1)*$PAGINATION_COUNT)
                            // ->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)->get();

          $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_model_id', $car_model_id)
                            ->where('year_id', $car_year_id)
                            ->where('producttype_id', 1)
                            ->count();
          foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products);
          
          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200); 
          }  // end user case

           if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $products = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_model_id', $car_model_id)
                            ->where('year_id', $car_year_id)
                            ->whereIn('producttype_id', [1, 2, 3])
                            // ->skip(($page-1)*$PAGINATION_COUNT)
                            // ->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)->get();

          $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_model_id', $car_model_id)
                            ->where('year_id', $car_year_id)
                            ->whereIn('producttype_id', [1, 2, 3])
                            ->count();
          foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products);
          
          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
            $products = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_model_id', $car_model_id)
                            ->where('year_id', $car_year_id)
                            ->where('producttype_id', 1)
                            // ->skip(($page-1)*$PAGINATION_COUNT)
                            // ->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)->get();

          $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_model_id', $car_model_id)
                            ->where('year_id', $car_year_id)
                            ->where('producttype_id', 1)
                            ->count();
          foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
          $data = FrontProductsApiResource::collection($products);
          
          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);         
        } // end guest case
     }
    // end search products with model year 

     // start  search_with_model_transmission 
     public function search_with_model_transmission($car_type_id, $car_model_id, $transmission_id, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
     {
      $lang = $this->getLang();
        // case logged in user role is user 
      // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $products = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_model_id', $car_model_id)
                            ->where('transmission_id', $transmission_id)
                            ->where('producttype_id', 1)
                            // ->skip(($page-1)*$PAGINATION_COUNT)
                            // ->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)->get();

          $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_model_id', $car_model_id)
                            ->where('transmission_id', $transmission_id)
                            ->where('producttype_id', 1)
                            // ->where('year_id', $car_year_id)
                            ->count();
          foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products);
          
          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);
          }  // end user case

           if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $products = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_model_id', $car_model_id)
                            ->where('transmission_id', $transmission_id)
                            ->whereIn('producttype_id', [1, 2, 3])
                            // ->skip(($page-1)*$PAGINATION_COUNT)
                            // ->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)->get();

          $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_model_id', $car_model_id)
                            ->where('transmission_id', $transmission_id)
                            ->whereIn('producttype_id', [1, 2, 3])
                            // ->where('year_id', $car_year_id)
                            ->count();
          foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products);
          
          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
            $products = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_model_id', $car_model_id)
                            ->where('transmission_id', $transmission_id)
                            ->where('producttype_id', 1)
                            // ->skip(($page-1)*$PAGINATION_COUNT)
                            // ->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)->get();

          $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_model_id', $car_model_id)
                            ->where('transmission_id', $transmission_id)
                            ->where('producttype_id', 1)
                            // ->where('year_id', $car_year_id)
                            ->count();
          foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
          $data = FrontProductsApiResource::collection($products);
          
          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);         
        } // end guest case
     }
    // end search_with_model_transmission

     // start search_with_year_transmission 
     public function search_with_year_transmission($car_type_id, $car_year_id, $transmission_id, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
     {
      $lang = $this->getLang();
        // case logged in user role is user 
      // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $products = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('transmission_id', $transmission_id)
                            ->where('year_id', $car_year_id)
                            ->where('producttype_id', 1)
                            // ->skip(($page-1)*$PAGINATION_COUNT)
                            // ->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)->get();

          $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('transmission_id', $transmission_id)
                            ->where('year_id', $car_year_id)
                            ->where('producttype_id', 1)
                            ->count();
          foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products);
          
          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);
          }  // end user case

           if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $products = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('transmission_id', $transmission_id)
                            ->where('year_id', $car_year_id)
                            ->whereIn('producttype_id', [1, 2, 3])
                            // ->skip(($page-1)*$PAGINATION_COUNT)
                            // ->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)->get();

          $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('transmission_id', $transmission_id)
                            ->where('year_id', $car_year_id)
                            ->whereIn('producttype_id', [1, 2, 3])
                            ->count();
          foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products);
          
          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
             $products = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('transmission_id', $transmission_id)
                            ->where('year_id', $car_year_id)
                            ->where('producttype_id', 1)
                            // ->skip(($page-1)*$PAGINATION_COUNT)
                            // ->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)->get();

          $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('transmission_id', $transmission_id)
                            ->where('year_id', $car_year_id)
                            ->where('producttype_id', 1)
                            ->count();
          foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
          $data = FrontProductsApiResource::collection($products);
          
          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);        
        } // end guest case
     }
    // end search_with_year_transmission 

     // start made_model_transmission 
     public function made_model_transmission($car_type_id, $car_made_id, $car_model_id, $transmission_id, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
     {
      $lang = $this->getLang();
        // case logged in user role is user 
      // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $products = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_made_id', $car_made_id)
                            ->where('car_model_id', $car_model_id)
                            ->where('transmission_id', $transmission_id)
                            ->where('producttype_id', 1)
                            // ->skip(($page-1)*$PAGINATION_COUNT)
                            // ->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)->get();

          $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_made_id', $car_made_id)
                            ->where('car_model_id', $car_model_id)
                            ->where('transmission_id', $transmission_id)
                            ->where('producttype_id', 1)
                            ->count();
          foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products);
          
          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);
          }  // end user case

           if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $products = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_made_id', $car_made_id)
                            ->where('car_model_id', $car_model_id)
                            ->where('transmission_id', $transmission_id)
                            ->whereIn('producttype_id', [1, 2, 3])
                            // ->skip(($page-1)*$PAGINATION_COUNT)
                            // ->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)->get();

          $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_made_id', $car_made_id)
                            ->where('car_model_id', $car_model_id)
                            ->where('transmission_id', $transmission_id)
                            ->whereIn('producttype_id', [1, 2, 3])
                            ->count();
          foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products);
          
          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
            $products = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_made_id', $car_made_id)
                            ->where('car_model_id', $car_model_id)
                            ->where('transmission_id', $transmission_id)
                            ->where('producttype_id', 1)
                            // ->skip(($page-1)*$PAGINATION_COUNT)
                            // ->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)->get();

          $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_made_id', $car_made_id)
                            ->where('car_model_id', $car_model_id)
                            ->where('transmission_id', $transmission_id)
                            ->where('producttype_id', 1)
                            ->count();
          foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
          $data = FrontProductsApiResource::collection($products);
          
          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);         
        } // end guest case
     }
    // end made_model_transmission 

     // start model_year_transmission 
     public function model_year_transmission($car_type_id, $car_model_id, $car_year_id, $transmission_id, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
     {
      $lang = $this->getLang();
        // case logged in user role is user 
      // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $products = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('transmission_id', $transmission_id)
                            ->where('car_model_id', $car_model_id)
                            ->where('year_id', $car_year_id)
                            ->where('producttype_id', 1)
                            //->skip(($page-1)*$PAGINATION_COUNT)
                            //->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)->get();

          $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('transmission_id', $transmission_id)
                            ->where('car_model_id', $car_model_id)
                            ->where('year_id', $car_year_id)
                            ->where('producttype_id', 1)
                            ->count();
          foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products);
          
          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);
          }  // end user case

           if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $products = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('transmission_id', $transmission_id)
                            ->where('car_model_id', $car_model_id)
                            ->where('year_id', $car_year_id)
                            ->whereIn('producttype_id', [1, 2, 3])
                            //->skip(($page-1)*$PAGINATION_COUNT)
                            //->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)->get();

          $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('transmission_id', $transmission_id)
                            ->where('car_model_id', $car_model_id)
                            ->where('year_id', $car_year_id)
                            ->whereIn('producttype_id', [1, 2, 3])
                            ->count();
          foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products);
          
          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
            $products = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('transmission_id', $transmission_id)
                            ->where('car_model_id', $car_model_id)
                            ->where('year_id', $car_year_id)
                            ->where('producttype_id', 1)
                            //->skip(($page-1)*$PAGINATION_COUNT)
                            //->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)->get();

          $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('transmission_id', $transmission_id)
                            ->where('car_model_id', $car_model_id)
                            ->where('year_id', $car_year_id)
                            ->where('producttype_id', 1)
                            ->count();
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
          $data = FrontProductsApiResource::collection($products);
          
          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);         
        } // end guest case
     }
    // end model_year_transmission 

     // start made_year_transmission 
     public function made_year_transmission($car_type_id, $car_made_id, $car_year_id, $transmission_id, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
     {
      $lang = $this->getLang();
        // case logged in user role is user 
      // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $products = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_made_id', $car_made_id)
                            ->where('transmission_id', $transmission_id)
                            ->where('year_id', $car_year_id)
                            ->where('producttype_id', 1)
                            //->skip(($page-1)*$PAGINATION_COUNT)
                            //->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)->get();

          $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_made_id', $car_made_id)
                            ->where('transmission_id', $transmission_id)
                            ->where('year_id', $car_year_id)
                            ->where('producttype_id', 1)
                            ->count();
          foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products);
          
          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);
          }  // end user case

           if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $products = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_made_id', $car_made_id)
                            ->where('transmission_id', $transmission_id)
                            ->where('year_id', $car_year_id)
                            ->whereIn('producttype_id', [1, 2, 3])
                            //->skip(($page-1)*$PAGINATION_COUNT)
                            //->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)->get();

          $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_made_id', $car_made_id)
                            ->where('transmission_id', $transmission_id)
                            ->where('year_id', $car_year_id)
                            ->whereIn('producttype_id', [1, 2, 3])
                            ->count();
          foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products);
          
          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
             $products = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_made_id', $car_made_id)
                            ->where('transmission_id', $transmission_id)
                            ->where('year_id', $car_year_id)
                            ->where('producttype_id', 1)
                            //->skip(($page-1)*$PAGINATION_COUNT)
                            //->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)->get();

          $total = Product::where('approved', 1)->where('cartype_id', $car_type_id)
                            ->where('car_made_id', $car_made_id)
                            ->where('transmission_id', $transmission_id)
                            ->where('year_id', $car_year_id)
                            ->where('producttype_id', 1)
                            ->count();
          foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
          $data = FrontProductsApiResource::collection($products);
          
          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);        
        } // end guest case
     }
    // end made_year_transmission 

     // start search products all_search 
     public function all_search($car_type_id, $car_made_id, $car_model_id, $car_year_id, $transmission_id, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
     {
      $lang = $this->getLang();

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
              $year = CarYear::where('id', $car_year_id)->first()->year;
              $model = CarModel::where('id', $car_model_id)->first()->carmodel;
              $products = Product::where('approved', 1)
                            ->where('cartype_id', $car_type_id)
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
                            // ->skip(($page-1)*$PAGINATION_COUNT)
                            // ->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)->get();

          $total = Product::where('approved', 1)
                            ->where('cartype_id', $car_type_id)
                            ->where('car_made_id', $car_made_id)
                          //  ->where('car_model_id', $car_model_id)
                          //  ->where('year_id', $car_year_id)
                            ->whereHas('car_model', function($q) use ($model){
                              $q->where('carmodel', $model);
                            })  
                            ->whereHas('year_from_func', function($q) use ($year){
                              $q->where('year', '<=', $year);
                            }) 
                            ->whereHas('year_to_func', function($q) use ($year){
                              $q->where('year', '>=', $year);
                            })  
                            ->whereIn('transmission_id', $transmission_id)
                            ->where('producttype_id', 1)
                            ->count();
            foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products);
          
          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) 
          {
            $year = CarYear::where('id', $car_year_id)->first()->year;
            $model = CarModel::where('id', $car_model_id)->first()->carmodel;
            $products = Product::where('approved', 1)
                            ->where('cartype_id', $car_type_id)
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
                            // ->skip(($page-1)*$PAGINATION_COUNT)
                            // ->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)->get();

          $total = Product::where('approved', 1)
                            ->where('cartype_id', $car_type_id)
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
                           // ->where('approved', 1)
                           // ->where('cartype_id', $car_type_id)
                           // ->where('car_made_id', $car_made_id)
                           // ->where('transmission_id', $transmission_id)
                           // ->whereIn('producttype_id', [1, 2, 3])
                            ->count();
          foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
          $data = FrontProductsApiResource::collection($products);
          
          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
          $year = CarYear::where('id', $car_year_id)->first()->year;
          $model = CarModel::where('id', $car_model_id)->first()->carmodel;
            $products = Product::where('approved', 1)
                            ->where('cartype_id', $car_type_id)
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
                            // ->skip(($page-1)*$PAGINATION_COUNT)
                            // ->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)->get();

          $total = Product::where('approved', 1)
                            ->where('cartype_id', $car_type_id)
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
                            ->count();
          foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
          $data = FrontProductsApiResource::collection($products);
          
          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);         
        } // end guest case
     }
    // end search products all_search  
}
