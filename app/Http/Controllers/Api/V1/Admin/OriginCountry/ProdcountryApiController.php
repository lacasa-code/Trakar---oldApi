<?php

namespace App\Http\Controllers\Api\V1\Admin\OriginCountry;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Gate;
use Symfony\Component\HttpFoundation\Response;
use Auth;
use App\Models\Prodcountry;
use App\Http\Resources\Admin\OriginCountry\OriginCountryApiResource;
use App\Http\Resources\Admin\OriginCountry\SpecificOriginCountryApiResource;
use App\Http\Requests\OriginCountry\AddOriginCountryApiRequest;
use App\Http\Requests\OriginCountry\UpdateOriginCountryApiRequest;
use App\Http\Requests\SearchApisRequest;
use App\Http\Requests\OriginCountry\MassDestroyOriginCountryRequest;

class ProdcountryApiController extends Controller
{
    public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }

    // start list all
     public function list_all()
     {
        $lang = $this->getLang();
      //  $data = Prodcountry::get();
        $data = Prodcountry::all();
        return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data' => $data], Response::HTTP_OK);
     }
     // end list all 

     // start index
     public function index(Request $request)
     {
         $lang = $this->getLang();
      abort_if(Gate::denies('origin_countries_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'DESC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;

      $user = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();

      // case logged in user role is Admin 
      if (in_array('Admin', $user_roles)) {
        $manufacturers = Prodcountry::skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                    ->orderBy($ordered_by, $sort_type)->get();
        $total = Prodcountry::count();                    
        $data          = OriginCountryApiResource::collection($manufacturers);
        return response()->json([
            'status_code' => 200,
            'message'     => 'success',
            'data'        => $data,
            'total'       => $total], Response::HTTP_OK);
      } // end admin case
       // case logged in user role is Vendor 
      elseif (in_array('Vendor', $user_roles)) {
        // $vendor    = AddVendor::where('userid_id', Auth::user()->id)->first();
        // $vendor_id = $vendor->id;
        $origin_countries = Prodcountry::skip(($page-1)*$PAGINATION_COUNT)
                                    ->take($PAGINATION_COUNT)
                                    ->orderBy($ordered_by, $sort_type)->get();
        $total = Prodcountry::count();
        $data  = OriginCountryApiResource::collection($origin_countries);
        return response()->json([
            'status_code' => 200,
            'message'     => 'success',
            'data'        => $data,
            'total'       => $total], Response::HTTP_OK);
      } // end case vendor
      else{
        return response()->json([
                'status_code'     => 401,
              //  'message'         => 'success',
                'message'  => 'un authorized access page due to permissions',
               ], 401);
      }
    }
     // end index 

     // start show
     public function show($id)
     {
         $lang = $this->getLang();
       // $request['lang'] = $lang;
      abort_if(Gate::denies('origin_countries_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $origin_country = Prodcountry::findOrFail($id);
        $data = new SpecificOriginCountryApiResource($origin_country);
        return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data' => $data], Response::HTTP_OK);
     }
     // end show 

     // start add_origin_countries
     public function add_origin_countries(AddOriginCountryApiRequest $request)
     {
         $lang = $this->getLang();
        $request['lang'] = $lang;
        $origin_country = Prodcountry::create($request->all());
        $data = new SpecificOriginCountryApiResource($origin_country);
        return response()->json([
            'status_code' => 201,
            'message' => 'success',
            'data' => $data], Response::HTTP_CREATED);
     }
     // end add_origin_countries 

     // start update
     public function update(UpdateOriginCountryApiRequest $request, $id)
     {
         $lang = $this->getLang();
        $request['lang'] = $lang;
        $origin_country = Prodcountry::findOrFail($id);
        $origin_country->update($request->all());
        $data = new SpecificOriginCountryApiResource($origin_country);
        return response()->json([
            'status_code' => 202,
            'message' => 'success',
            'data' => $data], Response::HTTP_ACCEPTED);
     }
     // end update 

     // start destroy
     public function destroy($id)
     {
     abort_if(Gate::denies('origin_countries_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $origin_country = Prodcountry::findOrFail($id);
        if ($origin_country->products->count() > 0) {
               return response()->json([
                'status_code'     => 401,
             //   'message'         => 'success',
                'errors' => 'this item is not empty te be deleted ('. $origin_country->country_name. ' )',
                ], Response::HTTP_UNAUTHORIZED);
            }
        $origin_country->delete();
        return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data' => null], Response::HTTP_OK);
     }
     // end destroy 

     // start mass delete origin countries
     public function mass_delete(MassDestroyOriginCountryRequest $request)
     {
        $ids = json_decode($request->ids);
        foreach ($ids as $id) {
            $origin_country = Prodcountry::findOrFail($id);
            if ($origin_country->products->count() > 0) {
               return response()->json([
                'status_code'     => 401,
             //   'message'         => 'success',
                'errors' => 'this item is not empty te be deleted ('. $origin_country->country_name. ' )',
                ], Response::HTTP_UNAUTHORIZED);
            }
        }
        Prodcountry::whereIn('id', $ids)->delete();
        return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => null
          ], 200);
        // return response(null, Response::HTTP_NO_CONTENT);
     }
     // end mass delete origin countries 

     // start search_with_name
     public function search_with_name(SearchApisRequest $request)
     {
         $lang = $this->getLang();
       // $request['lang'] = $lang;
        $default_count = \Config::get('constants.pagination.items_per_page');
        $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
          
        $request->page == '' ? $page = 1 : $page = $request->page;
        $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
        $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
          
        $search_index = $request->search_index;

        $origin_countries = Prodcountry::where('country_name', 'like', "%{$search_index}%")
                                ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                ->orderBy($ordered_by, $sort_type)->get();

        $total = Prodcountry::where('country_name', 'like', "%{$search_index}%")->count();
        $data  = OriginCountryApiResource::collection($origin_countries);
        
        return response()->json([
            'status_code' => 200,
            'message'     => 'success',
            'data'        => $data,
            'total'       => $total,
        ], Response::HTTP_OK);
     }
     // end search_with_name
}
