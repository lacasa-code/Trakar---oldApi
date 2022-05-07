<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\StoreApiAddVendorRequest;
use App\Http\Requests\UpdateAddVendorRequest;
use App\Http\Resources\Admin\AddVendorResource;
use App\Models\AddVendor;
use App\Models\Vendortype;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\StoreMediaVendorRequest;
use App\Http\Requests\StoreProductVendorRequest;
use App\Http\Resources\Admin\ProductResource;
use Auth;
use App\Models\Product;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use DB;
use App\Models\User;
use App\Http\Requests\SearchApisRequest;
use App\Http\Requests\MassDestroyAddVendorRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Admin\SpecificAddVendorApiResource;
use App\Http\Requests\Api\V1\Admin\AdminApproveVendorApiRequest;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendVendorDeclineMail;
use App\Mail\SendVendorRejectMail;
use App\Http\Requests\Api\V1\Admin\AdminRejectVendorApiRequest;
use App\Models\Rejectedvendor;
use App\Models\Rejectedreason;
use App\Mail\AdminApproveVendorMail;
use App\Models\Store;
// ahmed

class AddVendorApiController extends Controller
{
    use MediaUploadingTrait;

  public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }

  public function count_pending_vendors(Request $request)
  {
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      
      $pending_vendors = AddVendor::where('approved', '!=', 1)
                                          ->where('complete', 1)
                                          ->where('declined', 0)
                                          ->where('rejected', 0)
                                          //->whereMonth('created_at', '>=', $startDate_month)
                                         // ->whereMonth('created_at', '<=', $endDate_month)
                                          ->orderBy($ordered_by, $sort_type)
                                          ->get();

        return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data' => $pending_vendors,
            'total' => count($pending_vendors),
        ], 200);
  }

  public function decline_vendor(AdminApproveVendorApiRequest $request)
  {
      $vendor = AddVendor::findOrFail($request->vendor_id);
      $user   = User::findOrFail($vendor->userid_id);

      if ($vendor->complete != 1) {
        return response()->json([
            'status_code' => 400,
            'errors' => 'vendor profile not completed yet',
        ], 400);
      }
      if ($vendor->approved == 1) {
        return response()->json([
            'status_code' => 400,
            'errors' => 'vendor already approved',
        ], 400);
      }else{
        $name     = $vendor->vendor_name;
        $email    = $vendor->email;
        $role_id  = 2;
        $user_id  = $vendor->userid_id;

        $vendor->update(['declined' => 1]);
        Store::whereIn('vendor_id', [$vendor->id])->delete();
        // $vendor->stores->delete();
        $vendor->delete();

        $userTokens = User::where('id', $user_id)->first()->tokens;
        /* DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $accessToken->id)
            ->update([
                'revoked' => true
            ]); */

        foreach($userTokens as $token) {
           $token->revoke();   
        }

        $user->roles()->sync($role_id);
        Mail::to($email)->send(new SendVendorDeclineMail($name));
        
        return response()->json([
            'status_code' => 200,
            'message' => 'vendor got declined successfully with mail notification sent',
        ], 200);
      }
  }

  public function fileds_list(Request $request)
  {
      $reasons = Rejectedreason::all();
      return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data' => $reasons,
        ], 200);
  }

  public function reject_vendor(AdminRejectVendorApiRequest $request)
  {
      $vendor = AddVendor::findOrFail($request->vendor_id);
      if ($vendor->complete != 1) {
        return response()->json([
            'status_code' => 400,
            'errors' => 'vendor profile not completed yet',
        ], 400);
      }

      if ($vendor->declined == 1) {
        return response()->json([
            'status_code' => 400,
            'errors' => 'this vendor got declined earlier',
        ], 400);
      }

      if ($vendor->approved == 1) {
        return response()->json([
            'status_code' => 400,
            'errors' => 'vendor already approved',
        ], 400);
      }else{
        $reason = $request->reason;
        $name   = $vendor->vendor_name;
        $email  = $vendor->email;

        $fields = json_decode($request->commented_field);

        $rej_fields =  Rejectedreason::whereIn('id', $fields)->pluck('field');
      /*  foreach ($rej_fields as $rej_field) {
          return $rej_field;
        }*/
        $vendor->rejectedreason()->detach();
        $vendor->rejectedreason()->attach($fields, ['reason' => $request->reason]);
        
       /* foreach ($fields as $field) { 
            //collect all the ids 
            $pivot[$field] = ['reason' => $request->reason]; 
        } */
        //Insert into user table 
        // $user->groups()->sync($pivot);
        // $vendor->rejectedreason()->sync($pivot);

    
       Mail::to($email)->send(new SendVendorRejectMail($name, $reason, $rej_fields));
        $vendor->update(['rejected' => 1]);
        return response()->json([
            'status_code' => 200,
            'message' => 'Response has been sent',
        ], 200);
      }
  }

  public function approve_vendor(AdminApproveVendorApiRequest $request)
  {
      $vendor = AddVendor::findOrFail($request->vendor_id);
      if ($vendor->complete != 1) {
        return response()->json([
            'status_code' => 400,
            'errors' => 'vendor profile not completed yet',
        ], 400);
      }

      if ($vendor->approved == 1) {
        return response()->json([
            'status_code' => 400,
            'errors' => 'vendor already approved',
        ], 400);
      }else{
        $vendor->update(['approved' => 1]);
        $vendor->update(['rejected' => 0]);
        Mail::to($vendor->email)->send(new AdminApproveVendorMail($vendor->vendor_name));
        
        return response()->json([
            'status_code' => 200,
            'message' => 'vendor got approved successfully',
        ], 200);
      }
  }

    public function index(Request $request)
    {
        $lang = $this->getLang();
      //$PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'DESC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      
        abort_if(Gate::denies('add_vendor_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        // return new AddVendorResource(AddVendor::with(['userid'])->paginate($PAGINATION_COUNT));
        $data = AddVendor::with(['userid'])->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)->orderBy($ordered_by, $sort_type)->get();

      //  $add_v = AddVendor::find(2)->first();
        
        return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => new AddVendorResource($data),
            'total' => AddVendor::count(),
            //'files' => $add_v->getMedia('wholesaleDocs') == null ? null : $add_v->getMedia('wholesaleDocs')->pluck('id')->toArray(),
        ], 200);
    }

    public function get_vendor_userid_id(Request $request)
    {
        $authenticated_user = Auth::user();
        /*if ($authenticated_user->useridAddVendors->count() > 0){
            return response()->json(['data' => [
            $authenticated_user->id => $authenticated_user->name,
            ]], Response::HTTP_OK);
        }else{
            $userids = User::all()->pluck('name', 'id');
            return response()->json(['data' => $userids], Response::HTTP_OK);
        }*/
        // except users that have vendor
        $userids = User::whereDoesntHave('vendor')->whereHas('roles', function($q){
            $q->where('title', 'Vendor');
        })->pluck('name', 'id')->toArray();

        return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data' => $userids], Response::HTTP_OK);
    }

    public function edit_list($id)
    {
        $authenticated_user = Auth::user();
        $v = AddVendor::where('id', $id)->first();
        // except users that have vendor
        $userids = User::whereDoesntHave('vendor')->whereHas('roles', function($q){
            $q->where('title', 'Vendor');
        })->orWhere('id', '=', $v->userid_id)
        ->pluck('name', 'id');//->toArray();

        return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data' => $userids], Response::HTTP_OK);
    }

     public function showWithMedia($id)
    {
      $addVendor = AddVendor::find($id);
      return (new AddVendorResource($addVendor))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function store(StoreApiAddVendorRequest $request)
    {
        $lang = $this->getLang();
        $request['lang'] = $lang;

        $exist_user = User::findOrFail($request->userid_id);
        $user_roles = $exist_user->roles->pluck('title')->toArray();

        if (!$exist_user) {
             return response()->json([
              'status_code' => 400,
              'errors' => 'user not found'
           ], 400);
        }else{
            if ($exist_user->vendor != null) {
                return response()->json([
                  'status_code' => 400,
                  'errors' => 'invalid user to assign'
               ], 400);
            }
            
            if (!in_array('Vendor', $user_roles)) 
            {
                return response()->json([
                  'status_code' => 400,
                  'errors' => 'invalid user (not vendor) to assign'
               ], 400);
            }
        }
        $id = \DB::table('add_vendors')->latest('created_at')->first();
    
        if ($id === NULL) {
              if($request->type=='1')
            {
                $request['serial']='V001';
            }elseif ($request->type=='2')
            {
                $request['serial']='H001';
            }elseif ($request->type=='3')
            {
                $request['serial']='VH001';
            }
        }
        else{
                if($request->type=='1')
            {
                $request['serial']='V00'.($id->id + 1);
            }elseif ($request->type=='2')
            {
                $request['serial']='H00'.($id->id + 1);
            }elseif ($request->type=='3')
            {
                $request['serial']='VH00'.($id->id + 1);
            }
        }

        $request['email'] = $exist_user->email;

        $addVendor = AddVendor::create($request->all());
         /* new */
        $image = $request->file('images');
        // $imageFileName = time() . '.' . $image->getClientOriginalExtension();
        $path = Storage::disk('spaces')->putFile('add-vendors', $image);
        Storage::disk('spaces')->setVisibility($path, 'public');
        $url   = Storage::disk('spaces')->url($path);
        $addVendor->addMediaFromUrl($url)
                       ->toMediaCollection('images');
        /* new */

        /* new */
              $commercial_image = $request->file('commercialDocs');
              $tax_image = $request->file('taxCardDocs');
              $path1 = Storage::disk('spaces')
                      ->putFile('vendor-documents/commercial-documents', $commercial_image);
              $path2 = Storage::disk('spaces')->putFile('vendor-documents/tax-documents', $tax_image);
              Storage::disk('spaces')->setVisibility($path1, 'public');
              Storage::disk('spaces')->setVisibility($path2, 'public');
              $url1   = Storage::disk('spaces')->url($path1);
              $url2   = Storage::disk('spaces')->url($path2);
              $addVendor->addMediaFromUrl($url1)
                             ->toMediaCollection('commercialDocs');
              $addVendor->addMediaFromUrl($url2)
                             ->toMediaCollection('taxCardDocs');
              /* new */

        return $this->showWithMedia($addVendor->id);
    }

    public function show(AddVendor $addVendor)
    {
        $lang = $this->getLang();
        abort_if(Gate::denies('add_vendor_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        // return new AddVendorResource($addVendor->load(['userid']));
        
        $data = new SpecificAddVendorApiResource($addVendor);
        foreach ($data as $value) {
        $addVendor['gender'] = User::where('id', $addVendor->userid_id)->first()->gender;
        $addVendor['date_of_birth'] = User::where('id', $addVendor->userid_id)->first()->birthdate;
        $addVendor['phone'] = User::where('id', $addVendor->userid_id)->first()->phone_no;
        }
        return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => $data,
          ], 200);
    }

    public function showWithUpdated($id)
    {
        $addVendor = AddVendor::find($id);
        return (new AddVendorResource($addVendor))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function update(UpdateAddVendorRequest $request, AddVendor $addVendor)
    {
        $lang = $this->getLang();
        $request['lang'] = $lang;
        
        $exist_user = User::findOrFail($request->userid_id);
        $user_roles = $exist_user->roles->pluck('title')->toArray();

        if (!$exist_user) {
             return response()->json([
              'status_code' => 400,
              'errors' => 'user not found'
           ], 400);
        }
        else{
           /* if ( ($exist_user->vendor != null) && ($exist_user->id != $addVendor->userid_id) ){
                return response()->json([
                  'status_code' => 400,
                  'errors' => 'invalid user to assign'
               ], 400);
            }*/
            
            if (!in_array('Vendor', $user_roles)) 
            {
                return response()->json([
                  'status_code' => 400,
                  'errors' => 'invalid user (not vendor) to assign'
               ], 400);
            }
        }

        $unique_mail = User::where('id', '!=', $addVendor->userid_id)
                          // ->where('id', '!=', $request->userid_id)
                          ->where('email', $request->email)
                          ->first();
        if ($unique_mail != null) {
          return response()->json([
                  'status_code' => 400,
                  'errors' => 'email has already been taken'
               ], 400);
        }
        $addVendor->update($request->all());
        $exist_user->update(['email' => $addVendor->email]);
        // change media only on change of input request 
        if ($request->has('images') && $request->images != '') {
            if (!$addVendor->images || $request->file('images') !== $addVendor->images->file_name) {
                if ($addVendor->images) {
                    $addVendor->images->delete();
                }
                    /* new */
                    $image = $request->file('images');
                    $path = Storage::disk('spaces')->putFile('add-vendors', $image);
                    Storage::disk('spaces')->setVisibility($path, 'public');
                    $url   = Storage::disk('spaces')->url($path);
                    $addVendor->addMediaFromUrl($url)
                                     ->toMediaCollection('images');
                    /* new */
            }
        } 

        /* new */
              if ($request->has('commercialDocs') && $request->commercialDocs != '') 
              {
                $commercial_image = $request->file('commercialDocs');
                $path1 = Storage::disk('spaces')
                      ->putFile('vendor-documents/commercial-documents', $commercial_image);
                 Storage::disk('spaces')->setVisibility($path1, 'public');
                 $url1   = Storage::disk('spaces')->url($path1);
                 $addVendor->addMediaFromUrl($url1)
                             ->toMediaCollection('commercialDocs');
              }

              if ($request->has('taxCardDocs') && $request->taxCardDocs != '') 
              {
                  $tax_image = $request->file('taxCardDocs');
                  $path2 = Storage::disk('spaces')->putFile('vendor-documents/tax-documents', $tax_image);
                  Storage::disk('spaces')->setVisibility($path2, 'public');
                  $url2   = Storage::disk('spaces')->url($path2);
                  $addVendor->addMediaFromUrl($url2)
                                 ->toMediaCollection('taxCardDocs');
              }
              /* new */
        return $this->showWithUpdated($addVendor->id);
    }

    public function destroy(AddVendor $addVendor)
    {
        abort_if(Gate::denies('add_vendor_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user = User::where('id', $addVendor->userid_id)->first();
        if (count($user->orders)) 
        {
                 return response()->json([
                  'errors' => 'this item is not empty te be deleted ('. $user->name. ' )',
                  ], Response::HTTP_UNAUTHORIZED);
        }

         if ($addVendor->products->count() > 0 || $addVendor->stores->count() > 0 || $addVendor->orderDetails->count() > 0 || $addVendor->tickets->count() > 0 ) { 
               return response()->json([
                'status_code' => 401,
                'message' => 'fail',
                'errors' => 'this item is not empty te be deleted ('. $addVendor->vendor_name. ' )',
                ], Response::HTTP_UNAUTHORIZED);
            }

          User::where('id', $addVendor->userid_id)->delete();
          $addVendor->delete();

        return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => null
          ], 200);
        // return response(null, Response::HTTP_NO_CONTENT);
    }

    // added new
    // get products media 
    public function storeMedia(StoreMediaVendorRequest $request)
    {
        $vendor = AddVendor::find($request->id);
        $medias  = $vendor->images;
        return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data' => $medias], Response::HTTP_OK);
    }
    // get products media

    // start add products 
  /*  public function add_products(StoreProductVendorRequest $request)
    {
        // return $request->photo;
        abort_if(Gate::denies('add_vendor_add_products'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user_id   = Auth::user()->id;
      //  return count(Auth::user()->useridAddVendors);
        if (count(Auth::user()->useridAddVendors) <= 0) {
           return response()->json([
            'errors' => 'no vendors belongs to this user yet'
            ], Response::HTTP_UNAUTHORIZED);
        }

         $product = Product::create($request->all());
        $product->categories()->sync($request->input('categories', []));

       foreach ($request->file('photo', []) as $file) {
            // $name = $file->getClientOriginalName();
            $product->addMedia($file)
                    ->toMediaCollection('photo');
        }
    }*/
    // end add products 

    // start get vendor products 
    public function get_vendor_products()
    {
        abort_if(Gate::denies('add_vendor_access_products'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user_id  = Auth::user()->id;
        return new ProductResource(Product::where('vendor_id', $user_id)->with(['categories', 'car_made', 'car_model', 'year', 'part_category', 'vendor'])->get());
    }
    // end get vendor products 

    // start get vendor types 
    public function get_vendor_types()
    {
       /* $vendor_types = array([
            '1' => 'vendor',
            '2' => 'hot sale',
            '3' => 'Both',
        ]);*/
        $vendor_types = Vendortype::get();
        return response()->json([
                'status_code' => 200,
                'message' => 'success',
                'data' => $vendor_types
              ], Response::HTTP_OK);
    }
    // end get vendor types 

     // start search vendors with name
     public function search_with_name(SearchApisRequest $request)
     {
        $lang = $this->getLang();
       // $request['lang'] = $lang;
      //$PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      
        $search_index = $request->search_index;
        $vendors = AddVendor::where(function ($q) use ($search_index) {
                $q->where('vendor_name', 'like', "%{$search_index}%")
                  ->orWhere('email', 'like', "%{$search_index}%")
                  ->orWhere('serial', 'like', "%{$search_index}%");
                })->orWhereHas('userid', function($q) use ($search_index){
                                $q->where('name', 'like', "%{$search_index}%");
                })->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();

        foreach ($vendors as $value) {
           $value['userName'] = $value->userid->name;
        }

        $total = AddVendor::where(function ($q) use ($search_index) {
                $q->where('vendor_name', 'like', "%{$search_index}%")
                  ->orWhere('email', 'like', "%{$search_index}%")
                  ->orWhere('serial', 'like', "%{$search_index}%");
                })->orWhereHas('userid', function($q) use ($search_index){
                                $q->where('name', 'like', "%{$search_index}%");
                })->count();

        return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $vendors,
            'total' => $total,
        ], 200);
     }
    // end search vendors with name

     // start mass delete vendors
     public function mass_delete(MassDestroyAddVendorRequest $request)
     {
        $ids = json_decode($request->ids);
        foreach ($ids as $id) {
            $addVendor = AddVendor::findOrFail($id);
            if ($addVendor->products->count() > 0 || $addVendor->stores->count() > 0 || $addVendor->orderDetails->count() > 0 || $addVendor->tickets->count() > 0 ) { 
               return response()->json([
                'status_code' => 401,
                'message' => 'fail',
                'errors' => 'this item is not empty te be deleted ('. $addVendor->vendor_name. ' )',
                ], Response::HTTP_UNAUTHORIZED);
            }
        }
        AddVendor::whereIn('id', $ids)->delete();
        return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => null
          ], 200);
        // return response(null, Response::HTTP_NO_CONTENT);
     }
     // end mass delete vendors 

     // start list all
     public function list_all()
     {
        $lang = $this->getLang();
        $auth_user    = Auth::user();
        $auth_user_id = Auth::user()->id;
        $user_roles   = $auth_user->roles->pluck('title')->toArray();

        // case logged in user role is Admin 
      if (in_array('Admin', $user_roles)) {
           // $data = AddVendor::where('lang', $lang)->get();
            $data = AddVendor::all();
            return response()->json([
            'status_code'     => 200,
            'message'         => 'success',
            'data'            => $data,
            ], Response::HTTP_OK);
        } // end admin case
       // case logged in user role is Vendor
      elseif (in_array('Vendor', $user_roles)) {
            
      } // end case vendor
      else{
        return response()->json([
                'message'  => 'un authorized access page due to permissions',
                'status_code'     => 401,
               ], 401);
      } // end else 
     }
     // end list all 
}