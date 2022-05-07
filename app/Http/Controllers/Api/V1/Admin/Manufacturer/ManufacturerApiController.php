<?php

namespace App\Http\Controllers\Api\V1\Admin\Manufacturer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Gate;
use Symfony\Component\HttpFoundation\Response;
use Auth;
use App\Models\Manufacturer;
use App\Http\Resources\Admin\Manufacturer\ManufacturerApiResource;
use App\Http\Resources\Admin\Manufacturer\SpecificManufacturerApiResource;
use App\Http\Requests\Manufacturer\AddManufacturerApiRequest;
use App\Http\Requests\Manufacturer\UpdateManufacturerApiRequest;
use App\Http\Requests\SearchApisRequest;
use App\Http\Requests\Manufacturer\MassDestroyManufacturerRequest;

class ManufacturerApiController extends Controller
{
    public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }

    // start list all
     public function list_all()
     {
        $lang = $this->getLang();
       // $data = Manufacturer::get();
        $data = Manufacturer::all();
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
      abort_if(Gate::denies('manufacturers_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'DESC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;

      $user = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();

      // case logged in user role is Admin 
      if (in_array('Admin', $user_roles)) {
        $manufacturers = Manufacturer::skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                    ->orderBy($ordered_by, $sort_type)->get();
        $total = Manufacturer::count();                    
        $data          = ManufacturerApiResource::collection($manufacturers);
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
        $manufacturers = Manufacturer::skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                    ->orderBy($ordered_by, $sort_type)->get();
        $total = Manufacturer::count();
        $data          = ManufacturerApiResource::collection($manufacturers);
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

     // start add_manufacturers
     public function add_manufacturers(AddManufacturerApiRequest $request)
     {
         $lang = $this->getLang();
        $request['lang'] = $lang;
        $manufacturer = Manufacturer::create($request->all());
        $data = new SpecificManufacturerApiResource($manufacturer);
        return response()->json([
            'status_code' => 201,
            'message' => 'success',
            'data' => $data], Response::HTTP_CREATED);
     }
     // end add_manufacturers

     // start update
     public function update(UpdateManufacturerApiRequest $request, $id)
     {
         $lang = $this->getLang();
        $request['lang'] = $lang;
        $manufacturer = Manufacturer::findOrFail($id);
        $manufacturer->update($request->all());
        $data = new SpecificManufacturerApiResource($manufacturer);
        return response()->json([
            'status_code' => 202,
            'message' => 'success',
            'data' => $data], Response::HTTP_ACCEPTED);
     }
     // end update

     // start show
     public function show($id)
     {
         $lang = $this->getLang();
       // $request['lang'] = $lang;
        abort_if(Gate::denies('manufacturers_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $manufacturer = Manufacturer::findOrFail($id);
        $data = new SpecificManufacturerApiResource($manufacturer);
        return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data' => $data], Response::HTTP_OK);
     }
     // end show

     // start destroy
     public function destroy($id)
     {
        abort_if(Gate::denies('manufacturers_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $manufacturer = Manufacturer::findOrFail($id);
        if ($manufacturer->products->count() > 0) {
               return response()->json([
                'status_code'     => 401,
             //   'message'         => 'success',
                'errors' => 'this item is not empty te be deleted ('. $manufacturer->manufacturer_name. ' )',
                ], Response::HTTP_UNAUTHORIZED);
            }
        $manufacturer->delete();
        return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data' => null], Response::HTTP_OK);
     }
     // end destroy

     // start mass delete manufacturers
     public function mass_delete(MassDestroyManufacturerRequest $request)
     {
        $ids = json_decode($request->ids);
        foreach ($ids as $id) {
            $manufacturer = Manufacturer::findOrFail($id);
            if ($manufacturer->products->count() > 0) {
               return response()->json([
                'status_code'     => 401,
             //   'message'         => 'success',
                'errors' => 'this item is not empty te be deleted ('. $manufacturer->manufacturer_name. ' )',
                ], Response::HTTP_UNAUTHORIZED);
            }
        }
        Manufacturer::whereIn('id', $ids)->delete();
        return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => null
          ], 200);
        // return response(null, Response::HTTP_NO_CONTENT);
     }
     // end mass delete manufacturers 

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

        $manufacturers = Manufacturer::where('manufacturer_name', 'like', "%{$search_index}%")
                                ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                ->orderBy($ordered_by, $sort_type)->get();

        $total = Manufacturer::where('manufacturer_name', 'like', "%{$search_index}%")->count();
        $data  = ManufacturerApiResource::collection($manufacturers);
        
        return response()->json([
            'status_code' => 200,
            'message'     => 'success',
            'data'        => $data,
            'total'       => $total,
        ], Response::HTTP_OK);
     }
     // end search_with_name
}
