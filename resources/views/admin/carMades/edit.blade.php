@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('cruds.carMade.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.car-mades.update", [$carMade->id]) }}" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="form-group">
                <label class="required" for="categoryid_id">{{ trans('cruds.carMade.fields.categoryid') }}</label>
                <select class="form-control select2 {{ $errors->has('categoryid') ? 'is-invalid' : '' }}" name="categoryid_id" id="categoryid_id" required>
                    @foreach($categoryids as $id => $categoryid)
                        <option value="{{ $id }}" {{ (old('categoryid_id') ? old('categoryid_id') : $carMade->categoryid->id ?? '') == $id ? 'selected' : '' }}>{{ $categoryid }}</option>
                    @endforeach
                </select>
                @if($errors->has('categoryid'))
                    <span class="text-danger">{{ $errors->first('categoryid') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.carMade.fields.categoryid_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="car_made">{{ trans('cruds.carMade.fields.car_made') }}</label>
                <input class="form-control {{ $errors->has('car_made') ? 'is-invalid' : '' }}" type="text" name="car_made" id="car_made" value="{{ old('car_made', $carMade->car_made) }}">
                @if($errors->has('car_made'))
                    <span class="text-danger">{{ $errors->first('car_made') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.carMade.fields.car_made_helper') }}</span>
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