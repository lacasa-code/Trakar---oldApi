<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCarModelRequest;
use App\Http\Requests\UpdateCarModelRequest;
use App\Http\Resources\Admin\CarModelResource;
use App\Models\CarModel;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\SearchApisRequest;
use App\Http\Requests\MassDestroyCarModelRequest;
use App\Models\CarMade;

class CarModelApiController extends Controller
{
  public function getLang()
  {
      return $lang = \Config::get('app.locale');
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
       
        abort_if(Gate::denies('car_model_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
       // return new CarModelResource(CarModel::with(['carmade'])->paginate($PAGINATION_COUNT));
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
            'data'  => new CarModelResource(CarModel::with(['carmade'])->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)->orderBy($ordered_by, $sort_type)->get()),
            'total' => CarModel::count()
        ], 200);
    }

    public function store(StoreCarModelRequest $request)
    {
      $lang = $this->getLang();
      $request['lang'] = $lang;
      $exist_name = CarModel::where('carmodel', $request->carmodel)
                            ->where('carmade_id', $request->carmade_id)->first();
      if ($exist_name) {
        return response()->json([
          'status_code'   => 400,
          'errors'       => 'name already taken',
         // 'data'          => new CarModelResource($carModel),
        ], 400);
      }
        $carModel = CarModel::create($request->all());

        return response()->json([
          'status_code'   => 201,
          'message'       => 'success',
          'data'          => new CarModelResource($carModel),
        ], Response::HTTP_CREATED);
    }

    public function show(CarModel $carModel)
    {
      $lang = $this->getLang();
        abort_if(Gate::denies('car_model_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $data = new CarModelResource($carModel);
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data' => [
            "id"             => $carModel->id,
            "carmodel"       => $carModel->carmodel,
            'name_en'        => $carModel->name_en,
            "created_at"     => $carModel->created_at,
            "updated_at"     => $carModel->updated_at,
            "deleted_at"     => $carModel->deleted_at,
            "carmade_id"     => $carModel->carmade_id,
           // "carmadeName"    => $carModel->carmade->car_made,
            "carmade"        => $carModel->carmade,]
          ], Response::HTTP_OK);
     //   return new CarModelResource($carModel->load(['carmade']));
    }

    public function update(UpdateCarModelRequest $request, $id)
    {
      $lang = $this->getLang();
        $carModel = CarModel::findOrFail($id);
        $request['lang'] = $lang;

        $exist_name = CarModel::where('carmodel', $request->carmodel)
                            ->where('carmade_id', $request->carmade_id)
                            ->where('id', '!=', $carModel->id)->first();
        if ($exist_name) {
          return response()->json([
            'status_code'   => 400,
            'errors'       => 'name already taken',
           // 'data'          => new CarModelResource($carModel),
          ], 400);
        }
        
        $carModel->update($request->all());

        return response()->json([
          'status_code'   => 202,
          'message'       => 'success',
          'data'          => new CarModelResource($carModel),
        ], Response::HTTP_ACCEPTED);

        /*return (new CarModelResource($carModel))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);*/
    }

    public function destroy(CarModel $carModel)
    {
        abort_if(Gate::denies('car_model_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        if ($carModel->carModelProducts->count() > 0) {
           return response()->json([
            'status_code'   => 401,
            // 'message'       => 'success',
            'errors' => 'This car model is not empty to be deleted'], 
                                    Response::HTTP_UNAUTHORIZED);
        }
        else{
          $carModel->delete();
          return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => null
          ], 200);
            // return response(null, Response::HTTP_NO_CONTENT);
        }
    }

    // start search car models with name
     public function search_with_name(SearchApisRequest $request)
     {
     // $PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
     $default_count = \Config::get('constants.pagination.items_per_page');
     $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
     
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      
        $search_index = $request->search_index;
        $car_models = CarModel::where(function ($q) use ($search_index) {
                $q->where('carmodel', 'like', "%{$search_index}%")
                ->orWhere('name_en', 'like', "%{$search_index}%"); 
                })->orWhereHas('carmade', function($q) use ($search_index){
                                $q->where('car_made', 'like', "%{$search_index}%")
                                ->orWhere('name_en', 'like', "%{$search_index}%"); 
                })->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();

        foreach ($car_models as $value) {
           $value['carMadeName'] = $value->carmade->car_made;
        }

        $total = CarModel::where(function ($q) use ($search_index) {
                $q->where('carmodel', 'like', "%{$search_index}%");
                })->orWhereHas('carmade', function($q) use ($search_index){
                                $q->where('car_made', 'like', "%{$search_index}%")
                                ->orWhere('name_en', 'like', "%{$search_index}%"); 
                })->count(); 

        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
            'data'  => $car_models,
            'total' => $total,
        ], 200);
     }
    // end search car models with name

      // start mass delete car models
     public function mass_delete(MassDestroyCarModelRequest $request)
     {
        $ids = json_decode($request->ids);
        foreach ($ids as $id) {
            $carModel = CarModel::findOrFail($id);
            if ($carModel->carModelProducts->count() > 0) {
               return response()->json([
                'status_code'     => 401,
               // 'message'         => 'success',
                'errors' => 'this item is not empty te be deleted ('. $carModel->carmodel. ' )',
                ], Response::HTTP_UNAUTHORIZED);
            }
        }
        CarModel::whereIn('id', $ids)->delete();
        return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => null
          ], 200);
       // return response(null, Response::HTTP_NO_CONTENT);
     }
     // end mass delete car models 

      // start list all
     public function list_all($id)
     {
        $lang = $this->getLang();
        $car_made = CarMade::where('id', $id)->first();
      //  $car_made = CarMade::where('id', $id)->first();
        if (!$car_made) {
          return response()->json([
          'status_code'     => 400,
          'message'         => 'fail',
          'errors'          => 'wrong car made id',
          'data'            => null,], 400);
        }
        $data     = CarModel::where('carmade_id', $id)->get();
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data' => $data], Response::HTTP_OK);
     }
     // end list all 

    /* // start list all
     public function get_all()
     {
        $data     = CarModel::all();
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data' => $data], Response::HTTP_OK);
     }
     // end list all */
}
