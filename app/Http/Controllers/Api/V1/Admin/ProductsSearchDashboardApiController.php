<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Gate;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\SearchApisRequest;
use App\Models\AddVendor;
use App\Models\Vendorstaff;
use Auth;
use Illuminate\Support\Facades\Schema;
use Validator;
use App\Http\Resources\Api\V1\Admin\Dashboard\Products\SearchProductsApiResource;
use App\Models\User;
use App\Models\Allcategory;

class ProductsSearchDashboardApiController extends Controller
{
	public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }

     /* search dynamic columns */
     public function search_dynamic_columns(SearchApisRequest $request)
     {
      // $PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      $request->column_name == '' ? $column_name = '' : $column_name = $request->column_name;
      // return $column_name;
      if ($ordered_by != '') {
        if (!Schema::hasColumn('products', $ordered_by)) {
          return response()->json(['message'  =>'order column not found',], 400);
        }
        if ($ordered_by == 'tags' || $ordered_by == 'categories') {
          $ordered_by = 'id';
        }
      }
       
        $search_index = $request->search_index;
        if ($column_name == 'name') {
          return $this->search_with_name($search_index, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }
        if ($column_name == 'quantity') {
          return $this->search_with_quantity($search_index, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }
        if ($column_name == 'price') {
          return $this->search_with_price($search_index, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }
        if ($column_name == 'serial_number') {
          return $this->search_with_serial_number($search_index, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }
     /*   if ($column_name == 'part_category_id') {
          return $this->search_with_part_category($search_index, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        } */ 
        if ($column_name == 'store_id') {
          return $this->search_with_store($search_index, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }
        // exclude this filter when logged in as vendor
        if ($column_name == 'vendor_id') {
          return $this->search_with_vendor($search_index, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }
        if ($column_name == 'car_made_id') {
          return $this->search_with_car_made($search_index, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }
        if ($column_name == 'car_model_id') {
          return $this->search_with_car_model($search_index, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }
        if ($column_name == 'year_id') {
          return $this->search_with_car_year($search_index, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }
        if ($column_name == 'category_id') {
          return $this->search_with_product_category($search_index, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }
        if ($column_name == 'tags') {
          return $this->search_with_tags($search_index, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        } 
        else{
          return $this->hole_search_products($search_index, $page, $ordered_by, $sort_type, $PAGINATION_COUNT);
        }
     }
     /* search dynamic columns */

      // start hole search products 
     public function hole_search_products($search_index, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
     {
      // $PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      // $default_count = \Config::get('constants.pagination.items_per_page');
      // $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
      $user = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();
        // case logged in user role is Admin 
        if (in_array('Admin', $user_roles)) {
           $products = Product::where(function ($q) use ($search_index) {
                $q->where('name', 'like', "%{$search_index}%")
                ->orWhere('name_en', 'like', $search_index)
                ->orWhere('price', 'like', $search_index);
            })
              ->orWhereHas('car_made', function($q) use ($search_index){
                                $q->where('car_made', 'like', "%{$search_index}%")
                                ->orWhere('name_en', 'like', "%{$search_index}%");
                })
                ->orWhereHas('car_model', function($q) use ($search_index){
                                $q->where('carmodel', 'like', "%{$search_index}%")
                                ->orWhere('name_en', 'like', "%{$search_index}%");
                })
                ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                  })
                  ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                  })
                 ->orWhereHas('allcategory', function($q) use ($search_index){
                                $q->where('name', 'like', "%{$search_index}%")
                                ->orWhere('name_en', 'like', "%{$search_index}%");
                })
                 ->orWhereHas('store', function($q) use ($search_index){
                                $q->where('name', 'like', "%{$search_index}%");
                })
                 ->orWhereHas('vendor', function($q) use ($search_index){
                                $q->where('name', 'like', "%{$search_index}%");
                })
                 ->orWhereHas('transmission', function($q) use ($search_index){
                                $q->where('transmission_name', 'like', "%{$search_index}%")
                                ->orWhere('name_en', 'like', "%{$search_index}%");
                })
                 ->orWhereHas('origin_country', function($q) use ($search_index){
                                $q->where('country_name', 'like', "%{$search_index}%")
                                ->orWhere('name_en', 'like', "%{$search_index}%");
                })
                 ->orWhereHas('manufacturer', function($q) use ($search_index){
                                $q->where('manufacturer_name', 'like', "%{$search_index}%")
                                ->orWhere('name_en', 'like', "%{$search_index}%");
                })
                 ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();

        $total = Product::where(function ($q) use ($search_index) {
                $q->where('name', 'like', "%{$search_index}%")
                ->orWhere('name_en', 'like', $search_index)
                ->orWhere('price', 'like', $search_index);
            })
              ->orWhereHas('car_made', function($q) use ($search_index){
                                $q->where('car_made', 'like', "%{$search_index}%")
                                ->orWhere('name_en', 'like', "%{$search_index}%");
                })
                ->orWhereHas('car_model', function($q) use ($search_index){
                                $q->where('carmodel', 'like', "%{$search_index}%")
                                ->orWhere('name_en', 'like', "%{$search_index}%");
                })
                ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                  })
                  ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                  })
                  ->orWhereHas('store', function($q) use ($search_index){
                                $q->where('name', 'like', "%{$search_index}%");
                })
                 ->orWhereHas('vendor', function($q) use ($search_index){
                                $q->where('name', 'like', "%{$search_index}%");
                })
                 ->orWhereHas('transmission', function($q) use ($search_index){
                                $q->where('transmission_name', 'like', "%{$search_index}%")
                                ->orWhere('name_en', 'like', "%{$search_index}%");
                })
                 ->orWhereHas('origin_country', function($q) use ($search_index){
                                $q->where('country_name', 'like', "%{$search_index}%")
                                ->orWhere('name_en', 'like', "%{$search_index}%");
                })
                 ->orWhereHas('manufacturer', function($q) use ($search_index){
                                $q->where('manufacturer_name', 'like', "%{$search_index}%")
                                ->orWhere('name_en', 'like', "%{$search_index}%");
                })
                 ->orWhereHas('allcategory', function($q) use ($search_index){
                                $q->where('name', 'like', "%{$search_index}%")
                                ->orWhere('name_en', 'like', "%{$search_index}%");
                })->count();
        $data = SearchProductsApiResource::collection($products);

        return response()->json([
          'status_code' => 200,
          'message' => 'success',
          'data'  => $data,
          'total' => $total,
        ], 200);
        } // end admin case
         // case logged in user role is Vendor 
        elseif (in_array('Vendor', $user_roles)) {
           $vendor    = AddVendor::where('userid_id', Auth::user()->id)->first();
           $vendor_id = $vendor->id;
            $products = Product::where('name', 'like', "%{$search_index}%")
                               ->orWhere('name_en', 'like', $search_index)
                               ->orWhere('price', 'like', $search_index)
                               ->orWhereHas('car_made', function($q) use ($search_index){
                                                  $q->where('car_made', 'like', "%{$search_index}%")
                                                  ->orWhere('name_en', 'like', "%{$search_index}%");
                                  })
                                  ->orWhereHas('car_model', function($q) use ($search_index){
                                                  $q->where('carmodel', 'like', "%{$search_index}%")
                                                  ->orWhere('name_en', 'like', "%{$search_index}%");
                                  })
                                  ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                                              $q->where('year', 'like', "%{$search_index}%");
                                    })
                                    ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                                              $q->where('year', 'like', "%{$search_index}%");
                                    })
                                    ->orWhereHas('transmission', function($q) use ($search_index){
					                                $q->where('transmission_name', 'like', "%{$search_index}%")
					                                ->orWhere('name_en', 'like', "%{$search_index}%");
					                })
					                 ->orWhereHas('origin_country', function($q) use ($search_index){
					                                $q->where('country_name', 'like', "%{$search_index}%")
					                                ->orWhere('name_en', 'like', "%{$search_index}%");
					                })
					                 ->orWhereHas('manufacturer', function($q) use ($search_index){
					                                $q->where('manufacturer_name', 'like', "%{$search_index}%")
					                                ->orWhere('name_en', 'like', "%{$search_index}%");
					                })
                                   ->orWhereHas('allcategory', function($q) use ($search_index){
                                                  $q->where('name', 'like', "%{$search_index}%")
                                                  ->orWhere('name_en', 'like', "%{$search_index}%");
                                  })
                  ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();

        $products = $products->where('vendor_id', $vendor_id);
        $total    = Product::where('name', 'like', "%{$search_index}%")
                               ->orWhere('name_en', 'like', $search_index)
                               ->orWhere('price', 'like', $search_index)
                               ->orWhereHas('car_made', function($q) use ($search_index){
                                                  $q->where('car_made', 'like', "%{$search_index}%")
                                                  ->orWhere('name_en', 'like', "%{$search_index}%");
                                  })
                                  ->orWhereHas('car_model', function($q) use ($search_index){
                                                  $q->where('carmodel', 'like', "%{$search_index}%")
                                                  ->orWhere('name_en', 'like', "%{$search_index}%");
                                  })
                                  ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                                              $q->where('year', 'like', "%{$search_index}%");
                                    })
                                    ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                                              $q->where('year', 'like', "%{$search_index}%");
                                    })
                                    ->orWhereHas('transmission', function($q) use ($search_index){
					                                $q->where('transmission_name', 'like', "%{$search_index}%")
					                                ->orWhere('name_en', 'like', "%{$search_index}%");
					                })
					                 ->orWhereHas('origin_country', function($q) use ($search_index){
					                                $q->where('country_name', 'like', "%{$search_index}%")
					                                ->orWhere('name_en', 'like', "%{$search_index}%");
					                })
					                 ->orWhereHas('manufacturer', function($q) use ($search_index){
					                                $q->where('manufacturer_name', 'like', "%{$search_index}%")
					                                ->orWhere('name_en', 'like', "%{$search_index}%");
					                })
					                                   ->orWhereHas('allcategory', function($q) use ($search_index){
					                                                  $q->where('name', 'like', "%{$search_index}%")
					                                                  ->orWhere('name_en', 'like', "%{$search_index}%");
                                  })->count();

        $data = SearchProductsApiResource::collection($products);
        return response()->json([
          'status_code' => 200,
          'message' => 'success',
          'data'  => $data,
          'total' => $total,
        ], 200);
        } // end case vendor
         elseif (in_array('Manager', $user_roles) ||  in_array('Staff', $user_roles) ) 
      {
        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
        $staff_stores = $exist_staff->stores->pluck('id')->toArray();
        $products = Product::where('name', 'like', "%{$search_index}%")
                               ->orWhere('name_en', 'like', $search_index)
                               ->orWhere('price', 'like', $search_index)
                               ->orWhereHas('car_made', function($q) use ($search_index){
                                                  $q->where('car_made', 'like', "%{$search_index}%")
                                                  ->orWhere('name_en', 'like', "%{$search_index}%");
                                  })
                                  ->orWhereHas('car_model', function($q) use ($search_index){
                                                  $q->where('carmodel', 'like', "%{$search_index}%")
                                                  ->orWhere('name_en', 'like', "%{$search_index}%");
                                  })
                                  ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                                              $q->where('year', 'like', "%{$search_index}%");
                                    })
                                    ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                                              $q->where('year', 'like', "%{$search_index}%");
                                    })
                                    ->orWhereHas('transmission', function($q) use ($search_index){
					                                $q->where('transmission_name', 'like', "%{$search_index}%")
					                                ->orWhere('name_en', 'like', "%{$search_index}%");
					                })
					                 ->orWhereHas('origin_country', function($q) use ($search_index){
					                                $q->where('country_name', 'like', "%{$search_index}%")
					                                ->orWhere('name_en', 'like', "%{$search_index}%");
					                })
					                 ->orWhereHas('manufacturer', function($q) use ($search_index){
					                                $q->where('manufacturer_name', 'like', "%{$search_index}%")
					                                ->orWhere('name_en', 'like', "%{$search_index}%");
					                })
                                   ->orWhereHas('allcategory', function($q) use ($search_index){
                                                  $q->where('name', 'like', "%{$search_index}%")
                                                  ->orWhere('name_en', 'like', "%{$search_index}%");
                                  })
                  ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();

        $products = $products->where('vendor_id', $vendor_id);
        $total    = Product::where('name', 'like', "%{$search_index}%")
                               ->orWhere('name_en', 'like', $search_index)
                               ->orWhere('price', 'like', $search_index)
                               ->orWhereHas('car_made', function($q) use ($search_index){
                                                  $q->where('car_made', 'like', "%{$search_index}%")
                                                  ->orWhere('name_en', 'like', "%{$search_index}%");
                                  })
                                  ->orWhereHas('car_model', function($q) use ($search_index){
                                                  $q->where('carmodel', 'like', "%{$search_index}%")
                                                  ->orWhere('name_en', 'like', "%{$search_index}%");
                                  })
                                  ->orWhereHas('year_from_func', function ($q) use ($search_index) {
                                              $q->where('year', 'like', "%{$search_index}%");
                                    })
                                    ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                                              $q->where('year', 'like', "%{$search_index}%");
                                    })
                                    ->orWhereHas('transmission', function($q) use ($search_index){
					                                $q->where('transmission_name', 'like', "%{$search_index}%")
					                                ->orWhere('name_en', 'like', "%{$search_index}%");
					                })
					                 ->orWhereHas('origin_country', function($q) use ($search_index){
					                                $q->where('country_name', 'like', "%{$search_index}%")
					                                ->orWhere('name_en', 'like', "%{$search_index}%");
					                })
					                 ->orWhereHas('manufacturer', function($q) use ($search_index){
					                                $q->where('manufacturer_name', 'like', "%{$search_index}%")
					                                ->orWhere('name_en', 'like', "%{$search_index}%");
					                })
                                   ->orWhereHas('allcategory', function($q) use ($search_index){
                                                  $q->where('name', 'like', "%{$search_index}%")
                                                  ->orWhere('name_en', 'like', "%{$search_index}%");
                                  })->count();

        $data = SearchProductsApiResource::collection($products);
        return response()->json([
          'status_code' => 200,
          'message' => 'success',
          'data'  => $data,
          'total' => $total,
        ], 200);
      }
        else{
          return response()->json([
                  'status_code' => 401,
                  'message'  => 'un authorized access page due to permissions',
                 ], 401);
        }
     }
    // end hole search products

     // start search products with name
     public function search_with_name($search_index, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
     {
      //$PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
     // $default_count = \Config::get('constants.pagination.items_per_page');
     // $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;

      $user = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();

      // case logged in user role is Admin 
      if (in_array('Admin', $user_roles)) {
            $products = Product::where(function ($q) use ($search_index) {
                    $q->where('name', 'like', "%{$search_index}%")->orWhere('name_en', 'like', $search_index);
                })->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();
            $total = Product::where(function ($q) use ($search_index) {
                    $q->where('name', 'like', "%{$search_index}%")->orWhere('name_en', 'like', "%{$search_index}%");
                })->count();
            $data = SearchProductsApiResource::collection($products);
            return response()->json([
              'status_code' => 200,
              'message' => 'success',
              'data'  => $data,
              'total' => $total,
            ], 200);
      } // end admin case
       // case logged in user role is Vendor 
      elseif (in_array('Vendor', $user_roles)) {
         $vendor    = AddVendor::where('userid_id', Auth::user()->id)->first();
         $vendor_id = $vendor->id;
         $products = Product::where('vendor_id', $vendor_id)->where(function ($q) use ($search_index) {
                    $q->where('name', 'like', "%{$search_index}%")->orWhere('name_en', 'like', $search_index);
                })->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();
            /*$total = Product::where('vendor_id', $vendor_id)->where(function ($q) use ($search_index) {
                    $q->where('name', 'like', "%{$search_index}%");
                })->count();*/
              $products = $products->where('vendor_id', $vendor_id);
              $total    = count($products);

            $data = SearchProductsApiResource::collection($products);
            return response()->json([
              'status_code' => 200,
              'message' => 'success',
              'data'  => $data,
              'total' => $total,
            ], 200);
      } // end case vendor
      elseif (in_array('Manager', $user_roles) ||  in_array('Staff', $user_roles) ) 
      {
        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
        $staff_stores = $exist_staff->stores->pluck('id')->toArray();
        $products = Product::where('vendor_id', $vendor_id)->where(function ($q) use ($search_index) {
                    $q->where('name', 'like', "%{$search_index}%")->orWhere('name_en', 'like', $search_index);
                })->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();
            /*$total = Product::where('vendor_id', $vendor_id)->where(function ($q) use ($search_index) {
                    $q->where('name', 'like', "%{$search_index}%");
                })->count();*/
              $products = $products->where('vendor_id', $vendor_id);
              $total    = count($products);

            $data = SearchProductsApiResource::collection($products);
            return response()->json([
              'status_code' => 200,
              'message' => 'success',
              'data'  => $data,
              'total' => $total,
            ], 200);
        
      }
      else{
        return response()->json([
                'message'  => 'un authorized access page due to permissions',
               ], 401);
      }
     }
    // end search products with name

     // start search with quantity 
     public function search_with_quantity($search_index, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
     {
      //$PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
     // $default_count = \Config::get('constants.pagination.items_per_page');
     // $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
      $user = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();

      // case logged in user role is Admin 
      if (in_array('Admin', $user_roles)) {
            $products = Product::where(function ($q) use ($search_index) {
                    $q->where('quantity', '<=', $search_index);
                })->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();
            $total = Product::where(function ($q) use ($search_index) {
                    $q->where('quantity', '<=', $search_index);
                })->count();
            $data = SearchProductsApiResource::collection($products);
            return response()->json([
              'status_code' => 200,
              'message' => 'success',
              'data'  => $data,
              'total' => $total,
            ], 200);
      } // end admin case
       // case logged in user role is Vendor 
      elseif (in_array('Vendor', $user_roles)) {
         $vendor    = AddVendor::where('userid_id', Auth::user()->id)->first();
         $vendor_id = $vendor->id;
         $products = Product::where('vendor_id', $vendor_id)->where(function ($q) use ($search_index) {
                    $q->where('quantity', '<=', $search_index);
                })->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();
          /*$total = Product::where('vendor_id', $vendor_id)->where(function ($q) use ($search_index) {
                    $q->where('quantity', '<=', $search_index);
                })->count();*/
              $products = $products->where('vendor_id', $vendor_id);
              $total    = count($products);

          $data = SearchProductsApiResource::collection($products);
            return response()->json([
              'status_code' => 200,
              'message' => 'success',
              'data'  => $data,
              'total' => $total,
            ], 200);
      } // end case vendor
       elseif (in_array('Manager', $user_roles) ||  in_array('Staff', $user_roles) ) 
      {
        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
        $staff_stores = $exist_staff->stores->pluck('id')->toArray();
         $products = Product::where('vendor_id', $vendor_id)->where(function ($q) use ($search_index) {
                    $q->where('quantity', '<=', $search_index);
                })->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();
          /*$total = Product::where('vendor_id', $vendor_id)->where(function ($q) use ($search_index) {
                    $q->where('quantity', '<=', $search_index);
                })->count();*/
              $products = $products->where('vendor_id', $vendor_id);
              $total    = count($products);

          $data = SearchProductsApiResource::collection($products);
            return response()->json([
              'status_code' => 200,
              'message' => 'success',
              'data'  => $data,
              'total' => $total,
            ], 200);
      }
      else{
        return response()->json([
                'message'  => 'un authorized access page due to permissions',
               ], 401);
      }
     }
     // end search with quantity

    // start search products with price
     public function search_with_price($search_index, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
     {
      //$PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      //$default_count = \Config::get('constants.pagination.items_per_page');
      //$request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
      $user = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();

        // case logged in user role is Admin 
        if (in_array('Admin', $user_roles)) {
              $products = Product::where('price', '<=', $search_index)->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)->orderBy($ordered_by, $sort_type)->get();
            $total = Product::where('price', '<=', $search_index)->count();
            $data = SearchProductsApiResource::collection($products);
            return response()->json([
              'status_code' => 200,
              'message' => 'success',
              'data'  => $data,
              'total' => $total,
            ], 200);
        } // end admin case
         // case logged in user role is Vendor 
        elseif (in_array('Vendor', $user_roles)) {
           $vendor    = AddVendor::where('userid_id', Auth::user()->id)->first();
           $vendor_id = $vendor->id;
               $products = Product::where('vendor_id', $vendor_id)->where('price', '<=', $search_index)->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)->orderBy($ordered_by, $sort_type)->get();
            /*$total = Product::where('vendor_id', $vendor_id)->where('price', '<=', $search_index)
                            ->count();*/
            $products = $products->where('vendor_id', $vendor_id);
            $total    = count($products);
            $data = SearchProductsApiResource::collection($products);
            return response()->json([
              'status_code' => 200,
              'message' => 'success',
              'data'  => $data,
              'total' => $total,
            ], 200);
        } // end case vendor
         elseif (in_array('Manager', $user_roles) ||  in_array('Staff', $user_roles) ) 
      {
        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
        $staff_stores = $exist_staff->stores->pluck('id')->toArray();
         $products = Product::where('vendor_id', $vendor_id)->where('price', '<=', $search_index)->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)->orderBy($ordered_by, $sort_type)->get();
            /*$total = Product::where('vendor_id', $vendor_id)->where('price', '<=', $search_index)
                            ->count();*/
            $products = $products->where('vendor_id', $vendor_id);
            $total    = count($products);
            $data = SearchProductsApiResource::collection($products);
            return response()->json([
              'status_code' => 200,
              'message' => 'success',
              'data'  => $data,
              'total' => $total,
            ], 200);
      }
        else{
          return response()->json([
                  'message'  => 'un authorized access page due to permissions',
                 ], 401);
        }
     }
    // end search products with price

     // start search products with serial number
     public function search_with_serial_number($search_index, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
     {
      //$PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      //$default_count = \Config::get('constants.pagination.items_per_page');
      //$request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
      $user = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();
        // case logged in user role is Admin 
        if (in_array('Admin', $user_roles)) {
              $products = Product::where(function ($q) use ($search_index) {
                    $q->where('serial_number', 'like', "%{$search_index}%");
                })->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();
            $total = Product::where(function ($q) use ($search_index) {
                    $q->where('serial_number', 'like', "%{$search_index}%");
                })->count();
            $data = SearchProductsApiResource::collection($products);
            return response()->json([
              'status_code' => 200,
              'message' => 'success',
              'data'  => $data,
              'total' => $total,
            ], 200);
        } // end admin case
         // case logged in user role is Vendor 
        elseif (in_array('Vendor', $user_roles)) {
           $vendor    = AddVendor::where('userid_id', Auth::user()->id)->first();
           $vendor_id = $vendor->id;
               $products = Product::where('vendor_id', $vendor_id)->where(function ($q) use ($search_index) {
                    $q->where('serial_number', 'like', "%{$search_index}%");
                })->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();
               /*$total = Product::where('vendor_id', $vendor_id)->where(function ($q) use ($search_index) {
                    $q->where('serial_number', 'like', "%{$search_index}%");
                })->count();*/
              $products = $products->where('vendor_id', $vendor_id);
              $total    = count($products);
              
               $data = SearchProductsApiResource::collection($products);
            return response()->json([
              'status_code' => 200,
              'message' => 'success',
              'data'  => $data,
              'total' => $total,
            ], 200);
        } // end case vendor
         elseif (in_array('Manager', $user_roles) ||  in_array('Staff', $user_roles) ) 
      {
        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
        $staff_stores = $exist_staff->stores->pluck('id')->toArray();
        $products = Product::where('vendor_id', $vendor_id)->where(function ($q) use ($search_index) {
                    $q->where('serial_number', 'like', "%{$search_index}%");
                })->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();
               /*$total = Product::where('vendor_id', $vendor_id)->where(function ($q) use ($search_index) {
                    $q->where('serial_number', 'like', "%{$search_index}%");
                })->count();*/
              $products = $products->where('vendor_id', $vendor_id);
              $total    = count($products);
              
               $data = SearchProductsApiResource::collection($products);
            return response()->json([
              'status_code' => 200,
              'message' => 'success',
              'data'  => $data,
              'total' => $total,
            ], 200);
      }
        else{
          return response()->json([
                  'message'  => 'un authorized access page due to permissions',
                 ], 401);
        }
     }
    // end search products with serial number

     // start search products with car made 
     public function search_with_car_made($search_index, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
     {
      //$PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      //$default_count = \Config::get('constants.pagination.items_per_page');
      //$request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
      $user = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();
        // case logged in user role is Admin 
        if (in_array('Admin', $user_roles)) {
              $products = Product::whereHas('car_made', function($q) use ($search_index){
                                    $q->where('car_made', 'like', "%{$search_index}%")
                                    ->orWhere('name_en', 'like', "%{$search_index}%");
                    })->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                        ->orderBy($ordered_by, $sort_type)->get();
            $total = Product::whereHas('car_made', function($q) use ($search_index){
                                    $q->where('car_made', 'like', "%{$search_index}%")
                                    ->orWhere('name_en', 'like', "%{$search_index}%");
                    })->count();
            $data = SearchProductsApiResource::collection($products);
            return response()->json([
              'data'  => $data,
              'total' => $total,
              'status_code' => 200,
              'message' => 'success',
            ], 200);
        } // end admin case
         // case logged in user role is Vendor 
        elseif (in_array('Vendor', $user_roles)) {
           $vendor    = AddVendor::where('userid_id', Auth::user()->id)->first();
           $vendor_id = $vendor->id;
           $products = Product::whereHas('car_made', function($q) use ($search_index){
                                $q->where('car_made', 'like', "%{$search_index}%")
                                ->orWhere('name_en', 'like', "%{$search_index}%");
                })->where('vendor_id', $vendor_id)->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();
         /* $total = Product::whereHas('car_made', function($q) use ($search_index){
                                  $q->where('car_made', 'like', "%{$search_index}%");
                  })->where('vendor_id', $vendor_id)->count();*/
              $products = $products->where('vendor_id', $vendor_id);
              $total    = count($products);
          $data = SearchProductsApiResource::collection($products);
          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);
        } // end case vendor
         elseif (in_array('Manager', $user_roles) ||  in_array('Staff', $user_roles) ) 
      {
        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
        $staff_stores = $exist_staff->stores->pluck('id')->toArray();
         $products = Product::whereHas('car_made', function($q) use ($search_index){
                                $q->where('car_made', 'like', "%{$search_index}%")
                                ->orWhere('name_en', 'like', "%{$search_index}%");
                })->where('vendor_id', $vendor_id)->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();
         /* $total = Product::whereHas('car_made', function($q) use ($search_index){
                                  $q->where('car_made', 'like', "%{$search_index}%");
                  })->where('vendor_id', $vendor_id)->count();*/
              $products = $products->where('vendor_id', $vendor_id);
              $total    = count($products);
          $data = SearchProductsApiResource::collection($products);
          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);
      }
        else{
          return response()->json([
                  'status_code' => 401,
                  'message'  => 'un authorized access page due to permissions',
                 ], 401);
        }
     }
    // end search products with car made 

// start search products with vendor 

     public function search_with_vendor($search_index, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
     {
      //$PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
     // $default_count = \Config::get('constants.pagination.items_per_page');
     // $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
      $user = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();
        // case logged in user role is Admin 
        if (in_array('Admin', $user_roles)) {
          $products = Product::whereHas('vendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%")
                                  ->orWhere('email', 'like', "%{$search_index}%");
                })->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();
          $total = Product::whereHas('vendor', function($q) use ($search_index){
                                  $q->where('vendor_name', 'like', "%{$search_index}%")
                                    ->orWhere('email', 'like', "%{$search_index}%");
                  })->count();
          $data = SearchProductsApiResource::collection($products);

          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);
        } // end admin case
         // case logged in user role is Vendor 
        elseif (in_array('Vendor', $user_roles)) {
           $vendor    = AddVendor::where('userid_id', Auth::user()->id)->first();
           $vendor_id = $vendor->id;
           $products = Product::whereHas('vendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%")
                                  ->orWhere('email', 'like', "%{$search_index}%");
                })->where('vendor_id', $vendor_id)
                  ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();
            /*$total = Product::whereHas('vendor', function($q) use ($search_index){
                                    $q->where('vendor_name', 'like', "%{$search_index}%")
                                      ->orWhere('email', 'like', "%{$search_index}%");
                    })->where('vendor_id', $vendor_id)->count();*/
              $products = $products->where('vendor_id', $vendor_id);
              $total    = count($products);
            $data = SearchProductsApiResource::collection($products);
            return response()->json([
              'status_code' => 200,
              'message' => 'success',
              'data'  => $data,
              'total' => $total,
            ], 200);
        } // end case vendor
         elseif (in_array('Manager', $user_roles) ||  in_array('Staff', $user_roles) ) 
      {
        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
        $staff_stores = $exist_staff->stores->pluck('id')->toArray();
        $products = Product::whereHas('vendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%")
                                  ->orWhere('email', 'like', "%{$search_index}%");
                })->where('vendor_id', $vendor_id)
                  ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();
            /*$total = Product::whereHas('vendor', function($q) use ($search_index){
                                    $q->where('vendor_name', 'like', "%{$search_index}%")
                                      ->orWhere('email', 'like', "%{$search_index}%");
                    })->where('vendor_id', $vendor_id)->count();*/
              $products = $products->where('vendor_id', $vendor_id);
              $total    = count($products);
            $data = SearchProductsApiResource::collection($products);
            return response()->json([
              'status_code' => 200,
              'message' => 'success',
              'data'  => $data,
              'total' => $total,
            ], 200);
      }
        else{
          return response()->json([
                  'message'  => 'un authorized access page due to permissions',
                 ], 401);
        }
     }
    // end search products with vendor 

    // start search products with store 
     public function search_with_store($search_index, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
     {
      //$PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      //$default_count = \Config::get('constants.pagination.items_per_page');
      //$request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
      $user = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();
        // case logged in user role is Admin 
        if (in_array('Admin', $user_roles)) {
          $products = Product::whereHas('store', function($q) use ($search_index){
                                $q->where('name', 'like', "%{$search_index}%")
                                  ->orWhere('address', 'like', "%{$search_index}%");
                })->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();
          $total = Product::whereHas('store', function($q) use ($search_index){
                                  $q->where('name', 'like', "%{$search_index}%")
                                    ->orWhere('address', 'like', "%{$search_index}%");
                  })->count();
          $data = SearchProductsApiResource::collection($products);
          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);
        } // end admin case
         // case logged in user role is Vendor 
        elseif (in_array('Vendor', $user_roles)) {
           $vendor    = AddVendor::where('userid_id', Auth::user()->id)->first();
           $vendor_id = $vendor->id;
           $products = Product::whereHas('store', function($q) use ($search_index){
                                $q->where('name', 'like', "%{$search_index}%")
                                  ->orWhere('address', 'like', "%{$search_index}%");
                })->where('vendor_id', $vendor_id)
                  ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();
           /* $total = Product::whereHas('store', function($q) use ($search_index){
                                    $q->where('name', 'like', "%{$search_index}%")
                                      ->orWhere('address', 'like', "%{$search_index}%");
                    })->where('vendor_id', $vendor_id)->count();*/
            $products = $products->where('vendor_id', $vendor_id);
            $total    = count($products);

            $data = SearchProductsApiResource::collection($products);
            return response()->json([
              'status_code' => 200,
              'message' => 'success',
              'data'  => $data,
              'total' => $total,
            ], 200);
        } // end case vendor
         elseif (in_array('Manager', $user_roles) ||  in_array('Staff', $user_roles) ) 
      {
        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
        $staff_stores = $exist_staff->stores->pluck('id')->toArray();
         $products = Product::whereHas('store', function($q) use ($search_index){
                                $q->where('name', 'like', "%{$search_index}%")
                                  ->orWhere('address', 'like', "%{$search_index}%");
                })->where('vendor_id', $vendor_id)
                  ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();
           /* $total = Product::whereHas('store', function($q) use ($search_index){
                                    $q->where('name', 'like', "%{$search_index}%")
                                      ->orWhere('address', 'like', "%{$search_index}%");
                    })->where('vendor_id', $vendor_id)->count();*/
            $products = $products->where('vendor_id', $vendor_id);
            $total    = count($products);

            $data = SearchProductsApiResource::collection($products);
            return response()->json([
              'status_code' => 200,
              'message' => 'success',
              'data'  => $data,
              'total' => $total,
            ], 200);
      }
        else{
          return response()->json([
                  'message'  => 'un authorized access page due to permissions',
                 ], 401);
        }
     }
    // end search products with store 

      // start search products with car model 
     public function search_with_car_model($search_index, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
     {
      // $PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
     // $default_count = \Config::get('constants.pagination.items_per_page');
     // $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
      $user = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();
        // case logged in user role is Admin 
        if (in_array('Admin', $user_roles)) {
              $products = Product::whereHas('car_model', function($q) use ($search_index){
                                    $q->where('carmodel', 'like', "%{$search_index}%")
                                    ->orWhere('name_en', 'like', "%{$search_index}%");
                    })->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                        ->orderBy($ordered_by, $sort_type)->get();
            $total = Product::whereHas('car_model', function($q) use ($search_index){
                                    $q->where('carmodel', 'like', "%{$search_index}%")
                                    ->orWhere('name_en', 'like', "%{$search_index}%");
                    })->count();
            $data = SearchProductsApiResource::collection($products);
            return response()->json([
              'status_code' => 200,
              'message' => 'success',
              'data'  => $data,
              'total' => $total,
            ], 200);
        } // end admin case
         // case logged in user role is Vendor 
        elseif (in_array('Vendor', $user_roles)) {
           $vendor    = AddVendor::where('userid_id', Auth::user()->id)->first();
           $vendor_id = $vendor->id;
           $products = Product::whereHas('car_model', function($q) use ($search_index){
                                $q->where('carmodel', 'like', "%{$search_index}%")
                                ->orWhere('name_en', 'like', "%{$search_index}%");
                })->where('vendor_id', $vendor_id)
                   ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();
            /*$total = Product::whereHas('car_model', function($q) use ($search_index){
                                    $q->where('carmodel', 'like', "%{$search_index}%");
                    })->where('vendor_id', $vendor_id)->count();*/
                $products = $products->where('vendor_id', $vendor_id);
                $total    = count($products);
            $data = SearchProductsApiResource::collection($products);
            return response()->json([
              'status_code' => 200,
              'message' => 'success',
              'data'  => $data,
              'total' => $total,
            ], 200);
        } // end case vendor
         elseif (in_array('Manager', $user_roles) ||  in_array('Staff', $user_roles) ) 
      {
        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
        $staff_stores = $exist_staff->stores->pluck('id')->toArray();
        $products = Product::whereHas('car_model', function($q) use ($search_index){
                                $q->where('carmodel', 'like', "%{$search_index}%")
                                ->orWhere('name_en', 'like', "%{$search_index}%");
                })->where('vendor_id', $vendor_id)
                   ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();
            /*$total = Product::whereHas('car_model', function($q) use ($search_index){
                                    $q->where('carmodel', 'like', "%{$search_index}%");
                    })->where('vendor_id', $vendor_id)->count();*/
                $products = $products->where('vendor_id', $vendor_id);
                $total    = count($products);
            $data = SearchProductsApiResource::collection($products);
            return response()->json([
              'status_code' => 200,
              'message' => 'success',
              'data'  => $data,
              'total' => $total,
            ], 200);
      }
        else{
          return response()->json([
                  'message'  => 'un authorized access page due to permissions',
                 ], 401);
        }
     }
    // end search products with car model 

      // start search products with car year 
     public function search_with_car_year($search_index, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
     {
      //$PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      // $default_count = \Config::get('constants.pagination.items_per_page');
      // $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
      $user = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();
        // case logged in user role is Admin 
        if (in_array('Admin', $user_roles)) {
          $products = Product::whereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();
          $total = Product::whereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })->count();
          $data = SearchProductsApiResource::collection($products);
          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);
        } // end admin case
         // case logged in user role is Vendor 
        elseif (in_array('Vendor', $user_roles)) {
           $vendor    = AddVendor::where('userid_id', Auth::user()->id)->first();
           $vendor_id = $vendor->id;
           $products = Product::whereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })->where('vendor_id', $vendor_id)
                  ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();
            /*$total = Product::whereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })->where('vendor_id', $vendor_id)->count();*/
                $products = $products->where('vendor_id', $vendor_id);
                $total    = count($products);

            $data = SearchProductsApiResource::collection($products);
            return response()->json([
              'status_code' => 200,
              'message' => 'success',
              'data'  => $data,
              'total' => $total,
            ], 200);
        } // end case vendor
         elseif (in_array('Manager', $user_roles) ||  in_array('Staff', $user_roles) ) 
      {
        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
        $staff_stores = $exist_staff->stores->pluck('id')->toArray();
        $products = Product::whereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })->where('vendor_id', $vendor_id)
                  ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();
            /*$total = Product::whereHas('year_from_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })
                          ->orWhereHas('year_to_func', function ($q) use ($search_index) {
                            $q->where('year', 'like', "%{$search_index}%");
                          })->where('vendor_id', $vendor_id)->count();*/
                $products = $products->where('vendor_id', $vendor_id);
                $total    = count($products);

            $data = SearchProductsApiResource::collection($products);
            return response()->json([
              'status_code' => 200,
              'message' => 'success',
              'data'  => $data,
              'total' => $total,
            ], 200);
      }
        else{
          return response()->json([
                  'message'  => 'un authorized access page due to permissions',
                 ], 401);
        }
     }
    // end search products with car year 

     // start search products with part category 
     public function search_with_product_category($search_index, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
     { 
        //$PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
       // $default_count = \Config::get('constants.pagination.items_per_page');
       // $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
        
        $user = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();
          // case logged in user role is Admin 
          if (in_array('Admin', $user_roles)) {
            $products = Product::whereHas('allcategory', function($q) use ($search_index){
                                $q->where('name', 'like', "%{$search_index}%")
                                ->orWhere('name_en', 'like', "%{$search_index}%"); 
                })->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();
            $total = Product::whereHas('allcategory', function($q) use ($search_index){
                                $q->where('name', 'like', "%{$search_index}%")
                                ->orWhere('name_en', 'like', "%{$search_index}%"); 
                })->count();
            $data = SearchProductsApiResource::collection($products);
            return response()->json([
              'status_code' => 200,
              'message' => 'success',
              'data'  => $data,
              'total' => $total,
            ], 200);
          } // end admin case
           // case logged in user role is Vendor 
          elseif (in_array('Vendor', $user_roles)) {
             $vendor    = AddVendor::where('userid_id', Auth::user()->id)->first();
             $vendor_id = $vendor->id;
             $products = Product::whereHas('allcategory', function($q) use ($search_index){
                                $q->where('name', 'like', "%{$search_index}%")
                                ->orWhere('name_en', 'like', "%{$search_index}%"); 
                })->where('vendor_id', $vendor_id)
                  ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();
            /*$total = Product::whereHas('category', function($q) use ($search_index){
                                $q->where('name', 'like', "%{$search_index}%");
                })->where('vendor_id', $vendor_id)
                  ->count();*/
              $products = $products->where('vendor_id', $vendor_id);
              $total    = count($products);

            $data = SearchProductsApiResource::collection($products);
            return response()->json([
              'status_code' => 200,
              'message' => 'success',
              'data'  => $data,
              'total' => $total,
            ], 200);
          } // end case vendor
           elseif (in_array('Manager', $user_roles) ||  in_array('Staff', $user_roles) ) 
      {
        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
        $staff_stores = $exist_staff->stores->pluck('id')->toArray();
        $products = Product::whereHas('allcategory', function($q) use ($search_index){
                                $q->where('name', 'like', "%{$search_index}%")
                                ->orWhere('name_en', 'like', "%{$search_index}%"); 
                })->where('vendor_id', $vendor_id)
                  ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();
            /*$total = Product::whereHas('category', function($q) use ($search_index){
                                $q->where('name', 'like', "%{$search_index}%");
                })->where('vendor_id', $vendor_id)
                  ->count();*/
              $products = $products->where('vendor_id', $vendor_id);
              $total    = count($products);

            $data = SearchProductsApiResource::collection($products);
            return response()->json([
              'status_code' => 200,
              'message' => 'success',
              'data'  => $data,
              'total' => $total,
            ], 200);
      }
          else{
            return response()->json([
                    'message'  => 'un authorized access page due to permissions',
                   ], 401);
          }
     }
    // end search products with part category

     // start search products with tags 
     public function search_with_tags($search_index, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
     {
      //$PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      // $default_count = \Config::get('constants.pagination.items_per_page');
      // $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
      $user = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();
        // case logged in user role is Admin 
        if (in_array('Admin', $user_roles)) {
          $products = Product::whereHas('tags', function($q) use ($search_index){
                                $q->where('name', 'like', "%{$search_index}%");
                })->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();
          $total = Product::whereHas('tags', function($q) use ($search_index){
                                  $q->where('name', 'like', "%{$search_index}%");
                  })->count();
          $data = SearchProductsApiResource::collection($products);
          return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
          ], 200);
        } // end admin case
         // case logged in user role is Vendor 
        elseif (in_array('Vendor', $user_roles)) {
           $vendor    = AddVendor::where('userid_id', Auth::user()->id)->first();
           $vendor_id = $vendor->id;
           $products = Product::whereHas('tags', function($q) use ($search_index){
                                $q->where('name', 'like', "%{$search_index}%");
                })->where('vendor_id', $vendor_id)
                  ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();
           /* $total = Product::whereHas('tags', function($q) use ($search_index){
                                    $q->where('name', 'like', "%{$search_index}%");
                    })->where('vendor_id', $vendor_id)->count();*/
                $products = $products->where('vendor_id', $vendor_id);
                $total    = count($products);

            $data = SearchProductsApiResource::collection($products);
            return response()->json([
              'status_code' => 200,
              'message' => 'success',
              'data'  => $data,
              'total' => $total,
            ], 200);
        } // end case vendor
         elseif (in_array('Manager', $user_roles) ||  in_array('Staff', $user_roles) ) 
      {
        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
        $staff_stores = $exist_staff->stores->pluck('id')->toArray();
        $products = Product::whereHas('tags', function($q) use ($search_index){
                                $q->where('name', 'like', "%{$search_index}%");
                })->where('vendor_id', $vendor_id)
                  ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();
           /* $total = Product::whereHas('tags', function($q) use ($search_index){
                                    $q->where('name', 'like', "%{$search_index}%");
                    })->where('vendor_id', $vendor_id)->count();*/
                $products = $products->where('vendor_id', $vendor_id);
                $total    = count($products);

            $data = SearchProductsApiResource::collection($products);
            return response()->json([
              'status_code' => 200,
              'message' => 'success',
              'data'  => $data,
              'total' => $total,
            ], 200);
      }
        else{
          return response()->json([
                  'message'  => 'un authorized access page due to permissions',
                 ], 401);
        }
     }
    // end search products with tags
}
