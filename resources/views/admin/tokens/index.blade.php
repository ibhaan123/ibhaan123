
@extends('admin')

@section('body')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-users"></i> {{$page_title}}</h1>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item"><a href="{{url()->current()}}">{{$page_title}}</a></li>
        </ul>
    </div>


    <div class="row">
        <div class="col-md-12">
            <div class="tile">
                <h3 class="tile-title"><i class="fa fa-users"></i>  User List</h3>
                <div class="tile-body">
                    <div class="pull-right icon-btn">
                        <form method="get" class="form-inline" action="{{route('search.users')}}">
                            <input type="text" name="search" class="form-control" placeholder="Search">
                            <button class="btn btn-outline btn-circle  green" type="submit"><i
                                    class="fa fa-search"></i></button>
                        </form>
                    </div>

<!-- Main content -->
<!-- <section class="content">
    <div class="row">
        <div class="col-xs-12">

             <div class="box"> -->
                <!-- <div class="box-body">  -->
					<a href="{{ route('admin.tokens.create') }}" class="btn btn-success btn-sm" title="@lang('admin.Add') @lang('admin.New') Token">
						<i class="fa fa-plus" aria-hidden="true"></i> Add New
					</a>
					
					<a data-table="Token"  href="{{ route('admin.tokens.masstoggle') }}" data-ids="[]" class="btn btn-info btn-sm mass ajax_link refresh confirm" data-confirm="{{__('app.confirm_enable_selected')}}" data-status="1" title="{{__('admin.Enable')}} Tokens">
                            {{__('admin.Enable')}} : <span class="count">0</span> {{__('admin.selected')}} 
                        </a>
						<a data-table="Token" href="{{ route('admin.tokens.masstoggle') }}" data-ids="[]" class="btn btn-info btn-sm mass ajax_link refresh confirm" data-confirm="{{__('app.confirm_disable_selected')}}"  data-status="0" title=" {{__('admin.Disable')}} Tokens">
                            {{__('admin.Disable')}} : <span class="count">0</span> {{__('admin.selected')}} 
                        </a>
						<a data-table="Token"   href="{{ route('admin.tokens.massdelete') }}" data-ids="[]" class="btn btn-danger btn-sm ajax_link mass refresh confirm" data-confirm="{{__('app.confirm_delete_selected')}}" title="{{__('admin.Delete')}} Tokens">
                            {{__('admin.Delete')}} : <span class="count">0</span> {{__('admin.selected')}} 
                        </a>
						<a   href="javascript:;" class="btn btn-default btn-sm clear" title="Clear Selection Tokens">
                            {{__('admin.reset')}} : <span class="count">0</span> {{__('admin.selected')}} 
                        </a>
						
				    <hr>
                    <table id="Token" class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th >#</th>
                            <th >{!! trans("admin.Token")  !!}</th>
                            <th >{!! trans("admin.sym")  !!}</th>
                            <th >{!! trans("admin.Status")  !!}</th>
							<th>Max Bal</th>
							<th>Sweep excess to</th>
							<th>{!! trans("admin.actions")  !!}</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div><!-- /.box-body -->
            </div><!-- /.box -->
        </div><!-- /.col -->
    </div><!-- /.row -->
	@include('modals.modal-delete')
</section><!-- /.content -->




@endsection
@section('script') 

    <script>
    $( document ).ready(function() {
        console.log( "ready!" );
    });
        $(function() {
            console.log('asfdhagd');
			@include('scripts.delete-modal-script')
			var selected = [];
          	window.Token = $('#Token').dataTable({
                "order": [[ 0, 'asc' ]],
                "language": {
                    "sDecimal":        ",",
                    "sEmptyTable":     "{!! trans("admin.sEmptyTable")  !!}",
                    "sInfo":           "{!! trans("admin.sInfo")  !!}",
                    "sInfoEmpty":      "{!! trans("admin.sInfoEmpty")  !!}",
                    "sInfoFiltered":   "{!! trans("admin.sInfoFiltered")  !!}",
                    "sInfoPostFix":    "",
                    "sInfoThousands":  ".",
                    "sLengthMenu":     "{!! trans("admin.sLengthMenu")  !!}",
                    "sLoadingRecords": "{!! trans("admin.sLoadingRecords")  !!}",
                    "sProcessing":     "{!! trans("admin.sProcessing")  !!}",
                    "sSearch":         "{!! trans("admin.sSearch")  !!}",
                    "sZeroRecords":    "{!! trans("admin.sZeroRecords")  !!}",
                    "oPaginate": {
                        "sFirst":    "{!! trans("admin.sFirst")  !!}",
                        "sLast":     "{!! trans("admin.sLast")  !!}",
                        "sNext":     "{!! trans("admin.sNext")  !!}",
                        "sPrevious": "{!! trans("admin.sPrevious")  !!}"
                    },
                    "oAria": {
                        "sSortAscending":  "{!! trans("admin.sSortAscending")  !!}",
                        "sSortDescending": "{!! trans("admin.sSortDescending")  !!}"
                    }
                },
                processing: true,
                serverSide: true,
                "autoWidth": false,
				"rowCallback": function( row, data ) {
					if ( $.inArray(data.DT_RowId, selected) !== -1 ) {
						$(row).addClass('info');
						$(row).find('input.chkbx').prop('checked', true);
					}
				},
			    "ajax": {
					"url": "{{route('admin.tokens.table')}}",
					"type": "POST",
					'headers': { 'X-CSRF-TOKEN': '{{ csrf_token() }}'}
				},
				type:'POST',
                columns: [
                    {data: 'id', name: 'id', orderable: true},
					{data: 'name', name: 'name', orderable: true},
                    {data: 'symbol', name: 'symbol', orderable: true},
                    {data: 'active', name: 'active', orderable: true},
					{data: 'sweepthreshold', name: 'sweepthreshold'},
                    {data: 'sweeptoaddress', name: 'sweeptoaddress'},
					{data: 'actions', name: 'actions'},
                ]
            });
			$('#Token tbody').on('click', 'input', function () {
				var tr = $(this).parents('tr')
				var id = tr.attr('id');
				
				var index = $.inArray(id, selected);
				if ( index === -1 ) {
					selected.push( id );
				} else {
					selected.splice( index, 1 );
				}
				
				$(tr).toggleClass('info');
				$('a.btn.mass').data('ids',selected);
				$('.count').text(selected.length);
			} );
			
			$('a.btn.clear').click(function(e) {
				selected =[];
				$('.count').text(0);
				$('a.btn.mass').data('ids',[]);
				$('.chkbx').prop('checked', false);
				$('tr.info','#Token tbody').removeClass('info');
			});



        });
    </script>
    @stop
