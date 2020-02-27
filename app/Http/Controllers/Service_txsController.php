<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Service_tx;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use  Yajra\DataTables\DataTables;

class Service_txsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
               return view('_admin.service_txs.index');
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
        Service_tx::destroy($id);
		return response()->json(['status' => 'SUCCESS','message' => __('admin.action_ok',['item'=>__('admin.Service_tx'),'action'=> __('admin.Deleted')])]);
		
    }
	
	  /**toggle Item status.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function toggle_status($id)
    {
        $service_tx = Service_tx::findOrFail($id);
		$$service_tx->active = $service_tx->status  == 0? 1:0;
		$service_tx->save();
		$action= $service_tx->status == 1 ? __('admin.Activated'):__('admin.Deactivated');
		return response()->json(['status' => 'SUCCESS','message' => __('admin.action_ok',['item'=>__('admin.Service_tx'),'action'=> $action])]);
		
       
    }
	
	/**
     * Get the Table.
     *
     * 
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
	 public function table(){
		 $service_tx = Service_tx::with('user')->with('token')->get();
		return DataTables::of($service_tx)
			->escapeColumns([])
			->editColumn('user', function ($item) {
				return $item->user->email;
			})
			->editColumn('token', function ($item) {
				return $item->token->name;
			})
			->editColumn('service', function ($item) {
				return $item->service->number;
			})
			->editColumn('status', function ($item) {
				$name = __('admin.OFF');
				$label = 'danger';
				if($item->active == 1){
					$name = __('admin.ON');
					$label = 'success';
				}
				return '<a   class=" btn btn-sm btn-'.$label.' btn-block"data-toggle="tooltip" title="'.__('admin.Edit').'">'.$name.'</a>';
	
			})
			->addColumn('actions', function ($item) {
				 return'
				 <form method="POST" class="ajax_form" action="'.route('admin.service_txs.destroy' , $item->id) .'" accept-charset="UTF-8" style="display:inline">
				 '.method_field("DELETE") .'
				 '.csrf_field() .'
				 <button type="submit" class="btn btn-danger btn-sm" title="'.__('admin.Delete').' '.__('admin.Service_tx').'" ><i class="fa fa-trash-o" aria-hidden="true"></i>'.__('admin.Delete').'</button>
		</form>';
			}) ->toJson();
	}
}
