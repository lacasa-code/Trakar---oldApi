<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\CsvImportTrait;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\MassDestroyProductRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\CarMade;
use App\Models\CarModel;
use App\Models\CarYear;
use App\Models\PartCategory;
use App\Models\Product;
use App\Models\ProductCategory;
use Gate;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    use MediaUploadingTrait, CsvImportTrait;

    public function index(Request $request)
    {
        abort_if(Gate::denies('product_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->ajax()) {
            $query = Product::with(['categories', 'car_made', 'car_model', 'year', 'part_category'])->select(sprintf('%s.*', (new Product)->table));
            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate      = 'product_show';
                $editGate      = 'product_edit';
                $deleteGate    = 'product_delete';
                $crudRoutePart = 'products';

                return view('partials.datatablesActions', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'crudRoutePart',
                    'row'
                ));
            });

            $table->editColumn('id', function ($row) {
                return $row->id ? $row->id : "";
            });
            $table->editColumn('category', function ($row) {
                $labels = [];

                foreach ($row->categories as $category) {
                    $labels[] = sprintf('<span class="label label-info label-many">%s</span>', $category->name);
                }

                return implode(' ', $labels);
            });
            $table->addColumn('car_made_car_made', function ($row) {
                return $row->car_made ? $row->car_made->car_made : '';
            });

            $table->addColumn('car_model_carmodel', function ($row) {
                return $row->car_model ? $row->car_model->carmodel : '';
            });

            $table->addColumn('year_year', function ($row) {
                return $row->year ? $row->year->year : '';
            });

            $table->addColumn('part_category_category_name', function ($row) {
                return $row->part_category ? $row->part_category->category_name : '';
            });

            $table->editColumn('name', function ($row) {
                return $row->name ? $row->name : "";
            });
            $table->editColumn('description', function ($row) {
                return $row->description ? $row->description : "";
            });
            $table->editColumn('discount', function ($row) {
                return $row->discount ? $row->discount : "";
            });
            $table->editColumn('price', function ($row) {
                return $row->price ? $row->price : "";
            });
            $table->editColumn('photo', function ($row) {
                if (!$row->photo) {
                    return '';
                }

                $links = [];

                foreach ($row->photo as $media) {
                    $links[] = '<a href="' . $media->getUrl() . '" target="_blank"><img src="' . $media->getUrl('thumb') . '" width="50px" height="50px"></a>';
                }

                return implode(' ', $links);
            });

            $table->rawColumns(['actions', 'placeholder', 'category', 'car_made', 'car_model', 'year', 'part_category', 'photo']);

            return $table->make(true);
        }

        $product_categories = ProductCategory::get();
        $car_mades          = CarMade::get();
        $car_models         = CarModel::get();
        $car_years          = CarYear::get();
        $part_categories    = PartCategory::get();

        return view('admin.products.index', compact('product_categories', 'car_mades', 'car_models', 'car_years', 'part_categories'));
    }

    public function create()
    {
        abort_if(Gate::denies('product_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $categories = ProductCategory::all()->pluck('name', 'id');

        $car_mades = CarMade::all()->pluck('car_made', 'id')->prepend(trans('global.pleaseSelect'), '');

        $car_models = CarModel::all()->pluck('carmodel', 'id')->prepend(trans('global.pleaseSelect'), '');

        $years = CarYear::all()->pluck('year', 'id')->prepend(trans('global.pleaseSelect'), '');

        $part_categories = PartCategory::all()->pluck('category_name', 'id')->prepend(trans('global.pleaseSelect'), '');

        return view('admin.products.create', compact('categories', 'car_mades', 'car_models', 'years', 'part_categories'));
    }

    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->all());
        $product->categories()->sync($request->input('categories', []));

        foreach ($request->input('photo', []) as $file) {
            $product->addMedia(storage_path('tmp/uploads/' . $file))->toMediaCollection('photo');
        }

        if ($media = $request->input('ck-media', false)) {
            Media::whereIn('id', $media)->update(['model_id' => $product->id]);
        }

        return redirect()->route('admin.products.index');
    }

    public function edit(Product $product)
    {
        abort_if(Gate::denies('product_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $categories = ProductCategory::all()->pluck('name', 'id');

        $car_mades = CarMade::all()->pluck('car_made', 'id')->prepend(trans('global.pleaseSelect'), '');

        $car_models = CarModel::all()->pluck('carmodel', 'id')->prepend(trans('global.pleaseSelect'), '');

        $years = CarYear::all()->pluck('year', 'id')->prepend(trans('global.pleaseSelect'), '');

        $part_categories = PartCategory::all()->pluck('category_name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $product->load('categories', 'car_made', 'car_model', 'year', 'part_category');

        return view('admin.products.edit', compact('categories', 'car_mades', 'car_models', 'years', 'part_categories', 'product'));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->all());
        $product->categories()->sync($request->input('categories', []));

        if (count($product->photo) > 0) {
            foreach ($product->photo as $media) {
                if (!in_array($media->file_name, $request->input('photo', []))) {
                    $media->delete();
                }
            }
        }

        $media = $product->photo->pluck('file_name')->toArray();

        foreach ($request->input('photo', []) as $file) {
            if (count($media) === 0 || !in_array($file, $media)) {
                $product->addMedia(storage_path('tmp/uploads/' . $file))->toMediaCollection('photo');
            }
        }

        return redirect()->route('admin.products.index');
    }

    public function show(Product $product)
    {
        abort_if(Gate::denies('product_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $product->load('categories', 'car_made', 'car_model', 'year', 'part_category');

        return view('admin.products.show', compact('product'));
    }

    public function destroy(Product $product)
    {
        abort_if(Gate::denies('product_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $product->delete();

        return back();
    }

    public function massDestroy(MassDestroyProductRequest $request)
    {
        Product::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function storeCKEditorImages(Request $request)
    {
        abort_if(Gate::denies('product_create') && Gate::denies('product_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $model         = new Product();
        $model->id     = $request->input('crud_id', 0);
        $model->exists = true;
        $media         = $model->addMediaFromRequest('upload')->toMediaCollection('ck-media');

        return response()->json(['id' => $media->id, 'url' => $media->getUrl()], Response::HTTP_CREATED);
    }
}
