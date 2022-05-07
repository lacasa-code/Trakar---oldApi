<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\Admin\HelpCentersApiResource;
use App\Http\Resources\Admin\SpecificHelpCenterApiResource;
use App\Models\Helpcenter;
use Gate;
use App\Http\Requests\AddQuestionApiRequest;
use App\Http\Requests\UpdateQuestionApiRequest;
use App\Http\Requests\MassDestroyQuestionApiRequest;
use App\Http\Requests\SearchApisRequest;
use Auth;
use Symfony\Component\HttpFoundation\Response;

class HelpCenterApiController extends Controller
{
  public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }

    public function index(Request $request)
    {
      $lang = $this->getLang();
      $PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;

       abort_if(Gate::denies('help_center_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
       $faqs = Helpcenter::where('lang', $lang)->orderBy($ordered_by, $sort_type)->get();
       $data = HelpCentersApiResource::collection($faqs);
        return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => Helpcenter::where('lang', $lang)->count(),
        ], 200);
    }

    public function home_faqs(Request $request)
    {
      $lang = $this->getLang();
      $PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;

     //  abort_if(Gate::denies('help_center_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
       $faqs = Helpcenter::where('lang', $lang)->orderBy($ordered_by, $sort_type)->get();
       $data = HelpCentersApiResource::collection($faqs);
        return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $data,
            'total' => Helpcenter::where('lang', $lang)->count(),
            'lang'  => $lang,
        ], 200);
    }

    public function add_question(AddQuestionApiRequest $request)
    {
      $lang = $this->getLang();
      $request['lang'] = $lang;
        $help_center = Helpcenter::create($request->all());
        return response()->json([
            'status_code'   => 201,
            'message'       => 'success',
            'data'          => new SpecificHelpCenterApiResource($help_center),
          ], Response::HTTP_CREATED);
    }

    public function show_question(Helpcenter $question)
    {
      $lang = $this->getLang();
      //$request['lang'] = $lang;
        abort_if(Gate::denies('help_center_show_specific'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => new SpecificHelpCenterApiResource($question),
          ], 200);
    }

    public function update_question(UpdateQuestionApiRequest $request, Helpcenter $question)
    {
      $lang = $this->getLang();
      $request['lang'] = $lang;
        $question->update($request->all());
        return response()->json([
            'status_code'   => 202,
            'message'       => 'success',
            'data'          => new SpecificHelpCenterApiResource($question),
          ], Response::HTTP_ACCEPTED);
    }

    public function destroy_question(Helpcenter $question)
    {
        abort_if(Gate::denies('help_center_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $question->delete();
        return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => null
          ], 200);
        // return response(null, Response::HTTP_NO_CONTENT);
    }

     // start search questions with name
     public function search_with_name(SearchApisRequest $request)
     {
      $lang = $this->getLang();
     // $request['lang'] = $lang;
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;  
      $search_index = $request->search_index;
      $questions = Helpcenter::where(function ($q) use ($search_index) {
                  $q->where('question', 'like', "%{$search_index}%")
                    ->orWhere('answer', 'like', "%{$search_index}%");
                    })->orderBy($ordered_by, $sort_type)->get();
        $data  = HelpCentersApiResource::collection($questions);
        $total = Helpcenter::where(function ($q) use ($search_index) {
                  $q->where('question', 'like', "%{$search_index}%")
                    ->orWhere('answer', 'like', "%{$search_index}%");
                    })->count();

        
        return response()->json([
          'status_code' => 200,
          'message' => 'success',
            'data'  => $data,
            'total' => $total,
        ], 200);
     }
    // end search questions with name


      // start mass delete stores
     public function mass_delete(MassDestroyQuestionApiRequest $request)
     {
        $ids = json_decode($request->ids);
        Helpcenter::whereIn('id', $ids)->delete();
        return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => null
          ], 200);
        // return response(null, Response::HTTP_NO_CONTENT);
     }
     // end mass delete stores 
}
