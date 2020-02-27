
@extends('adminlte::page')
@section('title')
{{$token->name}}
@endsection
@push('css') 
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />

 <link href="{{asset('/vendor/adminlte/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css')}}" rel="stylesheet">

 
 <link href="{{asset('/assets/admin/mycustom.css')}}" rel="stylesheet">
@endpush

@section('content_header')
     <h1>
        {{$token->name}}
        <small></small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> {!! __("admin.dashboard")  !!}</a></li>
        <li class="active">{{$token->name}}</li>
    </ol>
@endsection

@section("content")

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div style="padding-bottom:30px" class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title"></h3>
            </div>
            
			<form enctype="multipart/form-data" data-edit="true" class="form-horizontal form-label-left ajax_form" method="post" action="{{route('admin.tokens.update',$token->id)}}">
			{{ csrf_field() }}
			{{method_field('PUT') }}
			<h3 style="text-align:center">{{__('admin.edit_token',['token'=>$token->name])}}</h3>
			<hr>

                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Name <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input id="name" class="form-control col-md-7 col-xs-12" data-validate-length-range="6" value="{{$token->name ?? ""}}" data-validate-words="2" name="name" placeholder="Enter Token Name" required="required" type="text">
                        </div>
                      </div>
					  
					  <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="website">Symbol <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input value="{{$token->symbol ?? ""}}" type="text" id="symbol" name="symbol" required="required" placeholder="Token symbol. Eg EKC" class="form-control col-md-7 col-xs-12">
                        </div>
                      </div>
					  
					  <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="website">Decimals <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input value="{{$token->decimals ?? ""}}" type="text" id="symbol" name="decimals" required="required" placeholder="Number of decimal Places Eg 18." class="form-control col-md-7 col-xs-12">
                        </div>
                      </div>
					  
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="email">Contract Address <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input value="{{$token->contract_address ?? ""}}" type="text" id="contract_address" name="contract_address" required="required" class="form-control col-md-7 col-xs-12">
                        </div>
                      </div>
					 
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="contract_ABI_array">Contract ABI Json <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <textarea id="contract_ABI_array" name="contract_ABI_array"  required="required"  class="form-control col-md-7 col-xs-12"> {{$token->contract_ABI_array ?? ""}}
						  
						  </textarea>
                        </div>
                      </div>
					  <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="contract_Bin">Contract BIN
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <textarea id="contract_Bin" name="contract_Bin" placeholder="Optional"  class="optional form-control col-md-7 col-xs-12">{{$token->contract_Bin ?? ""}}</textarea>
                        </div>
                      </div>
					
					  <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="price">Token Price ({{setting('siteCurrency')}})<span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input value="{{$token->price ?? ""}}" type="text" id="price" name="price" placeholder="price eg 200" class="form-control col-md-7 col-xs-12">
                        </div>
                      </div>
					   <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="price">Minimum Deposit/Withdraw<span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input value="{{$token->minimum ?? ""}}" type="text" id="minimum" name="minimum" placeholder="minimum eg 200" class="form-control col-md-7 col-xs-12">
                        </div>
                      </div>
					  <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="price">Maximum Deposit/Withdraw<span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input id="maximum" value="{{$token->maximum ?? ""}}" type="text"  name="maximim" placeholder="maximum eg 200" class="form-control col-md-7 col-xs-12">
                        </div>
                      </div>
					 <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="price">Deposit Fees %<span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input value="{{$token->deposit_fees ?? ""}}" type="text" id="deposit_fees" name="maximim" placeholder="Deposit Fees in % Eg 4" class="form-control col-md-7 col-xs-12">
                        </div>
                      </div>
				 <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="price">Withdraw Fees %<span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input value="{{$token->withdraw_fees ?? ""}}" type="text" id="withdraw_fees" name="withdraw_fees" placeholder="Withdraw Fees in % Eg 2" class="form-control col-md-7 col-xs-12">
                        </div>
                      </div>
					  <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="website">Logo <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="file" id="logo" name="logo" 
						  placeholder="" class="form-control col-md-7 col-xs-12">
                        </div>
                      </div> 
					  <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="website">Image <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="file" id="logo" name="image"  placeholder="" class="form-control col-md-7 col-xs-12">
                        </div>
                      </div> 
					  <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="website">Website URL 
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input value="{{$token->website ?? ""}}" type="text" id="website" name="website"  placeholder="www.website.com" class="form-control col-md-7 col-xs-12">
                        </div>
                      </div>
					  
					  
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="twitter">Twitter @name<span class="optional">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input value="{{$token->twitter ?? ""}}" id="twitter" type="text" name="twitter" class="optional form-control col-md-7 col-xs-12">
                        </div>
                      </div>
                      <div class="item form-group">
                        <label for="facebook" class="control-label col-md-3">facebook</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input value="{{$token->facebook ?? ""}}" id="facebook" type="text" name="facebook"  class="optional form-control col-md-7 col-xs-12" >
                        </div>
                      </div>
					  <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="description">Description <span class="optional">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <textarea id="description" name="description"  class="description optional form-control col-md-7 col-xs-12">{{$token->description ?? ""}}</textarea>
                        </div>
                      </div>
					  <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="features">Features <span class="optional">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <textarea id="features" name="features"  class="description optional form-control col-md-7 col-xs-12">{{$token->features ?? ""}}</textarea>
                        </div>
                      </div>
					  
					  
					    <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="sweeptoaddress">Sweep Excess Coins to Address
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text"  value="{{$token->sweeptoaddress ?? ""}}" id="sweeptoaddress" name="sweeptoaddress"  placeholder="Address to sweep Coins to" class="form-control col-md-7 col-xs-12">
                        </div>
                      </div> 
					  <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="sweepthreshold">Excess Threshold 
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input value="{{$token->sweepthreshold ?? ""}}" type="text" id="sweepthreshold" name="sweepthreshold"  placeholder="Sweep coins above this amount" class="form-control col-md-7 col-xs-12">
                        </div>
                      </div>
					  
                      <div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-6 col-md-offset-3">
                          <button type="submit" class="btn btn-primary">Cancel</button>
                          <button id="send" type="submit" class="btn btn-success">Submit</button>
                        </div>
                      </div>
                    </form>
			
			
          </div>
        </div><!-- /.col -->
    </div><!-- /.row -->
</section><!-- /.content -->


@endsection


@push('js')

<script src="{{asset('/vendor/adminlte/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js')}}"></script>
<script src="{{asset('/vendor/adminlte/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js')}}"></script>
<script src="{{asset('/assets/admin/FileSaver.min.js')}}"></script>
<script src="{{asset('/assets/admin/notify.min.js')}}"></script>
<script src="{{asset('/assets/admin/sweetalert2.all.js')}}"></script>
<script src="{{asset('/assets/admin/jquery.blockUI.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
<script src="{{asset('/assets/admin/init.js')}}"></script>
@endpush

@section('js')

    <script>
	$(function () {
		$('.description').wysihtml5();
		
	  })
       
    </script>
@endsection
