<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\AddProductApiRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\Admin\ProductResource;
use App\Models\Product;
use App\Models\Store;
use Gate;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\StoreMediaRequest;
use App\Http\Requests\SearchApisRequest;
use App\Http\Requests\MassDestroyProductRequest;
use Illuminate\Support\Facades\Storage;
use App\Models\ProductCategory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Http\Requests\RemoveSpecificProductMediaRequest;
use App\Models\AddVendor;
use Auth;
use App\Models\ProductTag;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\RemoveSpecificProductCheckedMediaRequest;
use App\Http\Resources\Admin\SpecificProductApiResource;
use Validator;
use App\Models\CarModel;
use App\Models\Productprice;
use App\Http\Resources\Website\Products\FrontProductsApiResource;
// use App\Models\Sanctum\PersonalAccessToken;
use App\Http\Resources\Api\V1\Admin\Dashboard\Products\SearchProductsApiResource;
use App\Http\Requests\Api\V1\Admin\Products\AddProductCopyApiRequest;
use App\Models\Productstorequantity;
use App\Http\Resources\Admin\AllProductsApiResource;

class ProductCopyApiController extends Controller
{
	public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }

  public function show(Product $product, Request $request)
    {
      $lang = $this->getLang();
      abort_if(Gate::denies('product_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
      return new SpecificProductApiResource($product);
    }

  public function index(Request $request)
    {
      $lang = $this->getLang();
      abort_if(Gate::denies('product_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
      // default is 1 id asc 
      //$PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;

      if ($ordered_by != '') {
        if (!Schema::hasColumn('products', $ordered_by)) {
          return response()->json(['message'  =>'order column not found',], 400);
        }
        if ($ordered_by == 'tags' || $ordered_by == 'categories') {
          $ordered_by = 'id';
        }
      }

      $user = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();

      if (in_array('Admin', $user_roles)) {
        $prods = Product::where('lang', $lang)->skip(($page-1)*$PAGINATION_COUNT)
                      ->take($PAGINATION_COUNT)->orderBy($ordered_by, $sort_type)
                      ->get();
        $data = AllProductsApiResource::collection($prods);
        $total = Product::where('lang', $lang)->count();
            return response()->json([
              'status_code' => 200,
              'message' => 'success',
              'data'  => $data,
              'total' => $total,
            ], 200);
      } 
       // case logged in user role is Vendor (show only his invoices)
      elseif (in_array('Vendor', $user_roles)) {
              $vendor        = AddVendor::where('userid_id', Auth::user()->id)->first();
              $vendor_id     = $vendor->id;
              $prods = Product::where('lang', $lang)->where('vendor_id', $vendor_id)
                      ->skip(($page-1)*$PAGINATION_COUNT)
                      ->take($PAGINATION_COUNT)->orderBy($ordered_by, $sort_type)
                      ->get();
              $data  = AllProductsApiResource::collection($prods);
              $total = Product::where('lang', $lang)->where('vendor_id', $vendor_id)->count();
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

    public function add_product_copy(AddProductCopyApiRequest $request)
    {
     // return Auth::user()->id;
     // to get categories and tags as array format
      $lang = $this->getLang();
      $request['lang'] = $lang;
       // $arr       = json_decode($request->categories);
        $tags_arr    = json_decode($request->tags);
        // $stores_arr  = json_decode($request->stores);
        // $qtys_arr    = json_decode($request->quantities);
        $models_arr  = json_decode($request->models);
        // $years_arr   = json_decode($request->years);
    
        $vendor = AddVendor::where('userid_id', Auth::user()->id)->first();
       // return $vendor;
        if ($vendor == null) {
          return response()->json([
            'status_code' => 400,
            'message' => 'fail',
            'errors' => 'this account has no vendors assigned yet',
          ], 400);
        }
        $vendor_id = $vendor->id;
        $request['vendor_id'] = $vendor->id;

        $exist_name = Product::where('name', $request->name)->first();
        if ($exist_name != null) {
          return response()->json([
            'status_code' => 400 , 
            'errors'      => 'this name has already been taken',
          ], 400);
        }

        $exist_serial = Product::where('serial_number', $request->serial_number)
                              ->first();
        if ($exist_serial != null) {
          return response()->json([
            'status_code' => 400 , 
            'errors'      => 'this serial number has already been taken',
          ], 400);
        }

        // discount validation
           if ($request->discount == '' || empty($request->discount) || !$request->has('discount')) {
             $request['discount'] = null;
            } 
            else{
              $v = Validator::make($request->all(), [
                'discount' => 'nullable|numeric|min:5|max:80',
              ]);
              if ($v->fails()) {
                return response()->json(['errors' => $v->errors()], 400);
              }
                 $request['discount'] = $request->discount;
            }
        // discount validation

            /********************* case type 1 ***************************/
            if ($request->producttype_id == 1) {

              $price_v = Validator::make($request->all(), [
                    'price' => 'required|numeric|min:1',
                    // 'no_of_orders'   => 'required|integer|min:1',
                  ]);

                  if ($price_v->fails()) {
                    return response()->json([
                      'status_code' => 400,
                      'errors' => $price_v->errors()], 400);
                  }

              $exist = Product::withTrashed()->latest()->first();
              
              $serial_coding = 'N0'. ($exist->id + 1);
              $request['serial_coding'] = $serial_coding;
              /* serial id seq */
              $latest_prod = Product::withTrashed()->where('vendor_id', $vendor->id)
                                ->orderBy('created_at', 'desc')->limit(1)->first();
              $prod_store = Store::findOrFail($request->store_id);
              if (!$latest_prod) {
                $serial_id = 'prod_'.$request->name.'_st_'.$prod_store->name.'_ven_'.$vendor->vendor_name.'_IDNO_'.$vendor->id.'_001';
              }else{
                $serial_id = 'prod_'.$request->name.'_st_'.$prod_store->name.'_ven_'.$vendor->vendor_name.'_IDNO_'.$vendor->id.'_00'.($latest_prod->id + 1);
              }
              $request['serial_id'] = $serial_id;
              /* serial id sec */
              $request['year_id'] = null;
              $request['car_model_id'] = null;

              $product   = Product::create($request->all());
              $product->update(['original_id' => $product->id]);
              // multiple section
              $product->tags()->sync($tags_arr);
              $product->car_model()->sync($models_arr);
              // $product->year()->sync($years_arr);

          /*foreach ($stores_arr as $key => $loop_id)
		      {
		        Productstorequantity::firstOrCreate([
	                'product_id'    => $product->id,
	                'store_id'      => $loop_id,
	                'quantity'      => $qtys_arr[$key],
                ]);
		      }*/
               // multiple section

              /* new */
            /*  if ($request->has('photo') && $request->photo != '') {
                foreach ($request->photo as $file) {
                    $path = Storage::disk('spaces')->putFile('products', $file);
                    Storage::disk('spaces')->setVisibility($path, 'public');
                    $url   = Storage::disk('spaces')->url($path);
                    $product->addMediaFromUrl($url)
                             ->toMediaCollection('photo');
                }
              }
              /* new */
              return $this->showWithMedia($product->id);
            }

            /********************* end case type 1 ***************************/

            /********************* case type 2 ***************************/

            if ($request->producttype_id == 2) {
              $holesale_price_v = Validator::make($request->all(), [
                    'holesale_price' => 'required|numeric|min:1',
                    'no_of_orders'   => 'required|integer|min:1',
                  ]);

                  if ($holesale_price_v->fails()) {
                    return response()->json([
                      'status_code' => 400,
                      'errors' => $holesale_price_v->errors()], 400);
                  }

              $exist = Product::withTrashed()->latest()->first();
              $serial_coding = 'H0'. ($exist->id + 1);
              $request['serial_coding'] = $serial_coding;
              $request['no_of_orders']   = $request->no_of_orders;
              $request['price']   = null;
              $request['holesale_price']   = $request->holesale_price;
              $request['year_id'] = null;
              $request['car_model_id'] = null;

              /* serial id seq */
              $latest_prod = Product::withTrashed()->where('vendor_id', $vendor->id)
                                ->orderBy('created_at', 'desc')->limit(1)->first();
              $prod_store = Store::findOrFail($request->store_id);
              if (!$latest_prod) {
                $serial_id = 'prod_'.$request->name.'_st_'.$prod_store->name.'_ven_'.$vendor->vendor_name.'_IDNO_'.$vendor->id.'_001';
              }else{
                $serial_id = 'prod_'.$request->name.'_st_'.$prod_store->name.'_ven_'.$vendor->vendor_name.'_IDNO_'.$vendor->id.'_00'.($latest_prod->id + 1);
              }
              $request['serial_id'] = $serial_id;
              /* serial id sec */

              $product   = Product::create($request->all());
              $product->update(['original_id' => $product->id]);
              $product->tags()->sync($tags_arr);
              $product->car_model()->sync($models_arr);

              /* new */
              if ($request->has('photo') && $request->photo != '') {
                foreach ($request->photo as $file) {
                    $path = Storage::disk('spaces')->putFile('products', $file);
                    Storage::disk('spaces')->setVisibility($path, 'public');
                    $url   = Storage::disk('spaces')->url($path);
                    $product->addMediaFromUrl($url)
                             ->toMediaCollection('photo');
                }
              }
              /* new */
              return $this->showWithMedia($product->id);
            }

            /********************* end case type 2 ***************************/

            /********************* case type 3 ***************************/

            if ($request->producttype_id == 3) {
              // start validation for holesale_price
                  $holesale_price_v = Validator::make($request->all(), [
                    'price'          => 'required|numeric|min:1',
                    'holesale_price' => 'required|numeric|min:1',
                    'no_of_orders'   => 'required|integer|min:1',
                  ]);

                  if ($holesale_price_v->fails()) {
                    return response()->json([
                      'status_code' => 400,
                      'errors' => $holesale_price_v->errors()], 400);
                  }

                  /* serial id seq */
              $latest_prod = Product::withTrashed()->where('vendor_id', $vendor->id)
                                ->orderBy('created_at', 'desc')->limit(1)->first();
              $prod_store = Store::findOrFail($request->store_id);
              if (!$latest_prod) {
                $serial_id = 'prod_'.$request->name.'_st_'.$prod_store->name.'_ven_'.$vendor->vendor_name.'_IDNO_'.$vendor->id.'_001';
              }else{
                $serial_id = 'prod_'.$request->name.'_st_'.$prod_store->name.'_ven_'.$vendor->vendor_name.'_IDNO_'.$vendor->id.'_00'.($latest_prod->id + 1);
              }
              $request['serial_id'] = $serial_id;
              /* serial id sec */

              // end validation for holesale_price
              $exist = Product::withTrashed()->where('producttype_id', 1)
                      ->latest()->first();
              // return $exist;
              if ($exist == null) {
                $serial_coding1 = 'BN01';
                $serial_coding2 = 'BH01';
              }else{
                $serial_coding1 = 'BN0'. ($exist->id + 1);
                $serial_coding2 = 'BH0'. ($exist->id + 1);
              }

              $holesale_price = $request->holesale_price;
              $no_of_orders = $request->no_of_orders;

              for ($i=0; $i < 2; $i++) { 
                  if ($i == 0) {
                    $request['serial_coding'] = $serial_coding1;
                    $request['holesale_price'] = null;
                    $request['no_of_orders']   = null;
                    $request['price'] = $request->price;
                    $request['producttype_id'] = 1;
                    $request['year_id'] = null;
                    $request['car_model_id'] = null;
                    $normal_one = Product::create($request->all());
                    $normal_one->update(['original_id' => $normal_one->id]);
                    $normal_one->tags()->sync($tags_arr);
                    $normal_one->car_model()->sync($models_arr);
                    /* new */
                   if ($request->has('photo') && $request->photo != '') {
                      foreach ($request->photo as $file) {
                          $path = Storage::disk('spaces')->putFile('products', $file);
                          Storage::disk('spaces')->setVisibility($path, 'public');
                          $url   = Storage::disk('spaces')->url($path);
                          $normal_one->addMediaFromUrl($url)
                                   ->toMediaCollection('photo');
                      }
                    }
                       /* new */
                  } // end i == 0
                  if ($i == 1) {

                    $request['serial_coding'] = $serial_coding2;
                    $request['producttype_id'] = 2;
                    $request['price'] = null;
                    $request['holesale_price'] = $holesale_price;
                    $request['no_of_orders']   = $no_of_orders;
                    $request['year_id'] = null;
                    $request['car_model_id'] = null;
                    // return $request->holesale_price;
                    $wholesale_one = Product::create($request->all());
                    $wholesale_one->update(['original_id' => $normal_one->id]);
                    
                    $wholesale_one->tags()->sync($tags_arr);
                    $wholesale_one->car_model()->sync($models_arr);
                    /* new */
                    if ($request->has('photo') && $request->photo != '') {
                      foreach ($request->photo as $file) {
                          $path = Storage::disk('spaces')->putFile('products', $file);
                          Storage::disk('spaces')->setVisibility($path, 'public');
                          $url   = Storage::disk('spaces')->url($path);
                          $wholesale_one->addMediaFromUrl($url)
                                   ->toMediaCollection('photo');
                      }
                    }
                    /* new */
                  } // end i == 1
              } // end for  */
              return $this->showWithMedia($wholesale_one->id);
            } // end prod type = 3

             /*********************************************************/
            /********************* case type 3 ***************************/
    }
}
