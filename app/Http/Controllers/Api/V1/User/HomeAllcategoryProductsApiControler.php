<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Models\Allcategory;
use Gate;
use Symfony\Component\HttpFoundation\Response;
// use App\Http\Requests\StoreMediaPartCategoryRequest;
use Auth;
use App\Http\Requests\SearchApisRequest;
use App\Http\Requests\Api\Admin\Allcategory\MassDestroyAllcategoryApiRequest;
use App\Http\Requests\Api\Admin\Allcategory\StoreAllcategoryApiRequest;
use App\Http\Requests\Api\Admin\Allcategory\UpdateAllcategoryApiRequest;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\Storage;

use App\Http\Resources\Api\Admin\Allcategory\HomeAllcategoryApiResource;
use App\Http\Resources\Api\Admin\Allcategory\HomeAllcategorySpecificApiResource;
use App\Models\Product;
use App\Http\Resources\Api\Admin\Allcategory\AllcategoryFrontProductsApiResource;
use App\Models\AddVendor;
use App\Http\Resources\Api\Admin\Allcategory\SpecificParentApiResource;

class HomeAllcategoryProductsApiControler extends Controller
{
  //  use MediaUploadingTrait;

    public function getLang()
    {
       return $lang = \Config::get('app.locale');
    }

    public function index_products(Request $request, $id)
    {
	      $lang = $this->getLang();
	      $default_count = \Config::get('constants.pagination.items_per_page');
	      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
	      
	      $request->page == '' ? $page = 1 : $page = $request->page;
	      $request->sort_type == '' ? $sort_type = 'DESC' : $sort_type = $request->sort_type;
	      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;

        if ($ordered_by == 'price') {
          $ordered_by = 'actual_price';
        }

	      $allcategory_id = $id;

	      return $this->fetch_all_allcategory_products($allcategory_id, $page, $ordered_by, $sort_type, $PAGINATION_COUNT);
    }

   /* public function part_origins_price_search($allcategory_id, $origins, $start_price, $end_price, $page, $ordered_by, $sort_type, $PAGINATION_COUNT, $cartype_id, $id) */
    public function fetch_all_allcategory_products($allcategory_id, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
    {
        $lang = $this->getLang();
        $current_cat = Allcategory::where('id', $allcategory_id)->first();
        // alraedy user logged in

        $target = Allcategory::findOrFail($allcategory_id);

        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
               $new_products = Product::where('approved', 1)
               ->where('producttype_id', 1)
              // ->where('cartype_id', $cartype_id) // added june 29 2021
               ->where('allcategory_id', $allcategory_id)         
               ->orderBy($ordered_by, $sort_type)->get(); 
        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
                //$new_product['part_name']      = $part_name;
              }
        $data = AllcategoryFrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'current_cat'       => $current_cat->name,
                'current_cat_en'    => $current_cat->name_en,
               // 'current_cat_sequence'  => array(new SpecificParentApiResource($current_cat)),
                'breadcrumbs'   => $target->getParentssAttribute(),
            ], 200);
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
             $new_products = Product::where('approved', 1)
             ->whereIn('producttype_id', [1, 2, 3])
             ->where('allcategory_id', $allcategory_id)
            // ->where('cartype_id', $cartype_id)
             ->orderBy($ordered_by, $sort_type)->get(); 

        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
                //$new_product['part_name']      = $part_name;
              }
        $data = AllcategoryFrontProductsApiResource::collection($new_products);        
            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'current_cat'       => $current_cat->name,
                'current_cat_en'    => $current_cat->name_en,
               // 'current_cat_sequence'  => array(new SpecificParentApiResource($current_cat)),
                'breadcrumbs'   => $target->getParentssAttribute(),
            ], 200);
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
             $new_products = Product::where('approved', 1)
             ->where('producttype_id', 1)
            // ->where('cartype_id', $cartype_id) // added june 29 2021
             ->where('allcategory_id', $allcategory_id)                            
             ->orderBy($ordered_by, $sort_type)->get(); 

        $total = count($new_products);
        foreach ($new_products as $new_product) {
                $new_product['in_cart']       = 0;
                $new_product['in_wishlist']   = 0;
                $new_product['in_favourites'] = 0;
                //$new_product['part_name']      = $part_name;
              }

        $data = AllcategoryFrontProductsApiResource::collection($new_products);     
        
        /*$arr = array(new SpecificParentApiResource($current_cat));
        $actual_parents = substr_count(json_encode(new SpecificParentApiResource($current_cat)), 'parent');
        $count_parents  = $actual_parents - 1;
        $ids = array(); */

            return response()->json([
                'status_code' => 200,
                'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
                'current_cat'       => $current_cat->name,
                'current_cat_en'    => $current_cat->name_en,
               // 'current_cat_sequence'  => array(new SpecificParentApiResource($current_cat)),
                'breadcrumbs'   => $target->getParentssAttribute(),
            ], 200);         
        } // end guest case
    }
}
