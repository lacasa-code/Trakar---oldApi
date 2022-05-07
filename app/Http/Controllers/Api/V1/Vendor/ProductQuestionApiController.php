<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Website\User\ProductReviews\AddProdQuestionApiRequest;
use App\Models\Productquestion;
use App\Models\Product;
use Gate;
use Symfony\Component\HttpFoundation\Response;
use Auth;
use App\Http\Requests\Website\User\ProductQuestions\VendorAnswerQuestionApiRequest;
use App\Models\AddVendor;
use App\Http\Requests\Website\User\ProductQuestions\GetVendorQuestionsApiRequest;
use App\Http\Resources\Api\V1\Vendor\ProdQuestionsApiResource;
use App\Http\Resources\Api\V1\Vendor\SpecificProdQuestionsApiResource;
use App\Http\Requests\Api\Vendor\Questions\VendorFetchQuestionsApiRequest;
use App\Models\Vendorstaff;
use App\Http\Requests\SearchApisRequest;

class ProductQuestionApiController extends Controller
{
    public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }

    public function vendor_answer_question(VendorAnswerQuestionApiRequest $request)
    {
        abort_if(Gate::denies('vendor_answer_question'), Response::HTTP_FORBIDDEN, '403 Forbidden');
       	$lang = $this->getLang();
        $request['lang'] = $lang;
        $user       = Auth::user();
        $user_id    = Auth::user()->id;
        $user_roles = $user->roles->pluck('title')->toArray();

        $question = Productquestion::findOrFail($request->question_id);

        if (in_array('Vendor', $user_roles)) 
        {
            $vendor   = AddVendor::where('userid_id', $user_id)->first();
            if ($vendor->id != $question->vendor_id) {
            	return response()->json([
                    'status_code' => 401,
                    'errors' => 'this question does not belong to you to answer',
                    //'data' => $data,
                ], 401);
            }
    
            	$question->update(['answer' => $request->answer]);
            	$data = $question;
            	return response()->json([
                        'status_code' => 200,
                        'message' => 'success',
                        'data' => $data,
                    ], 200);
        }
        elseif (in_array('Manager', $user_roles) || in_array('Staff', $user_roles) ) 
        {
         // abort_if(Gate::denies('stores_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
          $exist_staff   = Vendorstaff::where('email', Auth::user()->email)->first();
          $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
          $vendor_id     = $vendor->id;
          $staff_stores  = $exist_staff->stores->pluck('id')->toArray();   
           if ($vendor->id != $question->vendor_id) {
              return response()->json([
                    'status_code' => 401,
                    'errors' => 'this question does not belong to you to answer',
                    //'data' => $data,
                ], 401);
            }
    
              $question->update(['answer' => $request->answer]);
              $data = $question;
              return response()->json([
                        'status_code' => 200,
                        'message' => 'success',
                        'data' => $data,
                    ], 200);
        }
        else{
          return response()->json([
                'status_code' => 401,
                'errors' => 'only product vendor, staff or manger related can answer',
                //'data' => $data,
            ], 401);
        }
    }

   // public function vendor_fetch_questions(VendorFetchQuestionsApiRequest $request)
    public function vendor_fetch_questions(Request $request)
    {
        abort_if(Gate::denies('fetch_vendor_questions'), Response::HTTP_FORBIDDEN, '403 Forbidden');
    	// $id        = $request->vendor_id;
        $user = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();
       // $vendor_id  = $request->vendor_id; 

      $lang = $this->getLang();
      abort_if(Gate::denies('tickets_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'DESC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      //$PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;

      // case logged in user role is Admin (show all invoices)
      if (in_array('Admin', $user_roles)) { 
            $questions = Productquestion::skip(($page-1)*$PAGINATION_COUNT)
                      ->take($PAGINATION_COUNT)
                      ->orderBy($ordered_by, $sort_type)
                      ->get();
            $total     = Productquestion::count();
            $data = ProdQuestionsApiResource::collection($questions);
            return response()->json([
                    'status_code' => 200,
                    'message' => 'success',
                    'data'  => $data,
                    'total' => $total,
                ], 200);
      } // end admin case
       // case logged in user role is Vendor (show only his invoices)
      elseif (in_array('Vendor', $user_roles)) {
       $vendor    = AddVendor::where('userid_id', Auth::user()->id)->first();
       $vendor_id = $vendor->id;

            $questions = Productquestion::where('vendor_id', $vendor_id)
                            ->skip(($page-1)*$PAGINATION_COUNT)
                            ->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)
                            ->get();
            $total     = Productquestion::where('vendor_id', $vendor_id)->count();
            $data = ProdQuestionsApiResource::collection($questions);
            return response()->json([
                    'status_code' => 200,
                    'message' => 'success',
                    'data'  => $data,
                    'total' => $total,
                ], 200);
      } // end case vendor
      /* manager case */
      elseif (in_array('Manager', $user_roles) || in_array('Staff', $user_roles) ) 
      {
        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
        
            $questions = Productquestion::where('vendor_id', $vendor_id)
                            ->skip(($page-1)*$PAGINATION_COUNT)
                      ->take($PAGINATION_COUNT)
                      ->orderBy($ordered_by, $sort_type)
                      ->get();
            $total     = Productquestion::where('vendor_id', $vendor_id)->count();
            $data = ProdQuestionsApiResource::collection($questions);
            return response()->json([
                    'status_code' => 200,
                    'message' => 'success',
                    'data'  => $data,
                    'total' => $total,
                ], 200);
      }
      else{
        return response()->json([
                'status_code' => 401,
                'message'  => 'un authorized access page due to permissions',
               ], 401);
      }
    }

    public function search_prod_questions(SearchApisRequest $request)
    {
      $lang = $this->getLang();
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
        
        $search_index = $request->search_index;
        $user = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

        if (in_array('Admin', $user_roles)) {
          $questions = Productquestion::where(function ($q) use ($search_index) {
                $q->where('body_question', 'like', "%{$search_index}%")
                  ->orWhere('answer', 'like', "%{$search_index}%");
                })->orWhereHas('AddVendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })->orWhereHas('product', function($q) use ($search_index){
                                $q->where('name', 'like', "%{$search_index}%");
                })
                ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                ->orderBy($ordered_by, $sort_type)
                ->get();

            $data = ProdQuestionsApiResource::collection($questions);
            $total = count($questions);

            return response()->json([
              'status_code' => 200,
               'message' => 'success',
                'data'  => $data,
                'total' => $total,
            ], 200);
        } 

        elseif (in_array('Vendor', $user_roles)) {
            $vendor        = AddVendor::where('userid_id', Auth::user()->id)->first();
            $vendor_id     = $vendor->id;

            $questions = Productquestion::where(function ($q) use ($search_index) {
                $q->where('body_question', 'like', "%{$search_index}%")
                  ->orWhere('answer', 'like', "%{$search_index}%");
                })->orWhereHas('AddVendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })->orWhereHas('product', function($q) use ($search_index){
                                $q->where('name', 'like', "%{$search_index}%");
                })
                ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                ->orderBy($ordered_by, $sort_type)
                ->get();

            $get_questions = $questions->where('vendor_id', $vendor_id);
            $data = ProdQuestionsApiResource::collection($get_questions);
            $total = count($get_questions);

            return response()->json([
              'status_code' => 200,
               'message' => 'success',
                'data'  => $data,
                'total' => $total,
            ], 200);
        }
        elseif (in_array('Staff', $user_roles) || in_array('Manager', $user_roles)) {
            
            $exist_staff   = Vendorstaff::where('email', Auth::user()->email)->first();
            $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
            $vendor_id     = $vendor->id;

            $questions = Productquestion::where(function ($q) use ($search_index) {
                $q->where('body_question', 'like', "%{$search_index}%")
                  ->orWhere('answer', 'like', "%{$search_index}%");
                })->orWhereHas('AddVendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })->orWhereHas('product', function($q) use ($search_index){
                                $q->where('name', 'like', "%{$search_index}%");
                })
                ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                ->orderBy($ordered_by, $sort_type)
                ->get();

            $get_questions = $questions->where('vendor_id', $vendor_id);
            $data = ProdQuestionsApiResource::collection($get_questions);
            $total = count($get_questions);

            return response()->json([
              'status_code' => 200,
               'message' => 'success',
                'data'  => $data,
                'total' => $total,
            ], 200);
        }
        else{
        return response()->json([
          'status_code'     => 401,
                'message'  => 'un authorized access page due to permissions',
               ], 401);
      } // end else 
    }

    public function prod_all_questions($id)
    {
      //  abort_if(Gate::denies('fetch_vendor_questions'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $user = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();
        $product = Product::findOrFail($id);     

        //if (in_array('Vendor', $user_roles) || in_array('User', $user_roles)) 
        //{
         //   $vendor    = AddVendor::where('userid_id', $user->id)->first();
            $questions = Productquestion::where('product_id', $product->id)->get();
            $total     = Productquestion::where('product_id', $product->id)->count();
            $data = ProdQuestionsApiResource::collection($questions);
            return response()->json([
                    'status_code' => 200,
                    'message' => 'success',
                    'data'  => $data,
                    'total' => $total,
                ], 200);
      //  }
    } 

    public function all_questions_index(Request $request)
    {
        abort_if(Gate::denies('access_questions'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $lang = $this->getLang();
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
        
        $search_index = $request->search_index;
        $user = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

        if (in_array('Admin', $user_roles)) {
          $questions = Productquestion::skip(($page-1)*$PAGINATION_COUNT)
                ->take($PAGINATION_COUNT)
                ->orderBy($ordered_by, $sort_type)
                ->get();

            $total = count($questions);

            return response()->json([
              'status_code' => 200,
               'message' => 'success',
                'data'  => $questions,
                'total' => $total,
            ], 200);
        } 

        elseif (in_array('Vendor', $user_roles)) {
            $vendor        = AddVendor::where('userid_id', Auth::user()->id)->first();
            $vendor_id     = $vendor->id;

            $get_questions = Productquestion::where('vendor_id', $vendor_id)
                            ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)
                            ->get();
            $total = count($get_questions);

            return response()->json([
              'status_code' => 200,
               'message' => 'success',
                'data'  => $get_questions,
                'total' => $total,
            ], 200);
        }
        elseif (in_array('Staff', $user_roles) || in_array('Manager', $user_roles)) {
            
            $exist_staff   = Vendorstaff::where('email', Auth::user()->email)->first();
            $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
            $vendor_id     = $vendor->id;

            $get_questions = Productquestion::where('vendor_id', $vendor_id)
                            ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                            ->orderBy($ordered_by, $sort_type)
                            ->get();
            $total = count($get_questions);

            return response()->json([
              'status_code' => 200,
               'message' => 'success',
                'data'  => $get_questions,
                'total' => $total,
            ], 200);
        }
        else{
        return response()->json([
          'status_code'     => 401,
                'message'  => 'un authorized access page due to permissions',
               ], 401);
      } // end else 
    }

    public function vendor_fetch_specific_question($id)
    {
        // $id        = $request->vendor_id;
        $user = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();

       // if (in_array('Vendor', $user_roles)) 
       // {
            $question = Productquestion::findOrFail($id);
            $data = new SpecificProdQuestionsApiResource($question);
            return response()->json([
                    'status_code' => 200,
                    'message' => 'success',
                    'data'  => $data,
                  //  'total' => $total,
                ], 200);
       // }
    }
}
