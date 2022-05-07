<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\CsvImportTrait;
use App\Http\Requests\MassDestroyCarModelRequest;
use App\Http\Requests\StoreCarModelRequest;
use App\Http\Requests\UpdateCarModelRequest;
use App\Models\CarMade;
use App\Models\CarModel;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class CarModelController extends Controller
{
    use CsvImportTrait;

    public function index(Request $request)
    {
        abort_if(Gate::denies('car_model_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->ajax()) {
            $query = CarModel::with(['carmade'])->select(sprintf('%s.*', (new CarModel)->table));
            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate      = 'car_model_show';
                $editGate      = 'car_model_edit';
                $deleteGate    = 'car_model_delete';
                $crudRoutePart = 'car-models';

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
            $table->addColumn('carmade_car_made', function ($row) {
                return $row->carmade ? $row->carmade->car_made : '';
            });

            $table->editColumn('carmodel', function ($row) {
                return $row->carmodel ? $row->carmodel : "";
            });

            $table->rawColumns(['actions', 'placeholder', 'carmade']);

            return $table->make(true);
        }

        $car_mades = CarMade::get();

        return view('admin.carModels.index', compact('car_mades'));
    }

    public function create()
    {
        abort_if(Gate::denies('car_model_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $carmades = CarMade::all()->pluck('car_made', 'id')->prepend(trans('global.pleaseSelect'), '');

        return view('admin.carModels.create', compact('carmades'));
    }

    public function store(StoreCarModelRequest $request)
    {
        $carModel = CarModel::create($request->all());

        return redirect()->route('admin.car-models.index');
    }

    public function edit(CarModel $carModel)
    {
        abort_if(Gate::denies('car_model_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $carmades = CarMade::all()->pluck('car_made', 'id')->prepend(trans('global.pleaseSelect'), '');

        $carModel->load('carmade');

        return view('admin.carModels.edit', compact('carmades', 'carModel'));
    }

    public function update(UpdateCarModelRequest $request, CarModel $carModel)
    {
        $carModel->update($request->all());

        return redirect()->route('admin.car-models.index');
    }

    public function show(CarModel $carModel)
    {
        abort_if(Gate::denies('car_model_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $carModel->load('carmade', 'carModelProducts');

        return view('admin.carModels.show', compact('carModel'));
    }

    public function destroy(CarModel $carModel)
    {
        abort_if(Gate::denies('car_model_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $carModel->delete();

        return back();
    }

    public function massDestroy(MassDestroyCarModelRequest $request)
    {
        CarModel::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
