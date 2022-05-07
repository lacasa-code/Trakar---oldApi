<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Store;
use Gate;
use App\Http\Requests\AddStoreApiRequest;
use App\Http\Requests\UpdateStoreApiRequest;
use App\Http\Requests\MassDestroyStoresApiRequest;
use App\Http\Requests\FetchVendorStoresListRequest;
use App\Http\Requests\SearchApisRequest;
use App\Http\Resources\Admin\StoreApiResource;
use Auth;
use App\Models\AddVendor;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\Admin\SpecificStoreApiResource;
use App\Models\Vendorstaff;
use Propaganistas\LaravelPhone\PhoneNumber;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use App\Models\Country;
use Validator;
use Illuminate\Validation\Rule;

class StoresApiController extends Controller
{
  public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }

    public function index(Request $request)
    {
      $lang = $this->getLang();
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'DESC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      //$PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
        
      $user = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();

      // case logged in user role is Admin 
      if (in_array('Admin', $user_roles)) {
        abort_if(Gate::denies('stores_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $stores = Store::skip(($page-1)*$PAGINATION_COUNT)
                                          ->take($PAGINATION_COUNT)
                                          ->orderBy($ordered_by, $sort_type)->get();
        $data = StoreApiResource::collection($stores);
       /* foreach ($data as $value) {
          foreach ($value as $item) {
            $item['vendor_name'] = $item->vendor->vendor_name;
          }
        }*/
        return response()->json([
            'status_code'     => 200,
            'message'         => 'success',
            'data'  => $data,
            'total' => Store::count()
        ], 200);
      } // end admin case
       // case logged in user role is Vendor 
      elseif (in_array('Vendor', $user_roles)) {
        abort_if(Gate::denies('stores_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
         $vendor    = AddVendor::where('userid_id', Auth::user()->id)->first();
         $vendor_id = $vendor->id;
         $stores = Store::where('vendor_id', $vendor_id)
                                          ->skip(($page-1)*$PAGINATION_COUNT)
                                          ->take($PAGINATION_COUNT)
                                          ->orderBy($ordered_by, $sort_type)->get();

         $data = StoreApiResource::collection($stores);
        /* foreach ($data as $value) {
          foreach ($value as $item) {
            $item['vendor_name'] = $item->vendor->vendor_name;
          } 
        }*/
        return response()->json([
            'status_code'     => 200,
            'message'         => 'success',
            'data'  => $data,
            'total' => Store::where('vendor_id', $vendor_id)->count(),
        ], 200);
      } // end case vendor
      elseif (in_array('Manager', $user_roles)) 
      {
        abort_if(Gate::denies('stores_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
       // abort_if(Gate::denies('stores_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
        $staff_stores = $exist_staff->stores->pluck('id')->toArray();
       // return $staff_stores;

         $stores = Store::where('vendor_id', $vendor_id)
                                          ->skip(($page-1)*$PAGINATION_COUNT)
                                          ->take($PAGINATION_COUNT)
                                          ->orderBy($ordered_by, $sort_type)->get();

         $data = StoreApiResource::collection($stores);
        /* foreach ($data as $value) {
          foreach ($value as $item) {
            $item['vendor_name'] = $item->vendor->vendor_name;
          } 
        }*/
        return response()->json([
            'status_code'     => 200,
            'message'         => 'success',
            'data'  => $data,
            'total' => Store::where('vendor_id', $vendor_id)->count(),
        ], 200);    
      }
      elseif (in_array('Staff', $user_roles) ) 
      {
       // abort_if(Gate::denies('stores_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
        $staff_stores = $exist_staff->stores->pluck('id')->toArray();
       // return $staff_stores;


              $stores = Store::where('vendor_id', $vendor_id)
                      ->whereIn('id', $staff_stores)
                      ->skip(($page-1)*$PAGINATION_COUNT)
                      ->take($PAGINATION_COUNT)->orderBy($ordered_by, $sort_type)
                      ->get();
              $data  = StoreApiResource::collection($stores);
              $total = Store::where('vendor_id', $vendor_id)
                      ->whereIn('id', $staff_stores)->count();
               return response()->json([
                'status_code' => 200,
                'message' => 'success',
                'data'  => $data,
                'total' => $total,
              ], 200);
      }
      else{
        return response()->json([
                'status_code'     => 401,
              //  'message'         => 'success',
                'message'  => 'un authorized access page due to permissions',
               ], 401);
      }
    }

    public function add_store(AddStoreApiRequest $request)
    {
      $lang = $this->getLang();
      $request['lang'] = $lang;
      $user = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();

        $country = Country::where('id', $request->country_id)->first();
        $country_code = $country->country_code;
        $recipient_phone = $request->moderator_phone;

      if (in_array('Vendor', $user_roles)) 
      {
         $vendor = AddVendor::where('userid_id', Auth::user()->id)->first();
     
          if ($vendor == null) {
          return response()->json([
            'status_code' => 400,
            'message' => 'fail',
            'errors' => 'this account has no vendors assigned yet',
          ], 400);
        }

        $uniqueName = Store::where('name', $request->name)->where('vendor_id', $vendor->id)->first();
          if ($uniqueName != null) {
            return response()->json([
                    'status_code' => 400, 
                    // 'message'     => 'success',
                    'errors'  => 'this store name has already been taken',
                   ], 400);
          }
        $vendor_id = $vendor->id;
        $uniqueResult = $this->isUniquePhone($vendor_id, $recipient_phone, $country_code);
        if (!$uniqueResult) 
        {
             return response()->json([
                'errors' => 'phone number already taken',
                'status_code' => 400,
             ], 400);
        }

        // check if phone starts with desired format(E.164) according to selected Country Code
            $checkResult = $this->check_e164Format($country_code, $recipient_phone);
 
            if($checkResult)  // if e.164 format fails
            {
                // return $checkResult;
                return response()->json([
                    'errors' => $checkResult,
                    'status_code' => 400,
                 ], 400);
            } else
            {    // if e.164 format pass
                // if phone number belongs to country available phone numbers
                $code_validator = Validator::make($request->all(), [
                    'moderator_phone'   => Rule::phone()->country([$country_code]),//->type('mobile'),
                     ]);
                 if($code_validator->fails()) {
                        return response()->json([
                            'errors' => 'Phone format does not match with country code',
                            'status_code' => 400,
                         ], 400);
                }
            } 

    	$request['vendor_id'] = $vendor->id;
      $request['moderator_name'] = 0;
      $latest_store = Store::withTrashed()->where('vendor_id', $vendor->id)->orderBy('created_at', 'desc')->limit(1)->first();
      // return $latest_store->id;
      if (!$latest_store) {
        // $latest_exist = Store::withTrashed()->latest()
        $serial_id = 'st_'.$request->name.'_ven_'.$vendor->vendor_name.'_IDNO_'.$vendor->id.'_001';
      }else{
        $serial_id = 'st_'.$request->name.'_ven_'.$vendor->vendor_name.'_IDNO_'.$vendor->id.'_00'.($latest_store->id + 1);
      }
      $request['serial_id'] = $serial_id;
      
      $store = Store::create($request->all());
      // $store['vendor_name'] = $store->vendor->vendor_name;
      
      $data = new SpecificStoreApiResource($store);
      return response()->json([
            'status_code'   => 201,
            'message'       => 'success',
            'data'          => $data
          ], Response::HTTP_CREATED);
     }
       if (in_array('Manager', $user_roles) ||  in_array('Staff', $user_roles) ) 
      {
        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
        $staff_stores = $exist_staff->stores->pluck('id')->toArray(); 

        if ($vendor == null) {
          return response()->json([
            'status_code' => 400,
            'message' => 'fail',
            'errors' => 'this account has no vendors assigned yet',
          ], 400);
        }

        $uniqueName = Store::where('name', $request->name)->where('vendor_id', $vendor->id)->first();

      if ($uniqueName != null) {
        return response()->json([
                'status_code' => 400, 
                // 'message'     => 'success',
                'errors'  => 'this store name has already been taken',
               ], 400);
      }
        $uniqueResult = $this->isUniquePhone($vendor_id, $recipient_phone, $country_code);
        if (!$uniqueResult) 
        {
             return response()->json([
                'errors' => 'phone number already taken',
                'status_code' => 400,
             ], 400);
        }

        // check if phone starts with desired format(E.164) according to selected Country Code
            $checkResult = $this->check_e164Format($country_code, $recipient_phone);
            if($checkResult)  // if e.164 format fails
            {
                // return $checkResult;
                return response()->json([
                    'errors' => $checkResult,
                    'status_code' => 400,
                 ], 400);
            } else
            {    // if e.164 format pass
                // if phone number belongs to country available phone numbers
                $code_validator = Validator::make($request->all(), [
                    'moderator_phone'   => Rule::phone()->country([$country_code]),//->type('mobile'),
                     ]);
                 if($code_validator->fails()) {
                        return response()->json([
                            'errors' => 'Phone format does not match with country code',
                            'status_code' => 400,
                         ], 400);
                }
            } 


      $request['vendor_id'] = $vendor->id;
      $request['moderator_name'] = 0;
      $latest_store = Store::withTrashed()->where('vendor_id', $vendor->id)->orderBy('created_at', 'desc')->limit(1)->first();
      // return $latest_store->id;
      if (!$latest_store) {
        // $latest_exist = Store::withTrashed()->latest()
        $serial_id = 'st_'.$request->name.'_ven_'.$vendor->vendor_name.'_IDNO_'.$vendor->id.'_001';
      }else{
        $serial_id = 'st_'.$request->name.'_ven_'.$vendor->vendor_name.'_IDNO_'.$vendor->id.'_00'.($latest_store->id + 1);
      }
      $request['serial_id'] = $serial_id;
      
      $store = Store::create($request->all());
      $data = new SpecificStoreApiResource($store);
      return response()->json([
            'status_code'   => 201,
            'message'       => 'success',
            'data'          => $data
          ], Response::HTTP_CREATED);
      }
    }

    public function show(Store $store)
    {
      $lang = $this->getLang();
      //$request['lang'] = $lang;
        abort_if(Gate::denies('stores_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        // $store['vendor_name'] = $store->vendor->vendor_name;
        return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => new SpecificStoreApiResource($store),
          ], Response::HTTP_OK); 
    }

     // === Check if phone already exists ===
    function isUniquePhone($vendor_id, $phone, $countryCode)
    {
        $db_format = PhoneNumber::make($phone, $countryCode)->formatE164();
        $unique    = Store::where('moderator_phone', $phone)->where('vendor_id', '!=', $vendor_id)->first();
        
        if($unique)
        {
            return false;
        }
        else
        {
            return true;
        }
    }
    //=== End Function ===

     // === Validate e164 format ===
    function check_e164Format($countryCode, $phone)
    {
        $item         = Country::where('country_code', $countryCode)//->select('phonecode')
                               ->first();
        $len          = strlen($item->phonecode);
        $phone_prefix = substr($phone, 1, $len);

        if ($phone_prefix == $item->phonecode) 
        {
          return false;
        }
        else
        {
          return 'number should start with +'.$item->phonecode;
        }
    }
    //=== End Function ===

    public function update(UpdateStoreApiRequest $request, Store $store)
    {
      $lang = $this->getLang();
      $request['lang'] = $lang;
    	//$vendor = AddVendor::where('userid_id', Auth::user()->id)->first();
      $user = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();

      if ( !in_array('Vendor', $user_roles) && !in_array('Manager', $user_roles) && !in_array('Staff', $user_roles) ) {
          return response()->json([
                'status_code' => 401, 
                // 'message'     => 'success',
                'message'  => 'un authorized access page due to permissions',
               ], 401);
      } 

      if ( in_array('Vendor', $user_roles) ) {
        $vendor = AddVendor::where('userid_id', Auth::user()->id)->first();
        $vendor_id     = $vendor->id;
      } 

      if ( in_array('Manager', $user_roles) || in_array('Staff', $user_roles) ) {
            $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
            $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
            $vendor_id     = $vendor->id;
      } 
     
          if ($vendor == null) {
          return response()->json([
            'status_code' => 400,
            'message' => 'fail',
            'errors' => 'this account has no vendors assigned yet',
          ], 400);
        }

        if ($vendor->id != $store->vendor_id) {
          return response()->json([
            'status_code' => 400,
            'message' => 'fail',
            'errors' => 'this store does not belong to you to edit',
          ], 400);
        }

        $unique_store = Store::where('vendor_id', $vendor->id)->where('name', $request->name)
                            ->where('id', '!=', $store->id)
                            ->whereNull('deleted_at')->first();
        if ($unique_store != null) {
          return response()->json([
            'status_code' => 400,
            'message' => 'fail',
            'errors' => 'name has already been taken for this vendor',
          ], 400);
        }

        $country = Country::where('id', $request->country_id)->first();
        $country_code = $country->country_code;
        // return $country_code;
        $recipient_phone = $request->moderator_phone;
        $vendor_id = $vendor->id;

        $uniqueResult = $this->isUniquePhone($vendor_id, $recipient_phone, $country_code);
        // if number already taken 
        if (!$uniqueResult) 
        {
             return response()->json([
                'errors' => 'phone number already taken',
                'status_code' => 400,
             ], 400);
        }

        // check if phone starts with desired format(E.164) according to selected Country Code
            $checkResult = $this->check_e164Format($country_code, $recipient_phone);
 
            if($checkResult)  // if e.164 format fails
            {
                // return $checkResult;
                return response()->json([
                    'errors' => $checkResult,
                    'status_code' => 400,
                 ], 400);
            } else
            {    // if e.164 format pass
                // if phone number belongs to country available phone numbers
                $code_validator = Validator::make($request->all(), [
                    'moderator_phone'   => Rule::phone()->country([$country_code]),//->type('mobile'),
                     ]);
                 if($code_validator->fails()) {
                        return response()->json([
                            'errors' => 'Phone format does not match with country code',
                            'status_code' => 400,
                         ], 400);
                }
            } 

    	  $request['vendor_id'] = $store->vendor_id;
        $request['moderator_name'] = 0;
        $store->update($request->all());
        // $store['vendor_name'] = $store->vendor->vendor_name;
        $data = new SpecificStoreApiResource($store);
        return response()->json([
            'status_code'   => 202,
            'message'       => 'success',
            'data'          => $data,
          ], Response::HTTP_ACCEPTED);
    }

    public function destroy(Store $store)
    {
        abort_if(Gate::denies('stores_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $user = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

      if (!in_array('Vendor', $user_roles)) {
          return response()->json([
                'status_code' => 401, 
                // 'message'     => 'success',
                'message'  => 'un authorized access page due to permissions',
               ], 401);
      } 

      $vendor = AddVendor::where('userid_id', Auth::user()->id)->first();
      
          if ($vendor == null) {
          return response()->json([
            'status_code' => 400,
            'message' => 'fail',
            'errors' => 'this account has no vendors assigned yet',
          ], 400);
        }

        if ($vendor->id != $store->vendor_id) {
          return response()->json([
            'status_code' => 400,
            'message' => 'fail',
            'errors' => 'this store does not belong to you to edit',
          ], 400);
        }

         if ($store->products->count() > 0 || $store->vendorstaff->count() > 0) {
               return response()->json([
                'status_code'   => 401,
                'message'       => __('site_messages.can_not_delete_store'),
               // 'errors' => 'this item is not empty te be deleted ('. $store->name. ' )',
                'errors' => __('site_messages.can_not_delete_store'),
                ], Response::HTTP_UNAUTHORIZED);
            }
        $store->delete();
        return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => null
          ], 200);
       // return response(null, Response::HTTP_NO_CONTENT);
    }

     // start search stores with name
     public function search_with_name(SearchApisRequest $request)
     {
      $lang = $this->getLang();
     // $request['lang'] = $lang;
      // $PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
        
        $search_index = $request->search_index;
        $user = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

        if (in_array('Admin', $user_roles)) {
          $stores = Store::where(function ($q) use ($search_index) {
                $q->where('name', 'like', "%{$search_index}%")
                  ->orWhere('address', 'like', "%{$search_index}%")
                  ->orWhere('lat', 'like', "%{$search_index}%")
                  ->orWhere('long', 'like', "%{$search_index}%")
                  ->orWhere('moderator_name', 'like', "%{$search_index}%")
                  ->orWhere('moderator_phone', 'like', "%{$search_index}%")
                  ->orWhere('moderator_alt_phone', 'like', "%{$search_index}%");
                })->orWhereHas('vendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })->orWhereHas('country', function($q) use ($search_index){
                                $q->where('country_name', 'like', "%{$search_index}%")
                                ->orWhere('country_code', 'like', "%{$search_index}%");
                })->orWhereHas('area', function($q) use ($search_index){
                                $q->where('area_name', 'like', "%{$search_index}%");
                })->orWhereHas('city', function($q) use ($search_index){
                                $q->where('city_name', 'like', "%{$search_index}%");
                })->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();
        foreach ($stores as $value) {
           $value['vendorname'] = $value->vendor->name;
        }

        $total = Store::where(function ($q) use ($search_index) {
                $q->where('name', 'like', "%{$search_index}%")
                  ->orWhere('address', 'like', "%{$search_index}%")
                  ->orWhere('lat', 'like', "%{$search_index}%")
                  ->orWhere('long', 'like', "%{$search_index}%")
                  ->orWhere('moderator_name', 'like', "%{$search_index}%")
                  ->orWhere('moderator_phone', 'like', "%{$search_index}%")
                  ->orWhere('moderator_alt_phone', 'like', "%{$search_index}%");
                })->orWhereHas('vendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })->orWhereHas('country', function($q) use ($search_index){
                                $q->where('country_name', 'like', "%{$search_index}%")
                                ->orWhere('country_code', 'like', "%{$search_index}%");
                })->orWhereHas('area', function($q) use ($search_index){
                                $q->where('area_name', 'like', "%{$search_index}%");
                })->orWhereHas('city', function($q) use ($search_index){
                                $q->where('city_name', 'like', "%{$search_index}%");
                })->count();

            return response()->json([
              'status_code' => 200,
               'message' => 'success',
                'data'  => $stores,
                'total' => $total,
            ], 200);
        } 
         // case logged in user role is Vendor (show only his invoices)
        elseif (in_array('Vendor', $user_roles)) {
                $vendor        = AddVendor::where('userid_id', Auth::user()->id)->first();
                $vendor_id     = $vendor->id;

                $stores = Store::where(function ($q) use ($search_index) {
                $q->where('name', 'like', "%{$search_index}%")
                  ->orWhere('address', 'like', "%{$search_index}%")
                  ->orWhere('lat', 'like', "%{$search_index}%")
                  ->orWhere('long', 'like', "%{$search_index}%")
                  ->orWhere('moderator_name', 'like', "%{$search_index}%")
                  ->orWhere('moderator_phone', 'like', "%{$search_index}%")
                  ->orWhere('moderator_alt_phone', 'like', "%{$search_index}%");
                })->orWhereHas('vendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })->orWhereHas('country', function($q) use ($search_index){
                                $q->where('country_name', 'like', "%{$search_index}%")
                                ->orWhere('country_code', 'like', "%{$search_index}%");
                })->orWhereHas('area', function($q) use ($search_index){
                                $q->where('area_name', 'like', "%{$search_index}%");
                })->orWhereHas('city', function($q) use ($search_index){
                                $q->where('city_name', 'like', "%{$search_index}%");
                })
                  ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();

            $stores =  $stores->where('vendor_id', $vendor_id);
            $total  = count($stores);

        foreach ($stores as $value) {
           $value['vendorname'] = $value->vendor->name;
        }

        /*$total = Store::where('vendor_id', $vendor_id)->where(function ($q) use ($search_index) {
                $q->where('name', 'like', "%{$search_index}%")
                  ->orWhere('address', 'like', "%{$search_index}%")
                  ->orWhere('lat', 'like', "%{$search_index}%")
                  ->orWhere('long', 'like', "%{$search_index}%")
                  ->orWhere('moderator_name', 'like', "%{$search_index}%")
                  ->orWhere('moderator_phone', 'like', "%{$search_index}%")
                  ->orWhere('moderator_alt_phone', 'like', "%{$search_index}%");
                })->orWhereHas('vendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })->orWhereHas('country', function($q) use ($search_index){
                                $q->where('country_name', 'like', "%{$search_index}%")
                                ->orWhere('country_code', 'like', "%{$search_index}%");
                })->orWhereHas('area', function($q) use ($search_index){
                                $q->where('area_name', 'like', "%{$search_index}%");
                })->orWhereHas('city', function($q) use ($search_index){
                                $q->where('city_name', 'like', "%{$search_index}%");
                })->count();*/

          $data = StoreApiResource::collection($stores);
        
            return response()->json([
              'status_code' => 200,
               'message' => 'success',
                'data'  => $data,
                'total' => $total,
            ], 200);
        }
      elseif (in_array('Manager', $user_roles)) 
      {
        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
        $staff_stores = $exist_staff->stores->pluck('id')->toArray();
          $stores = Store::where(function ($q) use ($search_index) {
                $q->where('name', 'like', "%{$search_index}%")
                  ->orWhere('address', 'like', "%{$search_index}%")
                  ->orWhere('lat', 'like', "%{$search_index}%")
                  ->orWhere('long', 'like', "%{$search_index}%")
                  ->orWhere('moderator_name', 'like', "%{$search_index}%")
                  ->orWhere('moderator_phone', 'like', "%{$search_index}%")
                  ->orWhere('moderator_alt_phone', 'like', "%{$search_index}%");
                })->orWhereHas('vendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })->orWhereHas('country', function($q) use ($search_index){
                                $q->where('country_name', 'like', "%{$search_index}%")
                                ->orWhere('country_code', 'like', "%{$search_index}%");
                })->orWhereHas('area', function($q) use ($search_index){
                                $q->where('area_name', 'like', "%{$search_index}%");
                })->orWhereHas('city', function($q) use ($search_index){
                                $q->where('city_name', 'like', "%{$search_index}%");
                })
                  ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();

            $stores =  $stores->where('vendor_id', $vendor_id);

        foreach ($stores as $value) {
           $value['vendorname'] = $value->vendor->name;
        }

        $total = Store::where(function ($q) use ($search_index) {
                $q->where('name', 'like', "%{$search_index}%")
                  ->orWhere('address', 'like', "%{$search_index}%")
                  ->orWhere('lat', 'like', "%{$search_index}%")
                  ->orWhere('long', 'like', "%{$search_index}%")
                  ->orWhere('moderator_name', 'like', "%{$search_index}%")
                  ->orWhere('moderator_phone', 'like', "%{$search_index}%")
                  ->orWhere('moderator_alt_phone', 'like', "%{$search_index}%");
                })->orWhereHas('vendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })->orWhereHas('country', function($q) use ($search_index){
                                $q->where('country_name', 'like', "%{$search_index}%")
                                ->orWhere('country_code', 'like', "%{$search_index}%");
                })->orWhereHas('area', function($q) use ($search_index){
                                $q->where('area_name', 'like', "%{$search_index}%");
                })->orWhereHas('city', function($q) use ($search_index){
                                $q->where('city_name', 'like', "%{$search_index}%");
                })->count();

          $data = StoreApiResource::collection($stores);
        
            return response()->json([
              'status_code' => 200,
               'message' => 'success',
                'data'  => $data,
                'total' => $total,
            ], 200);
      }
      elseif (in_array('Staff', $user_roles) ) 
      {
        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
        $staff_stores = $exist_staff->stores->pluck('id')->toArray();
          $stores = Store::where(function ($q) use ($search_index) {
                $q->where('name', 'like', "%{$search_index}%")
                  ->orWhere('address', 'like', "%{$search_index}%")
                  ->orWhere('lat', 'like', "%{$search_index}%")
                  ->orWhere('long', 'like', "%{$search_index}%")
                  ->orWhere('moderator_name', 'like', "%{$search_index}%")
                  ->orWhere('moderator_phone', 'like', "%{$search_index}%")
                  ->orWhere('moderator_alt_phone', 'like', "%{$search_index}%");
                })->orWhereHas('vendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })->orWhereHas('country', function($q) use ($search_index){
                                $q->where('country_name', 'like', "%{$search_index}%")
                                ->orWhere('country_code', 'like', "%{$search_index}%");
                })->orWhereHas('area', function($q) use ($search_index){
                                $q->where('area_name', 'like', "%{$search_index}%");
                })->orWhereHas('city', function($q) use ($search_index){
                                $q->where('city_name', 'like', "%{$search_index}%");
                })
                  ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();

            $stores =  $stores->where('vendor_id', $vendor_id)->whereIn('id', $staff_stores);

        foreach ($stores as $value) {
           $value['vendorname'] = $value->vendor->name;
        }

        $total = Store::whereIn('id', $staff_stores)->where(function ($q) use ($search_index) {
                $q->where('name', 'like', "%{$search_index}%")
                  ->orWhere('address', 'like', "%{$search_index}%")
                  ->orWhere('lat', 'like', "%{$search_index}%")
                  ->orWhere('long', 'like', "%{$search_index}%")
                  ->orWhere('moderator_name', 'like', "%{$search_index}%")
                  ->orWhere('moderator_phone', 'like', "%{$search_index}%")
                  ->orWhere('moderator_alt_phone', 'like', "%{$search_index}%");
                })->orWhereHas('vendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })->orWhereHas('country', function($q) use ($search_index){
                                $q->where('country_name', 'like', "%{$search_index}%")
                                ->orWhere('country_code', 'like', "%{$search_index}%");
                })->orWhereHas('area', function($q) use ($search_index){
                                $q->where('area_name', 'like', "%{$search_index}%");
                })->orWhereHas('city', function($q) use ($search_index){
                                $q->where('city_name', 'like', "%{$search_index}%");
                })->whereIn('id', $staff_stores)->count();

          $data = StoreApiResource::collection($stores);
        
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
                  // 'message'     => 'success',
                  'message'  => 'un authorized access page due to permissions',
                 ], 401);
        }     
     }
    // end search stores with name


      // start mass delete stores
     public function mass_delete(MassDestroyStoresApiRequest $request)
     {
        $ids = json_decode($request->ids);
        foreach ($ids as $id) {
            $store = Store::findOrFail($id);
            if ($store->products->count() > 0) {
               return response()->json([
                'status_code'   => 401,
                'message'       => 'fail',
              //  'errors' => 'this item is not empty te be deleted ('. $store->name. ' )',
                'errors' => __('site_messages.can_not_delete_store'),
                ], Response::HTTP_UNAUTHORIZED);
            }
        }
        Store::whereIn('id', $ids)->delete();
        return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => null
          ], 200);
        // return response(null, Response::HTTP_NO_CONTENT);
     }
     // end mass delete stores 

      // start list all
     public function list_all()
     {
        $lang = $this->getLang();
        $auth_user    = Auth::user();
        $auth_user_id = Auth::user()->id;
        $user_roles   = $auth_user->roles->pluck('title')->toArray();

        if (in_array('Admin', $user_roles)) {
         // $data = Store::where('lang', $lang)->get();
          $data = Store::all();
          return response()->json([
            'status_code'     => 200,
            'message'         => 'success',
            'data' => $data], Response::HTTP_OK);
        } // end admin case
         // case logged in user role is Vendor
        elseif (in_array('Vendor', $user_roles)) {
          $vendor = AddVendor::where('userid_id', Auth::user()->id)
                             ->select('id', 'vendor_name')->first();
          $vendor_id = $vendor->id;
         // $data = Store::where('lang', $lang)->where('vendor_id', $vendor_id)->get();
          $data = Store::where('vendor_id', $vendor_id)->where('head_center', '!=', 1)->get();
          return response()->json([
            'status_code'     => 200,
            'message'         => 'success',
            'data' => $data], Response::HTTP_OK);
         
        } // end case vendor
      elseif (in_array('Manager', $user_roles) || in_array('Staff', $user_roles) ) 
      {
       // abort_if(Gate::denies('stores_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
        $staff_stores = $exist_staff->stores->pluck('id')->toArray();
       // return $staff_stores;


             /* $stores = Store::where('lang', $lang)->where('vendor_id', $vendor_id)
                      ->whereIn('id', $staff_stores)
                      ->get();*/

              $stores = Store::where('vendor_id', $vendor_id)
              ->where('head_center', '!=', 1)
              ->whereIn('id', $staff_stores)
              ->get();
             
               return response()->json([
                'status_code' => 200,
                'message' => 'success',
                'data'  => $stores,
               // 'total' => $total,
              ], 200);
      }
        else{
          return response()->json([
                  'status_code' => 401,
                  // 'message' => 'success',
                  'message'  => 'un authorized access page due to permissions',
                 ], 401);
        } // end else 
      }
     // end list all 
}
