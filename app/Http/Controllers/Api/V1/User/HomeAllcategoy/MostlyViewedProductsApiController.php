<?php

namespace App\Http\Controllers\Api\V1\User\HomeAllcategoy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Gate;
use App\Http\Requests\SearchApisRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Schema;
use App\Http\Resources\Website\Products\FrontProductsApiResource;
use App\Http\Resources\Website\Products\SpecificFrontProductsApiResource;
use Auth;
use App\Models\Productview;
use DB;
use App\Http\Resources\User\ProductReviews\ProductReviewsApiResource;
use App\Models\AddVendor;
use App\Models\Productreview;
use App\Models\Evaluationproduct;
use App\Http\Resources\Website\User\EvaluationProducts\UserEvaluationProductsApiResource;
use App\Http\Resources\Website\Products\MostlyViewedFrontProductsApiResource;
use App\Http\Requests\Api\V1\User\Front\SelectCarTypeApiRequest;
use App\Models\ProductCategory;
use App\Http\Resources\Admin\ProductCategoryResource;
use App\Models\Allcategory;
use App\Http\Resources\Api\Admin\Allcategory\HomeAllcategoryApiResource;
use App\Http\Resources\Api\Admin\Allcategory\HomeAllcategorySpecificApiResource;

class MostlyViewedProductsApiController extends Controller
{
  public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }

    public function mostly_viewed_products(SelectCarTypeApiRequest $request)
    {
        $lang = $this->getLang();
        $cartype_id = $request->cartype_id;
        $common_types = [$cartype_id, 7];

        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
            $indexes = Productview::whereHas('product', function($q) use ($cartype_id, $common_types){
                          $q->where('producttype_id', 1)->whereIn('cartype_id', $common_types);
                        })->select('category_id', DB::raw('count(*) as count'))
                          ->groupBy('category_id')
                          ->limit(6)
                          ->orderBy('count', 'desc')
                          ->pluck('category_id')->toArray();

        $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

              $products = Allcategory::whereIn('id', $indexes)
                                ->get()->sortBy($sorter);
              $data = HomeAllcategoryApiResource::collection($products);  
            return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'      => $data,
                // 'total'       => $total,
            ], 200);
              
          }  // end user case

          if (( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
            $indexes = Productview::whereHas('product', function($q) use ($cartype_id, $common_types){
              $q->where('producttype_id', 1)->whereIn('cartype_id', $common_types);
            })->select('category_id', DB::raw('count(*) as count'))
                   ->groupBy('category_id')
                   ->limit(6)
                   ->orderBy('count', 'desc')
                   ->pluck('category_id')->toArray();

        $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

              $products = Allcategory::whereIn('id', $indexes)
                                ->get()->sortBy($sorter);
              $data = HomeAllcategoryApiResource::collection($products);  
       // $data = MostlyViewedFrontProductsApiResource::collection($products);        
            return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'      => $data,
                // 'total'       => $total,
            ], 200);
              
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $indexes = Productview::whereHas('product', function($q) use ($cartype_id, $common_types){
              $q->whereIn('producttype_id', [1, 2, 3])->whereIn('cartype_id', $common_types);
            })->select('category_id', DB::raw('count(*) as count'))
                   ->groupBy('category_id')
                   ->limit(6)
                   ->orderBy('count', 'desc')
                   ->pluck('category_id')->toArray();

            $sorter = static function ($produto) use ($indexes) {
                      return array_search($produto->id, $indexes);
                   };

            $products = Allcategory::whereIn('id', $indexes)
                                ->get()->sortBy($sorter);

              $data = HomeAllcategoryApiResource::collection($products);  
      
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'      => $data,
                    // 'total'       => $total,
                ], 200);
            
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
              $indexes = Productview::whereHas('product', function($q) use ($cartype_id, $common_types){
              $q->where('producttype_id', 1)->whereIn('cartype_id', $common_types);
            })->select('category_id', DB::raw('count(*) as count'))
                   ->groupBy('category_id')
                   ->limit(6)
                   ->orderBy('count', 'desc')
                   ->pluck('category_id')->toArray();

              $sorter = static function ($produto) use ($indexes) {
                        return array_search($produto->id, $indexes);
                     };

              $products = Allcategory::whereIn('id', $indexes)
                                ->get()->sortBy($sorter);

              $data = HomeAllcategoryApiResource::collection($products);       
              
                  return response()->json([
                    'status_code' => 200,
                    'message'     => 'success',
                      'data'      => $data,
                      // 'total'       => $total,
                  ], 200); 
        } // end guest case
    }
}
