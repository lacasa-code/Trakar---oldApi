<?php

namespace App\Http\Controllers\Api\V1\User\Products;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\Website\User\EvaluationProducts\UserSingleEvaluationProductApiResource;
use App\Http\Resources\Website\User\EvaluationProducts\UserEvaluationProductsApiResource;
use Auth;
use App\Models\Evaluationproduct;
use App\Models\User;
use App\Models\Product;
use App\Http\Requests\Website\User\EvaluationProducts\UserAddEvaluationProductApiRequest;
use App\Http\Requests\Website\User\EvaluationProducts\UserGetEvaluationSpecificProductApiRequest;

class UserProductsEvaluationApiController extends Controller
{
    public function user_get_evaluation_products()
    {
    	$user = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

      if (in_array('User', $user_roles)) {
      	$user_id    = Auth::user()->id;
      	$favourites = Evaluationproduct::where('user_id', $user_id)->get();
      	$total      = Evaluationproduct::where('user_id', $user_id)->count();
        $data       = UserEvaluationProductsApiResource::collection($favourites);
      
        return response()->json([
        	        'status_code'  => 200,
	        	    'message'      => 'success',
                    'data'         => $data,
                    'total'        => $total,
            ], 200);
      } 
      else{
        return response()->json([
                'status_code' => 401,
                'message'  => 'un authorized access page due to permissions',
               ], 401);
      }
    } 

    public function user_get_evaluation_specific_product(UserGetEvaluationSpecificProductApiRequest $request)
    {
    	$user = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

      if (in_array('User', $user_roles)) {
      	$user_id    = Auth::user()->id;
      	$favourite = Evaluationproduct::where('user_id', $user_id)
      	                               ->where('product_id', $request->product_id)->first();
      	// $total      = Evaluationproduct::where('user_id', $user_id)->count();
        $data       = new UserSingleEvaluationProductApiResource($favourite);
      
        return response()->json([
        	        'status_code'  => 200,
	        	    'message'      => 'success',
                    'data'         => $data,
                   // 'total'        => $total,
            ], 200);
      } 
      else{
        return response()->json([
                'status_code' => 401,
                'message'  => 'un authorized access page due to permissions',
               ], 401);
      }
    } 

    public function user_add_evaluation(UserAddEvaluationProductApiRequest $request)
    {
    	  $user = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

      if (in_array('User', $user_roles)) {
      	$user_id   = Auth::user()->id;
      	$product   = Product::findOrFail($request->product_id);
      	$exist     = Evaluationproduct::where('user_id', $user_id)
      	                    ->where('product_id', $product->id)->first();
      	if ($exist != null) {
      		/*$exist->update([ 
                'evaluation_value' => $request->evaluation_value,
            ]);
                $data      = new UserSingleEvaluationProductApiResource($exist);*/
		        return response()->json([
		        	      'status_code'  => 400,
			        	  'message'      => 'you evaluated this product before',
		                  'data'         => $data,
		            ], 400);
      	}
      	$favourite = Evaluationproduct::create([
                'user_id'          => $user_id, 
                'product_id'       => $request->product_id,  
                'evaluation_value' => $request->evaluation_value,
    		]);
        $data      = new UserSingleEvaluationProductApiResource($favourite);
      
        return response()->json([
        	      'status_code'  => 200,
	        	  'message'      => 'success',
                  'data'         => $data,
            ], 200);
      } 
      else{
        return response()->json([
                'status_code'  => 401,
                'message'  => 'un authorized access page due to permissions',
               ], 401);
      }		
    }
}
