@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('cruds.product.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.products.store") }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="categories">{{ trans('cruds.product.fields.category') }}</label>
                <div style="padding-bottom: 4px">
                    <span class="btn btn-info btn-xs select-all" style="border-radius: 0">{{ trans('global.select_all') }}</span>
                    <span class="btn btn-info btn-xs deselect-all" style="border-radius: 0">{{ trans('global.deselect_all') }}</span>
                </div>
                <select class="form-control select2 {{ $errors->has('categories') ? 'is-invalid' : '' }}" name="categories[]" id="categories" multiple>
                    @foreach($categories as $id => $category)
                        <option value="{{ $id }}" {{ in_array($id, old('categories', [])) ? 'selected' : '' }}>{{ $category }}</option>
                    @endforeach
                </select>
                @if($errors->has('categories'))
                    <span class="text-danger">{{ $errors->first('categories') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.product.fields.category_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="car_made_id">{{ trans('cruds.product.fields.car_made') }}</label>
                <select class="form-control select2 {{ $errors->has('car_made') ? 'is-invalid' : '' }}" name="car_made_id" id="car_made_id" required>
                    @foreach($car_mades as $id => $car_made)
                        <option value="{{ $id }}" {{ old('car_made_id') == $id ? 'selected' : '' }}>{{ $car_made }}</option>
                    @endforeach
                </select>
                @if($errors->has('car_made'))
                    <span class="text-danger">{{ $errors->first('car_made') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.product.fields.car_made_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="car_model_id">{{ trans('cruds.product.fields.car_model') }}</label>
                <select class="form-control select2 {{ $errors->has('car_model') ? 'is-invalid' : '' }}" name="car_model_id" id="car_model_id" required>
                    @foreach($car_models as $id => $car_model)
                        <option value="{{ $id }}" {{ old('car_model_id') == $id ? 'selected' : '' }}>{{ $car_model }}</option>
                    @endforeach
                </select>
                @if($errors->has('car_model'))
                    <span class="text-danger">{{ $errors->first('car_model') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.product.fields.car_model_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="year_id">{{ trans('cruds.product.fields.year') }}</label>
                <select class="form-control select2 {{ $errors->has('year') ? 'is-invalid' : '' }}" name="year_id" id="year_id" required>
                    @foreach($years as $id => $year)
                        <option value="{{ $id }}" {{ old('year_id') == $id ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
                @if($errors->has('year'))
                    <span class="text-danger">{{ $errors->first('year') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.product.fields.year_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="part_category_id">{{ trans('cruds.product.fields.part_category') }}</label>
                <select class="form-control select2 {{ $errors->has('part_category') ? 'is-invalid' : '' }}" name="part_category_id" id="part_category_id" required>
                    @foreach($part_categories as $id => $part_category)
                        <option value="{{ $id }}" {{ old('part_category_id') == $id ? 'selected' : '' }}>{{ $part_category }}</option>
                    @endforeach
                </select>
                @if($errors->has('part_category'))
                    <span class="text-danger">{{ $errors->first('part_category') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.product.fields.part_category_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="name">{{ trans('cruds.product.fields.name') }}</label>
                <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name" id="name" value="{{ old('name', '') }}" required>
                @if($errors->has('name'))
                    <span class="text-danger">{{ $errors->first('name') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.product.fields.name_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="description">{{ trans('cruds.product.fields.description') }}</label>
                <textarea class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}" name="description" id="description">{{ old('description') }}</textarea>
                @if($errors->has('description'))
                    <span class="text-danger">{{ $errors->first('description') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.product.fields.description_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="discount">{{ trans('cruds.product.fields.discount') }}</label>
                <input class="form-control {{ $errors->has('discount') ? 'is-invalid' : '' }}" type="number" name="discount" id="discount" value="{{ old('discount', '') }}" step="1">
                @if($errors->has('discount'))
                    <span class="text-danger">{{ $errors->first('discount') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.product.fields.discount_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="price">{{ trans('cruds.product.fields.price') }}</label>
                <input class="form-control {{ $errors->has('price') ? 'is-invalid' : '' }}" type="number" name="price" id="price" value="{{ old('price', '') }}" step="0.01" required>
                @if($errors->has('price'))
                    <span class="text-danger">{{ $errors->first('price') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.product.fields.price_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="photo">{{ trans('cruds.product.fields.photo') }}</label>
                <div class="needsclick dropzone {{ $errors->has('photo') ? 'is-invalid' : '' }}" id="photo-dropzone">
                </div>
                @if($errors->has('photo'))
                    <span class="text-danger">{{ $errors->first('photo') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.product.fields.photo_helper') }}</span>
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

@section('scripts')
<script>
    var uploadedPhotoMap = {}
Dropzone.options.photoDropzone = {
    url: '{{ route('admin.products.storeMedia') }}',
    maxFilesize: 2, // MB
    acceptedFiles: '.jpeg,.jpg,.png,.gif',
    addRemoveLinks: true,
    headers: {
      'X-CSRF-TOKEN': "{{ csrf_token() }}"
    },
    params: {
      size: 2,
      width: 4096,
      height: 4096
    },
    success: function (file, response) {
      $('form').append('<input type="hidden" name="photo[]" value="' + response.name + '">')
      uploadedPhotoMap[file.name] = response.name
    },
    removedfile: function (file) {
      console.log(file)
      file.previewElement.remove()
      var name = ''
      if (typeof file.file_name !== 'undefined') {
        name = file.file_name
      } else {
        name = uploadedPhotoMap[file.name]
      }
      $('form').find('input[name="photo[]"][value="' + name + '"]').remove()
    },
    init: function () {
@if(isset($product) && $product->photo)
      var files = {!! json_encode($product->photo) !!}
          for (var i in files) {
          var file = files[i]
          this.options.addedfile.call(this, file)
          this.options.thumbnail.call(this, file, file.preview)
          file.previewElement.classList.add('dz-complete')
          $('form').append('<input type="hidden" name="photo[]" value="' + file.file_name + '">')
        }
@endif
    },
     error: function (file, response) {
         if ($.type(response) === 'string') {
             var message = response //dropzone sends it's own error messages in string
         } else {
             var message = response.errors.file
         }
         file.previewElement.classList.add('dz-error')
         _ref = file.previewElement.querySelectorAll('[data-dz-errormessage]')
         _results = []
         for (_i = 0, _len = _ref.length; _i < _len; _i++) {
             node = _ref[_i]
             _results.push(node.textContent = message)
         }

         return _results
     }
}
</script>
@endsection