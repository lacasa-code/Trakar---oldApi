<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\City;
use App\Models\Country;
use App\Models\Area;
use Gate;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\SearchApisRequest;

use App\Http\Requests\Api\V1\Admin\Areas\AddAreaApiRequest;
use App\Http\Requests\Api\V1\Admin\Areas\UpdateAreaApiRequest;
use App\Http\Requests\Api\V1\Admin\Areas\MassDestroyAreaRequest;

use App\Http\Resources\Api\V1\Admin\Areas\AreasApiResource;
use App\Http\Resources\Api\V1\Admin\Areas\SingleAreasApiResource;

class AreaApiController extends Controller
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
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
       
        abort_if(Gate::denies('areas_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $items = Area::skip(($page-1)*$PAGINATION_COUNT)
                    ->take($PAGINATION_COUNT)->orderBy($ordered_by, $sort_type)
                    ->get();

        $data = AreasApiResource::collection($items);
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
            'data'  => $data,
            'total' => Area::count(),
        ], 200);
    }

    public function store(AddAreaApiRequest $request)
    {
        $lang = $this->getLang();
        $request['lang'] = $lang;
        $item = Area::create($request->all());

        return response()->json([
          'status_code'   => 201,
          'message'       => 'success',
          'data'          => new SingleAreasApiResource($item),
        ], Response::HTTP_CREATED);
    }

    public function show($id)
    {
    	$lang = $this->getLang();
    	abort_if(Gate::denies('area_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
    	$item = Area::findOrFail($id);
    	$data = new SingleAreasApiResource($item);

        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data'            => $data,
        ], Response::HTTP_OK);
    }

    public function update(UpdateAreaApiRequest $request, $id)
    {
        $item = Area::findOrFail($id);
        $lang = $this->getLang();
        $request['lang'] = $lang;
        $item->update($request->all());

        $data = new SingleAreasApiResource($item);
        return response()->json([
          'status_code'   => 202,
          'message'       => 'success',
          'data' => $data,
        ], Response::HTTP_ACCEPTED);
    }

    public function destroy($id)
    {
        abort_if(Gate::denies('area_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $item = Area::findOrFail($id);
         if ($item->stores->count() > 0) {
               return response()->json([
                'status_code'     => 401,
                'errors' => 'this item is not empty te be deleted ('. $item->area_name. ' )',
                ], Response::HTTP_UNAUTHORIZED);
            }
            
            $item->delete();
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
        $items = Area::where(function ($q) use ($search_index) {
                $q->where('area_name', 'like', "%{$search_index}%")
                  ->orWhere('name_en', 'like', "%{$search_index}%");
            })->orWhereHas('country', function($q) use ($search_index){
                                $q->where('country_name', 'like', "%{$search_index}%");
                })->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                ->orderBy($ordered_by, $sort_type)->get();

        $total = Area::where(function ($q) use ($search_index) {
                $q->where('area_name', 'like', "%{$search_index}%")
                  ->orWhere('name_en', 'like', "%{$search_index}%");
                })->orWhereHas('country', function($q) use ($search_index){
                                $q->where('country_name', 'like', "%{$search_index}%");
                })->count();

        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
            'data'  => $items,
            'total' => $total,
        ], 200);
     }
    // end search car mades with name

     // start mass delete car mades
     public function mass_delete(MassDestroyAreaRequest $request)
     {
        $ids = json_decode($request->ids);
        foreach ($ids as $id) {
            $item = Area::findOrFail($id);
            if ($item->stores->count() > 0) {
               return response()->json([
                'status_code'     => 401,
                'errors' => 'this item is not empty te be deleted ('. $item->area_name. ' )',
                ], Response::HTTP_UNAUTHORIZED);
            }
        }
        Area::whereIn('id', $ids)->delete();
        return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => null
          ], 200);
        // return response(null, Response::HTTP_NO_CONTENT);
     }
     // end mass delete car mades 

      // start list all
     public function list_all($id)
     {
        $lang = $this->getLang();
        //$data = Area::where('country_id', $id)->get();
        $data = Area::where('country_id', $id)->get();
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data' => $data], Response::HTTP_OK);
     }

     public function list_all_list()
     {
      $lang = $this->getLang();
      //  $data = Area::get();
        $data = Area::all();
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data' => $data], Response::HTTP_OK);
     }
     // end list all 
}
