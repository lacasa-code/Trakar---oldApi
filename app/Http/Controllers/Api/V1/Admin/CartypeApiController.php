<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\Admin\CarTypes\CarTypeResource;
use App\Http\Resources\Admin\CarTypes\SpecificCarTypeResource;
use App\Http\Requests\Admin\CarTypes\StoreCarTypeApiRequest;
use App\Http\Requests\Admin\CarTypes\UpdateCarTypeApiRequest;
use App\Http\Requests\Admin\CarTypes\MassDestroyCarTypeRequest;
use App\Models\Cartype;
use Gate;
use Auth;
use App\Http\Requests\SearchApisRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\Storage;

class CartypeApiController extends Controller
{
  use MediaUploadingTrait;

    public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }
    public function index(Request $request)
    {
      $lang = $this->getLang();
      //return $lang;
      // $PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
       
      abort_if(Gate::denies('car_type_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $car_types = Cartype::skip(($page-1)*$PAGINATION_COUNT)
                            ->take($PAGINATION_COUNT)->orderBy($ordered_by, $sort_type)
                            ->get();
        $data = CarTypeResource::collection($car_types);

        return response()->json([
            'status_code'    => 200,
            'message'        => 'success',
            'data'           => $data,
            'total'          => Cartype::count()
        ], 200);
    }

    public function store(StoreCarTypeApiRequest $request)
    {
        $lang = $this->getLang();
        $request['lang'] = $lang;
        $carType = Cartype::create($request->all());

        // file data
        /* new */
        $image = $request->file('photo');
        $imageFileName = time() . '.' . $image->getClientOriginalExtension();
        $path = Storage::disk('spaces')->putFile('car-types', $image);
        Storage::disk('spaces')->setVisibility($path, 'public');
        $url   = Storage::disk('spaces')->url($path);
       // return $url;
        $carType->addMediaFromUrl($url)
                       ->toMediaCollection('photo');
        /* new */

        return response()->json([
          'status_code'   => 201,
          'message'       => 'success',
          'data'          => new CarTypeResource($carType),
        ], Response::HTTP_CREATED);

        /*return (new CarMadeResource($carMade))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);*/
    }

    public function show(Cartype $carType)
    {
        $lang = $this->getLang();
        abort_if(Gate::denies('car_type_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data' => [
              "id"            =>  $carType->id,
              "type_name"     =>  $carType->type_name,
              "lang"          =>  $carType->lang,
              "created_at"    =>  $carType->created_at,
              "updated_at"    =>  $carType->updated_at,
              "deleted_at"    =>  $carType->deleted_at,
          ]
        ], Response::HTTP_OK);
       // return new CarMadeResource($carMade->load(['categoryid']));
    }

    public function update(UpdateCarTypeApiRequest $request, $id)
    {
        $carType = Cartype::findOrFail($id);
        $lang = $this->getLang();
        $request['lang'] = $lang;
        $carType->update($request->all());

        // change media only on change of input request 
        if ($request->has('photo') && $request->photo != '') {
            if (!$carType->photo || $request->file('photo') !== $carType->photo->file_name) {
                if ($carType->photo) {
                    $carType->photo->delete();
                }
                    /* new */
                    $image = $request->file('photo');
                    $path = Storage::disk('spaces')->putFile('car-types', $image);
                    Storage::disk('spaces')->setVisibility($path, 'public');
                    $url   = Storage::disk('spaces')->url($path);
                     $carType->addMediaFromUrl($url)
                                     ->toMediaCollection('photo');
                    /* new */
            }
        } 

        return response()->json([
          'status_code'   => 202,
          'message'       => 'success',
          'data' => [
              "id"            =>  $carType->id,
              "lang"          =>  $carType->lang,
              "type_name"     =>  $carType->type_name,
              "photo"          =>  $carType->photo,
              "created_at"    =>  $carType->created_at,
              "updated_at"    =>  $carType->updated_at,
          ]
        ], Response::HTTP_ACCEPTED);

        //return (new CarMadeResource($carMade))
          //  ->response()
            //->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function destroy(Cartype $carType)
    {
        abort_if(Gate::denies('car_type_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
         if ($carType->products->count() > 0 || $carType->advertisements->count() > 0 || $carType->car_mades->count() > 0) {
               return response()->json([
                'status_code'     => 401,
             //   'message'         => 'success',
                'errors' => 'this item is not empty te be deleted ('. $carType->type_name. ' )',
                ], Response::HTTP_UNAUTHORIZED);
            }
            
            $carType->delete();
            return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => null
          ], 200);
           // return response(null, Response::HTTP_NO_CONTENT);
    }

     // start search car mades with name
     public function search_with_name(SearchApisRequest $request)
     {
      //$PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      
        $search_index = $request->search_index;
        $car_types = Cartype::where('type_name', 'like', "%{$search_index}%")
                                ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                ->orderBy($ordered_by, $sort_type)->get();

        $total = Cartype::where('type_name', 'like', "%{$search_index}%")
                                ->count();

        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
            'data'  => $car_types,
            'total' => $total,
        ], 200);
     }
    // end search car mades with name

     // start mass delete car mades
     public function mass_delete(MassDestroyCarTypeRequest $request)
     {
        $ids = json_decode($request->ids);
        foreach ($ids as $id) {
            $cartype = Cartype::findOrFail($id);
            if ($cartype->products->count() > 0 || $cartype->advertisements->count() > 0 || $cartype->car_mades->count() > 0) {
               return response()->json([
                'status_code'     => 401,
             //   'message'         => 'success',
                'errors' => 'this item is not empty te be deleted ('. $cartype->type_name. ' )',
                ], Response::HTTP_UNAUTHORIZED);
            }
        }
        Cartype::whereIn('id', $ids)->delete();
        return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => null
          ], 200);
        // return response(null, Response::HTTP_NO_CONTENT);
     }
     // end mass delete car mades 

      // start list all
     public function list_all()
     {
        $common = 7;
        $lang = $this->getLang();
        $data = Cartype::where('id', '!=', $common)->orderBy('type_name', 'ASC')->get();
        //$data = Cartype::orderBy('type_name', 'ASC')->get();
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data' => $data], Response::HTTP_OK);
     }
     // end list all 
}
