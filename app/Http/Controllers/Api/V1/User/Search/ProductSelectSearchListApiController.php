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
use App\Models\CarModel;
use App\Models\CarMade;
use App\Models\CarYear;
use Illuminate\Support\Facades\Cookie;
use App\Http\Requests\MakeOrderApiRequest;
use App\Models\Transmission;
use App\Models\Cartype;
use App\Models\Adpositions;
use App\Models\AddVendor;
use App\Models\Maincategory;
use App\Http\Resources\Api\V1\Admin\MainCategories\SingleMaincategoryApiResource;
use App\Http\Resources\Api\V1\Admin\MainCategories\MaincategoryApiResource;
use App\Http\Resources\Api\V1\Admin\MainCategories\SingleMaincategoryNestedApiResource;
use App\Models\ProductCategory;
use App\Models\PartCategory;
use App\Http\Requests\Api\V1\User\Front\SelectCategoryApiRequest;

class ProductSelectSearchListApiController extends Controller
{
  public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }
    // start list all
    public function get_all_car_models()
     {
      $lang = $this->getLang();
        $data   = CarModel::get();
        //$data   = CarModel::where('lang', $lang)->get();
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data' => $data], Response::HTTP_OK);
     }

     public function category_fetch_parts($id)
     {
            $lang  = $this->getLang();
       // $cat    = ProductCategory::where('lang', $lang)->where('id', $id)->first();
      //  $parts  = PartCategory::where('lang', $lang)->where('category_id', $id)->get();
        $cat    = ProductCategory::where('id', $id)->first();
        $parts  = PartCategory::where('category_id', $id)->get();
        $data = $parts;
        $total = count($parts);
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data' => $data,
          'total' => $total,
        ], Response::HTTP_OK);
     }

     public function fetch_vendors_list()
     {
            $data = AddVendor::all();
            return response()->json([
            'status_code'     => 200,
            'message'         => 'success',
            'data'            => $data,
            ], Response::HTTP_OK);
     }

     // end list all 
     public function list_all_car_models($id)
     {
      $lang = $this->getLang();
      if ($lang == 'ar') {
        $ordered_by = 'carmodel';
        $sort_type = 'ASC';
      }else{
        $ordered_by = 'name_en';
        $sort_type = 'ASC';
      }
        $car_made = CarMade::find($id);
        if (!$car_made) {
          return response()->json([
          'status_code'     => 400,
          'message'         => 'fail',
          'errors'          => 'wrong car made id',
          'data'            => null,], 400);
        }
        $data     = CarModel::where('carmade_id', $id)->orderBy($ordered_by, $sort_type)->get();
      //  $data     = CarModel::where('lang', $lang)->where('carmade_id', $id)->get();
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data' => $data], Response::HTTP_OK);
     }
     // end list all 

     // end list all 
     public function list_all_transmissions()
     {
      $lang = $this->getLang();
      //  $data = Transmission::where('lang', $lang)->get();
       if ($lang == 'ar') {
        $ordered_by = 'transmission_name';
        $sort_type = 'ASC';
      }else{
        $ordered_by = 'name_en';
        $sort_type = 'ASC';
      }

        $data = Transmission::orderBy($ordered_by, $sort_type)->get();

        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data' => $data], Response::HTTP_OK);
     }
     // end list all 

     // end list all 
     public function list_all_car_types()
     {
        $lang = $this->getLang();
        $data = Cartype::where('id', '!=', 7)->orderBy('type_name', 'ASC')->get();
      //  $data = Cartype::where('lang', $lang)->orderBy('type_name', 'ASC')->get();
        foreach ($data as $value) {
          $product   = Product::where('cartype_id', $value->id)->first();
          if ($product == null) {
            $someImage = null;
          }else{
            $someImage = $product['photo'][0]->image;
          }
          // return $someImage;
          $value['some_image'] = $someImage == '' ? null : $someImage;
        }
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data' => $data], Response::HTTP_OK);
     }
     // end list all 

     
     // start list all
     public function list_all_car_mades_cartype($id)
     {
       $lang = $this->getLang();
      
       if ($lang == 'ar') {
        $ordered_by = 'car_made';
        $sort_type = 'ASC';
      }else{
        $ordered_by = 'name_en';
        $sort_type = 'ASC';
      }

        $data = CarMade::where('cartype_id', $id)->orderBy($ordered_by, $sort_type)->get();
       // $data = CarMade::where('lang', $lang)->where('cartype_id', $id)->get();
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data' => $data], Response::HTTP_OK);
     }
     // end list all 

     // start list all
     public function list_all_car_mades()
     {
      $lang = $this->getLang();
       // $data = CarMade::where('lang', $lang)->get();
        $data = CarMade::get();
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data' => $data], Response::HTTP_OK);
     }
     // end list all 

     // start list all
     public function list_all_ads_positions()
     {
      $lang = $this->getLang();
        $data = Adpositions::get();
        // $data = Adpositions::where('lang', $lang)->get();
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data' => $data], Response::HTTP_OK);
     }
     // end list all 

      // start list all
     public function list_all_car_years()
     {
      $lang = $this->getLang();
        $data = CarYear::orderBy('year', 'ASC')->get();
        //$data = CarYear::where('lang', $lang)->orderBy('year', 'ASC')->get();
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data' => $data], Response::HTTP_OK);
     }
     // end list all 

      // start list all
     public function home_main_categories()
     {
      $lang = $this->getLang();
        //$data = Maincategory::where('lang', $lang)->get();
        $data = Maincategory::get();
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data' => $data], Response::HTTP_OK);
     }
     // end list all 

     // start list all
     public function home_main_categories_nested()
     {
        $lang = $this->getLang();
        $items = Maincategory::get();
       // $items = Maincategory::where('lang', $lang)->get();
        $data = SingleMaincategoryNestedApiResource::collection($items);
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data' => $data,
        ], Response::HTTP_OK);
     }
     // end list all 

     // start list all
     public function home_categories_parts($id)
     {
      // return 'bbb';
        $lang  = $this->getLang();
       // $cat    = ProductCategory::where('lang', $lang)->where('id', $id)->first();
      //  $parts  = PartCategory::where('lang', $lang)->where('category_id', $id)->get();
        $cat    = ProductCategory::where('id', $id)->first();
        $parts  = PartCategory::where('category_id', $id)->get();
        foreach ($parts as $value) 
        {
          $one = explode('/', $value->category_name);
         // return strstr($one[2], '-', true);
         
         $value['width']  = $one[0]; //strstr($one[2], '-', true);
         $value['height'] = $one[1];
         $value['size']   = $one[2];
         
        }
        $data = $parts;
        $total = count($parts);
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data' => $data,
          'total' => $total,
        ], Response::HTTP_OK);
     }
     // end list all 

     public function search_home_categories_parts(SelectCategoryApiRequest $request)
     {
        $lang  = $this->getLang();
       // $id = $request->category_id;
        $search_index = $request->attribute;
       
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
          return $this->attribute_only_search($search_index, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 2
        if ($manufacturers != '' && $origins == '' && $start_price == '') {
        $manufacturers   = json_decode($request->manufacturers);
          return $this->attribute_manufacturers_search($search_index, $manufacturers, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 3
        if ($manufacturers == '' && $origins != '' && $start_price == '') {
          $origins         = json_decode($request->origins);
          return $this->attribute_origins_search($search_index, $origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT);
        }

        // case 4
        if ($manufacturers == '' && $origins == '' && $start_price != '') {
          $origins         = json_decode($request->origins);
          return $this->attribute_price_search($search_index, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 4
        if ($manufacturers != '' && $origins != '' && $start_price == '') {
          $origins         = json_decode($request->origins);
          $manufacturers   = json_decode($request->manufacturers);
          return $this->attribute_manufacturers_origins_search($search_index, $origins, $manufacturers, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 4
        if ($manufacturers != '' && $origins == '' && $start_price != '') {
          $manufacturers         = json_decode($request->manufacturers);
          return $this->attribute_manufacturers_price_search($search_index, $manufacturers, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 4
        if ($manufacturers == '' && $origins != '' && $start_price != '') {
          $origins         = json_decode($request->origins);
          return $this->attribute_origins_price_search($search_index, $origins, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 4
        if ($manufacturers != '' && $origins != '' && $start_price != '') {
          $origins         = json_decode($request->origins);
          $manufacturers   = json_decode($request->manufacturers);
          return $this->all_search($search_index, $origins, $manufacturers, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }
    }

     public function attribute_only_search($search_index, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT)
    {
       if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {

            $products = Product::where('approved', 1)
                          ->where('producttype_id', 1)
                          ->whereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();
                                                        
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
                ], 200);         
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {

            $products = Product::where('approved', 1)
                          ->where('producttype_id', 1)
                          ->whereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();
                                                        
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
                ], 200);  
              
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
          $products = Product::where('approved', 1)->where('producttype_id', 1)
                          ->where('producttype_id', 1)
                         ->whereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();
                                                        
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
                ], 200);  
                    
        } // end guest case
    }

     public function attribute_manufacturers_search($search_index, $manufacturers, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT)
    {
       if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {

            $products = Product::where('approved', 1)
                          ->where('producttype_id', 1)
                          ->whereIn('manufacturer_id', $manufacturers)
                          ->whereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();
                                                        
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
                ], 200);         
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {

            $products = Product::where('approved', 1)
                          ->where('producttype_id', 1)
                          ->whereIn('manufacturer_id', $manufacturers)
                          ->whereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();
                                                        
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
                ], 200);  
              
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
          $products = Product::where('approved', 1)->where('producttype_id', 1)
                          ->where('producttype_id', 1)
                          ->whereIn('manufacturer_id', $manufacturers)
                         ->whereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();
                                                        
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
                ], 200);  
                    
        } // end guest case
    }

     public function attribute_origins_search($search_index, $origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
    {
       if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {

            $products = Product::where('approved', 1)
                          ->where('producttype_id', 1)
                          ->whereIn('prodcountry_id', $origins)
                          ->whereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();
                                                        
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
                ], 200);         
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {

            $products = Product::where('approved', 1)
                          ->where('producttype_id', 1)
                          ->whereIn('prodcountry_id', $origins)
                          ->whereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();
                                                        
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
                ], 200);  
              
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
          $products = Product::where('approved', 1)->where('producttype_id', 1)
                          ->where('producttype_id', 1)
                          ->whereIn('prodcountry_id', $origins)
                         ->whereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();
                                                        
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
                ], 200);  
                    
        } // end guest case
    }

     public function attribute_price_search($search_index, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT)
    {
       if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {

            $products = Product::where('approved', 1)
                          ->where('producttype_id', 1)
                          ->where('price', '>=', $start_price)
                          ->where('price', '<=', $end_price)
                          ->whereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();
                                                        
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
                ], 200);         
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {

            $products = Product::where('approved', 1)
                          ->where('producttype_id', 1)
                          ->where('price', '>=', $start_price)
                          ->where('price', '<=', $end_price)
                          ->whereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();
                                                        
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
                ], 200);  
              
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
          $products = Product::where('approved', 1)->where('producttype_id', 1)
                          ->where('producttype_id', 1)
                          ->where('price', '>=', $start_price)
                          ->where('price', '<=', $end_price)
                         ->whereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();
                                                        
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
                ], 200);  
                    
        } // end guest case
    }

     public function attribute_manufacturers_origins_search($search_index, $origins, $manufacturers, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT)
    {
       if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {

            $products = Product::where('approved', 1)
                          ->where('producttype_id', 1)
                          ->whereIn('prodcountry_id', $origins)
                          ->whereIn('manufacturer_id', $manufacturers)
                          ->whereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();
                                                        
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
                ], 200);         
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {

            $products = Product::where('approved', 1)
                          ->where('producttype_id', 1)
                          ->whereIn('prodcountry_id', $origins)
                          ->whereIn('manufacturer_id', $manufacturers)
                          ->whereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();
                                                        
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
                ], 200);  
              
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
          $products = Product::where('approved', 1)->where('producttype_id', 1)
                          ->where('producttype_id', 1)
                          ->whereIn('prodcountry_id', $origins)
                          ->whereIn('manufacturer_id', $manufacturers)
                         ->whereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();
                                                        
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
                ], 200);  
                    
        } // end guest case
    }

    public function attribute_manufacturers_price_search($search_index, $manufacturers, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT)
    {
       if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {

            $products = Product::where('approved', 1)
                          ->where('producttype_id', 1)
                          ->whereIn('manufacturer_id', $manufacturers)
                          ->where('price', '>=', $start_price)
                          ->where('price', '<=', $end_price)
                          ->whereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();
                                                        
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
                ], 200);         
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {

            $products = Product::where('approved', 1)
                          ->where('producttype_id', 1)
                          ->whereIn('manufacturer_id', $manufacturers)
                          ->where('price', '>=', $start_price)
                          ->where('price', '<=', $end_price)
                          ->whereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();
                                                        
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
                ], 200);  
              
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
          $products = Product::where('approved', 1)->where('producttype_id', 1)
                          ->where('producttype_id', 1)
                          ->whereIn('manufacturer_id', $manufacturers)
                          ->where('price', '>=', $start_price)
                          ->where('price', '<=', $end_price)
                         ->whereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();
                                                        
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
                ], 200);  
                    
        } // end guest case
    }

    public function attribute_origins_price_search($search_index, $origins, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT)
    {
       if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {

            $products = Product::where('approved', 1)
                          ->where('producttype_id', 1)
                          ->whereIn('prodcountry_id', $origins)
                          ->where('price', '>=', $start_price)
                          ->where('price', '<=', $end_price)
                          ->whereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();
                                                        
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
                ], 200);         
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {

            $products = Product::where('approved', 1)
                          ->where('producttype_id', 1)
                          ->whereIn('prodcountry_id', $origins)
                          ->where('price', '>=', $start_price)
                          ->where('price', '<=', $end_price)
                          ->whereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();
                                                        
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
                ], 200);  
              
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
          $products = Product::where('approved', 1)->where('producttype_id', 1)
                          ->where('producttype_id', 1)
                          ->whereIn('prodcountry_id', $origins)
                          ->where('price', '>=', $start_price)
                          ->where('price', '<=', $end_price)
                         ->whereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();
                                                        
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
                ], 200);  
                    
        } // end guest case
    }

    public function all_search($search_index, $origins, $manufacturers, $start_price, $end_price, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT)
    {
       if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {

            $products = Product::where('approved', 1)
                          ->where('producttype_id', 1)
                          ->whereIn('prodcountry_id', $origins)
                          ->whereIn('manufacturer_id', $manufacturers)
                          ->where('price', '>=', $start_price)
                          ->where('price', '<=', $end_price)
                          ->whereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();
                                                        
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
                ], 200);         
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {

            $products = Product::where('approved', 1)
                          ->where('producttype_id', 1)
                          ->whereIn('prodcountry_id', $origins)
                          ->whereIn('manufacturer_id', $manufacturers)
                          ->where('price', '>=', $start_price)
                          ->where('price', '<=', $end_price)
                          ->whereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();
                                                        
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
                ], 200);  
              
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
          $products = Product::where('approved', 1)->where('producttype_id', 1)
                          ->where('producttype_id', 1)
                          ->whereIn('prodcountry_id', $origins)
                          ->whereIn('manufacturer_id', $manufacturers)
                          ->where('price', '>=', $start_price)
                          ->where('price', '<=', $end_price)
                         ->whereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         ->orderBy($ordered_by, $sort_type)->get();
                                                        
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
                ], 200);  
                    
        } // end guest case
    }

     public function search_home_categories_parts_mlc(SelectCategoryApiRequest $request)
     {
        $lang  = $this->getLang();
       // $id = $request->category_id;
        $search_index = $request->attribute;
        $default_count = \Config::get('constants.pagination.items_per_page');
        $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
                
       $request->page == '' ? $page = 1 : $page = $request->page;
       $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
       $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
       $request->column_name == '' ? $column_name = '' : $column_name = $request->column_name;

      //  $cat    = ProductCategory::where('lang', $lang)->where('id', $id)->first();
      //  $parts  = PartCategory::where('lang', $lang)->where('category_id', $id)->get();

        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {

            $products = Product::where('approved', 1)
                          ->where('producttype_id', 1)
                          ->whereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         //->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                         ->orderBy($ordered_by, $sort_type)->get();
                                                        
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
                ], 200);         
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {

            $products = Product::where('approved', 1)
                         // ->where('category_id', $id)
                          ->whereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         //->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                         ->orderBy($ordered_by, $sort_type)->get();
                                                        
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
                ], 200);  
              
          } // end vendor case
        }  // end case logged in 
        else{ // guest case

          $products = Product::where('approved', 1)->where('producttype_id', 1)
                         // ->where('category_id', $id)
                          ->whereHas('allcategory', function ($q) use ($search_index) {
                            $q->where('name', 'like', "%{$search_index}%");
                          })
                         //->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                         ->orderBy($ordered_by, $sort_type)->get();
                                                        
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
                ], 200);  
                    
        } // end guest case
     }
}
