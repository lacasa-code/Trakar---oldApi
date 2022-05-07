<div class="m-3">
    @can('add_vendor_create')
        <div style="margin-bottom: 10px;" class="row">
            <div class="col-lg-12">
                <a class="btn btn-success" href="{{ route('admin.add-vendors.create') }}">
                    {{ trans('global.add') }} {{ trans('cruds.addVendor.title_singular') }}
                </a>
            </div>
        </div>
    @endcan
    <div class="card">
        <div class="card-header">
            {{ trans('cruds.addVendor.title_singular') }} {{ trans('global.list') }}
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class=" table table-bordered table-striped table-hover datatable datatable-useridAddVendors">
                    <thead>
                        <tr>
                            <th width="10">

                            </th>
                            <th>
                                {{ trans('cruds.addVendor.fields.id') }}
                            </th>
                            <th>
                                {{ trans('cruds.addVendor.fields.serial') }}
                            </th>
                            <th>
                                {{ trans('cruds.addVendor.fields.vendor_name') }}
                            </th>
                            <th>
                                {{ trans('cruds.addVendor.fields.email') }}
                            </th>
                            <th>
                                {{ trans('cruds.addVendor.fields.userid') }}
                            </th>
                            <th>
                                {{ trans('cruds.addVendor.fields.images') }}
                            </th>
                            <th>
                                &nbsp;
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($addVendors as $key => $addVendor)
                            <tr data-entry-id="{{ $addVendor->id }}">
                                <td>

                                </td>
                                <td>
                                    {{ $addVendor->id ?? '' }}
                                </td>
                                <td>
                                    {{ $addVendor->serial ?? '' }}
                                </td>
                                <td>
                                    {{ $addVendor->vendor_name ?? '' }}
                                </td>
                                <td>
                                    {{ $addVendor->email ?? '' }}
                                </td>
                                <td>
                                    {{ $addVendor->userid->name ?? '' }}
                                </td>
                                <td>
                                    @if($addVendor->images)
                                        <a href="{{ $addVendor->images->getUrl() }}" target="_blank" style="display: inline-block">
                                            <img src="{{ $addVendor->images->getUrl('thumb') }}">
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    @can('add_vendor_show')
                                        <a class="btn btn-xs btn-primary" href="{{ route('admin.add-vendors.show', $addVendor->id) }}">
                                            {{ trans('global.view') }}
                                        </a>
                                    @endcan

                                    @can('add_vendor_edit')
                                        <a class="btn btn-xs btn-info" href="{{ route('admin.add-vendors.edit', $addVendor->id) }}">
                                            {{ trans('global.edit') }}
                                        </a>
                                    @endcan

                                    @can('add_vendor_delete')
                                        <form action="{{ route('admin.add-vendors.destroy', $addVendor->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                            <input type="hidden" name="_method" value="DELETE">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <input type="submit" class="btn btn-xs btn-danger" value="{{ trans('global.delete') }}">
                                        </form>
                                    @endcan

                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@section('scripts')
@parent
<script>
    $(function () {
  let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)
@can('add_vendor_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.add-vendors.massDestroy') }}",
    className: 'btn-danger',
    action: function (e, dt, node, config) {
      var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) {
          return $(entry).data('entry-id')
      });

      if (ids.length === 0) {
        alert('{{ trans('global.datatables.zero_selected') }}')

        return
      }

      if (confirm('{{ trans('global.areYouSure') }}')) {
        $.ajax({
          headers: {'x-csrf-token': _token},
          method: 'POST',
          url: config.url,
          data: { ids: ids, _method: 'DELETE' }})
          .done(function () { location.reload() })
      }
    }
  }
  dtButtons.push(deleteButton)
@endcan

  $.extend(true, $.fn.dataTable.defaults, {
    orderCellsTop: true,
    order: [[ 3, 'desc' ]],
    pageLength: 100,
  });
  let table = $('.datatable-useridAddVendors:not(.ajaxTable)').DataTable({ buttons: dtButtons })
  $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
      $($.fn.dataTable.tables(true)).DataTable()
          .columns.adjust();
  });
  
})

</script>
@endsection