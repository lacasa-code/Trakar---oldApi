<?php

namespace App\Http\Controllers\Api\V1\User\Search;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Website\User\FavouriteCars\RemoveUserFavouriteCarApiRequest;
use App\Models\Product;
use Gate;
use App\Http\Requests\SearchApisRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Schema;
use App\Http\Resources\User\Search\ProductSearchApiResource;
use App\Http\Resources\Website\Products\FrontProductsApiResource;
use App\Http\Requests\Website\User\Products\AddFavouriteCarMadeApiRequest;
use App\Http\Resources\Website\User\Products\UserFavouriteCarsApiResource;
use App\Models\Favouritecar;
use Auth;
//use App\Http\Requests\Website\HomePage\DisplaySearchResultsApiRequest;
use App\Http\Requests\Api\V1\Website\HomePage\AddToMyFavouriteCarsApiRequest;
use App\Models\CarYear;
use App\Models\CarModel;
use App\Models\AddVendor;

class UserFavouriteCarsApiController extends Controller
{
   public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }
  
    public function show_favourite_cars(Request $request)
     {
       $lang = $this->getLang();
      $user = Auth::user();
      $car_made_id = $request->car_made_id;
      $user_id = Auth::user()->id;
      $user_roles = $user->roles->pluck('title')->toArray();

       // if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
          $favourites = Favouritecar::where('user_id', $user_id)
                                   ->get();
          $total = Favouritecar::where('user_id', $user_id)
                                   ->count();
          $data = UserFavouriteCarsApiResource::collection($favourites);
                return response()->json([
                      'status_code' => 200, 
                      'message'     => 'success',
                      'data' => $data,
                      'total' => $total,
                     ], 200);
      /*  } // end if user
        else{
            return response()->json([
                  'status_code' => 401, 
                  'message'  => 'un authorized access page due to permissions',
                 ], 401);
        }*/
     }

     public function select_from_favourites(Request $request, $id)
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

      $user       = Auth::user();
      $user_id    = Auth::user()->id;
      $user_roles = $user->roles->pluck('title')->toArray();

       // if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
          $favourite   = Favouritecar::findOrFail($id);
          if ($favourite->user_id != $user_id) {
            return response()->json([
                  'status_code' => 401, 
                  'message'  => 'item does not match',
                 ], 401);
          }

      $part_categories = $request->categories;
      $manufacturers   = $request->manufacturers;
      $origins         = $request->origins;
      $start_price     = $request->start_price;
      $end_price       = $request->end_price;
      $cartype_id      = $request->cartype_id;

      // case 1
        if ($part_categories == '' && $manufacturers == '' && $origins == '' && $start_price == '') {
          $filter_products =  $this->fetching_all_data($page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
         // return $filter_products;
        }

        // case 1
        if ($part_categories != '' && $manufacturers == '' && $origins == '' && $start_price == '') {
          $part_categories = json_decode($request->categories);
        // $manufacturers   = json_decode($request->manufacturers);
        // $origins         = json_decode($request->origins);
          $filter_products = $this->part_categories_search($part_categories, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 2
        if ($part_categories == '' && $manufacturers != '' && $origins == '' && $start_price == '') {
          // $part_categories = json_decode($request->categories);
        $manufacturers   = json_decode($request->manufacturers);
        // $origins         = json_decode($request->origins);
          $filter_products = $this->manufacturers_search($manufacturers, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 3
        if ($part_categories == '' && $manufacturers == '' && $origins != '' && $start_price == '') {
          // $part_categories = json_decode($request->categories);
        // $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          $filter_products = $this->origins_search($origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT);
        }

        // case 4
        if ($part_categories == '' && $manufacturers == '' && $origins == '' && $start_price != '') {
          // $part_categories = json_decode($request->categories);
        // $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          $filter_products = $this->price_search($start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 5
        if ($part_categories != '' && $manufacturers != '' && $origins == '' && $start_price == '') {
          $part_categories = json_decode($request->categories);
          // $filter_products = $part_categories;
        $manufacturers   = json_decode($request->manufacturers);
        //$origins         = json_decode($request->origins);
        //$filter_products = 'bbbv';
          $filter_products = $this->part_categories_manufacturers_search($part_categories, $manufacturers, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 6
        if ($part_categories != '' && $manufacturers == '' && $origins != '' && $start_price == '') {
          $part_categories = json_decode($request->categories);
        //$manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          $filter_products =  $this->part_categories_origins_search($part_categories, $origins, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 7
        if ($part_categories != '' && $manufacturers == '' && $origins == '' && $start_price != '') {
          $part_categories = json_decode($request->categories);
        //$manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          $filter_products =  $this->part_categories_price_search($part_categories, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 8
        if ($part_categories != '' && $manufacturers != '' && $origins != '' && $start_price == '') {
          $part_categories = json_decode($request->categories);
        $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          $filter_products =  $this->part_manufacturers_origins_search($part_categories, $manufacturers, $origins, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 9
        if ($part_categories != '' && $manufacturers != '' && $origins == '' && $start_price != '') {
          $part_categories = json_decode($request->categories);
        $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          $filter_products =  $this->part_manufacturers_price_search($part_categories, $manufacturers, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 10
        if ($part_categories != '' && $manufacturers == '' && $origins != '' && $start_price != '') {
          $part_categories = json_decode($request->categories);
        $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          $filter_products =  $this->part_origins_price_search($part_categories, $origins, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 11
        if ($part_categories != '' && $manufacturers != '' && $origins != '' && $start_price != '') {
          $part_categories = json_decode($request->categories);
        $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          $filter_products =  $this->four_hand_search($part_categories, $manufacturers, $origins, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 12
        if ($part_categories == '' && $manufacturers != '' && $origins != '' && $start_price == '') {
          //$part_categories = json_decode($request->categories);
        $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          $filter_products =  $this->manufacturers_origins_search($manufacturers, $origins, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 13
        if ($part_categories == '' && $manufacturers != '' && $origins == '' && $start_price != '') {
            //$part_categories = json_decode($request->categories);
            $manufacturers   = json_decode($request->manufacturers);
            $origins         = json_decode($request->origins);
          $filter_products =  $this->manufacturers_price_search($manufacturers, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 14
        if ($part_categories == '' && $manufacturers != '' && $origins != '' && $start_price != '') {
            //$part_categories = json_decode($request->categories);
            $manufacturers   = json_decode($request->manufacturers);
            $origins         = json_decode($request->origins);
          $filter_products =  $this->manufacturers_origins_price_search($manufacturers, $origins, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }


        // case 15
        if ($part_categories == '' && $manufacturers == '' && $origins != '' && $start_price != '') {
            //$part_categories = json_decode($request->categories);
            // $manufacturers   = json_decode($request->manufacturers);
            $origins         = json_decode($request->origins);
          $filter_products =  $this->origins_price_search($origins, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

          $unique_array = array();

          $car_type_id       = $favourite->car_type_id == null ? 0 : $favourite->car_type_id;
          $car_made_id       = $favourite->car_made_id == null ? 0 : $favourite->car_made_id;
          $car_model_id      = $favourite->car_model_id == null ? 0 : $favourite->car_model_id;
          $car_year_id       = $favourite->car_year_id == null ? 0 : $favourite->car_year_id;
          $transmission_id   = $favourite->transmission_id == null ? 0 : $favourite->transmission_id;

          if ($transmission_id != 0) {
            array_push($unique_array, [
              'transmission_id' => $transmission_id,
            ]);
          }
          if ($car_made_id != 0) {
            array_push($unique_array, [
              'car_made_id' => $car_made_id,
            ]);
          }
          if ($car_model_id != 0) {
            array_push($unique_array, [
              'car_model_id' => $car_model_id,
            ]);
          }
          if ($car_year_id != 0) {
            array_push($unique_array, [
              'year_id' => $car_year_id,
            ]);
          }
           $count =  count($unique_array);
          
          if ($count == 1) {

            $one   = trim(json_encode(array_keys($unique_array[0])), '["');
            $first =  trim($one,']"');

            $ids =  $filter_products->pluck('id')->toArray();
            $products = Product::whereIn('id', $ids)
                                 ->where('cartype_id', $car_type_id)
                                 ->whereIn($first, array_values($unique_array[0]))
                                 ->get();
          }
          if ($count == 2) { // count 2
           
            $one   = trim(json_encode(array_keys($unique_array[0])), '["');
            $first =  trim($one,']"');
            $two   = trim(json_encode(array_keys($unique_array[1])), '["');
            $second =  trim($two,']"');
            if ($first == 'transmission_id') 
            {
              if (array_values($unique_array[0]) == [3]) 
              {
                $ids =  $filter_products->pluck('id')->toArray();
                // return 'mn';
                $products = Product::whereIn('id', $ids)
                                 ->where('cartype_id', $car_type_id)
                                 ->whereIn($first, [1, 2])
                                 ->whereIn($second, array_values($unique_array[1]))
                                 ->get();
              }else{
                $ids =  $filter_products->pluck('id')->toArray();
                // return 'mn';
                $products = Product::whereIn('id', $ids)
                                 ->where('cartype_id', $car_type_id)
                                 ->whereIn($first, array_values($unique_array[0]))
                                 ->whereIn($second, array_values($unique_array[1]))
                                 ->get();
              }
            }
            if ($second == 'transmission_id') 
            {
              if (array_values($unique_array[1]) ==  [3]) 
              {
                $ids =  $filter_products->pluck('id')->toArray();
               // return 'mn';
                $products = Product::whereIn('id', $ids)
                                 ->where('cartype_id', $car_type_id)
                                 ->whereIn($first, array_values($unique_array[0]))
                                 ->whereIn($second, [1, 2])
                                 ->get();
              }
            }else{
                $ids =  $filter_products->pluck('id')->toArray();
                $model = CarModel::whereIn('id', array_values($unique_array[1]))
                                ->first()->carmodel;
                // return 'mn';
                $products = Product::whereIn('id', $ids)
                                 ->where('cartype_id', $car_type_id)
                                 ->whereIn($first, array_values($unique_array[0]))
                                 ->whereHas('car_model', function($q) use ($model){
                              $q->where('carmodel', $model);
                            })->get();
              }
          } // count 2
          if ($count == 3) {
            // return 'bbb';
            $one   = trim(json_encode(array_keys($unique_array[0])), '["');
            $first =  trim($one,']"');
            $two   = trim(json_encode(array_keys($unique_array[1])), '["');
            $second =  trim($two,']"');
            $three   = trim(json_encode(array_keys($unique_array[2])), '["');
            $third =  trim($three,']"');
            
          /*  if ($first == 'transmission_id') 
            {
              if (array_values($unique_array[0]) == [3]) 
              {
                $search_index = CarYear::whereIn('id', array_values($unique_array[2]))->first()->year;
                $ids =  $filter_products->pluck('id')->toArray();
                $products = Product::whereIn('id', $ids)
                                     ->where('cartype_id', $car_type_id)
                                     ->whereIn($first, [1, 2])
                                     ->whereIn($second, array_values($unique_array[1]));
                                     // ->whereIn($third, array_values($unique_array[2]))
                $products = $products->whereHas('year_from_func', function ($q) use ($search_index) {
                                                $q->where('year', 'like', "%{$search_index}%");
                                      })->orWhereHas('year_to_func', function ($q) use ($search_index) {
                                      $q->where('year', 'like', "%{$search_index}%");
                                      })->get();
           //   }else{*/
                $search_index = CarYear::whereIn('id', array_values($unique_array[2]))->first()->year;
               
                $model = CarModel::whereIn('id', array_values($unique_array[1]))
                                ->first()->carmodel;

                $ids =  $filter_products->pluck('id')->toArray();
                $products = Product::whereIn('id', $ids)
                                     ->where('cartype_id', $car_type_id)
                                     ->whereIn($first, array_values($unique_array[0]))
                                     //->whereIn($second, array_values($unique_array[1]));
                                     // ->whereIn($third, array_values($unique_array[2]))
                            ->whereHas('car_model', function($q) use ($model){
                              $q->where('carmodel', $model);
                            })  
                            ->whereHas('year_from_func', function($q) use ($search_index){
                              $q->where('year', '<=', $search_index);
                            }) 
                            ->whereHas('year_to_func', function($q) use ($search_index){
                              $q->where('year', '>=', $search_index);
                            })->get();
               /* $products = $products->whereHas('year_from_func', function ($q) use ($search_index) {
                                                $q->where('year', 'like', "%{$search_index}%");
                                      })->orWhereHas('year_to_func', function ($q) use ($search_index) {
                                      $q->where('year', 'like', "%{$search_index}%");
                                      })->get();*/
           /*   }
            } // end first
            if ($second == 'transmission_id') 
            {
              if (array_values($unique_array[1]) ==  [3]) 
              {
                $search_index = CarYear::whereIn('id', array_values($unique_array[2]))->first()->year;
                $ids =  $filter_products->pluck('id')->toArray();
                $products = Product::whereIn('id', $ids)
                                     ->where('cartype_id', $car_type_id)
                                     ->whereIn($first, array_values($unique_array[0]))
                                     ->whereIn($second, [1, 2]);
                                     // ->whereIn($third, array_values($unique_array[2]))
                $products = $products->whereHas('year_from_func', function ($q) use ($search_index) {
                                                $q->where('year', 'like', "%{$search_index}%");
                                      })->orWhereHas('year_to_func', function ($q) use ($search_index) {
                                      $q->where('year', 'like', "%{$search_index}%");
                                      })->get();
              }else{
                $search_index = CarYear::whereIn('id', array_values($unique_array[2]))->first()->year;
                $ids =  $filter_products->pluck('id')->toArray();
                $products = Product::whereIn('id', $ids)
                                     ->where('cartype_id', $car_type_id)
                                     ->whereIn($first, array_values($unique_array[0]))
                                     ->whereIn($second, array_values($unique_array[1]));
                                     // ->whereIn($third, array_values($unique_array[2]))
                $products = $products->whereHas('year_from_func', function ($q) use ($search_index) {
                                                $q->where('year', 'like', "%{$search_index}%");
                                      })->orWhereHas('year_to_func', function ($q) use ($search_index) {
                                      $q->where('year', 'like', "%{$search_index}%");
                                      })->get();
              }
            } // end second*/
          }
          if ($count == 4) 
          {
            $one   = trim(json_encode(array_keys($unique_array[0])), '["');
            $first =  trim($one,']"');
            $two   = trim(json_encode(array_keys($unique_array[1])), '["');
            $second =  trim($two,']"');
            $three   = trim(json_encode(array_keys($unique_array[2])), '["');
            $third =  trim($three,']"');
            $four   = trim(json_encode(array_keys($unique_array[3])), '["');
            $fourth =  trim($four,']"');

         //   return $fourth;

          
              if (array_values($unique_array[0]) == [1]) 
              {
                $transmission_id = [1, 3];
              }
              if (array_values($unique_array[0]) == [2]) 
              {
                $transmission_id = [2, 3];
              }
              if (array_values($unique_array[0]) == [3]) 
              {
                $transmission_id = [1, 2, 3];
              }
                $search_index = CarYear::whereIn('id', array_values($unique_array[3]))
                                      ->first()->year;
                $model = CarModel::whereIn('id', array_values($unique_array[2]))
                                ->first()->carmodel;
                 $ids =  $filter_products->pluck('id')->toArray();

            $products = Product::whereIn('id', $ids)
                            ->where('cartype_id', $car_type_id)
                            ->whereHas('car_model', function($q) use ($model){
                                  $q->where('carmodel', $model);
                                })  
                            ->whereHas('year_from_func', function($q) use ($search_index){
                                  $q->where('year', '<=', $search_index);
                                }) 
                            ->whereHas('year_to_func', function($q) use ($search_index){
                                  $q->where('year', '>=', $search_index);
                                })
                            ->whereIn('transmission_id', $transmission_id)
                            ->get();
                                 // ->whereIn($third, array_values($unique_array[2]))
            
          }

          foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }

          $data = FrontProductsApiResource::collection($products);
          $total = count($products);
                return response()->json([
                      'status_code' => 200, 
                      'message'     => 'success',
                      'data' => $data,
                      'total' => $total,
                     ], 200);
    }

    public function fetching_all_data($page, $ordered_by, $sort_type, $PAGINATION_COUNT)
    {
        $lang = $this->getLang();
      //return $part_categories;
        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) 
          {
                $new_products = Product::where('approved', 1)
                                       ->where('producttype_id', 1)
                                        // added june 29 2021
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
        return $data = FrontProductsApiResource::collection($new_products);        
            
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
                $new_products = Product::where('approved', 1)
                                ->whereIn('producttype_id', [1, 2, 3])
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                ->orderBy($ordered_by, $sort_type)->get(); 

        $total = count($new_products);
                        
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
        return $data = FrontProductsApiResource::collection($new_products);        
            
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
            $new_products = Product::where('approved', 1)
                                      ->where('producttype_id', 1)
                                       // added june 29 2021
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }

       return $data = FrontProductsApiResource::collection($new_products);        
                      
        } // end guest case
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
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
                $new_products = Product::where('approved', 1)
                                       ->where('producttype_id', 1)
                                        // added june 29 2021
                                       ->whereIn('category_id', $part_categories)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
 return $data = FrontProductsApiResource::collection($new_products);        
            
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
                $new_products = Product::where('approved', 1)
                                ->whereIn('producttype_id', [1, 2, 3])
                                
                                ->whereIn('category_id', $part_categories)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                ->orderBy($ordered_by, $sort_type)->get(); 

        $total = count($new_products);
                        
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
 return $data = FrontProductsApiResource::collection($new_products);        
            
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
            $new_products = Product::where('approved', 1)
                                   ->where('producttype_id', 1)
                                    // added june 29 2021
                                   ->whereIn('category_id', $part_categories)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                    ->orderBy($ordered_by, $sort_type)->get(); 

        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }

 return $data = FrontProductsApiResource::collection($new_products);        
                      
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
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
                $new_products = Product::where('approved', 1)
                                      ->where('producttype_id', 1)
                                      // added june 29 2021
                                        ->whereIn('category_id', $part_categories)
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
              }

 return $data = FrontProductsApiResource::collection($new_products);        
            
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
              $new_products = Product::where('approved', 1)
                                        ->whereIn('producttype_id', [1, 2, 3])
                                        
                                        ->whereIn('category_id', $part_categories)
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
              }
 return $data = FrontProductsApiResource::collection($new_products);        
            
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
              $new_products = Product::where('approved', 1)
                                        ->where('producttype_id', 1)
                                         // added june 29 2021
                                        ->whereIn('category_id', $part_categories)
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
              }

 return $data = FrontProductsApiResource::collection($new_products);        
                     
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
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $new_products = Product::where('approved', 1)
                                      ->where('producttype_id', 1)
                                       // added june 29 2021
                                        ->whereIn('category_id', $part_categories)
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
              }
 return $data = FrontProductsApiResource::collection($new_products);        
            
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $new_products = Product::where('approved', 1)
                                        ->whereIn('producttype_id', [1, 2, 3])
                                        
                                        ->whereIn('category_id', $part_categories)
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
              }
 return $data = FrontProductsApiResource::collection($new_products);        
            
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
             $new_products = Product::where('approved', 1)
                                       ->where('producttype_id', 1)
                                        // added june 29 2021
                                        ->whereIn('category_id', $part_categories)
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
              }

 return $data = FrontProductsApiResource::collection($new_products);        
                    
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
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
               $new_products = Product::where('approved', 1)
                                        ->where('producttype_id', 1)
                                         // added june 29 2021
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
              }
 return $data = FrontProductsApiResource::collection($new_products);        
            
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
             $new_products = Product::where('approved', 1)
             ->whereIn('producttype_id', [1, 2, 3])
             
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
              }
 return $data = FrontProductsApiResource::collection($new_products);        
            
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
             $new_products = Product::where('approved', 1)
             ->where('producttype_id', 1)
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
              }

 return $data = FrontProductsApiResource::collection($new_products);        
                     
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
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
               $new_products = Product::where('approved', 1)
               ->where('producttype_id', 1)
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
              }
 return $data = FrontProductsApiResource::collection($new_products);        
            
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
             $new_products = Product::where('approved', 1)
             ->whereIn('producttype_id', [1, 2, 3])
             
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
              }
 return $data = FrontProductsApiResource::collection($new_products);        
            
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
             $new_products = Product::where('approved', 1)
             ->where('producttype_id', 1)
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
              }
        
 return $data = FrontProductsApiResource::collection($new_products);        
                     
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
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
                $new_products = Product::where('approved', 1)
                ->where('producttype_id', 1)
                                        ->whereIn('category_id', $part_categories)
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
              }
 return $data = FrontProductsApiResource::collection($new_products);        
             
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
               $new_products = Product::where('approved', 1)
               ->whereIn('producttype_id', [1, 2, 3])
               
                                        ->whereIn('category_id', $part_categories)
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
              }
 return $data = FrontProductsApiResource::collection($new_products);        
            
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
               $new_products = Product::where('approved', 1)
               ->where('producttype_id', 1)
                                        ->whereIn('category_id', $part_categories)
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
              }

 return $data = FrontProductsApiResource::collection($new_products);        
                     
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
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
               $new_products = Product::where('approved', 1)
               ->where('producttype_id', 1)
                // added june 29 2021

                                        ->whereIn('category_id', $part_categories)
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
              }
 return $data = FrontProductsApiResource::collection($new_products);        
            
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
             $new_products = Product::where('approved', 1)
             ->whereIn('producttype_id', [1, 2, 3])
             
                                        ->whereIn('category_id', $part_categories)
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
              }
 return $data = FrontProductsApiResource::collection($new_products);        
            
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
             $new_products = Product::where('approved', 1)
             ->where('producttype_id', 1)
                                        ->whereIn('category_id', $part_categories)
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
              }

 return $data = FrontProductsApiResource::collection($new_products);        
                     
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
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
               $new_products = Product::where('approved', 1)
               ->where('producttype_id', 1)
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
              }
 return $data = FrontProductsApiResource::collection($new_products);        
            
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
             $new_products = Product::where('approved', 1)
             ->whereIn('producttype_id', [1, 2, 3])
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
              }

 return $data = FrontProductsApiResource::collection($new_products);        
            
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
            $new_products = Product::where('approved', 1)
            ->where('producttype_id', 1)
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
              }

 return $data = FrontProductsApiResource::collection($new_products);        
                      
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
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $new_products = Product::where('approved', 1)
              ->where('producttype_id', 1)
                                ->where('price', '>=', $start_price)
                                ->where('price', '<=', $end_price)
                                ->whereIn('category_id', $part_categories)
                                        //->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
 return $data = FrontProductsApiResource::collection($new_products);        
             
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
             $new_products = Product::where('approved', 1)
             ->whereIn('producttype_id', [1, 2, 3])
                                ->where('price', '>=', $start_price)
                                ->where('price', '<=', $end_price)
                                ->whereIn('category_id', $part_categories)
                                        //->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
 return $data = FrontProductsApiResource::collection($new_products);        
            
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
             $new_products = Product::where('approved', 1)
             ->where('producttype_id', 1)
                                ->where('price', '>=', $start_price)
                                ->where('price', '<=', $end_price)
                                ->whereIn('category_id', $part_categories)
                                        //->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }

 return $data = FrontProductsApiResource::collection($new_products);        
                     
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
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
                $new_products = Product::where('approved', 1)
                ->where('producttype_id', 1)
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
              }
 return $data = FrontProductsApiResource::collection($new_products);        
            
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
              $new_products = Product::where('approved', 1)
              ->whereIn('producttype_id', [1, 2, 3])
              
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
              }
 return $data = FrontProductsApiResource::collection($new_products);        
            
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
              $new_products = Product::where('approved', 1)
              ->where('producttype_id', 1)
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
              }
        
 return $data = FrontProductsApiResource::collection($new_products);        
                     
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
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $new_products = Product::where('approved', 1)
                                        ->where('producttype_id', 1)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
 return $data = FrontProductsApiResource::collection($new_products);        
            
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $new_products = Product::where('approved', 1)
            ->whereIn('producttype_id', [1, 2, 3])
            
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
 return $data = FrontProductsApiResource::collection($new_products);             
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
            $new_products = Product::where('approved', 1)
                                        ->where('producttype_id', 1)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }

 return $data = FrontProductsApiResource::collection($new_products);             
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
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $new_products = Product::where('approved', 1)
                                        ->where('producttype_id', 1)
                                        ->whereIn('prodcountry_id', $origins)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
 return $data = FrontProductsApiResource::collection($new_products);        
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $new_products = Product::where('approved', 1)
            ->whereIn('producttype_id', [1, 2, 3])
            
                                          ->whereIn('prodcountry_id', $origins)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
 return $data = FrontProductsApiResource::collection($new_products);        
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
            $new_products = Product::where('approved', 1)
                                        ->where('producttype_id', 1)
                                        ->whereIn('prodcountry_id', $origins)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }

 return $data = FrontProductsApiResource::collection($new_products);              
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
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
             $new_products = Product::where('approved', 1)
                                        ->where('producttype_id', 1)
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
            $total = count($new_products);
            foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
     return $data = FrontProductsApiResource::collection($new_products);        
                
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $new_products = Product::where('approved', 1)
                                        ->whereIn('producttype_id', [1, 2, 3])
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
            $total = count($new_products);
            foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
     return $data = FrontProductsApiResource::collection($new_products);        
                return response()->json([
                    'status_code' => 200,
                    'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
          // return $part_categories;
           $new_products = Product::where('approved', 1)
                                        ->where('producttype_id', 1)
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('manufacturer_id', $manufacturers)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
            $total = count($new_products);
            foreach ($new_products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }

     return $data = FrontProductsApiResource::collection($new_products);        
                         
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
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $new_products = Product::where('approved', 1)
                                       ->where('producttype_id', 1)
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
                $total = count($new_products);
                foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
         return $data = FrontProductsApiResource::collection($new_products);        
                    return response()->json([
                        'status_code' => 200,
                        'message'     => 'success',
                        'data'        => $data,
                        'total'       => $total,
                    ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $new_products = Product::where('approved', 1)
                                        ->whereIn('producttype_id', [1, 2, 3])
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
            $total = count($new_products);
            foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
     return $data = FrontProductsApiResource::collection($new_products);        
                return response()->json([
                    'status_code' => 200,
                    'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
            $new_products = Product::where('approved', 1)
                                        ->where('producttype_id', 1)
                                        ->whereIn('category_id', $part_categories)
                                        ->whereIn('prodcountry_id', $origins)
                                        // ->skip(($page-1)*$PAGINATION_COUNT)
                                        // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
            $total = count($new_products);
            foreach ($new_products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }

     return $data = FrontProductsApiResource::collection($new_products);        
                        
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
            if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
              $new_products = Product::where('approved', 1)
                                          ->where('producttype_id', 1)
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
                }
       return $data = FrontProductsApiResource::collection($new_products);        
                  
            }  // end user case

            if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
              $new_products = Product::where('approved', 1)
                                          ->whereIn('producttype_id', [1, 2, 3])
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
                }
       return $data = FrontProductsApiResource::collection($new_products);        
                  
            } // end vendor case
          }  // end case logged in 
          else{ // guest case
              $new_products = Product::where('approved', 1)
                                          ->where('producttype_id', 1)
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
                }
       return $data = FrontProductsApiResource::collection($new_products);                 
          } // end guest case
      }  // end probs   

     /*   } // end if user
        else{
            return response()->json([
                  'status_code' => 401, 
                  'message'  => 'un authorized access page due to permissions',
                 ], 401);
        }*/
    // }

  /*   public function add_favourite_car(AddFavouriteCarMadeApiRequest $request)
     {
      $user = Auth::user();
      $car_made_id = $request->car_made_id;
      $user_id = Auth::user()->id;
      $user_roles = $user->roles->pluck('title')->toArray();

        if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
          $favourite = Favouritecar::where('user_id', $user_id)
                                   ->where('car_made_id', $car_made_id)
                                   ->first();
              if ($favourite) {
                return response()->json([
                      'status_code' => 400, 
                      'message'  => 'you already added this car before',
                     ], 400);
              }
              else{
                Favouritecar::create([
                  'user_id'     => $user_id,
                  'car_made_id' => $car_made_id,
                ]);
                return response()->json([
                      'status_code' => 200, 
                      'message'  => 'car added to your favourites successfully',
                     ], 200);
              }
        } // end if user
        else{
            return response()->json([
                  'status_code' => 401, 
                  'message'  => 'un authorized access page due to permissions',
                 ], 401);
        }
     }*/

     // start search products with name
     public function add_to_my_favourites(AddToMyFavouriteCarsApiRequest $request)
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
     /* if ($ordered_by != '') {
        if (!Schema::hasColumn('products', $ordered_by)) {
          return response()->json(['message'  =>'order column not found',], 400);
        }
        if ($ordered_by == 'tags' || $ordered_by == 'categories') {
          $ordered_by = 'id';
        }
      } // end if*/

      $car_type_id       = $request->car_type_id;
      $car_made_id       = $request->car_made_id;
      $car_model_id      = $request->car_model_id;
      $car_year_id       = $request->car_year_id;
      $transmission_id   = $request->transmission_id;

      $user = Auth::user();
      $user_id = Auth::user()->id;
      $user_roles = $user->roles->pluck('title')->toArray();

        if (in_array('User', $user_roles) || in_array('Vendor', $user_roles)) {
          $favourite = Favouritecar::where('user_id', $user_id)
                                  ->where('car_type_id', $car_type_id)
                                  ->where('car_made_id', $car_made_id)
                                  ->where('car_model_id', $car_model_id)
                                  ->where('car_year_id', $car_year_id)
                                  ->where('transmission_id', $transmission_id)
                                  ->first();
              if ($favourite) {
                return response()->json([
                      'status_code' => 400, 
                      'message'  => 'you already added this car filter before',
                     ], 400);
              }
              else{
                Favouritecar::create([
                  'user_id'           => $user_id,
                  'car_type_id'       => $car_type_id,
                  'car_made_id'       => $car_made_id,
                  'car_model_id'      => $car_model_id,
                  'car_year_id'       => $car_year_id,
                  'transmission_id'   => $transmission_id,
                ]);
                return response()->json([
                      'status_code' => 200, 
                    //  'message'  => 'car added to your favourites successfully',
                      'message' => __('site_messages.vehicle_added_to_your_list'),
                     ], 200);
              }
        } // end if user
        else{
            return response()->json([
                  'status_code' => 401, 
                  'message'  => 'un authorized access page due to permissions',
                 ], 401);
        }
    }


     public function remove_favourite_car(RemoveUserFavouriteCarApiRequest $request)
     {
      $user = Auth::user();
      $id = $request->id;
      $user_id = Auth::user()->id;
      $user_roles = $user->roles->pluck('title')->toArray();

       // if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
          $favourite = Favouritecar::findOrFail($id);
	          if ($favourite->user_id != $user_id) {
	          	return response()->json([
	                      'status_code' => 400, 
	                      'message'  => 'this item does not belong to you to remove',
	                     ], 400);
	          }

              $favourite->delete();
              return response()->json([
                      'status_code' => 200, 
                      // 'message'  => 'success',
                      'message' => __('site_messages.vehicle_removed_from_your_list'),
                      'data' => null,
                     ], 200);
       /* } // end if user
        else{
            return response()->json([
                  'status_code' => 401, 
                  'message'  => 'un authorized access page due to permissions',
                 ], 401);
        }*/
     }
}
