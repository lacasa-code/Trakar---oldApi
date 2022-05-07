@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('cruds.addVendor.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.add-vendors.update", [$addVendor->id]) }}" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="form-group">
                <label class="required" for="vendor_name">{{ trans('cruds.addVendor.fields.vendor_name') }}</label>
                <input class="form-control {{ $errors->has('vendor_name') ? 'is-invalid' : '' }}" type="text" name="vendor_name" id="vendor_name" value="{{ old('vendor_name', $addVendor->vendor_name) }}" required>
                @if($errors->has('vendor_name'))
                    <span class="text-danger">{{ $errors->first('vendor_name') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.addVendor.fields.vendor_name_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="email">{{ trans('cruds.addVendor.fields.email') }}</label>
                <input class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" type="email" name="email" id="email" value="{{ old('email', $addVendor->email) }}" required>
                @if($errors->has('email'))
                    <span class="text-danger">{{ $errors->first('email') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.addVendor.fields.email_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required">{{ trans('cruds.addVendor.fields.type') }}</label>
                @foreach(App\Models\AddVendor::TYPE_RADIO as $key => $label)
                    <div class="form-check {{ $errors->has('type') ? 'is-invalid' : '' }}">
                        <input class="form-check-input" type="radio" id="type_{{ $key }}" name="type" value="{{ $key }}" {{ old('type', $addVendor->type) === (string) $key ? 'checked' : '' }} required>
                        <label class="form-check-label" for="type_{{ $key }}">{{ $label }}</label>
                    </div>
                @endforeach
                @if($errors->has('type'))
                    <span class="text-danger">{{ $errors->first('type') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.addVendor.fields.type_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="userid_id">{{ trans('cruds.addVendor.fields.userid') }}</label>
                <select class="form-control select2 {{ $errors->has('userid') ? 'is-invalid' : '' }}" name="userid_id" id="userid_id" required>
                    @foreach($userids as $id => $userid)
                        <option value="{{ $id }}" {{ (old('userid_id') ? old('userid_id') : $addVendor->userid->id ?? '') == $id ? 'selected' : '' }}>{{ $userid }}</option>
                    @endforeach
                </select>
                @if($errors->has('userid'))
                    <span class="text-danger">{{ $errors->first('userid') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.addVendor.fields.userid_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="images">{{ trans('cruds.addVendor.fields.images') }}</label>
                <div class="needsclick dropzone {{ $errors->has('images') ? 'is-invalid' : '' }}" id="images-dropzone">
                </div>
                @if($errors->has('images'))
                    <span class="text-danger">{{ $errors->first('images') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.addVendor.fields.images_helper') }}</span>
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
    Dropzone.options.imagesDropzone = {
    url: '{{ route('admin.add-vendors.storeMedia') }}',
    maxFilesize: 2, // MB
    acceptedFiles: '.jpeg,.jpg,.png,.gif',
    maxFiles: 1,
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
      $('form').find('input[name="images"]').remove()
      $('form').append('<input type="hidden" name="images" value="' + response.name + '">')
    },
    removedfile: function (file) {
      file.previewElement.remove()
      if (file.status !== 'error') {
        $('form').find('input[name="images"]').remove()
        this.options.maxFiles = this.options.maxFiles + 1
      }
    },
    init: function () {
@if(isset($addVendor) && $addVendor->images)
      var file = {!! json_encode($addVendor->images) !!}
          this.options.addedfile.call(this, file)
      this.options.thumbnail.call(this, file, file.preview)
      file.previewElement.classList.add('dz-complete')
      $('form').append('<input type="hidden" name="images" value="' + file.file_name + '">')
      this.options.maxFiles = this.options.maxFiles - 1
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