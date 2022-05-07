<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\AddProductApiRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\Admin\ProductResource;
use App\Models\Product;
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
use App\Models\Vendorstaff;
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
use App\Http\Requests\Api\V1\Admin\Products\MarkDefaultProductImageApiRequest;
use App\Http\Resources\Admin\AllProductsApiResource;
use App\Http\Requests\Api\V1\Admin\Products\AddProductCopyApiRequest;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendAdminProductApprovalMail;
use App\Http\Requests\Api\V1\Admin\Products\AddProductCopyV2ApiRequest;
use App\Http\Requests\Api\V1\Admin\Products\UpdateProductCopyV2ApiRequest;
use App\Models\Allcategory;

class ProductApiController extends Controller
{
    use MediaUploadingTrait;

  public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }

    public function index(Request $request)
    {
      $lang = $this->getLang();
      // default is 1 id asc 
      //$PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $request->page == '' ? $page = 1 : $page = $request->page;
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;

      $user = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();

      if (in_array('Admin', $user_roles)) {
         $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
         $request->ordered_by == '' ? $ordered_by = 'approved' : $ordered_by = $request->ordered_by;
        abort_if(Gate::denies('product_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // ahmed 
        $prods = Product::orderBy('approved', 'ASC');
        $prods = $prods->skip(($page-1)*$PAGINATION_COUNT)
                      ->take($PAGINATION_COUNT)->orderBy('id', 'DESC')->get();
        $data = AllProductsApiResource::collection($prods);
        $total = Product::count();

            return response()->json([
              'status_code' => 200,
              'message' => 'success',
              'data'  => $data,
              'total' => $total,
            ], 200);
      } 
       // case logged in user role is Vendor (show only his invoices)
      elseif (in_array('Vendor', $user_roles)) 
      {
        // $ordered_by = 'created_at';
        $request->sort_type == '' ? $sort_type = 'DESC' : $sort_type = $request->sort_type;
        $request->ordered_by == '' ? $ordered_by = 'created_at' : $ordered_by = $request->ordered_by;

        abort_if(Gate::denies('product_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
              $vendor        = AddVendor::where('userid_id', Auth::user()->id)->first();
              $vendor_id     = $vendor->id;

              $prods = Product::orderBy($ordered_by, $sort_type)
                      ->where('vendor_id', $vendor_id)->get();
              $prods = $prods->skip(($page-1)*$PAGINATION_COUNT)
                      ->take($PAGINATION_COUNT);

              $data  = AllProductsApiResource::collection($prods);
              $total = Product::where('vendor_id', $vendor_id)->count();
               return response()->json([
                'status_code' => 200,
                'message' => 'success',
                'data'  => $data,
                'total' => $total,
              ], 200);
      }
      elseif (in_array('Manager', $user_roles) || in_array('Staff', $user_roles) ) 
      {
        abort_if(Gate::denies('product_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $request->sort_type == '' ? $sort_type = 'DESC' : $sort_type = $request->sort_type;
        $request->ordered_by == '' ? $ordered_by = 'created_at' : $ordered_by = $request->ordered_by;

        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
       
              $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
              $vendor_id     = $vendor->id;

              $prods = Product::orderBy($ordered_by, $sort_type)
                      ->where('vendor_id', $vendor_id)->get();
              $prods = $prods->skip(($page-1)*$PAGINATION_COUNT)
                      ->take($PAGINATION_COUNT);

              $data  = AllProductsApiResource::collection($prods);
              $total = Product::where('vendor_id', $vendor_id)->count();
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

    public function ss()
    {
      $product = Product::find(202);
      return count($product->photo);//[0]->image;
    }

    public function fetch_image($id)
    {
      $product = Product::find($id);
      return $product->photo[0]->id;
    }    

     public function showWithMedia($id)
    {
      $product = Product::find($id);
      return response()->json([
                'status_code' => 200,
               // 'message' => 'success',
                'message' => __('site_messages.product_added_successfully'),
                'data'  => new ProductResource($product),
              //  'total' => $total,
              ], Response::HTTP_CREATED);
    }    

    public function add_product_v2(AddProductCopyV2ApiRequest $request)
    {
      if (!$request->header('Accept-Language'))
        {
           return response()->json([
            'status_code' => 400,
            'errors'      => 'No Language header Found'], 400);
        }

        $lang = $this->getLang();
        $request['lang'] = $lang;

        $parents = json_decode($request->allcategory);
        $parent = $parents[0];
        $count = count($parents);
        $child = $parents[$count - 1];

        // start name and description validation
         if ($lang == 'ar') {
          $ar_v = Validator::make($request->all(), [
                'name'        => 'required|string',
                'description' => 'required|string|min:2|max:255',
                'name_en'        => 'nullable|string',
                'description_en' => 'nullable|string|min:2|max:255',
              ]);
              if ($ar_v->fails()) {
                return response()->json(['errors' => $ar_v->errors()], 400);
              }
        }
        if ($lang == 'en') {
          $en_v = Validator::make($request->all(), [
                'name'        => 'nullable|string',
                'description' => 'nullable|string|min:2|max:255',
                'name_en'        => 'required|string',
                'description_en' => 'required|string|min:2|max:255',
              ]);
              if ($en_v->fails()) {
                return response()->json(['errors' => $en_v->errors()], 400);
              }
        }
        // end name and description validation

      $user       = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();

        if (!in_array('Vendor', $user_roles) && !in_array('Staff', $user_roles) && !in_array('Manager', $user_roles)) {
              return response()->json([
                'status_code' => 401,
                'message' => 'fail',
                'errors' => 'unautorized access',
              ], 401);
        }
    // case logged in user role is Vendor (show only his invoices)
       
        $tags_arr    = json_decode($request->tags);
        $models_arr  = json_decode($request->models);

        if (in_array('Vendor', $user_roles)){
          $vendor = AddVendor::where('userid_id', Auth::user()->id)->first();
          $request['vendor_id'] = $vendor->id;
          $vendor_id     = $vendor->id;  
        }
        if (in_array('Staff', $user_roles) || in_array('Manager', $user_roles)) {
          $exist_staff   = Vendorstaff::where('email', Auth::user()->email)->first();
          $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
          $vendor_id     = $vendor->id;  
          $request['vendor_id'] = $vendor->id; 
        }
        

        $exist_name = Product::where('name', $request->name)->whereNull('deleted_at')->first();
        $exist_serial = Product::where('serial_number', $request->serial_number)
                              ->whereNull('deleted_at')
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
                'discount' => 'nullable|numeric|min:1|max:80',
              ]);
              if ($v->fails()) {
                return response()->json(['errors' => $v->errors()], 400);
              }
                 $request['discount'] = $request->discount;
            }
        // discount validation

            // tyres validation 
           $parents = json_decode($request->allcategory);
            $need_attributes = Allcategory::whereIn('id', $parents)->pluck('need_attributes')->toArray();
            if (in_array(1, $need_attributes)) {
              $tyres_v = Validator::make($request->all(), [
                'width'  => 'required|numeric',
                'size'   => 'required|numeric',
                'height' => 'required|numeric',
              ]);
              if ($tyres_v->fails()) {
                return response()->json(['errors' => $tyres_v->errors()], 400);
              }
            } 
            // tyres validation 

            $request['cartype_id']     = $parent;
            $request['allcategory_id'] = $child;

            /********************* case type 1 ***************************/
            if ($request->producttype_id == 1) {

              $price_v = Validator::make($request->all(), [
                    'price' => 'required|numeric|min:1',
                    'quantity'   => 'required|integer|min:1',
                    'qty_reminder'   => 'required|integer|min:1',
                  ]);

                  if ($price_v->fails()) {
                    return response()->json([
                      'status_code' => 400,
                      'errors' => $price_v->errors()], 400);
                  }

              $exist = Product::withTrashed()->latest()->first();
              
              if ($exist == null) {
                  $serial_coding = 'N01';
              }else{
                 $serial_coding = 'N0'. ($exist->id + 1);
              }
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
              $actual_price = $product->producttype_id == 1 ? $product->PriceAfterDiscount() : $product->holesale_price;
              $product->update(['actual_price' => $actual_price]);
              // multiple section
              $product->tags()->sync($tags_arr);
              $product->car_model()->sync($models_arr);
              $product->allcategory()->sync($parents);
              // $product->year()->sync($years_arr);

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
        // send admin email
        $def_image = $this->fetch_image($product->id);
        $product->update(['default_media' => $def_image]);
        $admin = User::findOrFail(1);
       Mail::to($admin->email)->send(new SendAdminProductApprovalMail($product));
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
              if ($exist == null) {
                 $serial_coding = 'H01';
              }else{
                $serial_coding = 'H0'. ($exist->id + 1);
              }
              $request['serial_coding'] = $serial_coding;
              $request['no_of_orders']   = $request->no_of_orders;
              $request['price']   = null;
              $request['holesale_price']   = $request->holesale_price;
              $request['actual_price']     = $request->holesale_price;
              $request['year_id'] = null;
              $request['car_model_id'] = null;
              $request['discount'] = 0;
              $request['quantity'] = null;
              $request['qty_reminder'] = null;

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
              $actual_price = $product->producttype_id == 1 ? $product->PriceAfterDiscount() : $product->holesale_price;
              $product->update(['actual_price' => $actual_price]);
              $product->tags()->sync($tags_arr);
              $product->car_model()->sync($models_arr);
              $product->allcategory()->sync($parents);

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

              // send admin email
        $admin = User::findOrFail(1);
        Mail::to($admin->email)->send(new SendAdminProductApprovalMail($product));
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
                    'quantity'   => 'required|integer|min:1',
                    'qty_reminder'   => 'required|integer|min:1',
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
                    $actual_price = $normal_one->producttype_id == 1 ? $normal_one->PriceAfterDiscount() : $normal_one->holesale_price;
                    $normal_one->update(['actual_price' => $actual_price]);
                    $normal_one->update(['original_id' => $normal_one->id]);
                    $normal_one->tags()->sync($tags_arr);
                    $normal_one->car_model()->sync($models_arr);
                    $normal_one->allcategory()->sync($parents);
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
                    $request['discount'] = 0;
                    
                    $request['quantity'] = null;
                    $request['qty_reminder'] = null;

                    $request['holesale_price'] = $holesale_price;
                   // $request['actual_price']     = $holesale_price;
                    $request['no_of_orders']   = $no_of_orders;
                    $request['year_id'] = null;
                    $request['car_model_id'] = null;
                    // return $request->holesale_price;
                    $wholesale_one = Product::create($request->all());
                    $wholesale_one->update(['original_id' => $normal_one->id]);
                    $actual_price = $wholesale_one->producttype_id == 1 ? $wholesale_one->PriceAfterDiscount() : $wholesale_one->holesale_price;
                    $wholesale_one->update(['actual_price' => $actual_price]);
                    
                    $wholesale_one->tags()->sync($tags_arr);
                    $wholesale_one->car_model()->sync($models_arr);
                    $wholesale_one->allcategory()->sync($parents);
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
              // send admin email
        $admin = User::findOrFail(1);
        // Mail::to($admin->email)->send(new SendAdminProductApprovalMail($wholesale_one));
              return $this->showWithMedia($wholesale_one->id);
            } // end prod type = 3

             /*********************************************************/
            /********************* case type 3 ***************************/
    }

    public function show(Product $product, Request $request)
    {
      $lang = $this->getLang();
      abort_if(Gate::denies('product_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        return new SpecificProductApiResource($product);
    }

    public function showWithUpdated($id)
    {
        $product = Product::find($id);
        return response()->json([
                'status_code' => 200,
              //  'message' => 'success',
                'message' => __('site_messages.product_edited_successfully'),
                'data'    => new ProductResource($product),
              ], Response::HTTP_ACCEPTED);
    }

    public function update_v2(UpdateProductCopyV2ApiRequest $request, Product $product)
    {
      if (!$request->header('Accept-Language'))
        {
           return response()->json([
            'status_code' => 400,
            'errors'      => 'No Language header Found'], 400);
        }

      $lang = $this->getLang();
      $request['lang'] = $lang;
      $models_arr  = json_decode($request->models);
      $tags_arr    = json_decode($request->tags);

        $parents = json_decode($request->allcategory);
        $parent = $parents[0];
        $count = count($parents);
        $child = $parents[$count - 1];

      // start name and description validation
         if ($lang == 'ar') {
          $ar_v = Validator::make($request->all(), [
                'name'           => 'required|string',
                'description'    => 'required|string|min:2|max:255',
                'name_en'        => 'nullable|string',
                'description_en' => 'nullable|string|min:2|max:255',
              ]);
              if ($ar_v->fails()) {
                return response()->json(['errors' => $ar_v->errors()], 400);
              }
        }
        if ($lang == 'en') {
          $en_v = Validator::make($request->all(), [
                'name'        => 'nullable|string',
                'description' => 'nullable|string|min:2|max:255',
                'name_en'        => 'required|string',
                'description_en' => 'required|string|min:2|max:255',
              ]);
              if ($en_v->fails()) {
                return response()->json(['errors' => $en_v->errors()], 400);
              }
        }
        // end name and description validation

      $user       = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();

           // case logged in user role is Vendor (show only his invoices)
        if (in_array('Vendor', $user_roles)) 
        {
          $vendor = AddVendor::where('userid_id', Auth::user()->id)->first();
          $vendor_id = $vendor->id;
          $request['vendor_id'] = $vendor->id;
        }
        elseif (in_array('Manager', $user_roles) || in_array('Staff', $user_roles) ) 
       {
        // abort_if(Gate::denies('stores_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $exist_staff   = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
        $request['vendor_id'] = $vendor->id;
      //  $staff_stores  = $exist_staff->stores->pluck('id')->toArray();
      }else
      {
           return response()->json([
            'status_code' => 400,
            'errors'      => 'Not authorized'], 400);
      }



        $tags_arr  = json_decode($request->tags);
       
       // start discount validation
        if ($request->discount == '' || empty($request->discount) || !$request->has('discount'))
        {
             $request['discount'] = null;
        } 
        else{
              $v = Validator::make($request->all(), [
                'discount' => 'nullable|numeric|min:1|max:80',
              ]);
              if ($v->fails()) {
                return response()->json(['errors' => $v->errors()], 400);
              }
              $request['discount'] = $request->discount;
        }  // end discount validation

           // tyres validation 
           $parents = json_decode($request->allcategory);
            $need_attributes = Allcategory::whereIn('id', $parents)->pluck('need_attributes')->toArray();
            if (in_array(1, $need_attributes)) {
              $tyres_v = Validator::make($request->all(), [
                'width'  => 'required|numeric',
                'size'   => 'required|numeric',
                'height' => 'required|numeric',
              ]);
              if ($tyres_v->fails()) {
                return response()->json(['errors' => $tyres_v->errors()], 400);
              }
            } 
            // tyres validation 

            $exist_name = Product::where('original_id', '!=', $product->original_id)
                                ->where('name', $request->name)
                                ->whereNull('deleted_at')->first();

              $exist_serial = Product::where('original_id', '!=', $product->original_id)
                                  ->whereNull('deleted_at')
                                  ->where('serial_number', $request->serial_number)
                                  ->first();
              if ($exist_serial != null) {
                return response()->json([
                  'status_code' => 400 , 
                  'errors'      => 'this serial number has already been taken',
                ], 400);
              }

              $request['cartype_id']     = $parent;
              $request['allcategory_id'] = $child;

            /*********************************************************/
            /////////////***************************************///////////////
                ///////////     case type 3       //////
            if ($request->producttype_id == 3) 
            {
              if ($product->producttype_id == 1) 
              {
                $already_exist = Product::where('original_id', $product->original_id)
                                        ->where('producttype_id', 2)->first();
                if ($already_exist != null) {
                  return response()->json(['errors' => 'this product has wholesale price'], 400);
                }else{
                  $holesale_price_v = Validator::make($request->all(), [
                    'price' => 'required|numeric|min:1',
                    'holesale_price' => 'required|numeric|min:1',
                    'no_of_orders'   => 'required|integer|min:1',
                  ]);

                  if ($holesale_price_v->fails()) {
                    return response()->json([
                      'status_code' => 400,
                      'errors' => $holesale_price_v->errors()], 400);
                  }
                    $val = $product->serial_coding;
                    $const = strstr($val, '0');
                    $serial_coding = 'H'.$const;
                    $request['producttype_id'] = $product->producttype_id;
                   // $request['holesale_price'] = null;
                   // $request['no_of_orders']   = null;
                    $request['year_id'] = null;
                    $request['car_model_id'] = null;
                    $request['original_id'] = $product->original_id;
                    $request['vendor_id'] = $vendor->id;
                    $product->update($request->all());
                   
                    $product->tags()->sync($tags_arr);
                    $product->car_model()->sync($models_arr);
                    $product->allcategory()->sync($parents);

                    $actual_price = $product->producttype_id == 1 ? $product->PriceAfterDiscount() : $product->holesale_price;
                    $product->update(['actual_price' => $actual_price]);
                    $request['serial_coding'] = $serial_coding;
                    $request['producttype_id'] = 2;
                   
                    $request['holesale_price'] = $request->holesale_price;
                    $request['no_of_orders']   = $request->no_of_orders;
                    // return $request->holesale_price;
                    $request['discount'] = 0;
                    $request['quantity'] = null;
                    $request['qty_reminder'] = null;
                    $request['price'] = 0;
                    $request['original_id'] = $product->original_id;
                    $wholesale_one = Product::create($request->all());
                    $actual_price = $wholesale_one->producttype_id == 1 ? $wholesale_one->PriceAfterDiscount() : $wholesale_one->holesale_price;
                     $wholesale_one->update(['actual_price' => $wholesale_one->holesale_price]);
                   // added ahmed
                     $wholesale_one->tags()->sync($tags_arr);
                     $wholesale_one->car_model()->sync($models_arr);
                     $wholesale_one->allcategory()->sync($parents);

                    $product->update(['no_of_orders' => 0, 'holesale_price' => 0]);
                    // ahmedmodels
                 //   $product->car_model()->sync($models_arr);
                } // end else
                  /* new */
                  if ($request->has('photo') && $request->photo != '') {
                    foreach ($request->photo as $file) {
                        $path = Storage::disk('spaces')->putFile('products', $file);
                        Storage::disk('spaces')->setVisibility($path, 'public');
                        $url   = Storage::disk('spaces')->url($path);
                        $wholesale_one->addMediaFromUrl($url)
                                 ->toMediaCollection('photo');
                    }
                  }//else{
                      //$wholesale_one->photo = $product->photo;
                      // $wholesale_one->media = $product->media;
                     // $wholesale_one->save();
                    //  $item = $product->photo[0]->getUrl();
                      //$wholesale_one->addMediaFromUrl($item)
                              //   ->toMediaCollection('photo');
                   // }
                    /* new */
                    return $this->showWithUpdated($wholesale_one->id); 
              }
              if ($product->producttype_id == 2) 
              {
                $already_exist = Product::where('original_id', $product->original_id)
                                        ->where('producttype_id', 1)->first();
                if ($already_exist != null) {
                  return response()->json(['errors' => 'this product has normal price'], 400);
                }else
                {
                  $holesale_price_v = Validator::make($request->all(), [
                    'quantity'       => 'required|integer|min:1',
                    'qty_reminder' => 'required|integer|min:1',
                    'price'          => 'required|numeric|min:1',
                    'holesale_price' => 'required|numeric|min:1',
                    'no_of_orders'   => 'required|integer|min:1',
                  ]);

                  if ($holesale_price_v->fails()) {
                    return response()->json([
                      'status_code' => 400,
                      'errors' => $holesale_price_v->errors()], 400);
                  }
                    $val = $product->serial_coding;
                    $const = strstr($val, '0');
                    $serial_coding = 'N'.$const;
                    $request['producttype_id'] = $product->producttype_id;

                    $request['producttype_id'] = $product->producttype_id;
                    $request['holesale_price'] = $request->holesale_price;
                    $request['no_of_orders']   = $request->no_of_orders;
                    $request['original_id']    = $product->original_id;
                    $request['year_id'] = null;
                    $request['car_model_id'] = null;
                    $request['vendor_id'] = $vendor->id;

                    $product->update($request->all());

                    // added ahmed
                     $product->tags()->sync($tags_arr);
                     $product->car_model()->sync($models_arr);
                     $product->allcategory()->sync($parents);


                    $actual_price = $product->producttype_id == 1 ? $product->PriceAfterDiscount() : $product->holesale_price;
                    $product->update(['actual_price' => $request->holesale_price]);
                    $request['serial_coding']  = $serial_coding;
                    $request['producttype_id'] = 1;
                    $request['price']          = $request->price;
                    $request['holesale_price'] = 0;
                    $request['no_of_orders']   = 0;
                    // return $request->holesale_price;
                    $normal_one = Product::create($request->all());
                    $actual_price = $normal_one->producttype_id == 1 ? $normal_one->PriceAfterDiscount() : $normal_one->holesale_price;
                     $normal_one->update(['actual_price' => $actual_price]);
                    // added ahmed
                     $normal_one->tags()->sync($tags_arr);
                     $normal_one->car_model()->sync($models_arr);
                     $normal_one->allcategory()->sync($parents);

                    $product->update(['price' => 0]);
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
                    return $this->showWithUpdated($normal_one->id);  
              }
             }
            }  // end request type 3
            /////////////// case type 3 ////////////////////////

            /////////////***************************************///////////////
            /********************* case type 1 ***************************/
          
            if ($request->producttype_id == 1) 
            {
              $price_v = Validator::make($request->all(), [
                    'price' => 'required|numeric|min:1',
                    'quantity'       => 'required|integer|min:1',
                    'qty_reminder' => 'required|integer|min:1',
                  ]);

                  if ($price_v->fails()) {
                    return response()->json([
                      'status_code' => 400,
                      'errors' => $price_v->errors()], 400);
                  }
              if ($product->producttype_id == 1) 
              {
                 $request['price'] = $request->price;
                 $request['holesale_price'] = null;
                 $request['no_of_orders'] = null;
                  $request['year_id'] = null;
                  $request['car_model_id'] = null;
                  $product->update($request->all());
                  $actual_price = $product->producttype_id == 1 ? $product->PriceAfterDiscount() : $product->holesale_price;
                    $product->update(['actual_price' => $actual_price]);
                    
                    // added ahmed
                     $product->tags()->sync($tags_arr);
                     $product->car_model()->sync($models_arr);
                     $product->allcategory()->sync($parents);

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
                    return $this->showWithUpdated($product->id); 
              }
              if ($product->producttype_id == 2) 
              {
                $already_exist = Product::where('original_id', $product->original_id)
                                      ->where('producttype_id', 1)
                                      ->first();
                if ($already_exist != null) {
                  return response()->json(['errors' => 'this product has normal price'], 400);
                }
                $price_v = Validator::make($request->all(), [
                    'price' => 'required|numeric|min:1',
                    'quantity'  => 'required|integer|min:1',
                    'qty_reminder' => 'required|integer|min:1',
                  ]);

                  if ($price_v->fails()) {
                    return response()->json([
                      'status_code' => 400,
                      'errors' => $price_v->errors()], 400);
                  }
                    $val = $product->serial_coding;
                    $const = strstr($val, '0');
                    $serial_coding = 'N'.$const;
                    $request['price'] = $request->price;
                    $request['serial_coding'] = $serial_coding;

                  $request['serial_coding'] = $serial_coding;
                  $request['price'] = $request->price;
                  $request['holesale_price'] = 0;
                  // $request['quantity'] = 0;
                  $request['no_of_orders'] = 0;
                  $request['year_id'] = null;
                  $request['car_model_id'] = null;
                  $product->update($request->all());
                  $actual_price = $product->producttype_id == 1 ? $product->PriceAfterDiscount() : $product->holesale_price;
                    $product->update(['actual_price' => $actual_price]);
                  // added ahmed
                     $product->tags()->sync($tags_arr);
                     $product->car_model()->sync($models_arr);
                     $product->allcategory()->sync($parents);

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
                    return $this->showWithUpdated($product->id);  
              }
            }  // end request type 2

            if ($request->producttype_id == 2) 
            {
              $holesale_price_v = Validator::make($request->all(), [
                    'holesale_price' => 'required|numeric|min:1',
                    'no_of_orders'   => 'required|integer|min:1',
                  ]);

                  if ($holesale_price_v->fails()) {
                    return response()->json([
                      'status_code' => 400,
                      'errors' => $holesale_price_v->errors()], 400);
                  }
              if ($product->producttype_id == 1) 
              {
                $already_exist = Product::where('original_id', $product->original_id)
                                        ->where('producttype_id', 2)->first();
                if ($already_exist != null) {
                  return response()->json(['errors' => 'this product has wholesale price'], 400);
                }
                $val = $product->serial_coding;
                $const = strstr($val, '0');
                $serial_coding = 'H'.$const;
                $request['price'] = 0;
                $request['discount'] = 0;
                $request['quantity'] = null;
                $request['serial_coding'] = $serial_coding;
                $request['qty_reminder'] = null;
                $request['year_id'] = null;
                $request['car_model_id'] = null;

                  $product->update($request->all());
                  $actual_price = $product->producttype_id == 1 ? $product->PriceAfterDiscount() : $product->holesale_price;
                    $product->update(['actual_price' => $request->holesale_price]);
                  // added ahmed
                     $product->tags()->sync($tags_arr);
                     $product->car_model()->sync($models_arr);
                     $product->allcategory()->sync($parents);

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
                    return $this->showWithUpdated($product->id);  
              }
              if ($product->producttype_id == 2) 
              {
                 $request['price'] = 0;
                 $request['year_id'] = null;
                 $request['car_model_id'] = null;
                 $request['quantity'] = null;
                 $request['qty_reminder'] = null;
                 $request['discount'] = 0;
                 $request['price'] = 0;
                 $product->update($request->all());
                 $actual_price = $product->producttype_id == 1 ? $product->PriceAfterDiscount() : $product->holesale_price;
                    $product->update(['actual_price' => $actual_price]);
                  // added ahmed
                     $product->tags()->sync($tags_arr);
                     $product->car_model()->sync($models_arr);
                     $product->allcategory()->sync($parents);

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
                    return $this->showWithUpdated($product->id); 
              } // end prod type 2 
          }    // end request type 2
    }

    // start remove media checked
    public function remove_checked_media(RemoveSpecificProductCheckedMediaRequest $request)
    {
        abort_if(Gate::denies('product_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $arr          = json_decode($request->media_ids);
        $checkedMedia = Media::whereIn('id', $arr)
                            ->where('model_id', $request->product_id)->delete();
        //return response(null, Response::HTTP_NO_CONTENT);
        return response()->json([
                  'status_code' => 200,
                    'message'  => 'removed successfully',
                    'message' => __('site_messages.product_removed_successfully'),
                   ], 200);
    }

    public function mark_default_media(MarkDefaultProductImageApiRequest $request) // default
    {
      $user       = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();
      $media_id   = $request->media_id;
      $product_id = $request->product_id;
      $exist_product = Product::findOrFail($product_id);

      $media  = Media::where('id', $media_id)->where('model_id', $product_id)
                    ->first();
        if (!$media) {
          return response()->json([
                  'status_code' => 400,
                    'errors'  => 'wrong media selected',
                   ], 400);
        }
        if ($media) 
        {
            $exist_media = Media::where('id', $media_id)->where('model_id', $product_id)
                                ->take(1)->get();
            $model_name = $exist_media[0]->model_type;
            // return $model_name;
            if ($model_name != 'App\Models\Product') {
              return response()->json([
                        'status_code' => 400,
                          'errors'  => 'model name wrong',
                         ], 400);
            }
         
              if ($exist_product->default_media == $media_id) {
                return response()->json([
                        'status_code' => 400,
                          'errors'  => 'it is aleady default',
                         ], 400);
              }else{
                $exist_product->update(['default_media' => $media_id]);
                return response()->json([
                        'status_code' => 200,
                        'message'  => 'image marked as default successfully',
                        ], 200);
              } 
       }
    } // default

    public function destroy(Product $product)
    {
        abort_if(Gate::denies('product_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        if ($product->orderDetails->count() > 0 || $product->views->count() > 0 || $product->productreviews->count() > 0 ) {
               return response()->json([
                'errors' => 'this item is not empty te be deleted ('. $product->name. ' )',
                ], Response::HTTP_UNAUTHORIZED);
            }
        $product->delete();
        return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => null
          ], 200);
       // return response(null, Response::HTTP_NO_CONTENT);
    }

    // added new
    // get products media 
    public function storeMedia(StoreMediaRequest $request)
    {
        $product = Product::find($request->id);
        $medias  = $product->photo;
        return response()->json(['data' => $medias], Response::HTTP_OK);
    }
    // get products media 

     // start mass delete car years
     public function mass_delete(MassDestroyProductRequest $request)
     {
        $ids = json_decode($request->ids);
        foreach ($ids as $id) {
            $product = Product::findOrFail($id);
            // check for orders made with this product
            if ($product && $product->orderDetails->count() > 0) {
               return response()->json([
                'errors' => 'this item is not empty te be deleted ('. $product->name. ' )',
                ], Response::HTTP_UNAUTHORIZED);
            }
        }
        Product::whereIn('id', $ids)->delete();
        return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => null
          ], 200);
        // return response(null, Response::HTTP_NO_CONTENT);
     }
     // end mass delete car years

     // start list all
     public function list_all()
     {
        $lang = $this->getLang();
        $auth_user    = Auth::user();
        $auth_user_id = Auth::user()->id;
        $user_roles   = $auth_user->roles->pluck('title')->toArray();

        // case logged in user role is Admin 
      if (in_array('Admin', $user_roles)) {
            $data = Product::get();
            return response()->json([
            'status_code'     => 200,
            'message'         => 'success',
              'data' => $data,
            ], Response::HTTP_OK);
        } // end admin case
       // case logged in user role is Vendor
      elseif (in_array('Vendor', $user_roles)) {
            $vendor       = AddVendor::where('userid_id', Auth::user()->id)->first();
            $vendorId     = $vendor->id;

            $data = Product::where('vendor_id', $vendorId)->get();        
            return response()->json([
              'status_code'     => 200,
              'message'         => 'success',
              'data' => $data], Response::HTTP_OK);
      } // end case vendor
      elseif (in_array('Manager', $user_roles) || in_array('Staff', $user_roles) ) 
      {
            $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
       
              $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
              $vendor_id     = $vendor->id;
              $prods = Product::where('vendor_id', $vendor_id)
                      ->get();
             // $total = Product::where('vendor_id', $vendor_id)->count();
               return response()->json([
                'status_code' => 200,
                'message' => 'success',
                'data'  => $prods,
               // 'total' => $total,
              ], 200);
      }
      else{
        return response()->json([
                'message'  => 'un authorized access page due to permissions',
                'status_code'     => 401,
               ], 401);
      } // end else 
     }
     // end list all 
}
