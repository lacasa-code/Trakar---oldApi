<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\StoreProductCategoryRequest;
use App\Http\Requests\UpdateProductCategoryRequest;
use App\Http\Resources\Admin\ProductCategoryResource;
use App\Models\ProductCategory;
use App\Models\Product;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Http\Requests\StoreMediaCategoryRequest;
use App\Http\Requests\SearchApisRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\MassDestroyProductCategoryRequest;
use App\Models\Avatar;
use Auth;
use App\Models\Maincategory;

class ProductCategoryApiController extends Controller
{
    use MediaUploadingTrait;

    public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }

    public function index(Request $request)
    {
      $lang = $this->getLang();
      // $PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'DESC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
        abort_if(Gate::denies('product_category_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
       // return new ProductCategoryResource(ProductCategory::paginate(PAGINATION_COUNT));
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
        'data'  => new ProductCategoryResource(ProductCategory::skip(($page-1)*$PAGINATION_COUNT)
                        ->take($PAGINATION_COUNT)->orderBy($ordered_by, $sort_type)->get()),
        'total' => ProductCategory::count()
        ], 200);
    }

    public function showWithMedia($id)
    {
      $productCategory = ProductCategory::find($id);
      return (new ProductCategoryResource($productCategory))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function store(StoreProductCategoryRequest $request)
    {
        $lang = $this->getLang();
        $request['lang'] = $lang;
        $productCategory = ProductCategory::create($request->all());
        // file data
        /* new */
        $image = $request->file('photo');
        $imageFileName = time() . '.' . $image->getClientOriginalExtension();
        $path = Storage::disk('spaces')->putFile('product-categories', $image);
        Storage::disk('spaces')->setVisibility($path, 'public');
        $url   = Storage::disk('spaces')->url($path);
       // return $url;
        $productCategory->addMediaFromUrl($url)
                       ->toMediaCollection('photo');
        /* new */
        return $this->showWithMedia($productCategory->id);
    }

    public function show(ProductCategory $productCategory)
    {
      $lang = $this->getLang();
      abort_if(Gate::denies('product_category_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
      return response()->json([
        'status_code'     => 200,
        'message'         => 'success',
        'data' => [
          'id'           =>  $productCategory->id,
          "name"         =>  $productCategory->name,
          "description"  =>  $productCategory->description,
          "created_at"   =>  $productCategory->created_at,
          "updated_at"   =>  $productCategory->updated_at,
          "deleted_at"   =>  $productCategory->deleted_at,
          "photo"        =>  $productCategory->photo,
          "media"        =>  $productCategory->media,
        ]
      ], 200);
     // return new ProductCategoryResource($productCategory);
    }

    public function showWithUpdated($id)
    {
      $productCategory = ProductCategory::find($id);
      return (new ProductCategoryResource($productCategory))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }
    
    public function update(UpdateProductCategoryRequest $request, ProductCategory $productCategory)
    {
        $lang = $this->getLang();
        $request['lang'] = $lang;
        $productCategory->update($request->all());

// change media only on change of input request 
        if ($request->has('photo') && $request->photo != '') {
            if (!$productCategory->photo || $request->file('photo') !== $productCategory->photo->file_name) {
                if ($productCategory->photo) {
                    $productCategory->photo->delete();
                }
                    /* new */
                    $image = $request->file('photo');
                    $path = Storage::disk('spaces')->putFile('product-categories', $image);
                    Storage::disk('spaces')->setVisibility($path, 'public');
                    $url   = Storage::disk('spaces')->url($path);
                     $productCategory->addMediaFromUrl($url)
                                     ->toMediaCollection('photo');
                    /* new */
            }
        } 
        //elseif ($productCategory->photo) {
          //  $productCategory->photo->delete();
       // }
        return $this->showWithUpdated($productCategory->id);
    }

    public function destroy(ProductCategory $productCategory)
    {
        abort_if(Gate::denies('product_category_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        if ($productCategory->categoryidproducts->count() > 0) {
           return response()->json(['errors' => 'This product category is not empty to be deleted'], 
                                    Response::HTTP_UNAUTHORIZED);
        }
        else{
            $productCategory->delete();
            return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => null
          ], 200);
          // return response(null, Response::HTTP_NO_CONTENT);
        }
    }

    // added new
    // get products media 
    public function storeMedia(StoreMediaCategoryRequest $request)
    {
        $productCategory = ProductCategory::find($request->id);
        $medias          = $productCategory->photo;
        return response()->json([
          'status_code' => 200,
          'message' => 'success',
          'data' => $medias], Response::HTTP_OK);
    }
    // get products media

    // start search categories with name
     public function search_with_name(SearchApisRequest $request)
     {
      $lang = $this->getLang();
      // $PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
        $search_index = $request->search_index;
        $categories = ProductCategory::where(function ($q) use ($search_index) {
                $q->where('name', 'like', "%{$search_index}%")
                ->orWhere('description', 'like', "%{$search_index}%");
            })->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                                ->orderBy($ordered_by, $sort_type)->get();

        $total = ProductCategory::where(function ($q) use ($search_index) {
                $q->where('name', 'like', "%{$search_index}%")
                ->orWhere('description', 'like', "%{$search_index}%");
            })->count();
        return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data' => $categories,
            'total' => $total,
        ], 200);
     }
    // end search categories with name

     // start mass delete categories
     public function mass_delete(MassDestroyProductCategoryRequest $request)
     {
        $ids = json_decode($request->ids);
        foreach ($ids as $id) {
            $productCategory = ProductCategory::findOrFail($id);
            if ($productCategory->categoryidproducts->count() > 0) {
               return response()->json([
                'status_code' => 401,
                'message' => 'fail',
                'errors' => 'this item is not empty te be deleted ('. $productCategory->name. ' )',
                ], Response::HTTP_UNAUTHORIZED);
            }
        }
        ProductCategory::whereIn('id', $ids)->delete();
        return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => null
          ], 200);
        // return response(null, Response::HTTP_NO_CONTENT);
     }
     // end mass delete categories

     // start list all
     public function list_all()
     {
        $lang = $this->getLang();
        //$data = ProductCategory::get();
        $data = ProductCategory::get();
        return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data' => $data], Response::HTTP_OK);
     }
     // end list all 

      // start list all
     public function list_all_maincategory($id)
     {
        $lang = $this->getLang();
      //  $data = ProductCategory::where('maincategory_id', $id)->get();
        $data = ProductCategory::where('maincategory_id', $id)->get();
        return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data' => $data], Response::HTTP_OK);
     }
     // end list all 
}
