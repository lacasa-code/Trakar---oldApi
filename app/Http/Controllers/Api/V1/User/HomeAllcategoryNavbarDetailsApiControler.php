<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Models\Allcategory;
use Gate;
use Symfony\Component\HttpFoundation\Response;
use Auth;
use App\Http\Resources\Api\Admin\Allcategory\HomeAllcategoryNestedSpecificApiResource;

class HomeAllcategoryNavbarDetailsApiControler extends Controller
{
	public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }

   public function navbar_details(Request $request, $id)
    {
      $lang = $this->getLang();
      //$PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      // abort_if(Gate::denies('all_category_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
      $request->page == '' ? $page = 1 : $page = $request->page;
    //  $request->sort_type == '' ? $sort_type = 'DESC' : $sort_type = $request->sort_type;
    //  $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;

      if ($id == 1) {
        $ordered_by = 'sequence';
        $sort_type = 'ASC';
      }
      if ($id == 3) {
        $ordered_by = 'commercial_sequence';
        $sort_type = 'ASC';
      }

      $target = Allcategory::findOrFail($id);
      $categories = Allcategory::where('car_navbar', $id)
                              ->orWhere('commercial_navbar', $id)
                              ->where('navbar', 1)
                              ->orderBy($ordered_by, $sort_type)
                              ->get();
      $total = Allcategory::where('car_navbar', $id)
                              ->orWhere('commercial_navbar', $id)
                              ->where('navbar', 1)->count();
      $data = HomeAllcategoryNestedSpecificApiResource::collection($categories->where('navbar', 1));
        return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => $data,
            'total'         => $total,
            'breadcrumbs'   => $target->getParentssAttribute(),
        ], 200);
    }
}
