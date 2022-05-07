<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCarYearRequest;
use App\Http\Requests\UpdateCarYearRequest;
use App\Http\Resources\Admin\CarYearResource;
use App\Models\CarYear;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\SearchApisRequest;
use App\Http\Requests\MassDestroyCarYearRequest;

use App\Http\Controllers\Traits\MediaUploadingTrait;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\Storage;
use App\Models\Allcategory;

class CarYearApiController extends Controller
{
  use MediaUploadingTrait;

  public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }

    public function index(Request $request)
    {
      $lang = $this->getLang();
     // $PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
       
        abort_if(Gate::denies('car_year_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        // return new CarYearResource(CarYear::paginate($PAGINATION_COUNT));
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
            'data'  => new CarYearResource(CarYear::skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)->orderBy($ordered_by, $sort_type)->get()),
            'total' => CarYear::count(),
        ], 200);
    }

    public function store(StoreCarYearRequest $request)
    {
      $lang = $this->getLang();
      $request['lang'] = $lang;
        $carYear = CarYear::create($request->all());

        return response()->json([
          'status_code'   => 201,
          'message'       => 'success',
          'data'          => new CarYearResource($carYear)
        ], Response::HTTP_CREATED);
    }

    public function show(CarYear $carYear)
    {
      $lang = $this->getLang();
        abort_if(Gate::denies('car_year_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data' => [
              "id"            =>  $carYear->id,
              'year'          => $carYear->year,
              "updated_at"    =>  $carYear->updated_at,
              "created_at"    =>  $carYear->deleted_at,
             // "cate"            =>  $carYear->categoryid,
          ]
        ], Response::HTTP_OK);
        //return new CarYearResource($carYear);
    }

    public function update(UpdateCarYearRequest $request, $id)
    {
        $carYear = CarYear::findOrFail($id);
        $lang = $this->getLang();
        $request['lang'] = $lang;
        $carYear->update($request->all());

        return response()->json([
          'status_code'   => 202,
          'message'       => 'success',
          'data'          => new CarYearResource($carYear)
        ], Response::HTTP_ACCEPTED);
    }

    public function destroy(CarYear $carYear)
    {
        abort_if(Gate::denies('car_year_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        if ($carYear->yearProducts->count() > 0) {
           return response()->json([
            'status_code'   => 401,
            // 'message'       => 'success',
            'errors' => 'This car year is not empty to be deleted'], 
                                    Response::HTTP_UNAUTHORIZED);
        }
        else{
            $carYear->delete();
            return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => null
          ], 200);
           //return response(null, Response::HTTP_NO_CONTENT);
        }
    }

    // start search car years with name
     public function search_with_name(SearchApisRequest $request)
     {
     // $PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      
        $search_index = $request->search_index;
        $car_years = CarYear::where(function ($q) use ($search_index) {
                $q->where('year', 'like', "%{$search_index}%");
            })->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
              ->orderBy($ordered_by, $sort_type)->get();
        $total = CarYear::where(function ($q) use ($search_index) {
                $q->where('year', 'like', "%{$search_index}%");
            })->count();
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
            'data'  => $car_years,
            'total' => $total,
        ], 200);
     }
    // end search car years with name

    // start mass delete car years
     public function mass_delete(MassDestroyCarYearRequest $request)
     {
        $ids = json_decode($request->ids);
        foreach ($ids as $id) {
            $carYear = CarYear::findOrFail($id);
            if ($carYear && $carYear->yearProducts->count() > 0) {
               return response()->json([
                'status_code'     => 401,
             //   'message'         => 'success',
                'errors' => 'this item is not empty te be deleted ('. $carYear->year. ' )',
                ], Response::HTTP_UNAUTHORIZED);
            }
        }
        CarYear::whereIn('id', $ids)->delete();
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
        $data = CarYear::orderBy('year', 'ASC')->get();
        //$data = CarYear::orderBy('year', 'ASC')->get();
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data' => $data], Response::HTTP_OK);
     }
     // end list all 

     // start list all
     public function logos(Request $request)
     {
        $lang = $this->getLang();
        $id = $request->id;
        $allcategories = Allcategory::where('allcategory_id', $id)->get();
              // $image = $request->file('photo');
              $images = $request->photo;
              foreach ($allcategories as $key => $allcategory) {
                  //  $imageFileName = time() . '.' . $images[$key]->getClientOriginalExtension();
                    $path = Storage::disk('spaces')->putFile('all-categories', $images[$key]);
                    Storage::disk('spaces')->setVisibility($path, 'public');
                    $url   = Storage::disk('spaces')->url($path);
                 // return $url;
                    $allcategory->addMediaFromUrl($url)
                                 ->toMediaCollection('photo');
              }

                             return response()->json([
                                    'status_code'     => 200,
                                    'message'         => 'success',
                                    'data' => 'done'], 200);
     }// end list all 
     
}
