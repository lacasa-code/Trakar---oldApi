<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\CsvImportTrait;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\MassDestroyPartCategoryRequest;
use App\Http\Requests\StorePartCategoryRequest;
use App\Http\Requests\UpdatePartCategoryRequest;
use App\Models\PartCategory;
use Gate;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class PartCategoryController extends Controller
{
    use MediaUploadingTrait, CsvImportTrait;

    public function index(Request $request)
    {
        abort_if(Gate::denies('part_category_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->ajax()) {
            $query = PartCategory::query()->select(sprintf('%s.*', (new PartCategory)->table));
            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate      = 'part_category_show';
                $editGate      = 'part_category_edit';
                $deleteGate    = 'part_category_delete';
                $crudRoutePart = 'part-categories';

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
            $table->editColumn('category_name', function ($row) {
                return $row->category_name ? $row->category_name : "";
            });
            $table->editColumn('photo', function ($row) {
                if ($photo = $row->photo) {
                    return sprintf(
                        '<a href="%s" target="_blank"><img src="%s" width="50px" height="50px"></a>',
                        $photo->url,
                        $photo->thumbnail
                    );
                }

                return '';
            });

            $table->rawColumns(['actions', 'placeholder', 'photo']);

            return $table->make(true);
        }

        return view('admin.partCategories.index');
    }

    public function create()
    {
        abort_if(Gate::denies('part_category_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.partCategories.create');
    }

    public function store(StorePartCategoryRequest $request)
    {
        $partCategory = PartCategory::create($request->all());

        if ($request->input('photo', false)) {
            $partCategory->addMedia(storage_path('tmp/uploads/' . $request->input('photo')))->toMediaCollection('photo');
        }

        if ($media = $request->input('ck-media', false)) {
            Media::whereIn('id', $media)->update(['model_id' => $partCategory->id]);
        }

        return redirect()->route('admin.part-categories.index');
    }

    public function edit(PartCategory $partCategory)
    {
        abort_if(Gate::denies('part_category_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.partCategories.edit', compact('partCategory'));
    }

    public function update(UpdatePartCategoryRequest $request, PartCategory $partCategory)
    {
        $partCategory->update($request->all());

        if ($request->input('photo', false)) {
            if (!$partCategory->photo || $request->input('photo') !== $partCategory->photo->file_name) {
                if ($partCategory->photo) {
                    $partCategory->photo->delete();
                }

                $partCategory->addMedia(storage_path('tmp/uploads/' . $request->input('photo')))->toMediaCollection('photo');
            }
        } elseif ($partCategory->photo) {
            $partCategory->photo->delete();
        }

        return redirect()->route('admin.part-categories.index');
    }

    public function show(PartCategory $partCategory)
    {
        abort_if(Gate::denies('part_category_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $partCategory->load('partCategoryProducts');

        return view('admin.partCategories.show', compact('partCategory'));
    }

    public function destroy(PartCategory $partCategory)
    {
        abort_if(Gate::denies('part_category_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $partCategory->delete();

        return back();
    }

    public function massDestroy(MassDestroyPartCategoryRequest $request)
    {
        PartCategory::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function storeCKEditorImages(Request $request)
    {
        abort_if(Gate::denies('part_category_create') && Gate::denies('part_category_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $model         = new PartCategory();
        $model->id     = $request->input('crud_id', 0);
        $model->exists = true;
        $media         = $model->addMediaFromRequest('upload')->toMediaCollection('ck-media');

        return response()->json(['id' => $media->id, 'url' => $media->getUrl()], Response::HTTP_CREATED);
    }
}
