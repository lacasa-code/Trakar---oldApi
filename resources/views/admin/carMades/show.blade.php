@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.carMade.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.car-mades.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('cruds.carMade.fields.id') }}
                        </th>
                        <td>
                            {{ $carMade->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.carMade.fields.categoryid') }}
                        </th>
                        <td>
                            {{ $carMade->categoryid->name ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.carMade.fields.car_made') }}
                        </th>
                        <td>
                            {{ $carMade->car_made }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.car-mades.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        {{ trans('global.relatedData') }}
    </div>
    <ul class="nav nav-tabs" role="tablist" id="relationship-tabs">
        <li class="nav-item">
            <a class="nav-link" href="#carmade_car_models" role="tab" data-toggle="tab">
                {{ trans('cruds.carModel.title') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#car_made_products" role="tab" data-toggle="tab">
                {{ trans('cruds.product.title') }}
            </a>
        </li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane" role="tabpanel" id="carmade_car_models">
            @includeIf('admin.carMades.relationships.carmadeCarModels', ['carModels' => $carMade->carmadeCarModels])
        </div>
        <div class="tab-pane" role="tabpanel" id="car_made_products">
            @includeIf('admin.carMades.relationships.carMadeProducts', ['products' => $carMade->carMadeProducts])
        </div>
    </div>
</div>

@endsection