<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\CsvImportTrait;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\MassDestroyAddVendorRequest;
use App\Http\Requests\StoreAddVendorRequest;
use App\Http\Requests\UpdateAddVendorRequest;
use App\Models\AddVendor;
use App\Models\User;
use Gate;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class AddVendorController extends Controller
{
    use MediaUploadingTrait, CsvImportTrait;

    public function index(Request $request)
    {
        abort_if(Gate::denies('add_vendor_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->ajax()) {
            $query = AddVendor::with(['userid'])->select(sprintf('%s.*', (new AddVendor)->table));
            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate      = 'add_vendor_show';
                $editGate      = 'add_vendor_edit';
                $deleteGate    = 'add_vendor_delete';
                $crudRoutePart = 'add-vendors';

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
            $table->editColumn('serial', function ($row) {
                return $row->serial ? $row->serial : "";
            });
            $table->editColumn('vendor_name', function ($row) {
                return $row->vendor_name ? $row->vendor_name : "";
            });
            $table->editColumn('email', function ($row) {
                return $row->email ? $row->email : "";
            });
            $table->addColumn('userid_name', function ($row) {
                return $row->userid ? $row->userid->name : '';
            });

            $table->editColumn('images', function ($row) {
                if ($photo = $row->images) {
                    return sprintf(
                        '<a href="%s" target="_blank"><img src="%s" width="50px" height="50px"></a>',
                        $row->avatar,
                        $photo->thumbnail
                    );
                }

                return '';
            });

            $table->rawColumns(['actions', 'placeholder', 'userid', 'images']);

            return $table->make(true);
        }

        $users = User::get();

        return view('admin.addVendors.index', compact('users'));
    }

    public function create()
    {
        abort_if(Gate::denies('add_vendor_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $userids = User::all()->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        return view('admin.addVendors.create', compact('userids'));
    }

    public function store(StoreAddVendorRequest $request)
    {
        $id = \DB::table('add_vendors')->latest('created_at')->first();
    
        if ($id === NULL) {
              if($request->type=='1')
            {
                $request['serial']='V001';
            }elseif ($request->type=='2')
            {
                $request['serial']='H001';
            }elseif ($request->type=='3')
            {
                $request['serial']='VH001';
            }
        }
        else{
                if($request->type=='1')
            {
                $request['serial']='V00'.($id->id + 1);
            }elseif ($request->type=='2')
            {
                $request['serial']='H00'.($id->id + 1);
            }elseif ($request->type=='3')
            {
                $request['serial']='VH00'.($id->id + 1);
            }
        }

        $addVendor = AddVendor::create($request->all());

        if ($request->input('images', false)) {
            $addVendor->addMedia(storage_path('tmp/uploads/' . $request->input('images')))->toMediaCollection('images');
        }

        if ($media = $request->input('ck-media', false)) {
            Media::whereIn('id', $media)->update(['model_id' => $addVendor->id]);
        }

        return redirect()->route('admin.add-vendors.index');
    }

    public function edit(AddVendor $addVendor)
    {
        abort_if(Gate::denies('add_vendor_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $userids = User::all()->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $addVendor->load('userid');

        return view('admin.addVendors.edit', compact('userids', 'addVendor'));
    }

    public function update(UpdateAddVendorRequest $request, AddVendor $addVendor)
    {

        $addVendor->update($request->all());

        if ($request->input('images', false)) {
            if (!$addVendor->images || $request->input('images') !== $addVendor->images->file_name) {
                if ($addVendor->images) {
                    $addVendor->images->delete();
                }

                $addVendor->addMedia(storage_path('tmp/uploads/' . $request->input('images')))->toMediaCollection('images');
            }
        } elseif ($addVendor->images) {
            $addVendor->images->delete();
        }

        return redirect()->route('admin.add-vendors.index');
    }

    public function show(AddVendor $addVendor)
    {
        abort_if(Gate::denies('add_vendor_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $addVendor->load('userid');

        return view('admin.addVendors.show', compact('addVendor'));
    }

    public function destroy(AddVendor $addVendor)
    {
        abort_if(Gate::denies('add_vendor_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $addVendor->delete();

        return back();
    }

    public function massDestroy(MassDestroyAddVendorRequest $request)
    {
        AddVendor::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function storeCKEditorImages(Request $request)
    {
        abort_if(Gate::denies('add_vendor_create') && Gate::denies('add_vendor_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $model         = new AddVendor();
        $model->id     = $request->input('crud_id', 0);
        $model->exists = true;
        $media         = $model->addMediaFromRequest('upload')->toMediaCollection('ck-media');

        return response()->json(['id' => $media->id, 'url' => $media->getUrl()], Response::HTTP_CREATED);
    }
}
