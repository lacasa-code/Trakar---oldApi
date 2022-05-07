<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\CsvImportTrait;
use App\Http\Requests\MassDestroyCarMadeRequest;
use App\Http\Requests\StoreCarMadeRequest;
use App\Http\Requests\UpdateCarMadeRequest;
use App\Models\CarMade;
use App\Models\ProductCategory;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class CarMadeController extends Controller
{
    use CsvImportTrait;

    public function index(Request $request)
    {
        abort_if(Gate::denies('car_made_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->ajax()) {
            $query = CarMade::with(['categoryid'])->select(sprintf('%s.*', (new CarMade)->table));
            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate      = 'car_made_show';
                $editGate      = 'car_made_edit';
                $deleteGate    = 'car_made_delete';
                $crudRoutePart = 'car-mades';

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
            $table->addColumn('categoryid_name', function ($row) {
                return $row->categoryid ? $row->categoryid->name : '';
            });

            $table->editColumn('car_made', function ($row) {
                return $row->car_made ? $row->car_made : "";
            });

            $table->rawColumns(['actions', 'placeholder', 'categoryid']);

            return $table->make(true);
        }

        $product_categories = ProductCategory::get();

        return view('admin.carMades.index', compact('product_categories'));
    }

    public function create()
    {
        abort_if(Gate::denies('car_made_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $categoryids = ProductCategory::all()->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        return view('admin.carMades.create', compact('categoryids'));
    }

    public function store(StoreCarMadeRequest $request)
    {
        $carMade = CarMade::create($request->all());

        return redirect()->route('admin.car-mades.index');
    }

    public function edit(CarMade $carMade)
    {
        abort_if(Gate::denies('car_made_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $categoryids = ProductCategory::all()->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $carMade->load('categoryid');

        return view('admin.carMades.edit', compact('categoryids', 'carMade'));
    }

    public function update(UpdateCarMadeRequest $request, CarMade $carMade)
    {
        // return $carMade;
        $carMade->update($request->all());

        return redirect()->route('admin.car-mades.index');
    }

    public function show(CarMade $carMade)
    {
        abort_if(Gate::denies('car_made_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $carMade->load('categoryid', 'carmadeCarModels', 'carMadeProducts');

        return view('admin.carMades.show', compact('carMade'));
    }

    public function destroy(CarMade $carMade)
    {
        abort_if(Gate::denies('car_made_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $carMade->delete();

        return back();
    }

    public function massDestroy(MassDestroyCarMadeRequest $request)
    {
        CarMade::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
