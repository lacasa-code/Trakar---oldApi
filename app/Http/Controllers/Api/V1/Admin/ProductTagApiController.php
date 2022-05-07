<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductTagRequest;
use App\Http\Requests\UpdateProductTagRequest;
use App\Http\Resources\Admin\ProductTagResource;
use App\Models\ProductTag;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\SearchApisRequest;
use App\Http\Requests\MassDestroyProductTagRequest;

class ProductTagApiController extends Controller
{
  public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }

    public function index(Request $request)
    {
       $lang = $this->getLang();
      // $PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page; 
        abort_if(Gate::denies('product_tag_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
       // return new ProductTagResource(ProductTag::paginate($PAGINATION_COUNT));
        return response()->json([
          'status_code' => 200,
          'message' => 'success',
            'data'  => new ProductTagResource(ProductTag::where('lang', $lang)->skip(($page-1)*$PAGINATION_COUNT)
                                    ->take($PAGINATION_COUNT)
                                    ->orderBy($ordered_by, $sort_type)->get()),
            'total' => ProductTag::where('lang', $lang)->count()
        ], 200);
    }

    public function store(StoreProductTagRequest $request)
    {
       $lang = $this->getLang();
      $request['lang'] = $lang;
        $productTag = ProductTag::create($request->all());

        return response()->json([
            'status_code'   => 201,
            'message'       => 'success',
            'data'          => new ProductTagResource($productTag),
          ], Response::HTTP_CREATED);
    }

    public function show(ProductTag $productTag)
    {
       $lang = $this->getLang();
      //$request['lang'] = $lang;
        abort_if(Gate::denies('product_tag_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => new ProductTagResource($productTag),
          ], 200);
    }

    public function update(UpdateProductTagRequest $request, ProductTag $productTag)
    {
       $lang = $this->getLang();
      $request['lang'] = $lang;
        $productTag->update($request->all());

        return response()->json([
            'status_code'   => 202,
            'message'       => 'success',
            'data'          => new ProductTagResource($productTag),
          ], Response::HTTP_ACCEPTED);
    }

    public function destroy(ProductTag $productTag)
    {
        abort_if(Gate::denies('product_tag_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $productTag->delete();
        return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => null
          ], 200);
       // return response(null, Response::HTTP_NO_CONTENT);
    }

    // start mass delete tags
     public function mass_delete(MassDestroyProductTagRequest $request)
     {
        $ids = json_decode($request->ids);
        foreach ($ids as $id) {
            $productTag = ProductTag::findOrFail($id);
            if ($productTag && $productTag->tagidproducts->count() > 0) {
               return response()->json([
                'status_code' => 200,
                'message' => 'fail',
                'errors' => 'this item is not empty te be deleted ('. $productTag->name. ' )',
                ], Response::HTTP_UNAUTHORIZED);
            }
        }
        ProductTag::whereIn('id', $ids)->delete();
        return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => null
          ], 200);
        // return response(null, Response::HTTP_NO_CONTENT);
     }
     // end mass delete tags

      // start list all
     public function list_all()
     {
        $lang = $this->getLang();
        $data = ProductTag::get();
        //$data = ProductTag::where('lang', $lang)->get();
        return response()->json([
          'status_code' => 200,
          'message' => 'success',
          'data' => $data], Response::HTTP_OK);
     }
     // end list all 
}
