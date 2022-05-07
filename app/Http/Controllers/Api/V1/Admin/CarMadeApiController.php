<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCarMadeRequest;
use App\Http\Requests\UpdateCarMadeRequest;
use App\Http\Resources\Admin\CarMadeResource;
use App\Models\CarMade;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\SearchApisRequest;
use App\Http\Requests\MassDestroyCarMadeRequest;
use App\Models\Cartype;
use App\Models\Allcategory;

class CarMadeApiController extends Controller
{
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
      $request->sort_type == '' ? $sort_type = 'DESC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
       
      abort_if(Gate::denies('car_made_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        // return new CarMadeResource(CarMade::with(['categoryid'])->paginate($PAGINATION_COUNT));
        $data = new CarMadeResource(CarMade::with(['car_type'])->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)->orderBy($ordered_by, $sort_type)->get());
       // return $data[1]->id;
        /*foreach ($data as $item) {
            foreach ($item as $value) {
                $value['catName'] = $value->categoryid->name;
            }
        }*/

        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
            'data'  => $data,
            'total' => CarMade::count()
        ], 200);
    }

    public function store(StoreCarMadeRequest $request)
    {
      $lang = $this->getLang();
      $request['lang'] = $lang;

        $exist_name = CarMade::where('car_made', $request->car_made)
                            ->where('cartype_id', $request->cartype_id)->first();
        if ($exist_name) {
          return response()->json([
            'status_code'   => 400,
            'errors'       => 'name already taken',
           // 'data'          => new CarModelResource($carModel),
          ], 400);
        }
        $carMade = CarMade::create($request->all());

        return response()->json([
          'status_code'   => 201,
          'message'       => 'success',
          'data'          => new CarMadeResource($carMade),
        ], Response::HTTP_CREATED);

        /*return (new CarMadeResource($carMade))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);*/
    }

    public function show(CarMade $carMade)
    {
      $lang = $this->getLang();
        abort_if(Gate::denies('car_made_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data' => [
             "id"             =>  $carMade->id,
              "car_made"      =>  $carMade->car_made,
              "name_en"       =>  $carMade->name_en,
              "created_at"    =>  $carMade->created_at,
              "updated_at"    =>  $carMade->updated_at,
              "deleted_at"    =>  $carMade->deleted_at,
              "categoryid_id" =>  $carMade->categoryid_id,
              "cartype_id"    => $carMade->car_type,
          ]
        ], Response::HTTP_OK);
       // return new CarMadeResource($carMade->load(['categoryid']));
    }

    public function update(UpdateCarMadeRequest $request, $id)
    {
        $carMade = CarMade::findOrFail($id);
        $lang = $this->getLang();
        $request['lang'] = $lang;

        // unique insite its car type

        $exist_name = CarMade::where('car_made', $request->car_made)
                            ->where('cartype_id', $request->cartype_id)
                            ->where('id', '!=', $carMade->id)->first();
        if ($exist_name) {
          return response()->json([
            'status_code'   => 400,
            'errors'       => 'name already taken',
           // 'data'          => new CarModelResource($carModel),
          ], 400);
        }

        $carMade->update($request->all());

        return response()->json([
          'status_code'   => 202,
          'message'       => 'success',
          'data' => [
             "id"             =>  $carMade->id,
             "lang"           =>  $carMade->lang,
              "car_made"      =>  $carMade->car_made,
              "created_at"    =>  $carMade->created_at,
              "updated_at"    =>  $carMade->updated_at,
              "deleted_at"    =>  $carMade->deleted_at,
              "cartype_id" =>  $carMade->car_type,
             // "categoryid"    => $carMade->categoryid,
          ]
        ], Response::HTTP_ACCEPTED);

        //return (new CarMadeResource($carMade))
          //  ->response()
            //->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function destroy(CarMade $carMade)
    {
        abort_if(Gate::denies('car_made_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
         if ($carMade->carmadeCarModels->count() > 0 || $carMade->carMadeProducts->count() > 0) {
               return response()->json([
                'status_code'     => 401,
             //   'message'         => 'success',
                'errors' => 'this item is not empty te be deleted ('. $carMade->car_made. ' )',
                ], Response::HTTP_UNAUTHORIZED);
            }
            
            $carMade->delete();
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
        $car_mades = CarMade::where(function ($q) use ($search_index) {
                $q->where('car_made', 'like', "%{$search_index}%")
                  ->orWhere('name_en', 'like', "%{$search_index}%"); 
            })->orWhereHas('car_type', function($q) use ($search_index){
                                $q->where('name', 'like', "%{$search_index}%")
                                ->orWhere('name_en', 'like', "%{$search_index}%");
                })->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                    ->orderBy($ordered_by, $sort_type)->get();

        foreach ($car_mades as $value) {
           $value['cartype_name'] = $value->car_type->name;
           $value['cartype_name_en'] = $value->car_type->name_en;
        }

        $total = count($car_mades);

        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
            'data'  => $car_mades,
            'total' => $total,
        ], 200);
     }
    // end search car mades with name

     // start mass delete car mades
     public function mass_delete(MassDestroyCarMadeRequest $request)
     {
        $ids = json_decode($request->ids);
        foreach ($ids as $id) {
            $carMade = CarMade::findOrFail($id);
            if ($carMade->carmadeCarModels->count() > 0 || $carMade->carMadeProducts->count() > 0) {
               return response()->json([
                'status_code'     => 401,
             //   'message'         => 'success',
                'errors' => 'this item is not empty te be deleted ('. $carMade->car_made. ' )',
                ], Response::HTTP_UNAUTHORIZED);
            }
        }
        CarMade::whereIn('id', $ids)->delete();
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
        $lang = $this->getLang();
        //$data = CarMade::get();
        $data = CarMade::get();
        // $data = Allcategory::whereNull('allcategory_id')->get();
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data' => $data], Response::HTTP_OK);
     }
     // end list all 

     // start list all
     public function list_all_related($id)
     {
        $lang = $this->getLang();
        //$car_type = Cartype::where('id', $id)->first();
        $car_type = Allcategory::where('id', $id)->first();
        if (!$car_type) {
          return response()->json([
          'status_code'     => 400,
          'message'         => 'fail',
          'errors'          => 'wrong car type id',
          'data'            => null,], 400);
        }
       // $data     = CarMade::where('cartype_id', $id)->get();
        $data     = CarMade::where('cartype_id', $id)->get();
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data' => $data], Response::HTTP_OK);
     }
     // end list all 

     
}
