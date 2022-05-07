<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Models\Allcategory;
use Gate;
use Symfony\Component\HttpFoundation\Response;
use Auth;
use App\Http\Resources\Api\Admin\Allcategory\HomeAllcategoryApiResource;

class HomeAllcategoryIndexApiControler extends Controller
{
    public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }

    public function index(Request $request)
    {
      $lang = $this->getLang();
      //$PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
          // abort_if(Gate::denies('all_category_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'DESC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
       
      $categories = Allcategory::whereNull('allcategory_id')
                              ->skip(($page-1)*$PAGINATION_COUNT)
                              ->take($PAGINATION_COUNT)
                              ->orderBy($ordered_by, $sort_type)->get();
      foreach ($categories as $value) {
        $cats = Allcategory::where('allcategory_id', $value->id)
                              ->orWhere('allcategory_id', 7)
                              ->get();
       $value['cats'] = $cats;
      }
      $data = HomeAllcategoryApiResource::collection($categories);
        return response()->json([
            'status_code'     => 200,
            'message'         => 'success',
            'data'          => $data,
            'total'         => Allcategory::whereNull('allcategory_id')->count(),
        ], 200);
    }
}
