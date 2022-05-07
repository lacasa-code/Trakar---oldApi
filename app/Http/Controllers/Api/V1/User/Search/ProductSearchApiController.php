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
use Auth;
use App\Models\AddVendor;
use App\Http\Requests\HomePageApiRequest;

class ProductSearchApiController extends Controller
{
  /*public function __construct()
  {
      $this->middleware('auth:api');
  }*/

  public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }

     public function search_with_name_render(HomePageApiRequest $request)
     {
                $lang = $this->getLang();
                $default_count = \Config::get('constants.pagination.items_per_page');
                $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
                
                $request->page == '' ? $page = 1 : $page = $request->page;
                $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
                $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
                $request->column_name == '' ? $column_name = '' : $column_name = $request->column_name;

                $search_index = $request->search_index;
                $cartype_id   = $request->cartype_id;

                $part_categories = $request->categories;
               // return $part_categories;
                $manufacturers   = $request->manufacturers;
                $origins         = $request->origins;
                $start_price     = $request->start_price;
                $end_price       = $request->end_price;
      
                

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
          return $this->fetching_all_data($page, $ordered_by , $sort_type, $PAGINATION_COUNT, $cartype_id, $search_index);
        }

        // case 1
        if ($part_categories != '' && $manufacturers == '' && $origins == '' && $start_price == '') {
          $part_categories = json_decode($request->categories);
        // $manufacturers   = json_decode($request->manufacturers);
        // $origins         = json_decode($request->origins);
          return $this->part_categories_search($part_categories, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $search_index);
        }

        // case 2
        if ($part_categories == '' && $manufacturers != '' && $origins == '' && $start_price == '') {
          // $part_categories = json_decode($request->categories);
        $manufacturers   = json_decode($request->manufacturers);
        // $origins         = json_decode($request->origins);
          return $this->manufacturers_search($manufacturers, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $search_index);
        }

        // case 3
        if ($part_categories == '' && $manufacturers == '' && $origins != '' && $start_price == '') {
          // $part_categories = json_decode($request->categories);
        // $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->origins_search($origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $search_index);
        }

        // case 4
        if ($part_categories == '' && $manufacturers == '' && $origins == '' && $start_price != '') {
          // $part_categories = json_decode($request->categories);
        // $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->price_search($start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $search_index);
        }

        // case 5
        if ($part_categories != '' && $manufacturers != '' && $origins == '' && $start_price == '') {
          $part_categories = json_decode($request->categories);
          // return $part_categories;
        $manufacturers   = json_decode($request->manufacturers);
        //$origins         = json_decode($request->origins);
        //return 'bbbv';
          return $this->part_categories_manufacturers_search($part_categories, $manufacturers, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $search_index);
        }

        // case 6
        if ($part_categories != '' && $manufacturers == '' && $origins != '' && $start_price == '') {
          $part_categories = json_decode($request->categories);
        //$manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->part_categories_origins_search($part_categories, $origins, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $search_index);
        }

        // case 7
        if ($part_categories != '' && $manufacturers == '' && $origins == '' && $start_price != '') {
          $part_categories = json_decode($request->categories);
        //$manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->part_categories_price_search($part_categories, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $search_index);
        }

        // case 8
        if ($part_categories != '' && $manufacturers != '' && $origins != '' && $start_price == '') {
          $part_categories = json_decode($request->categories);
        $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->part_manufacturers_origins_search($part_categories, $manufacturers, $origins, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $search_index);
        }

        // case 9
        if ($part_categories != '' && $manufacturers != '' && $origins == '' && $start_price != '') {
          $part_categories = json_decode($request->categories);
        $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->part_manufacturers_price_search($part_categories, $manufacturers, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $search_index);
        }

        // case 10
        if ($part_categories != '' && $manufacturers == '' && $origins != '' && $start_price != '') {
          $part_categories = json_decode($request->categories);
        $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->part_origins_price_search($part_categories, $origins, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $search_index);
        }

        // case 11
        if ($part_categories != '' && $manufacturers != '' && $origins != '' && $start_price != '') {
          $part_categories = json_decode($request->categories);
        $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->four_hand_search($part_categories, $manufacturers, $origins, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $search_index);
        }

        // case 12
        if ($part_categories == '' && $manufacturers != '' && $origins != '' && $start_price == '') {
          //$part_categories = json_decode($request->categories);
        $manufacturers   = json_decode($request->manufacturers);
        $origins         = json_decode($request->origins);
          return $this->manufacturers_origins_search($manufacturers, $origins, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $search_index);
        }

        // case 13
        if ($part_categories == '' && $manufacturers != '' && $origins == '' && $start_price != '') {
            //$part_categories = json_decode($request->categories);
            $manufacturers   = json_decode($request->manufacturers);
            $origins         = json_decode($request->origins);
          return $this->manufacturers_price_search($manufacturers, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $search_index);
        }

        // case 14
        if ($part_categories == '' && $manufacturers != '' && $origins != '' && $start_price != '') {
            //$part_categories = json_decode($request->categories);
            $manufacturers   = json_decode($request->manufacturers);
            $origins         = json_decode($request->origins);
          return $this->manufacturers_origins_price_search($manufacturers, $origins, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $search_index);
        }


        // case 15
        if ($part_categories == '' && $manufacturers == '' && $origins != '' && $start_price != '') {
            //$part_categories = json_decode($request->categories);
            // $manufacturers   = json_decode($request->manufacturers);
            $origins         = json_decode($request->origins);
          return $this->origins_price_search($origins, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT, $cartype_id, $search_index);
        }

      } // end search function 

       public function fetching_all_data($page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $search_index)
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
               $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhere('price', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('manufacturer', function ($q) use ($search_index) {
                            $q->where('manufacturer_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('origin_country', function ($q) use ($search_index) {
                            $q->where('country_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

           $car_types = [$cartype_id, 7];
              $products = $products->whereIn('cartype_id', $car_types);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1, 2, 3])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1, 2, 3])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200);         
              
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhere('price', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('manufacturer', function ($q) use ($search_index) {
                            $q->where('manufacturer_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('origin_country', function ($q) use ($search_index) {
                            $q->where('country_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

           $car_types = [$cartype_id, 7];
              $products = $products->whereIn('cartype_id', $car_types);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1, 2, 3])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1, 2, 3])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200);    
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
                 $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhere('price', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('manufacturer', function ($q) use ($search_index) {
                            $q->where('manufacturer_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('origin_country', function ($q) use ($search_index) {
                            $q->where('country_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

                        $car_types = [$cartype_id, 7];
                        $products = $products->whereIn('cartype_id', $car_types);
                                                        
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              }
        
                $data = ProductSearchApiResource::collection($products->where('producttype_id', 1)->where('approved', 1));

                $total = $products->where('approved', 1)
                ->where('producttype_id', 1)
                ->count();
                              
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200);
        } // end guest case
    }


    public function part_categories_search($part_categories, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $search_index)
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
                $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhere('price', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('manufacturer', function ($q) use ($search_index) {
                            $q->where('manufacturer_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('origin_country', function ($q) use ($search_index) {
                            $q->where('country_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('allcategory', function ($q) use ($search_index, $part_categories) {
                            $q->whereIn('id', $part_categories);
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              $products = $products->whereIn('cartype_id', $car_types);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200);  
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
              $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhere('price', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('manufacturer', function ($q) use ($search_index) {
                            $q->where('manufacturer_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('origin_country', function ($q) use ($search_index) {
                            $q->where('country_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('allcategory', function ($q) use ($search_index, $part_categories) {
                            $q->whereIn('id', $part_categories);
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              $products = $products->whereIn('cartype_id', $car_types);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1, 2, 3])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1, 2, 3])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200);  
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
            $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhere('price', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('manufacturer', function ($q) use ($search_index) {
                            $q->where('manufacturer_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('origin_country', function ($q) use ($search_index) {
                            $q->where('country_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('allcategory', function ($q) use ($search_index, $part_categories) {
                            $q->whereIn('id', $part_categories);
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              $products = $products->whereIn('cartype_id', $car_types);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200);  
        } // end guest case
    }

    public function part_manufacturers_origins_search($part_categories, $manufacturers, $origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $search_index)
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
              $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhere('price', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              
              $products->whereIn('cartype_id', $car_types)
                        ->WhereHas('allcategory', function ($q) use ($search_index, $part_categories) {
                            $q->whereIn('id', $part_categories);
                          })
                        ->whereIn('prodcountry_id', $origins)
                        ->whereIn('manufacturer_id', $manufacturers);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200);  
          } // end vendor case
          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
             $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhere('price', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              $products->whereIn('cartype_id', $car_types)
                        ->WhereHas('allcategory', function ($q) use ($search_index, $part_categories) {
                            $q->whereIn('id', $part_categories);
                          })
                        ->whereIn('prodcountry_id', $origins)
                        ->whereIn('manufacturer_id', $manufacturers);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1, 2, 3])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1, 2, 3])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200); 
          }
        }  // end case logged in 
        else{ // guest case
               $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhere('price', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              $products->whereIn('cartype_id', $car_types)
                        ->WhereHas('allcategory', function ($q) use ($search_index, $part_categories) {
                            $q->whereIn('id', $part_categories);
                          })
                        ->whereIn('prodcountry_id', $origins)
                        ->whereIn('manufacturer_id', $manufacturers);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200);       
        } // end guest case
    }

    public function part_manufacturers_price_search($part_categories, $manufacturers, $start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $search_index)
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
               $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('origin_country', function ($q) use ($search_index) {
                            $q->where('country_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              $products = $products->whereIn('cartype_id', $car_types)
                         ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price)
                        ->WhereHas('allcategory', function ($q) use ($search_index, $part_categories) {
                            $q->whereIn('id', $part_categories);
                          })
                         ->whereIn('manufacturer_id', $manufacturers);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200); 
          } // end user case
          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
             $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('origin_country', function ($q) use ($search_index) {
                            $q->where('country_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              $products = $products->whereIn('cartype_id', $car_types)
                         ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price)
                        ->WhereHas('allcategory', function ($q) use ($search_index, $part_categories) {
                            $q->whereIn('id', $part_categories);
                          })
                         ->whereIn('manufacturer_id', $manufacturers);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1, 2, 3])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1, 2, 3])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
           $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('origin_country', function ($q) use ($search_index) {
                            $q->where('country_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              $products = $products->whereIn('cartype_id', $car_types)
                         ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price)
                        ->WhereHas('allcategory', function ($q) use ($search_index, $part_categories) {
                            $q->whereIn('id', $part_categories);
                          })
                         ->whereIn('manufacturer_id', $manufacturers);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;//$user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = 0;//$user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = 0;//$user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200);          
        } // end guest case     
    }

    public function origins_price_search($origins, $start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $search_index)
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
               $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('manufacturer', function ($q) use ($search_index) {
                            $q->where('manufacturer_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              $products = $products->whereIn('cartype_id', $car_types)
                         ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price)
                         ->whereIn('prodcountry_id', $origins);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200); 
          } // end user case
          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
             $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('manufacturer', function ($q) use ($search_index) {
                            $q->where('manufacturer_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              $products = $products->whereIn('cartype_id', $car_types)
                         ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price)
                         ->whereIn('prodcountry_id', $origins);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1, 2, 3])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1, 2, 3])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200); 
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
               $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('manufacturer', function ($q) use ($search_index) {
                            $q->where('manufacturer_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              $products = $products->whereIn('cartype_id', $car_types)
                         ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price)
                         ->whereIn('prodcountry_id', $origins);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;//$user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = 0;//$user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = 0;//$user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200);     
        } // end guest case   
    }

    public function manufacturers_origins_price_search($manufacturers, $origins, $start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $search_index)
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
               $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              $products = $products->whereIn('cartype_id', $car_types);

              $products = $products->whereIn('cartype_id', $car_types)
                         ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price)
                         ->whereIn('prodcountry_id', $origins)
                         ->whereIn('manufacturer_id', $manufacturers);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200); 
          } // end user case
          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
              $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              $products = $products->whereIn('cartype_id', $car_types);

              $products = $products->whereIn('cartype_id', $car_types)
                         ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price)
                         ->whereIn('prodcountry_id', $origins)
                         ->whereIn('manufacturer_id', $manufacturers);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1, 2, 3])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1, 2, 3])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200); 
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
               $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              $products = $products->whereIn('cartype_id', $car_types);

              $products = $products->whereIn('cartype_id', $car_types)
                         ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price)
                         ->whereIn('prodcountry_id', $origins)
                         ->whereIn('manufacturer_id', $manufacturers);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;//$user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = 0;//$user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = 0;//$user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200); 
        } // end guest case     
        
    }

    public function four_hand_search($part_categories, $manufacturers, $origins, $start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $search_index)
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
               $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              $products = $products->whereIn('cartype_id', $car_types)
                         ->orWhereHas('allcategory', function ($q) use ($search_index, $part_categories) {
                            $q->whereIn('id', $part_categories);
                          })
                         ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price)
                        ->whereIn('prodcountry_id', $origins)
                        ->whereIn('manufacturer_id', $manufacturers);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200); 
          } // end user case
          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
             $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              $products = $products->whereIn('cartype_id', $car_types)
                         ->orWhereHas('allcategory', function ($q) use ($search_index, $part_categories) {
                            $q->whereIn('id', $part_categories);
                          })
                         ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price)
                        ->whereIn('prodcountry_id', $origins)
                        ->whereIn('manufacturer_id', $manufacturers);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1, 2, 3])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1, 2, 3])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200); 
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
             $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              $products = $products->whereIn('cartype_id', $car_types)
                         ->orWhereHas('allcategory', function ($q) use ($search_index, $part_categories) {
                            $q->whereIn('id', $part_categories);
                          })
                         ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price)
                        ->whereIn('prodcountry_id', $origins)
                        ->whereIn('manufacturer_id', $manufacturers);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200);      
        } // end guest case  
        
    }

    public function part_origins_price_search($part_categories, $origins, $start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $search_index)
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
               $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('manufacturer', function ($q) use ($search_index) {
                            $q->where('manufacturer_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

            $car_types = [$cartype_id, 7];
             $products = $products->whereIn('cartype_id', $car_types)
                         ->orWhereHas('allcategory', function ($q) use ($search_index, $part_categories) {
                            $q->whereIn('id', $part_categories);
                          })
                         ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price)
                        ->whereIn('prodcountry_id', $origins);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200); 
          } // end user case
          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
             $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('manufacturer', function ($q) use ($search_index) {
                            $q->where('manufacturer_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

            $car_types = [$cartype_id, 7];
             $products = $products->whereIn('cartype_id', $car_types)
                         ->orWhereHas('allcategory', function ($q) use ($search_index, $part_categories) {
                            $q->whereIn('id', $part_categories);
                          })
                         ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price)
                        ->whereIn('prodcountry_id', $origins);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1, 2, 3])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1, 2, 3])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200); 
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
            $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('manufacturer', function ($q) use ($search_index) {
                            $q->where('manufacturer_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

            $car_types = [$cartype_id, 7];
             $products = $products->whereIn('cartype_id', $car_types)
                         ->orWhereHas('allcategory', function ($q) use ($search_index, $part_categories) {
                            $q->whereIn('id', $part_categories);
                          })
                         ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price)
                        ->whereIn('prodcountry_id', $origins);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;//$user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = 0;//$user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = 0;//$user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200);  
        } // end guest case      
    }

public function price_search($start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $search_index)
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
              $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('manufacturer', function ($q) use ($search_index) {
                            $q->where('manufacturer_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('origin_country', function ($q) use ($search_index) {
                            $q->where('country_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              $products = $products->whereIn('cartype_id', $car_types)
                         ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200); 
          } // end user case
          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('manufacturer', function ($q) use ($search_index) {
                            $q->where('manufacturer_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('origin_country', function ($q) use ($search_index) {
                            $q->where('country_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              $products = $products->whereIn('cartype_id', $car_types)
                         ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1, 2, 3])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1, 2, 3])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200); 
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
           $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('manufacturer', function ($q) use ($search_index) {
                            $q->where('manufacturer_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('origin_country', function ($q) use ($search_index) {
                            $q->where('country_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              $products = $products->whereIn('cartype_id', $car_types)
                         ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200);      
        } // end guest case  
        
    }

    public function part_categories_price_search($part_categories, $start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $search_index)
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
              $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('manufacturer', function ($q) use ($search_index) {
                            $q->where('manufacturer_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('origin_country', function ($q) use ($search_index) {
                            $q->where('country_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
               $products = $products->whereIn('cartype_id', $car_types)
                         ->orWhereHas('allcategory', function ($q) use ($search_index, $part_categories) {
                            $q->whereIn('id', $part_categories);
                          })
                         ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200);  
          } // end user case
          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
             $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('manufacturer', function ($q) use ($search_index) {
                            $q->where('manufacturer_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('origin_country', function ($q) use ($search_index) {
                            $q->where('country_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
               $products = $products->whereIn('cartype_id', $car_types)
                         ->orWhereHas('allcategory', function ($q) use ($search_index, $part_categories) {
                            $q->whereIn('id', $part_categories);
                          })
                         ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1, 2, 3])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1, 2, 3])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200);  
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
             $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('manufacturer', function ($q) use ($search_index) {
                            $q->where('manufacturer_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('origin_country', function ($q) use ($search_index) {
                            $q->where('country_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
               $products = $products->whereIn('cartype_id', $car_types)
                         ->orWhereHas('allcategory', function ($q) use ($search_index, $part_categories) {
                            $q->whereIn('id', $part_categories);
                          })
                         ->where('actual_price', '>=', $start_price)
                        ->where('actual_price', '<=', $end_price);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0; // $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = 0; // $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = 0; // $user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200);       
        } // end guest case     
        
    }

    public function manufacturers_price_search($manufacturers, $start_price, $end_price, $page, $ordered_by , $sort_type, $PAGINATION_COUNT, $cartype_id, $search_index)
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
               $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('origin_country', function ($q) use ($search_index) {
                            $q->where('country_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              $products = $products->whereIn('cartype_id', $car_types)
                         ->whereIn('manufacturer_id', $manufacturers)
                         ->where('actual_price', '>=', $start_price)
                         ->where('actual_price', '<=', $end_price);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200); 
          } // end user case
          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
             $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('origin_country', function ($q) use ($search_index) {
                            $q->where('country_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              $products = $products->whereIn('cartype_id', $car_types)
                         ->whereIn('manufacturer_id', $manufacturers)
                         ->where('actual_price', '>=', $start_price)
                         ->where('actual_price', '<=', $end_price);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1, 2, 3])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1, 2, 3])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200); 
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
            $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('origin_country', function ($q) use ($search_index) {
                            $q->where('country_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              $products = $products->whereIn('cartype_id', $car_types)
                         ->whereIn('manufacturer_id', $manufacturers)
                         ->where('actual_price', '>=', $start_price)
                         ->where('actual_price', '<=', $end_price);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;//$user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = 0;//$user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = 0;//$user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200);        
        } // end guest case     
        
    }

    public function manufacturers_search($manufacturers, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $search_index)
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
              $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhere('price', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('origin_country', function ($q) use ($search_index) {
                            $q->where('country_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('allcategory', function ($q) use ($search_index) {
                            $q->whwhere('name', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              $products = $products->whereIn('cartype_id', $car_types)
                         ->whereIn('manufacturer_id', $manufacturers);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200); 
          } // end user case
          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhere('price', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('origin_country', function ($q) use ($search_index) {
                            $q->where('country_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('allcategory', function ($q) use ($search_index) {
                            $q->whwhere('name', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              $products = $products->whereIn('cartype_id', $car_types)
                         ->whereIn('manufacturer_id', $manufacturers);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1, 2, 3])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1, 2, 3])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200); 
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
             $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhere('price', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('origin_country', function ($q) use ($search_index) {
                            $q->where('country_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('allcategory', function ($q) use ($search_index) {
                            $q->whwhere('name', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              $products = $products->whereIn('cartype_id', $car_types)
                         ->whereIn('manufacturer_id', $manufacturers);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;// $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = 0;// $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = 0;// $user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200);     
        } // end guest case
        
    }

    public function origins_search($origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $search_index)
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
              $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhere('price', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('manufacturer', function ($q) use ($search_index) {
                            $q->where('manufacturer_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              $products = $products->whereIn('cartype_id', $car_types)
                         ->whereIn('prodcountry_id', $origins);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200); 
          } // end user case
          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
             $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhere('price', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('manufacturer', function ($q) use ($search_index) {
                            $q->where('manufacturer_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              $products = $products->whereIn('cartype_id', $car_types)
                         ->whereIn('prodcountry_id', $origins);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1, 2, 3])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1, 2, 3])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
             $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhere('price', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('manufacturer', function ($q) use ($search_index) {
                            $q->where('manufacturer_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              $products = $products->whereIn('cartype_id', $car_types)
                         ->whereIn('prodcountry_id', $origins);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;//$user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = 0;//$user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = 0;//$user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200); 
        } // end guest case   
        
    }

    public function part_categories_manufacturers_search($part_categories, $manufacturers, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $search_index)
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
               $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhere('price', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('origin_country', function ($q) use ($search_index) {
                            $q->where('country_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              $products = $products->whereIn('cartype_id', $car_types)
                         ->orWhereHas('allcategory', function ($q) use ($search_index, $part_categories) {
                            $q->whereIn('id', $part_categories);
                          })
                         ->whereIn('manufacturer_id', $manufacturers);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200); 
          } // end user case
          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
             $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhere('price', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('origin_country', function ($q) use ($search_index) {
                            $q->where('country_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              $products = $products->whereIn('cartype_id', $car_types)
                         ->orWhereHas('allcategory', function ($q) use ($search_index, $part_categories) {
                            $q->whereIn('id', $part_categories);
                          })
                         ->whereIn('manufacturer_id', $manufacturers);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1, 2, 3])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1, 2, 3])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200); 
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
            $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhere('price', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('origin_country', function ($q) use ($search_index) {
                            $q->where('country_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              $products = $products->whereIn('cartype_id', $car_types)
                         ->orWhereHas('allcategory', function ($q) use ($search_index, $part_categories) {
                            $q->whereIn('id', $part_categories);
                          })
                         ->whereIn('manufacturer_id', $manufacturers);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;//$user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = 0;//$user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = 0;//$user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200);      
        } // end guest case     
        
    }

    public function part_categories_origins_search($part_categories, $origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $search_index)
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
               $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhere('price', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('manufacturer', function ($q) use ($search_index) {
                            $q->where('manufacturer_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              $products = $products->whereIn('cartype_id', $car_types)
                         ->orWhereHas('allcategory', function ($q) use ($search_index, $part_categories) {
                            $q->whereIn('id', $part_categories);
                          })
                         ->whereIn('prodcountry_id', $origins);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200); 
          } // end user case
          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhere('price', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('manufacturer', function ($q) use ($search_index) {
                            $q->where('manufacturer_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              $products = $products->whereIn('cartype_id', $car_types)
                         ->orWhereHas('allcategory', function ($q) use ($search_index, $part_categories) {
                            $q->whereIn('id', $part_categories);
                          })
                         ->whereIn('prodcountry_id', $origins);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1, 2, 3])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1, 2, 3])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200); 
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
            $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhere('price', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('manufacturer', function ($q) use ($search_index) {
                            $q->where('manufacturer_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              $products = $products->whereIn('cartype_id', $car_types)
                         ->orWhereHas('allcategory', function ($q) use ($search_index, $part_categories) {
                            $q->whereIn('id', $part_categories);
                          })
                         ->whereIn('prodcountry_id', $origins);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;//$user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = 0;//$user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = 0;//$user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200);       
        } // end guest case    
    }

      public function manufacturers_origins_search($manufacturers, $origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $search_index)
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
               $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhere('price', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              $products = $products->whereIn('cartype_id', $car_types)
                         ->whereIn('prodcountry_id', $origins)
                         ->whereIn('manufacturer_id', $manufacturers);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200); 
          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhere('price', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              $products = $products->whereIn('cartype_id', $car_types)
                         ->whereIn('prodcountry_id', $origins)
                         ->whereIn('manufacturer_id', $manufacturers);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1, 2, 3])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1, 2, 3])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200); 
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
               $products = Product::where('name', 'like', "%{$search_index}%")
                          ->orWhere('name_en', 'like', "%{$search_index}%")
                          ->orWhere('description', 'like', "%{$search_index}%")
                          ->orWhere('serial_coding', 'like', "%{$search_index}%")
                          ->orWhere('serial_number', 'like', "%{$search_index}%")
                          ->orWhere('price', 'like', "%{$search_index}%")
                          ->orWhereHas('car_made', function ($q) use ($search_index) {
                            $q->where('car_made', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('store', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('vendor', function ($q) use ($search_index) {
                            $q->where('vendor_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('transmission', function ($q) use ($search_index) {
                            $q->where('transmission_name', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('car_model', function ($q) use ($search_index) {
                            $q->where('carmodel', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();

              $car_types = [$cartype_id, 7];
              $products = $products->whereIn('cartype_id', $car_types)
                         ->whereIn('prodcountry_id', $origins)
                         ->whereIn('manufacturer_id', $manufacturers);
            
              foreach ($products as $new_product) {
                $new_product['in_cart']       = 0;//$user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = 0;//$user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = 0;//$user->revise_favourites($new_product->id);
              } 
                            
                $data = ProductSearchApiResource::collection($products->whereIn('producttype_id', [1])
                                      ->where('approved', 1));

                $total = $products->whereIn('producttype_id', [1])
                                      ->where('approved', 1)
                                      ->count();
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'        => $data,
                    'total'       => $total,
                ], 200);    
        } // end guest case             
      }  // end probs
        // alraedy user logged in
  }  
} // end class
