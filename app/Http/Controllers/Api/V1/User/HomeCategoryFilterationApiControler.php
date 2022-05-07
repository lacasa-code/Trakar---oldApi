<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Allcategory;
use Gate;
use Symfony\Component\HttpFoundation\Response;
use Auth;
use App\Http\Requests\HomeCategoryFilterationApiRequest;
use App\Http\Requests\Api\Admin\Allcategory\StoreAllcategoryApiRequest;

use App\Http\Resources\Api\Admin\Allcategory\HomeAllcategorySpecificApiResource;
use App\Http\Resources\Api\Admin\Allcategory\HomeCategoryFilterationApiResource;
use App\Models\Product;
use App\Models\Manufacturer;
use App\Models\Prodcountry;

class HomeCategoryFilterationApiControler extends Controller
{
    public function getLang()
    {
      return $lang = \Config::get('app.locale');
    }

    public function category_filterations(Request $request)
    {
    	// abort_if(Gate::denies('all_category_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
      $lang = $this->getLang();
      //$PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'DESC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      $id = $request->id;
  
      $target = Allcategory::findOrFail($id);
      $cats = Allcategory::where('allcategory_id', $id)
                              ->orWhere('allcategory_id', 7)
                              ->orderBy($ordered_by, $sort_type)
                              ->get();
     
      $manufacturers_arr = Product::where('allcategory_id', $id)->groupBy('manufacturer_id')
                                   ->pluck('manufacturer_id')->toArray();

      $origins_arr = Product::where('allcategory_id', $id)->groupBy('prodcountry_id')
                                   ->pluck('prodcountry_id')->toArray();
      $manufacturers  = Manufacturer::whereIn('id', $manufacturers_arr)->get();
      $origins        = Prodcountry::whereIn('id', $origins_arr)->get();

        $target['cats']          = $cats;
        $target['manufacturers'] = $manufacturers;
        $target['origins']       = $origins;

      $data = new HomeCategoryFilterationApiResource($target);
     
        return response()->json([
            'status_code'     => 200,
            'message'         => 'success',
            'data'          => $data,
            // 'total'         => Allcategory::whereNull('allcategory_id')->count(),
        ], 200);
    }

}
