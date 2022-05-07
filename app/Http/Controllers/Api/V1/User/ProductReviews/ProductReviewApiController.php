<?php

namespace App\Http\Controllers\Api\V1\User\ProductReviews;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Productreview;
use Gate;
use Symfony\Component\HttpFoundation\Response;
use Auth;
use App\Http\Resources\User\ProductReviews\ProductReviewsApiResource;
use App\Http\Resources\User\ProductReviews\SpecificProductReviewApiResource;
use App\Http\Requests\Website\User\ProductReviews\AddReviewApiRequest;
use App\Http\Requests\Website\User\ProductReviews\UpdateReviewApiRequest;
use App\Http\Requests\Website\User\ProductReviews\MassDestroyReviewsRequest;
use App\Http\Requests\SearchApisRequest;
// use App\Http\Requests\OriginCountry\MassDestroyProductreviewRequest;
use App\Http\Requests\Website\User\ProductReviews\AddProdQuestionApiRequest;
use App\Models\Productquestion;
use App\Models\Product;
use App\Models\AddVendor;
use App\Models\Orderdetail;
use App\Models\Order;
use App\Mail\SendVendorQuestionRequestMail;
use Illuminate\Support\Facades\Mail;

class ProductReviewApiController extends Controller
{
    public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }

    // start index
     public function index(Request $request)
     {
         $lang = $this->getLang();
      //abort_if(Gate::denies('reviews_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;

      $user = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();

      // case logged in user role is Admin 
      if (in_array('Admin', $user_roles)) {
        $reviews = Productreview::where('lang', $lang)->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                    ->orderBy($ordered_by, $sort_type)->get();
        $total = Productreview::where('lang', $lang)->count();                    
        $data          = ProductReviewsApiResource::collection($reviews);
        return response()->json([
            'status_code' => 200,
            'message'     => 'success',
            'data'        => $data,
            'total'       => $total], Response::HTTP_OK);
      } // end admin case
       // case logged in user role is Vendor 
      elseif (in_array('Vendor', $user_roles)) {
        $vendor    = AddVendor::where('userid_id', Auth::user()->id)->first();
        $vendor_id = $vendor->id;
        $reviews = Productreview::where('lang', $lang)->whereHas('product', function($q) use($vendor_id)
        	{
        		$q->where('vendor_id', $vendor_id);
        	})->skip(($page-1)*$PAGINATION_COUNT)
                                    ->take($PAGINATION_COUNT)
                                    ->orderBy($ordered_by, $sort_type)->get();
        $total = Productreview::where('lang', $lang)->whereHas('product', function($q) use($vendor_id)
        	{
        		$q->where('vendor_id', $vendor_id);
        	})->count();
        $data  = ProductReviewsApiResource::collection($reviews);
        return response()->json([
            'status_code' => 200,
            'message'     => 'success',
            'data'        => $data,
            'total'       => $total], Response::HTTP_OK);
      } // end case vendor
      else{ // start case user
      	$reviews = Productreview::where('lang', $lang)->where('user_id', $user->id)->skip(($page-1)*$PAGINATION_COUNT)
                                    ->take($PAGINATION_COUNT)
                                    ->orderBy($ordered_by, $sort_type)->get();
        $total = Productreview::where('lang', $lang)->where('user_id', $user->id)->count();
        $data  = ProductReviewsApiResource::collection($reviews);
        return response()->json([
            'status_code' => 200,
            'message'     => 'success',
            'data'        => $data,
            'total'       => $total], Response::HTTP_OK);
      }  // end case user
    }
     // end index 

     // start show
     public function show($id)
     {
         $lang = $this->getLang();
     // $request['lang'] = $lang;
       // abort_if(Gate::denies('review_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
     	$user = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();
        $review = Productreview::findOrFail($id);
     	// case logged in user role is User
	      if (in_array('Admin', $user_roles)) {
	        $data = new SpecificProductReviewApiResource($review);
	        return response()->json([
	            'status_code' => 200,
	            'message' => 'success',
	            'data' => $data], Response::HTTP_OK);
	      } // end admin user
	      else{
	      	    if ($review->user_id !=  Auth::user()->id) {
	               return response()->json([
	                'status_code'     => 401,
	             //   'message'         => 'success',
	                'errors' => 'this review does not belong to you to view ( '.$review->body_review. ' )',
	                ], Response::HTTP_UNAUTHORIZED);
                }else{
                	$review = Productreview::findOrFail($id);
			        $data = new SpecificProductReviewApiResource($review);
			        return response()->json([
			            'status_code' => 200,
			            'message' => 'success',
			            'data' => $data], Response::HTTP_OK);
                }
	      } // end else
     }
     // end show 

     // start store
     public function store(AddReviewApiRequest $request)
     {
        $lang = $this->getLang();
        $request['lang'] = $lang;
     	$user = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

        $prod = Product::findOrFail($request->product_id);

        $bought = Order::where('user_id', $user->id)->where('paid', 1)->pluck('id')->toArray();
        // return $bought;
        $details = Orderdetail::whereIn('order_id', $bought)->where('approved', 1)
                   ->pluck('product_id')->toArray();
        if (!in_array($request->product_id, $details)) {
            return response()->json([
                'status_code' => 400,
             //   'message' => 'fail, you did not have this product in your history yet',
                'message' => __('site_messages.purchase_to_leave_review'),
                //'data' => $data,
            ], 400);
        }
        
     	// case logged in user role is User
	      if (in_array('User', $user_roles) || in_array('Vendor', $user_roles)) 
          {
	      	$request['user_id'] = Auth::user()->id;

                $count_review = Productreview::where('user_id', $user->id)
                                    ->where('product_id', $prod->id)->count();
                if ($count_review > 0) {
                return response()->json([
                    'status_code' => 400,
                 //   'message' => 'fail, can not add, you reviwed this product before',
                    'message' => __('site_messages.already_added_review'),
                    //'data' => $data,
                ], 400);
                }

	        $review = Productreview::create($request->all());
	        $data = new SpecificProductReviewApiResource($review);
	        return response()->json([
	            'status_code' => 201,
	          //  'message' => 'success',
                'message' => __('site_messages.review_added_successfully'),
	            'data' => $data], Response::HTTP_CREATED);
	      } // end admin user
	      else{
	        return response()->json([
	                'status_code'     => 401,
	              //  'message'         => 'success',
	                'message'  => 'un authorized access page due to permissions',
	               ], 401);
	      }
     }
     // end store 

     // start update
     public function update(UpdateReviewApiRequest $request, $id)
     {
         $lang = $this->getLang();
      $request['lang'] = $lang;
     	$user = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();
     	// case logged in user role is User
	      if (in_array('User', $user_roles)) {
	      	$review = Productreview::findOrFail($id);
	      	if ($review->user_id !=  Auth::user()->id) {
               return response()->json([
                'status_code'     => 401,
             //   'message'         => 'success',
                'errors' => 'this review does not belong to you to edit ( '.$review->body_review. ' )',
                ], Response::HTTP_UNAUTHORIZED);
            }
	        $request['user_id'] = Auth::user()->id;
	        $review->update($request->all());
	        $data = new SpecificProductReviewApiResource($review);
	        return response()->json([
	            'status_code' => 202,
	            'message' => 'success',
	            'data' => $data], Response::HTTP_ACCEPTED);
	      } // end admin user
	      else{
	        return response()->json([
	                'status_code'     => 401,
	              //  'message'         => 'success',
	                'message'  => 'un authorized access page due to permissions',
	               ], 401);
	      }
     }
     // end update 

     // start destroy
     public function destroy($id)
     {
     //abort_if(Gate::denies('reviews_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $review = Productreview::findOrFail($id);
        if ($review->user_id !=  Auth::user()->id) {
               return response()->json([
                'status_code'     => 401,
             //   'message'         => 'success',
                'errors' => 'this review does not belong to you to delete ( '. $review->body_review. ' )',
                ], Response::HTTP_UNAUTHORIZED);
            }
        $review->delete();
        return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data' => null], Response::HTTP_OK);
     }
     // end destroy 

     // start mass delete origin countries
     public function mass_delete(MassDestroyReviewsRequest $request)
     {
        $ids = json_decode($request->ids);
        foreach ($ids as $id) {
            $review = Productreview::findOrFail($id);
            if ($review->user_id !=  Auth::user()->id) {
               return response()->json([
                'status_code'     => 401,
             //   'message'         => 'success',
                'errors' => 'this review does not belong to you to delete ( '. $review->body_review. ' )',
                ], Response::HTTP_UNAUTHORIZED);
            }
        }
        Productreview::whereIn('id', $ids)->delete();
        return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => null
          ], 200);
        // return response(null, Response::HTTP_NO_CONTENT);
     }
     // end mass delete origin countries 

     public function prod_question_add(AddProdQuestionApiRequest $request)
    {
        $lang = $this->getLang();
        $request['lang'] = $lang;
        $user = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();
        $request['user_id'] = Auth::user()->id;
        $prod = Product::findOrFail($request->product_id);

        $count_review = Productquestion::where('user_id', $user->id)
                                ->where('product_id', $prod->id)->count();
            if ($count_review > 0) {
            return response()->json([
                'status_code' => 400,
                'message' => 'fail, can not add, you made aquestion on this product before',
              //  'data' => 
            ], 400);
            }
            $request['vendor_id'] = $prod->vendor_id;
            $review = Productquestion::create($request->all());
            $data = $review; // new SpecificProductReviewApiResource($review);
            $product_name = $prod->name;
            $user_name    = $user->name;
            $vendor_email = $prod->vendor->email;

            Mail::to($vendor_email)->send(new SendVendorQuestionRequestMail($user_name, $product_name));
            
            return response()->json([
                'status_code' => 201,
             //   'message' => 'success',
                'message' => __('site_messages.question_added'),
                'data' => $data], Response::HTTP_CREATED);
    }


     // start search_with_name
    /* public function search_with_name(SearchApisRequest $request)
     {
        $default_count = \Config::get('constants.pagination.items_per_page');
        $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
          
        $request->page == '' ? $page = 1 : $page = $request->page;
        $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
        $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
          
        $search_index = $request->search_index;

        $origin_countries = Prodcountry::where('country_name', 'like', "%{$search_index}%")
                                ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                ->orderBy($ordered_by, $sort_type)->get();

        $total = Prodcountry::where('country_name', 'like', "%{$search_index}%")->count();
        $data  = OriginCountryApiResource::collection($origin_countries);
        
        return response()->json([
            'status_code' => 200,
            'message'     => 'success',
            'data'        => $data,
            'total'       => $total,
        ], Response::HTTP_OK);
     }*/
     // end search_with_name
}
