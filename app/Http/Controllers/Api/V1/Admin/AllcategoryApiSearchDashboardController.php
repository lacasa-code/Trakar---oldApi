<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\Api\Admin\Allcategory\AllcategoryApiResource;
use App\Models\Allcategory;
use Gate;
use Symfony\Component\HttpFoundation\Response;
use Auth;
use App\Http\Requests\SearchApisRequest;

class AllcategoryApiSearchDashboardController extends Controller
{
	 public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }

     // start search part categories with name
     public function search_with_name(SearchApisRequest $request)
     {
      $lang = $this->getLang();
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      
        $search_index = $request->search_index;
       
        $categories = Allcategory::where(function ($q) use ($search_index) {
                $q->where('name', 'like', "%{$search_index}%")
                ->orWhere('name_en', 'like', "%{$search_index}%");
            })->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
              ->orderBy($ordered_by, $sort_type)->get();

        $total = Allcategory::where(function ($q) use ($search_index) {
                $q->where('name', 'like', "%{$search_index}%")
                ->orWhere('name_en', 'like', "%{$search_index}%");
            })->count();

        $data = AllcategoryApiResource::collection($categories);

        return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => $total,
        ], 200);
     }
    // end search part categories with name
}
