<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Yajra\Datatables\Datatables;
use JsValidator;


class TokensController extends \App\Http\Controllers\Controller
{ /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
		$data['page_title'] = "ERC20";
        //$data['users'] = User::latest()->paginate(20);
        return view('admin.tokens.index', $data);

		//return view('admin.tokens.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
		
		//this->authorize('create', Token::class);
		$jsvalidator = JsValidator::make([
			'user_id' => 'numeric|nullable|exits:users,id',
			'account' => 'numeric|required|exits:accounts,id',
			'name' => 'required|string|max:75',
			'slug' => 'nullable|string',
			'contract_address' => 'nullable|string|max:42',
			'contract_ABI_array' => 'nullable|string',
			'contract_Bin' => 'nullable|string',
			'token_price' => 'required|numeric',
			'symbol' => 'required|string',
			'decimals' => 'required|numeric',
			'logo' => 'required|file',
			'image' => 'required|file',
			'website' => 'required|string|url',
			'twitter' => 'required|string|url',
			'facebook' => 'required|string|url',
			'description' => 'required|string',
			'technology' => 'nullable|string',
			'features' => 'required|string',
		]);
		$accounts = auth()->user()->accounts;
		$page_title = "title";
        //$data['users'] = User::latest()->paginate(20);
        return view('admin.tokens.create',compact('jsvalidator','accounts','page_title'));
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
		//$this->authorize('create', Token::class);
		
		$request->validate([
			'user_id' => 'numeric|nullable|exits:users,id',
			'account_id' => 'numeric|exits:accounts,id',
			'name' => 'required|string|max:75',
			'slug' => 'nullable|string',
			'contract_address' => 'nullable|string|max:42',
			'contract_ABI_array' => 'nullable|string',
			'contract_Bin' => 'nullable|string',
			'token_price' => 'required|numeric',
			'symbol' => 'required|string',
			'decimals' => 'required|numeric',
			'logo' => 'required|file',
			'image' => 'required|file',
			'website' => 'required|string|url',
			'twitter' => 'required|string|url',
			'facebook' => 'required|string|url',
			'description' => 'required|string',
			'technology' => 'nullable|string',
			'features' => 'required|string',
			'sweepthreshold'=>'nullable|numeric',
			'sweeptoaddress'=>'nullable|string',
		]);
        $requestData = $request->all();
		 $requestData['slug']= str_slug($request->name);
        if ($request->hasFile('logo')) {
            $requestData['logo'] = $request->file('logo')
                ->store('uploads', 'public');
        }
        if ($request->hasFile('image')) {
            $requestData['image'] = $request->file('image')
                ->store('uploads', 'public');
        }

        Token::create($requestData);
		return response()->json(['status' => 'SUCCESS','message' => __('admin.action_ok',['item'=>__('tokens.Token'),'action'=> __('admin.Added')])]);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function show(Token $token)
    {
        return view('_admin.tokens.show', compact('token'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function edit(Request $request, Token $token)
    {
		//$this->authorize('update', $token);
		//add the jsvalidator
		$jsvalidator = JsValidator::make([
			'user_id' => 'numeric|nullable|exits:users,id',
			'account_id' => 'numeric|required|exits:accounts,id',
			'name' => 'required|string|max:75',
			'contract_address' => 'nullable|string|max:42',
			'mainsale_address' => 'nullable|string|max:42',
			'contract_ABI_array' => 'nullable|string',
			'contract_Bin' => 'nullable|string',
			'token_price' => 'required|numeric',
			'symbol' => 'required|string',
			'decimals' => 'required|numeric',
			'logo' => 'nullable|file',
			'image' => 'nullable|file',
			'template' => 'nullable|string',
			'website' => 'required|string|url',
			'twitter' => 'required|string|url',
			'facebook' => 'required|string|url',
			'whitepaper' => 'nullable|string|url',
			'description' => 'required|string',
			'technology' => 'nullable|string',
			'features' => 'required|string',
			'sweepthreshold'=>'nullable|numeric',
			'sweeptoaddress'=>'nullable|string',
		]);
	
		$users = \App\Models\User::all();
		$accounts = \App\Models\Account::all();
        return view('admin.tokens.edit', compact('token', 'jsvalidator','users','accounts'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, Token $token)
    {
		//$this->authorize('update', $token);
        $this->validate($request, [
			'user_id' => 'numeric|nullable|exits:users,id',
			'account_id' => 'numeric|required|exits:accounts,id',
			'name' => 'required|string|max:75',
			'contract_address' => 'nullable|string|max:42',
			'mainsale_address' => 'nullable|string|max:42',
			'contract_ABI_array' => 'nullable|string',
			'contract_Bin' => 'nullable|string',
			'token_price' => 'required|numeric',
			'symbol' => 'required|string',
			'decimals' => 'required|numeric',
			'logo' => 'nullable|file',
			'image' => 'nullable|file',
			'template' => 'nullable|string',
			'website' => 'required|string|url',
			'twitter' => 'required|string|url',
			'facebook' => 'required|string|url',
			'whitepaper' => 'nullable|string|url',
			'description' => 'required|string',
			'technology' => 'nullable|string',
			'features' => 'required|string',
			'sweepthreshold'=>'nullable|numeric',
			'sweeptoaddress'=>'nullable|string',
			
		]);
		$requestData['slug']= str_slug($request->name);
        $requestData = $request->all();
                if ($request->hasFile('logo')) {
            $requestData['logo'] = $request->file('logo')
                ->store('uploads', 'public');
        }
        if ($request->hasFile('image')) {
            $requestData['image'] = $request->file('image')
                ->store('uploads', 'public');
        }

        $token->update($requestData);
		return response()->json(['status' => 'SUCCESS','message' => __('admin.action_ok',['item'=>__('tokens.Token'),'action'=> __('admin.Updated')])]);
     
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy(Request $request, Token $token)
    {
		//$this->authorize('delete', $token);
		$token->delete();
		return response()->json(['status' => 'SUCCESS','message' => __('admin.action_ok',['item'=>__('tokens.Token'),'action'=> __('admin.Deleted')])]);

    }
	
	  /**toggle Item status.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function toggle_status(Request $request, Token $token)
    {
		$key = $request->id;	// added by Radha
		$token=Token::find($key); // added by Radha
		if($token->active == 3){
			return response()->json(['status' => 'SUCCESS','message' => 'Process Failed. This Token has not been confirmed on the blockchain Yet.']);
		}
		$token->active = $token->active==1?0:1;
				
		$token->save();
		$action= $token->active==1?'Activated':'Deactivated';
		return response()->json(['status' => 'SUCCESS','message' => 'Token '.$action.' Successfully']);
    }
	
	/**
     * Remove the specified resources from storage.
     *
     * @param  Request  $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function delete(Request $request)
    {
		
		if(!count($request->ids))
		return response()->json(['status' => 'SUCCESS','message' => __('admin.nothing_selected')]);
		$tokens = Token::findMany($id);
		foreach ($tokens as $token ){
			$this->authorize('delete', $token);
		}
        Token::destroy($request->ids);
		return response()->json(['status' => 'SUCCESS','message' => __('admin.action_ok',['item'=>__('tokens.Token'),'action'=> __('admin.Deleted')])]);
    }
	
	
	
	  /**mass toggle Items status.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function toggle_statuses(Request $request)
    {
		if(!count($request->ids))
		return response()->json(['status' => 'SUCCESS','message' => __('admin.nothing_selected')]);
		$tokens = Token::findMany($request->ids);
		// foreach ($tokens as $token ){
		// 	$this->authorize('update', $token);
		// }
        Token::where('active','!=', 3)->whereIn('id', $request->ids)->update(['active'=>$request->status]);
		$action= $request->status == 1 ?  __('admin.Activated'): __('admin.Deactivated');
		return response()->json(['status' => 'SUCCESS','message' => __('admin.action_ok',['item'=>__('tokens.Token'),'action'=> $action ])]);
    }
	
	
	
	
	/**
     * Get the Table.
     *
     * 
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
	 public function table(){
		 $token = Token::with('user')->get();
		return Datatables::of($token)
			->rawColumns(['id','active','actions'])
			->setRowId(function ($item) {
				return $item->id ;
			})
			->editColumn('id', function ($item) {
				return '<input name="ids[]" class="chkbx" type="checkbox" value="'.$item->id.'"/>';
			})
			
			->addColumn('sweepthreshold', function ($token) {
				return  empty($token->sweepthreshold)?'':$token->sweepthreshold.$token->symbol;
		
			})
			
			->editColumn('active', function ($item) {
				$name = __('app.disabled');
				$label = 'danger';
				if($item->active){
					$name = __('app.enabled');;
					$label = 'success';
				}
				return '<a data-table="Token" class="ajax_link refresh btn btn-sm btn-'.$label.' btn-block" href="'.route('admin.tokens.toggle_status', $item->id).'" data-toggle="tooltip" title="'.__('admin.Edit').'">
							<span class="hidden-xs hidden-sm hidden-md">'.$name.'</span>
						 </a>';
	
			})
			->addColumn('actions', function ($item) {
				 return'<a href="'.route('admin.tokens.edit', $item->id) .'" title="'.__('admin.Edit').' '.__('tokens.Token').'"><button class="btn btn-primary btn-sm"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit</button></a>
				 <form data-table="Token"  method="POST" class="ajax_form refresh" action="'.route('admin.tokens.destroy' , $item->id) .'" accept-charset="UTF-8" style="display:inline">
				 '.method_field("DELETE") .'
				 '.csrf_field() .'
				 <a  data-title="Please Confirm Delete" data-message="Do your really want to Delete this Token? This Action cannot be reversed" data-toggle="modal" href="#confirmDelete" data-target="#confirmDelete"  class="btn btn-danger btn-sm" title="'.__('admin.Delete').' '.__('tokens.Token').'" ><i class="fa fa-trash-o" aria-hidden="true"></i> '.__('admin.Delete').'</a>
		</form>';
			}) ->toJson();
	}}
