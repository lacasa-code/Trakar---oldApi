<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Models\Allcategory;
use Gate;
use Symfony\Component\HttpFoundation\Response;
use Auth;
use App\Http\Resources\Api\Admin\Allcategory\HomeAllcategorySpecificApiResource;

class HomeAllcategoryIndexDetailsApiControler extends Controller
{
	public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }
  
    public function index_details(Request $request, $id)
    {
      $lang = $this->getLang();
      //$PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      // abort_if(Gate::denies('all_category_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'DESC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;

      $target = Allcategory::findOrFail($id);
      if ($target->allcategory_id == null) {
        $categories = Allcategory::where('allcategory_id', $id)
                            //  ->skip(($page-1)*$PAGINATION_COUNT)
                             // ->take($PAGINATION_COUNT)
                              ->orWhere('allcategory_id', 7)
                              ->orderBy($ordered_by, $sort_type)
                              ->get();
      }else{
        $categories = Allcategory::where('allcategory_id', $id)
                            //  ->skip(($page-1)*$PAGINATION_COUNT)
                             // ->take($PAGINATION_COUNT)
                             // ->orWhere('allcategory_id', 7)
                              ->orderBy($ordered_by, $sort_type)
                              ->get();
      }
      $data = HomeAllcategorySpecificApiResource::collection($categories);
        return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => $data,
            'total'         => Allcategory::where('allcategory_id', $id)->orWhere('allcategory_id', 7)->count(),
            'breadcrumbs'   => $target->getParentssAttribute(),
        ], 200);
    }
}
