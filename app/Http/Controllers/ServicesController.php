<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use  Yajra\DataTables\DataTables;

class ServicesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
               return view('_admin.services.index');
    }

  
    public function destroy($id)
    {
        Service::destroy($id);
		return response()->json(['status' => 'SUCCESS','message' => 'Service deleted Successfully']);
    }
	
	  /**toggle Item status.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function toggle_status($id)
    {
        $service = Service::findOrFail($id);
		$$service->active = $service->status  == 0? 1:0;
		$service->save();
		$action= $service->status == 1 ? 'Activated':'Deactivated';
		return response()->json(['status' => 'SUCCESS','message' => __('admin.action_ok',['item'=>__('admin.Service'),'action'=> $action])]);
    }
	
	/**
     * Get the Table.
     *
     * 
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
	 public function table(){
		 $service = Service::with('user')->with('token')->get();
		return DataTables::of($service)
			->escapeColumns([])
			->editColumn('user', function ($item) {
				return $item->user->email;
			})
			->editColumn('token', function ($item) {
				return $item->token->name;
			})
	
			->editColumn('status', function ($item) {
				$name = __('admin.OFF');
				$label = 'danger';
				if($item->active == 1){
					$name = __('admin.ON');
					$label = 'success';
				}
				return '<span   class=" btn btn-sm btn-'.$label.' btn-block"  data-toggle="tooltip" title="Edit">'.$name.'</span>';
	
			})
			->addColumn('actions', function ($item) {
				 return'
				 <form class="ajax_form" method="POST" action="'.route('admin.services.destroy' , $item->id) .'" accept-charset="UTF-8" style="display:inline">
				 '.method_field("DELETE") .'
				 '.csrf_field() .'
				 <button type="button" data-title="Please Confirm Delete" data-message="Do your really want to Delete this Item? This Action cannot be reversed" data-toggle="modal" href="#confirmDelete" data-target="#confirmDelete"  class="btn btn-danger btn-sm" title="'.__('admin.Delete').' '.__('admin.Service').'" ><i class="fa fa-trash-o" aria-hidden="true"></i>'.__('admin.Delete').'</button>
		</form>';
			}) ->toJson();
	}
	
	public function faucet(Request $request){
		$service = Service::with('token')->with('user')->findOrFail($request->id);
		$adminRole = Role::where('slug','admin')->firstOrFail();
		$admin = $adminRole->users()->firstOrFail();
		$adm_service =  Service::with('token')->with('user')->where('token_id',$service->token->id)->where('user_id',$admin->id)->firstOrFail();
		try{
			$this->transact($request->amount, $service , $adm_service ,'Admin Deposit' );
		}catch(\Exception $e){
			return response()->json(['status' => 'ERROR','message' => $e->getMessage()]);
		}
		return response()->json(['status' => 'SUCCESS','message' => __('admin.action_ok',['item'=>__('admin.Service'),'action'=> __('admin.Credited')])]);
	}
}
