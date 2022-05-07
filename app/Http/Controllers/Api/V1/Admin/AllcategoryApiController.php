<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Traits\MediaUploadingTrait;

use App\Http\Resources\Api\Admin\Allcategory\AllcategoryApiResource;
use App\Http\Resources\Api\Admin\Allcategory\SpecificAllcategoryApiResource;
use App\Models\Allcategory;
use Gate;
use Symfony\Component\HttpFoundation\Response;
// use App\Http\Requests\StoreMediaPartCategoryRequest;
use Auth;
use App\Http\Requests\SearchApisRequest;
use App\Http\Requests\Api\Admin\Allcategory\MassDestroyAllcategoryApiRequest;
use App\Http\Requests\Api\Admin\Allcategory\StoreAllcategoryApiRequest;
use App\Http\Requests\Api\Admin\Allcategory\UpdateAllcategoryApiRequest;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Api\Admin\Allcategory\AllcategoryListApiResource;

class AllcategoryApiController extends Controller
{
     use MediaUploadingTrait;

  public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }

    public function showWithMedia($id)
    {
      $allcategory = Allcategory::find($id);
      return response()->json([
          'status_code'   => 200,
          'message'       => 'success',
          'data'          => new SpecificAllcategoryApiResource($allcategory),
        ], 200);
    }

    public function store(StoreAllcategoryApiRequest $request)
    {
        $lang = $this->getLang();
        $request['lang'] = $lang;
        $name = Allcategory::where('name', $request->name)->where('allcategory_id', $request->allcategory_id)->first();
            if ($name != null) {
              return response()->json([
              'status_code'   => 400,
              'errors'       => 'this name has already been taken',
            ], 400);
          }
        $name_en = Allcategory::where('name', $request->name_en)->where('allcategory_id', $request->allcategory_id)
                                                                ->first();
              if ($name_en != null) {
                return response()->json([
                'status_code'   => 400,
                'errors'       => 'this english name has already been taken',
              ], 400);
            }
        $allcategory = Allcategory::create($request->all());

        // file data
        /* new */
        $image = $request->file('photo');
        $imageFileName = time() . '.' . $image->getClientOriginalExtension();
        $path = Storage::disk('spaces')->putFile('all-categories', $image);
        Storage::disk('spaces')->setVisibility($path, 'public');
        $url   = Storage::disk('spaces')->url($path);
       // return $url;
        $allcategory->addMediaFromUrl($url)
                       ->toMediaCollection('photo');
        /* new */
        return $this->showWithMedia($allcategory->id);
    }

    public function show(Allcategory $allcategory)
    {
        $lang = $this->getLang();
        // abort_if(Gate::denies('all_category_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        return response()->json([
          'status_code'   => 200,
          'message'       => 'success',
          'data'          => new SpecificAllcategoryApiResource($allcategory),
        ], 200);
       // return new PartCategoryResource($partCategory);
    }

    public function showWithUpdated($id)
    {
      $allcategory = Allcategory::find($id);
      return response()->json([
          'status_code'   => 200,
          'message'       => 'success',
          'data'          => new SpecificAllcategoryApiResource($allcategory),
        ], 200);
    }

    public function update(UpdateAllcategoryApiRequest $request, Allcategory $allcategory)
    {
        $lang = $this->getLang();
        $request['lang'] = $lang;

         $name = Allcategory::where('name', $request->name)
                            ->where('allcategory_id', $request->allcategory_id)
                            ->where('id', '!=', $allcategory->id)
                            ->first();

            if ($name != null) {
              return response()->json([
              'status_code'   => 400,
              'errors'       => 'this name has already been taken',
            ], 400);
          }
        $name_en = Allcategory::where('name', $request->name_en)
                           ->where('allcategory_id', $request->allcategory_id)
                            ->where('id', '!=', $allcategory->id)
                            ->first();
                            
              if ($name_en != null) {
                return response()->json([
                'status_code'   => 400,
                'errors'       => 'this english name has already been taken',
              ], 400);
            }

        $allcategory->update($request->all());

        // change media only on change of input request 
        if ($request->has('photo') && $request->photo != '') {
            if (!$allcategory->photo || $request->file('photo') !== $allcategory->photo->file_name) {
                if ($allcategory->photo) {
                    $allcategory->photo->delete();
                }
                    /* new */
                    $image = $request->file('photo');
                    $path = Storage::disk('spaces')->putFile('all-categories', $image);
                    Storage::disk('spaces')->setVisibility($path, 'public');
                    $url   = Storage::disk('spaces')->url($path);
                     $allcategory->addMediaFromUrl($url)
                                     ->toMediaCollection('photo');
                    /* new */
            }
        } 
        return $this->showWithUpdated($allcategory->id);
    }

    public function destroy(Allcategory $allcategory)
    {
        // abort_if(Gate::denies('all_category_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
         if ($allcategory->products->count() > 0 || $allcategory->allcategories->count() > 0) {
           return response()->json([
            'errors' => 'This category is not empty to be deleted'], 
                                    Response::HTTP_UNAUTHORIZED);
        }
        else{
            $allcategory->delete();
            return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => null
          ], 200);
          //  return response(null, Response::HTTP_NO_CONTENT);
        }
    }

     // start mass delete part categories
     public function mass_delete(MassDestroyAllcategoryApiRequest $request)
     {
        $ids = json_decode($request->ids);
        foreach ($ids as $id) {
            $allcategory = Allcategory::findOrFail($id);
            if ($allcategory->products->count() > 0 || $allcategory->allcategories->count() > 0) {
               return response()->json([
            'status_code'   => 401,
            'message'       => 'fail',
            'errors' => 'this item is not empty te be deleted ('. $allcategory->name. ' )',
                ], Response::HTTP_UNAUTHORIZED);
            }
        }
        Allcategory::whereIn('id', $ids)->delete();
        return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => null
          ], 200);
        // return response(null, Response::HTTP_NO_CONTENT);
     }
     // end mass delete part categories

     // start list all
     public function list_all($id)
     {
        $lang = $this->getLang();
        $onecategory = Allcategory::findOrFail($id);
        $data     = Allcategory::where('allcategory_id', $id)->get();
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data' => $data], Response::HTTP_OK);
     }
     // end list all 

     public function nested_list_all(Request $request)
    {
      $lang = $this->getLang();
      //$PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'DESC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
       
    // abort_if(Gate::denies('all_category_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
      $categories = Allcategory::whereNull('allcategory_id')
                              ->orderBy($ordered_by, $sort_type)->get();
      $data = AllcategoryListApiResource::collection($categories);
        return response()->json([
            'status_code'     => 200,
            'message'         => 'success',
            'data'          => $data,
           // 'total'         => Allcategory::whereNull('allcategory_id')->count(),
           // Allcategory::findOrFail(1)->allcategories->count(),
        ], 200);
    }

}
