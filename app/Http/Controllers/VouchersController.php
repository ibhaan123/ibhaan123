<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use  Yajra\DataTables\DataTables;

class VouchersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
		return view('_admin.vouchers.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('_admin.vouchers.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        $this->validate($request, [
			'token' => 'required',
			'value' => 'required|numeric',
			'amount' => 'required|numeric'
		]);
		$token = explode(':',$request->token);
		$data = [
			'token_id' => $token[0],
			'token_type' => $token[1],
			'value' => $request->value,
		];
		
		for($i=0; $i < $request->amount; $i++ ){
			Voucher::create($data);
		}
		return response()->json(['status' => 'SUCCESS','message' => __('admin.action_ok',['item'=>__('vouchers.Voucher'),'action'=> __('admin.Added')])]);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $voucher = Voucher::findOrFail($id);
        return view('_admin.vouchers.show', compact('voucher'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $voucher = Voucher::findOrFail($id);

        return view('_admin.vouchers.edit', compact('voucher'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
			'value' => 'required|numeric',
		]);
        $requestData = $request->all();
        $voucher = Voucher::findOrFail($id);
        $voucher->update($requestData);
		return response()->json(['status' => 'SUCCESS','message' => __('admin.action_ok',['item'=>__('vouchers.Voucher'),'action'=> __('admin.Updated')])]);
     
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy($id)
    {
        Voucher::destroy($id);
		return response()->json(['status' => 'SUCCESS','message' => __('admin.action_ok',['item'=>__('vouchers.Voucher'),'action'=> __('admin.Deleted')])]);

    }
	
	/**
     * Remove the specified resources from storage.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function delete(Request $request)
    {
		if(!count($request->ids))
		return response()->json(['status' => 'SUCCESS','message' => __('vouchers.nothing_selected')]);
        Voucher::destroy($request->ids);
		return response()->json(['status' => 'SUCCESS','message' => __('admin.action_ok',['item'=>__('vouchers.Voucher'),'action'=> __('admin.Deleted')])]);

    }
	
	  /**toggle Item status.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function toggle_statuses(Request $request)
    {
		if(!count($request->ids))

		return response()->json(['status' => 'SUCCESS','message' => __('vouchers.nothing_selected')]);
        $vouchers = Voucher::where('status','!=', 3)->whereIn('id', $request->ids)->update(['status'=>$request->status]);
		$action= $request->status == 1 ?  __('admin.Activated'): __('admin.Deactivated');
		return response()->json(['status' => 'SUCCESS','message' => __('admin.action_ok',['item'=>__('vouchers.Voucher'),'action'=> $action ])]);
    }
	
	/**toggle Item status. 
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function toggle_status($id)
    {
        try{
			$voucher = Voucher::findOrFail($id);
		}catch(Exception $e){
			return response()->json(['status' => 'ERROR','message' =>$e->getMessage()]);
		}
		if($voucher->status == 3){
			return response()->json(['status' => 'ERROR','message' => __('voucher.voucher_used')]);
		}
		$voucher->status = $voucher->status  == 0? 1:0;
		$voucher->save();
		$action= $voucher->status == 1 ?  __('admin.Activated'): __('admin.Deactivated');
		
		return response()->json(['status' => 'SUCCESS','message' => __('admin.action_ok',['item'=>__('vouchers.Voucher'),'action'=> $action ])]);
       
    }
	
	/**
     * Get the Table.
     *
     * 
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
	 public function table(){
		 $voucher = Voucher::with('token')->get();
		return DataTables::of($voucher)
			->setRowId(function ($item) {
				return $item->id;
			})
			->rawColumns(['status','actions','id','used','user_id'])
			->editColumn('id', function ($item) {
				return '<input name="ids[]" class="chkbx" type="checkbox" value="'.$item->id.'"/>';
			})
			->editColumn('token_id', function ($item) {
				return $item->token->name .' ('. $item->token->symbol.')';
			})
			->editColumn('user_id', function ($item) {
				return $item->user()->count()?'<span class="btn btn-sm btn-danger">'.$item->user->email.'</span>':'<span class="btn btn-sm btn-success">New</span>';
			})
			->editColumn('used', function ($item) {
				return !is_null($item->used)?$item->used:'<span class="btn btn-sm btn-success">Unused</span>';
			})
			->editColumn('status', function ($item) {
				$name = __('admin.OFF');
				$label = 'danger';
				if($item->status == 1){
					$name = __('admin.ON');;
					$label = 'success';
				}
				return '<a data-table="Voucher" class="ajax_link refresh btn btn-sm btn-'.$label.' btn-block" href="'.route('admin.vouchers.toggle_status', $item->id).'" data-toggle="tooltip" title="'.__('admin.Edit').'">
							<span class="hidden-xs hidden-sm hidden-md">'.$name.'</span>
						 </a>';
	
			})
			->addColumn('actions', function ($item) {
				 return'<a href="'.route('admin.vouchers.edit', $item->id) .'" title="'.__('admin.Edit').' '.__('vouchers.Voucher').'"><button class="btn btn-primary btn-sm"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit</button></a>
				 <form data-table="Voucher" method="POST" class="ajax_form refresh" action="'.route('admin.vouchers.destroy' , $item->id) .'" accept-charset="UTF-8" style="display:inline">
				 '.method_field("DELETE") .'
				 '.csrf_field() .'
				 <button type="submit" class="btn btn-danger btn-sm" title="'.__('admin.Delete').' '.__('vouchers.Voucher').'" ><i class="fa fa-trash-o" aria-hidden="true"></i> '.__('admin.Delete').'</button>
		</form>';
			}) ->toJson();
	}
}
