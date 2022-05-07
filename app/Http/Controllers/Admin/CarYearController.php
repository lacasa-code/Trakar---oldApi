<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\CsvImportTrait;
use App\Http\Requests\MassDestroyCarYearRequest;
use App\Http\Requests\StoreCarYearRequest;
use App\Http\Requests\UpdateCarYearRequest;
use App\Models\CarYear;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class CarYearController extends Controller
{
    use CsvImportTrait;

    public function index(Request $request)
    {
        abort_if(Gate::denies('car_year_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->ajax()) {
            $query = CarYear::query()->select(sprintf('%s.*', (new CarYear)->table));
            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate      = 'car_year_show';
                $editGate      = 'car_year_edit';
                $deleteGate    = 'car_year_delete';
                $crudRoutePart = 'car-years';

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
            $table->editColumn('year', function ($row) {
                return $row->year ? $row->year : "";
            });

            $table->rawColumns(['actions', 'placeholder']);

            return $table->make(true);
        }

        return view('admin.carYears.index');
    }

    public function create()
    {
        abort_if(Gate::denies('car_year_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.carYears.create');
    }

    public function store(StoreCarYearRequest $request)
    {
        $carYear = CarYear::create($request->all());

        return redirect()->route('admin.car-years.index');
    }

    public function edit(CarYear $carYear)
    {
        abort_if(Gate::denies('car_year_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.carYears.edit', compact('carYear'));
    }

    public function update(UpdateCarYearRequest $request, CarYear $carYear)
    {
        $carYear->update($request->all());

        return redirect()->route('admin.car-years.index');
    }

    public function show(CarYear $carYear)
    {
        abort_if(Gate::denies('car_year_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $carYear->load('yearProducts');

        return view('admin.carYears.show', compact('carYear'));
    }

    public function destroy(CarYear $carYear)
    {
        abort_if(Gate::denies('car_year_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $carYear->delete();

        return back();
    }

    public function massDestroy(MassDestroyCarYearRequest $request)
    {
        CarYear::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
