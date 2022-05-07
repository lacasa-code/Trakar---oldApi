<?php

namespace App\Http\Controllers\Api\V1\User\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\Website\Products\FrontProductsApiResource;
use App\Http\Resources\Website\Products\SpecificFrontProductsApiResource;
use Gate;
use Auth;
use DB;
use App\Models\Product;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\Website\User\CheckboxFilter\FetchCheckboxFilterApiRequest;
use App\Http\Resources\Api\Admin\Allcategory\HomeAllcategoryApiResource;
use App\Http\Resources\Api\Admin\Allcategory\HomeAllcategorySpecificApiResource;
use App\Http\Resources\Api\Admin\Allcategory\HomeAllcategoryNestedSpecificApiResource;
use App\Models\Allcategory;
use App\Http\Requests\Api\V1\User\Front\AllcategorySelectCarTypeApiRequest;

class AllcategoryFetchMultipleCheckboxApiController extends Controller
{
	 public function getLang()
  {
      return $lang = \Config::get('app.locale');
  } 

     public function categories_nested_part(AllcategorySelectCarTypeApiRequest $request)
    {
        $lang = $this->getLang();
        $cartype_id = $request->cartype_id;
        if ($cartype_id == 1) {
            $ordered_by = 'sequence';
            $sort_type = 'ASC';
          }
          if ($cartype_id == 3) {
            $ordered_by = 'commercial_sequence';
            $sort_type = 'ASC';
          }
      
        $categories = Allcategory::orderBy('id', 'DESC')->where('allcategory_id', $cartype_id)
                              ->orWhere('allcategory_id', 7)->get();
        foreach ($categories as $value) {
          if($value->car_navbar != null && $value->commercial_navbar == null){
            $got_seq = $value->sequence;
            $commercial_seq = 0;
          }elseif($value->commercial_navbar != null && $value->car_navbar == null){
            $got_seq = $value->commercial_sequence;
            $commercial_seq = 0;
          }elseif($value->commercial_navbar != null && $value->car_navbar != null){
            $got_seq = $value->sequence;
            $commercial_seq = $value->commercial_sequence;
          }
          else{
            $got_seq = $value->sequence;
            $commercial_seq = 0;
          }
          $value['got_seq'] = $got_seq;
          $value['commercial_seq'] = $commercial_seq;
        }

      $nav_cats = Allcategory::where('car_navbar', $cartype_id)
                              ->orWhere('commercial_navbar', $cartype_id)
                              ->where('navbar', 1)
                              ->orderBy($ordered_by, $sort_type)
                              ->get();

        $data_nav_cats = HomeAllcategoryNestedSpecificApiResource::collection($nav_cats->where('navbar', 1));
        $data = HomeAllcategoryNestedSpecificApiResource::collection($categories);
        return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => $data,
            'nav_cats'      => $data_nav_cats,
            //'total'         => Allcategory::where('allcategory_id', $id)->count(),
        ], 200);

    }
}
