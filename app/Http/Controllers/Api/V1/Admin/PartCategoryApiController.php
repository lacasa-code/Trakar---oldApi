<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\StorePartCategoryRequest;
use App\Http\Requests\UpdatePartCategoryRequest;
use App\Http\Resources\Admin\PartCategoryResource;
use App\Models\PartCategory;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\StoreMediaPartCategoryRequest;
use Auth;
use App\Http\Requests\SearchApisRequest;
use App\Http\Requests\MassDestroyPartCategoryRequest;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\Storage;
use App\Models\ProductCategory;

class PartCategoryApiController extends Controller
{
    use MediaUploadingTrait;

  public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }

    public function index(Request $request)
    {
      $lang = $this->getLang();
      //$PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'DESC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
       
        abort_if(Gate::denies('part_category_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
            'data'  => new PartCategoryResource(PartCategory::with('category')->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)->orderBy($ordered_by, $sort_type)->get()),
            'total' => PartCategory::count()
        ], 200);
    }

    public function showWithMedia($id)
    {
      $partCategory = PartCategory::find($id);
      return (new PartCategoryResource($partCategory))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function store(StorePartCategoryRequest $request)
    {
      $lang = $this->getLang();
      $request['lang'] = $lang;
        $partCategory = PartCategory::create($request->all());
        // file data
        /* new */
        $image = $request->file('photo');
        $imageFileName = time() . '.' . $image->getClientOriginalExtension();
        $path = Storage::disk('spaces')->putFile('part-categories', $image);
        Storage::disk('spaces')->setVisibility($path, 'public');
        $url   = Storage::disk('spaces')->url($path);
       // return $url;
        $partCategory->addMediaFromUrl($url)
                       ->toMediaCollection('photo');
        /* new */
        return $this->showWithMedia($partCategory->id);
    }

    public function show(PartCategory $partCategory)
    {
      $lang = $this->getLang();
        abort_if(Gate::denies('part_category_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data' => [
                    "id"            => $partCategory->id,
                    "category_name" => $partCategory->category_name,
                    'name_en'       => $partCategory->name_en,
                    "created_at"    => $partCategory->created_at, 
                    "updated_at"    => $partCategory->updated_at, 
                    "deleted_at"    => $partCategory->deleted_at, 
                    "photo"         => $partCategory->photo, 
                    "media"         => $partCategory->media, 
                    "product_category_id" => $partCategory->category_id,
                    "product_category_name" => $partCategory->category->name,  
        ]], 200);
       // return new PartCategoryResource($partCategory);
    }

    public function showWithUpdated($id)
    {
      $partCategory = PartCategory::find($id);
      return (new PartCategoryResource($partCategory))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function update(UpdatePartCategoryRequest $request, PartCategory $partCategory)
    {
      $lang = $this->getLang();
      $request['lang'] = $lang;
        $partCategory->update($request->all());

        // change media only on change of input request 
        if ($request->has('photo') && $request->photo != '') {
            if (!$partCategory->photo || $request->file('photo') !== $partCategory->photo->file_name) {
                if ($partCategory->photo) {
                    $partCategory->photo->delete();
                }
                    /* new */
                    $image = $request->file('photo');
                    $path = Storage::disk('spaces')->putFile('part-categories', $image);
                    Storage::disk('spaces')->setVisibility($path, 'public');
                    $url   = Storage::disk('spaces')->url($path);
                     $partCategory->addMediaFromUrl($url)
                                     ->toMediaCollection('photo');
                    /* new */
            }
        } 
        //elseif ($partCategory->photo) {
          //  $partCategory->photo->delete();
       // }
        return $this->showWithUpdated($partCategory->id);
    }

    public function destroy(PartCategory $partCategory)
    {
        abort_if(Gate::denies('part_category_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
         if ($partCategory->partCategoryProducts->count() > 0) {
           return response()->json([
            'errors' => 'This part category is not empty to be deleted'], 
                                    Response::HTTP_UNAUTHORIZED);
        }
        else{
            $partCategory->delete();
            return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => null
          ], 200);
          //  return response(null, Response::HTTP_NO_CONTENT);
        }
    }

    // added new
    // get products media 
    public function storeMedia(StoreMediaPartCategoryRequest $request)
    {
        $partCategory = PartCategory::find($request->id);
        $medias       = $partCategory->photo;
        return response()->json([
          'status_code' => 200,
          'message' => 'success',
          'data' => $medias], Response::HTTP_OK);
    }
    // get products media

     // start search part categories with name
     public function search_with_name(SearchApisRequest $request)
     {
      $lang = $this->getLang();
     // $request['lang'] = $lang;
      //$PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      
        $search_index = $request->search_index;
        $part_categories = PartCategory::where(function ($q) use ($search_index) {
                $q->where('category_name', 'like', "%{$search_index}%")
                ->orWhere('name_en', 'like', "%{$search_index}%");
            })->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
              ->orderBy($ordered_by, $sort_type)->get();
        $total = PartCategory::where(function ($q) use ($search_index) {
                $q->where('category_name', 'like', "%{$search_index}%")
                ->orWhere('name_en', 'like', "%{$search_index}%");
            })->count();
        return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data'  => $part_categories,
            'total' => $total,
        ], 200);
     }
    // end search part categories with name

     // start mass delete part categories
     public function mass_delete(MassDestroyPartCategoryRequest $request)
     {
        $ids = json_decode($request->ids);
        foreach ($ids as $id) {
            $partCategory = PartCategory::findOrFail($id);
            if ($partCategory->partCategoryProducts->count() > 0) {
               return response()->json([
            'status_code'   => 401,
            'message'       => 'fail',
            'errors' => 'this item is not empty te be deleted ('. $partCategory->category_name. ' )',
                ], Response::HTTP_UNAUTHORIZED);
            }
        }
        PartCategory::whereIn('id', $ids)->delete();
        return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => null
          ], 200);
        // return response(null, Response::HTTP_NO_CONTENT);
     }
     // end mass delete part categories

      // start list all
    /* public function list_all()
     {
        $data = PartCategory::all();
        return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data' => $data], Response::HTTP_OK);
     }
     // end list all */

     // start list all
     public function list_all($id)
     {
        $lang = $this->getLang();
        $productCategory = ProductCategory::find($id);
        if (!$productCategory) {
          return response()->json([
          'status_code'     => 400,
          'message'         => 'fail',
          'errors'          => 'wrong Product Category id',
          'data'            => null,], 400);
        }
        $data     = PartCategory::where('category_id', $id)->get();
       // $data     = PartCategory::where('category_id', $id)->get();
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data' => $data], Response::HTTP_OK);
     }
     // end list all 

     // start list all
     public function list_all_pure()
     {
        $lang = $this->getLang();
        //$data     = PartCategory::get();
        $data     = PartCategory::all();
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data' => $data], Response::HTTP_OK);
     }
     // end list all 

}
