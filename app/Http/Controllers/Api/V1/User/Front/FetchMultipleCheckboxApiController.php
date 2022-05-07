<?php

namespace App\Http\Controllers\Api\V1\User\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\Website\Products\FrontProductsApiResource;
use App\Http\Resources\Website\Products\SpecificFrontProductsApiResource;
use Gate;
use Auth;
use DB;
use App\Models\Product;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\Website\User\CheckboxFilter\FetchCheckboxFilterApiRequest;
use App\Models\ProductCategory;
use App\Models\PartCategory;
use App\Models\Maincategory;
use App\Http\Resources\Api\V1\Admin\MainCategories\SingleMaincategoryApiResource;
use App\Http\Resources\Api\V1\Admin\MainCategories\MaincategoryApiResource;
use App\Http\Resources\Api\V1\Admin\MainCategories\SingleMaincategoryNestedApiResource;

class FetchMultipleCheckboxApiController extends Controller
{
     public function getLang()
  {
      return $lang = \Config::get('app.locale');
  } 

    public function fetch_multiple_checkbox(FetchCheckboxFilterApiRequest $request)
    {
        $lang = $this->getLang();
    	$part_categories = $request->part_categories;
    	$manufacturers   = $request->manufacturers;
    	$origins         = $request->origins;
        //$start_price     =  $request->start_price;
        //$end_price       =  $request->end_price;

    	$default_count = \Config::get('constants.pagination.items_per_page');
        $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
        
        $request->page == '' ? $page = 1 : $page = $request->page;
        $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
        $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
        $request->column_name == '' ? $column_name = '' : $column_name = $request->column_name;
        if ($ordered_by == 'price') {
        $ordered_by = 'actual_price';
      }

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
        if ($part_categories == '' && $manufacturers == '' && $origins == '') {
          return response()->json(['errors'  =>'select at least one item',], 400);
        }

        // case 1
        if ($part_categories != '' && $manufacturers == '' && $origins == '') {
        	$part_categories = json_decode($request->part_categories);
	    	// $manufacturers   = json_decode($request->manufacturers);
	    	// $origins         = json_decode($request->origins);
          return $this->part_categories_search($part_categories, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 1
        if ($part_categories == '' && $manufacturers != '' && $origins == '') {
        	// $part_categories = json_decode($request->part_categories);
	    	$manufacturers   = json_decode($request->manufacturers);
	    	// $origins         = json_decode($request->origins);
          return $this->manufacturers_search($manufacturers, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 1
        if ($part_categories == '' && $manufacturers == '' && $origins != '') {
        	// $part_categories = json_decode($request->part_categories);
	    	// $manufacturers   = json_decode($request->manufacturers);
	    	$origins         = json_decode($request->origins);
          return $this->origins_search($origins, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 1
        if ($part_categories != '' && $manufacturers != '' && $origins == '' ) {
        	$part_categories = json_decode($request->part_categories);
	    	$manufacturers   = json_decode($request->manufacturers);
	    	//$origins         = json_decode($request->origins);
          return $this->part_categories_manufacturers_search($part_categories, $manufacturers, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 1
        if ($part_categories != '' && $manufacturers == '' && $origins != '') {
        	$part_categories = json_decode($request->part_categories);
	    	//$manufacturers   = json_decode($request->manufacturers);
	    	$origins         = json_decode($request->origins);
          return $this->part_categories_origins_search($part_categories, $origins, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 1
        if ($part_categories == '' && $manufacturers != '' && $origins != '') {
        	//$part_categories = json_decode($request->part_categories);
	    	$manufacturers   = json_decode($request->manufacturers);
	    	$origins         = json_decode($request->origins);
          return $this->manufacturers_origins_search($manufacturers, $origins, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }

        // case 1
        if ($part_categories != '' && $manufacturers != '' && $origins != '') {
        	$part_categories = json_decode($request->part_categories);
	    	$manufacturers   = json_decode($request->manufacturers);
	    	$origins         = json_decode($request->origins);
            return $this->all_search($part_categories, $manufacturers, $origins, $page, $ordered_by ,$sort_type, $PAGINATION_COUNT);
        }
    }

    public function part_categories_search($part_categories, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
    {
        $lang = $this->getLang();
    	//return $part_categories;
    	$new_products = Product::where('lang', $lang)->where('approved', 1)->whereIn('part_category_id', $part_categories)
    	                               // ->skip(($page-1)*$PAGINATION_COUNT)
    	                               // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 

        $total = Product::where('lang', $lang)->where('approved', 1)->whereIn('part_category_id', $part_categories)->count();

        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
            	'status_code' => 200,
            	'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
    }

    public function manufacturers_search($manufacturers, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
    {
        $lang = $this->getLang();
    	$new_products = Product::where('lang', $lang)->where('approved', 1)->whereIn('manufacturer_id', $manufacturers)
    	                               // ->skip(($page-1)*$PAGINATION_COUNT)
    	                               // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = Product::where('lang', $lang)->where('approved', 1)->whereIn('manufacturer_id', $manufacturers)->count();

        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
            	'status_code' => 200,
            	'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
    }

    public function origins_search($origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
    {
        $lang = $this->getLang();
    	$new_products = Product::where('lang', $lang)->where('approved', 1)->whereIn('prodcountry_id', $origins)
    	                               // ->skip(($page-1)*$PAGINATION_COUNT)
    	                               // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = Product::where('lang', $lang)->where('approved', 1)->whereIn('prodcountry_id', $origins)->count();

        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
            	'status_code' => 200,
            	'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
    }

    public function part_categories_manufacturers_search($part_categories, $manufacturers, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
    {
        $lang = $this->getLang();
    	$new_products = Product::where('lang', $lang)->where('approved', 1)->whereIn('part_category_id', $part_categories)
    	                                ->whereIn('manufacturer_id', $manufacturers)
    	                               // ->skip(($page-1)*$PAGINATION_COUNT)
    	                               // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = Product::where('lang', $lang)->where('approved', 1)->whereIn('part_category_id', $part_categories)
    	                                ->whereIn('manufacturer_id', $manufacturers)
    	                                ->count();

        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
            	'status_code' => 200,
            	'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
    }

    public function part_categories_origins_search($part_categories, $origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
    {
        $lang = $this->getLang();
    	$new_products = Product::where('lang', $lang)->where('approved', 1)->whereIn('part_category_id', $part_categories)
    	                                ->whereIn('prodcountry_id', $origins)
    	                               // ->skip(($page-1)*$PAGINATION_COUNT)
    	                               // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = Product::where('lang', $lang)->where('approved', 1)->whereIn('part_category_id', $part_categories)
    	                                ->whereIn('prodcountry_id', $origins)
    	                                ->count();

        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
            	'status_code' => 200,
            	'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
    }

    public function manufacturers_origins_search($manufacturers, $origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
    {
        $lang = $this->getLang();
    	$new_products = Product::where('lang', $lang)->where('approved', 1)->whereIn('manufacturer_id', $manufacturers)
    	                                ->whereIn('prodcountry_id', $origins)
    	                               // ->skip(($page-1)*$PAGINATION_COUNT)
    	                               // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = Product::where('lang', $lang)->where('approved', 1)->whereIn('manufacturer_id', $manufacturers)
    	                                ->whereIn('prodcountry_id', $origins)
    	                                ->count();

        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
            	'status_code' => 200,
            	'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
    }

    public function all_search($part_categories, $manufacturers, $origins, $page, $ordered_by, $sort_type, $PAGINATION_COUNT)
    {
        $lang = $this->getLang();
    	$new_products = Product::where('lang', $lang)->where('approved', 1)->whereIn('part_category_id', $part_categories)
    	                                ->whereIn('part_category_id', $part_categories)
    	                                ->whereIn('prodcountry_id', $origins)
    	                               // ->skip(($page-1)*$PAGINATION_COUNT)
    	                               // ->take($PAGINATION_COUNT)
                                        ->orderBy($ordered_by, $sort_type)->get(); 
        $total = Product::where('lang', $lang)->where('approved', 1)->whereIn('part_category_id', $part_categories)
    	                                ->whereIn('part_category_id', $part_categories)
    	                                ->whereIn('prodcountry_id', $origins)
    	                                ->count();

        $data = FrontProductsApiResource::collection($new_products);        
            return response()->json([
            	'status_code' => 200,
            	'message'     => 'success',
                'data'        => $data,
                'total'       => $total,
            ], 200);
    }

   /* public function categories_nested_part()
    {
        $lang = $this->getLang();
    	$categories = ProductCategory::where('lang', $lang)->with('part_categories')->get();
    	$data = $categories;
    	return response()->json([
            	'status_code' => 200,
            	'message'     => 'success',
                'data'        => $data,
               // 'total'       => $total,
            ], 200);

    }*/

    public function categories_nested_part()
    {
        $lang = $this->getLang();
      //  $items = Maincategory::where('lang', $lang)->get();
        $items = Maincategory::get();
        $data = SingleMaincategoryNestedApiResource::collection($items);
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data' => $data,
        ], Response::HTTP_OK);

    }

    // start list all
     public function categories_nested_part_specific($id)
     {
        $items = Maincategory::findOrFail($id);
        $data = new SingleMaincategoryNestedApiResource($items);
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data' => $data], Response::HTTP_OK);
     }
     // end list all 

     // start list all
     /*public function categories_nested_part_specific($id)
     {
        $lang = $this->getLang();
        $productCategory = ProductCategory::find($id);
        if (!$productCategory) {
          return response()->json([
          'status_code'     => 400,
          'message'         => 'fail',
          'errors'          => 'wrong Product Category id',
          'data'            => null,], 400);
        }
        $data     = PartCategory::where('lang', $lang)->where('category_id', $id)->get();
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data' => $data], Response::HTTP_OK);
     }*/
     // end list all 
}
