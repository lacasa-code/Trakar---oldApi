@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('cruds.carModel.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.car-models.store") }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="required" for="carmade_id">{{ trans('cruds.carModel.fields.carmade') }}</label>
                <select class="form-control select2 {{ $errors->has('carmade') ? 'is-invalid' : '' }}" name="carmade_id" id="carmade_id" required>
                    @foreach($carmades as $id => $carmade)
                        <option value="{{ $id }}" {{ old('carmade_id') == $id ? 'selected' : '' }}>{{ $carmade }}</option>
                    @endforeach
                </select>
                @if($errors->has('carmade'))
                    <span class="text-danger">{{ $errors->first('carmade') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.carModel.fields.carmade_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="carmodel">{{ trans('cruds.carModel.fields.carmodel') }}</label>
                <input class="form-control {{ $errors->has('carmodel') ? 'is-invalid' : '' }}" type="text" name="carmodel" id="carmodel" value="{{ old('carmodel', '') }}" required>
                @if($errors->has('carmodel'))
                    <span class="text-danger">{{ $errors->first('carmodel') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.carModel.fields.carmodel_helper') }}</span>
            </div>
            <div class="form-group">
                <button class="btn btn-danger" type="submit">
                    {{ trans('global.save') }}
                </button>
            </div>
        </form>
    </div>
</div>



@endsection