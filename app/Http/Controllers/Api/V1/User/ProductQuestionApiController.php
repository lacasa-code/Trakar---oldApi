<?php

namespace App\Http\Controllers\Api\V1\User;

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
use App\Models\Helpcenter;
use App\Http\Resources\Admin\HelpCentersApiResource;

class ProductQuestionApiController extends Controller
{
    public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }

    public function prod_all_questions(Request $request, $id)
    {
        // $id        = $request->vendor_id;
       // $user = Auth::user();
        //$user_roles = $user->roles->pluck('title')->toArray();
       $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'DESC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;

        $product = Product::findOrFail($id);     

        //if (in_array('Vendor', $user_roles) || in_array('User', $user_roles)) 
        //{
         //   $vendor    = AddVendor::where('userid_id', $user->id)->first();
            $questions = Productquestion::where('product_id', $product->id)
                                    ->orderBy($ordered_by, $sort_type)
                                    ->get();
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

    public function home_faqs(Request $request)
    {
      $lang = $this->getLang();
      $PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;

     //  abort_if(Gate::denies('help_center_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
      // $faqs = Helpcenter::where('lang', $lang)->orderBy($ordered_by, $sort_type)->get();
       $faqs = Helpcenter::where('lang', $lang)->orderBy($ordered_by, $sort_type)->get();
       $data = HelpCentersApiResource::collection($faqs);
        return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => Helpcenter::where('lang', $lang)->count(),
        ], 200);
    }

}
