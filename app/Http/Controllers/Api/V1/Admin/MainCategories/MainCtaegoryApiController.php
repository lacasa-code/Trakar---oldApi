<?php

namespace App\Http\Controllers\Api\V1\Admin\MainCategories;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Maincategory;
use Gate;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\SearchApisRequest;

use App\Http\Requests\Api\V1\Admin\MainCategories\AddMaincategoryApiRequest;
use App\Http\Requests\Api\V1\Admin\MainCategories\UpdateMaincategoryApiRequest;
use App\Http\Requests\Api\V1\Admin\MainCategories\MassDestroyMaincategoryRequest;

use App\Http\Resources\Api\V1\Admin\MainCategories\SingleMaincategoryApiResource;
use App\Http\Resources\Api\V1\Admin\MainCategories\MaincategoryApiResource;
use App\Http\Resources\Api\V1\Admin\MainCategories\SingleMaincategoryNestedApiResource;

class MainCtaegoryApiController extends Controller
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
       
      //abort_if(Gate::denies('main_categories_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $items = Maincategory::skip(($page-1)*$PAGINATION_COUNT)
                    ->take($PAGINATION_COUNT)->orderBy($ordered_by, $sort_type)
                    ->get();

        $data = MaincategoryApiResource::collection($items);
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
            'data'  => $data,
            'total' => Maincategory::count(),
        ], 200);
    }

    public function store(AddMaincategoryApiRequest $request)
    {
        $lang = $this->getLang();
        $request['lang'] = $lang;
        $item = Maincategory::create($request->all());

        return response()->json([
          'status_code'   => 201,
          'message'       => 'success',
          'data'          => new SingleMaincategoryApiResource($item),
        ], Response::HTTP_CREATED);
    }

    public function show($id)
    {
    	$lang = $this->getLang();
    	//abort_if(Gate::denies('main_category_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
    	$item = Maincategory::findOrFail($id);
    	$data = new SingleMaincategoryApiResource($item);

        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data'            => $data,
        ], Response::HTTP_OK);
    }

    public function update(UpdateMaincategoryApiRequest $request, $id)
    {
        $item = Maincategory::findOrFail($id);
        $lang = $this->getLang();
        $request['lang'] = $lang;
        $item->update($request->all());

        $data = new SingleMaincategoryApiResource($item);
        return response()->json([
          'status_code'   => 202,
          'message'       => 'success',
          'data' => $data,
        ], Response::HTTP_ACCEPTED);
    }

    public function destroy($id)
    {
        //abort_if(Gate::denies('main_category_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $item = Maincategory::findOrFail($id);
         if ($item->categories->count() > 0) {
               return response()->json([
                'status_code'     => 401,
                'errors' => 'this item is not empty te be deleted ('. $item->main_category_name. ' )',
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
        $items = Maincategory::where(function ($q) use ($search_index) {
                $q->where('main_category_name', 'like', "%{$search_index}%");
                })->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                ->orderBy($ordered_by, $sort_type)->get();

        $total = Maincategory::where(function ($q) use ($search_index) {
                $q->where('main_category_name', 'like', "%{$search_index}%");
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
     public function mass_delete(MassDestroyMaincategoryRequest $request)
     {
        $ids = json_decode($request->ids);
        foreach ($ids as $id) {
            $item = Maincategory::findOrFail($id);
            if ($item->categories->count() > 0) {
               return response()->json([
                'status_code'     => 401,
                'errors' => 'this item is not empty te be deleted ('. $item->main_category_name. ' )',
                ], Response::HTTP_UNAUTHORIZED);
            }
        }
        Maincategory::whereIn('id', $ids)->delete();
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
        $data = Maincategory::all();
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data' => $data], Response::HTTP_OK);
     }
     // end list all 

     // start list all
     public function list_all_fetched()
     {
        $items = Maincategory::get();
        $data = SingleMaincategoryNestedApiResource::collection($items);
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data' => $data,
        ], Response::HTTP_OK);
     }
     // end list all 

     // start list all
     public function list_specific_fetched($id)
     {
        $items = Maincategory::findOrFail($id);
        $data = new SingleMaincategoryNestedApiResource($items);
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data' => $data], Response::HTTP_OK);
     }
     // end list all 
}
