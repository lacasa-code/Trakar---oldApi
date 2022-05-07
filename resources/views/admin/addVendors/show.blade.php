@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.addVendor.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.add-vendors.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('cruds.addVendor.fields.id') }}
                        </th>
                        <td>
                            {{ $addVendor->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.addVendor.fields.serial') }}
                        </th>
                        <td>
                            {{ $addVendor->serial }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.addVendor.fields.vendor_name') }}
                        </th>
                        <td>
                            {{ $addVendor->vendor_name }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.addVendor.fields.email') }}
                        </th>
                        <td>
                            {{ $addVendor->email }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.addVendor.fields.type') }}
                        </th>
                        <td>
                            {{ App\Models\AddVendor::TYPE_RADIO[$addVendor->type] ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.addVendor.fields.userid') }}
                        </th>
                        <td>
                            {{ $addVendor->userid->name ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.addVendor.fields.images') }}
                        </th>
                        <td>
                            @if($addVendor->images)
                                <a href="{{ $addVendor->images->getUrl() }}" target="_blank" style="display: inline-block">
                                    <img src="{{ $addVendor->images->getUrl('thumb') }}">
                                </a>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.add-vendors.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>



@endsection