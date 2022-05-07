<?php

namespace App\Http\Controllers\ApiV1UserProducts;

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

class HomeReccentlyViewedProductsApiController extends Controller
{
    public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }

  public function recently_viewed_products(SelectCarTypeApiRequest $request)
    {
        $lang = $this->getLang();
        $cartype_id = $request->cartype_id;

        // alraedy user logged in
        if (Auth::guard('api')->check() && Auth::user()) 
        {
            $user = Auth::user();
            $user_roles = $user->roles->pluck('title')->toArray();
          
          // case logged in user role is User 
          if ( in_array('User', $user_roles) || ( in_array('Vendor', $user_roles) && (AddVendor::where('userid_id', $user->id)->first()->complete != 1 || AddVendor::where('userid_id', $user->id)->first()->approved != 1) ) ) {
            $indexes = Productview::whereHas('product', function($q) use ($cartype_id){
              $q->where('producttype_id', 1)->where('cartype_id', $cartype_id);
            })->select('product_id', DB::raw('count(*) as count'))
                   ->groupBy('product_id')
                   ->limit(10)
                   ->orderBy('count', 'desc')
                   ->pluck('product_id')->toArray();

        $sorter = static function ($produto) use ($indexes) {
                  return array_search($produto->id, $indexes);
               };

        $products = Product::where('lang', $lang)->where('approved', 1)
                          ->where('producttype_id', 1)
                          ->where('cartype_id', $cartype_id)
                          ->whereIn('id', $indexes)->get()->sortBy($sorter);
                          foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
        $data = MostlyViewedFrontProductsApiResource::collection($products);        
            return response()->json([
              'status_code' => 200,
              'message'     => 'success',
                'data'      => $data,
                // 'total'       => $total,
            ], 200);
              
          }  // end user case

          if (in_array('Vendor', $user_roles) && AddVendor::where('userid_id', $user->id)->first()->complete == 1 && AddVendor::where('userid_id', $user->id)->first()->approved == 1) {
            $indexes = Productview::whereHas('product', function($q) use ($cartype_id){
              $q->whereIn('producttype_id', [1, 2, 3])->where('cartype_id', $cartype_id);
            })->select('product_id', DB::raw('count(*) as count'))
                   ->groupBy('product_id')
                   ->limit(10)
                   ->orderBy('count', 'desc')
                   ->pluck('product_id')->toArray();

            $sorter = static function ($produto) use ($indexes) {
                      return array_search($produto->id, $indexes);
                   };

            $products = Product::where('lang', $lang)->where('approved', 1)
                              ->whereIn('producttype_id', [1, 2, 3])
                              ->where('cartype_id', $cartype_id)
                              ->whereIn('id', $indexes)->get()->sortBy($sorter);
                              foreach ($products as $new_product) {
                $new_product['in_cart']       = $user->revise_cart($new_product->id);
                $new_product['in_wishlist']   = $user->revise_wishlist($new_product->id);
                $new_product['in_favourites'] = $user->revise_favourites($new_product->id);
              }
            $data = MostlyViewedFrontProductsApiResource::collection($products);        
                return response()->json([
                  'status_code' => 200,
                  'message'     => 'success',
                    'data'      => $data,
                    // 'total'       => $total,
                ], 200);
            
          } // end vendor case
        }  // end case logged in 
        else{ // guest case
              $indexes = Productview::whereHas('product', function($q) use ($cartype_id){
              $q->where('producttype_id', 1)->where('cartype_id', $cartype_id);
            })->select('product_id', DB::raw('count(*) as count'))
                   ->groupBy('product_id')
                   ->limit(10)
                   ->orderBy('count', 'desc')
                   ->pluck('product_id')->toArray();

              $sorter = static function ($produto) use ($indexes) {
                        return array_search($produto->id, $indexes);
                     };

              $products = Product::where('lang', $lang)->where('approved', 1)
                                ->where('producttype_id', 1)
                                ->where('cartype_id', $cartype_id)
                                ->whereIn('id', $indexes)->get()->sortBy($sorter);
                                foreach ($products as $new_product) {
                $new_product['in_cart']  = 0;
                $new_product['in_wishlist']  = 0;
                $new_product['in_favourites']  = 0;
              }
              $data = MostlyViewedFrontProductsApiResource::collection($products);        
                  return response()->json([
                    'status_code' => 200,
                    'message'     => 'success',
                      'data'      => $data,
                      // 'total'       => $total,
                  ], 200); 
        } // end guest case
    }
}
