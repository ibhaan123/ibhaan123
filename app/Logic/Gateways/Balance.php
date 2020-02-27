<?php
namespace App\Logic\Gateways;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use \App\Models\Token;
use \App\Models\Order;
use jeremykenedy\LaravelRoles\Models\Role;

class Balance implements Gateway {
	use \App\Traits\ServiceTrait;
	protected $view = NULL;
	protected $gate = NULL;
	protected $sendHash = NULL;
	protected $collectHash = NULL;
	protected $order = NULL;
	protected $orderManager = NULL;
	public function __construct(){
	}
	
	public function boot(  Order $order){
		$this->order = $order;
		return $this;
	}
	
	public function payout(){
		$o  = $this->order;
		$recv = $this->user_service($o->account->user, $o->token);
		$sender = $this->user_service($o->user, $o->token);
		try{
			$this->transact($o->amount, $recv, $sender, $o->description , $o->reference);
		}catch(\Exception $e ){
			$order->logg .= "<br> PAYOUT FAILED <br>".$e->getMessage();
		}
		$o->logg .= "<br> PAYOUT COMPLETE SUCCESSFULLY <br>".$o->reference;		
		//order	
		$o->status = "COMPLETE";
		$o->txid = $tx_hash;
		$o->save();
		return $tx_hash;
	}
	
	public function form_validation( ){ //validate if the gateway has a view
		return [
            'email'=> 'required',
            'order_id'  => 'required',
            'password' => 'required|min:3',
        ];
		 
	}
	
	public function collect( ){
		$request = request();
		if ($request->session()->has('auth')) {
			 $request->session()->forget('auth');
		}
		$order = $this->order;
		$token =$order->token;
		// GET the USer
		if(!auth()->check()){
			$credentials = $request->only('email', 'password');
			if (Auth::attempt($credentials) == false) {
			   return response()->json(['status' => 'ERROR','message' => 'Invalid User. Please Check your Account Details']);
			}
			session(['auth' => true]);
		}
		$user = auth()->user();
		$o  = $this->order;
		$o->user_id == $user->id;
		$o->save();
		$rcv = $this->user_service($o->account->user, $o->token);
		$sender = $this->user_service($user, $o->token);
		
		try{
			$this->transact($o->amount,  $rcv, $sender , $o->description , $o->reference);
		}catch(\Exception $e ){
		    $url = isset($o->item_data['cancel_url'])?'<br><a href="'.$o->item_data['cancel_url'].'">Cancel Transaction</a>':'';
			return response()->json(['status' => 'ERROR','message' => 'Sent Tx Failed , '.$message.$url ]);
		}
		if($order->status!='MARKET' ||$order->status!='PARTIAL'){
			$order->status =='CONFIRMING';
		}
		$order->save();
		if ($request->session()->has('auth')) {
			 Auth::logout();
			 $request->session()->forget('auth');
		}
		if(!$o->complete($o)){
			return response()->json(['status' => 'ERROR','message' => $o->logg ]);
		};
		$m = $o->item =='api'?'Return To Merchant':'Go Back';
		return response()->json(['status' => 'SUCCESS','message' => 'Order Completed Successfully:<a target="_blank"  href="'.$o->item_url.'">Return to </a>']);
		
	}
	
	
	public function ipn(){
		return false;
	}
	
	
	public function form(){
		$items = collect([]);
		$adminRole = Role::where('slug','admin')->firstOrFail();
		$admin = $adminRole->users()->firstOrFail();
		$siteOrder = $this->order->account->id == $admin->account->id;
		$order = $this->order;
		if(isset($this->order->item_data['items'])){
			$items = collect( $this->order->item_data['items'] );
		}
		$this->view =  View::make('gateways.service', compact('order','items','siteOrder'));
		return $this;
	}
	
	public function isRedirect( ){
		return !is_null($this->gate)&&is_null($this->view);
	}
	
	public function getView( ){
		return $this->view;
	}
	
	public function redirect(){
		if(is_string($this->gate)){
			if(request()->ajax())
			return response()->json(["URL"=> $this->gate,'status' => 'SUCCESS','message' =>'Please Wait ..']); 
			return redirect($this->gate);
		}else{
			return  $this->gate->redirect();
		}
	}
	
	
}