<?php
namespace App\Logic\Gateways;
use App\Logic\Gateways\Gateway;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use \App\Models\Token;
use \App\Models\Order;
use Illuminate\Http\Request;
use jeremykenedy\LaravelRoles\Models\Role;

class Blockchain implements Gateway {
	use \App\Traits\WalletTrait;
	protected $view = NULL;
	protected $gate = NULL;
	protected $sendHash = NULL;
	protected $collectHash = NULL;
	protected $order = NULL;
	
	public function __construct(){
		
	}
	
	public function boot($order){
		$this->order = $order;
		return $this;
	}
	
	public function payout(){
		$order = $this->order;
		$internal = $order->account()->count() > 0;
		$token = $order->token;
		$uaccount = $order->user->account;// user
		$user = $order->user;
		$maccount = $internal ? $order->account:NULL; // merchant
		$muser =  $internal ? $order->account->user:NULL;
		$cryptopass  = env('CRYPTO','password');
			if($token->family =='ethereum'){
				$gasLimit = NULL;
				$gasPrice = NULL;
				try{
					$tx_hash = $this->send(
						$order->amount, 
						$order->address, 
						$uaccount, 
						$cryptopass, 
						$token->symbol == "ETH"?NULL:$token,
						$gasLimit,
						$gasPrice
					 ) ;
				}catch(\Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $e){
				
					$message = $e->getMessage();
					$message.='<br>: Admin Tx Authorization Failed. Sent Tx Failed'.$message;
					$order->logg .= $message;
					$order->status="FAILED";
					$order->save();
					return false;
				}catch(\Exception $e){
				
					$message = $e->getMessage();
					$order->status="FAILED";
					$order->logg .= $message;
					$order->save();
					return false;
				}
				$order->logg .= "<br> TX COMPLETE SUCCESSFULLY <br>".$this->tx_link($tx_hash);
			}elseif($token->family =='bitFamily'){
				$fee = setting('defaultFees','medium');
				try{
					$tx_hash = $this->coin_send(
						$order->amount, 
						$order->address, 
						$order->wallet, 
						$cryptopass, 
						$order->id,
						$fees??'high'
					 ) ;
				}catch(\Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $e){
						throw $e;
					$message = $e->getMessage();
					$message.='<br>: INVALID ADMIN PASSWORD .Sent Tx Failed'.$message;
					$order->logg .= $message;
					$order->status="FAILED";
					$order->save();
					return false;
				}catch(\Exception $e){
						throw $e;
					$message = $e->getMessage();
					$order->logg .= $message;
					$order->status="FAILED";
					$order->save();
					return false;
				}
				$order->logg .= "<br> TX COMPLETE SUCCESSFULLY <br>".$this->coin_tx_link($tx_hash , $token->symbol );	
			}
			
		//order	
		$order->status = "COMPLETE";
		$order->txid = $tx_hash;
		$order->save(); 
		return $tx_hash;
		 
	}
	
	public function form_validation( ){
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
		if(!$token instanceof Token){
			return response()->json(['status' => 'ERROR','message' => 'Gateway Mismatch. Blockchain Gateway Cannot process this Payment']);
		}
		// GET the USer
		if(!auth()->check()){
			$credentials = $request->only('email', 'password');
			if (Auth::attempt($credentials) == false) {
			   return response()->json(['status' => 'ERROR','message' => 'Invalid User. Please Check your Account Details']);
			}
			session(['auth' => true]);
		}
		$user = auth()->user();
	
		if($token ->family == 'ethereum'){
			$gasLimit = empty($request->input('gasLimit'))?NULL:$request->input('gasLimit');
			$gasPrice = empty($request->input('gasPrice'))?NULL:$request->input('gasPrice');
			$account = $user->account;
			// merchant
			$maccount = $order->account;
			$muser = $order->account->user;
			if($order->user()->count() < 1){
				$order->user()->associate($user)->save();
			}
			try{
				$tx_hash = $this->send(
					$order->amount+$order->fees, 
					$order->address, 
					$account, 
					$request->input('password'), 
					$token->symbol == "ETH"?NULL:$token,
					$gasLimit,
					$gasPrice
				 ) ;
			}catch(\Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $e){
				$message = $e->getMessage();
				$message.=': INVALID PASSWORD';
				return response()->json(['status' => 'ERROR','message' => 'Sent Tx Failed , '.$message]);
			}catch(\Exception $e){
				return response()->json(['status' => 'ERROR','message' => 'Sent Tx Failed , '.$e->getMessage()]);
			}
			$order->txid = $tx_hash;
			if($order->status!='MARKET' ||$order->status!='PARTIAL'){
				$order->status =='CONFIRMING';
			}
			$order->save();
			if ($request->session()->has('auth')) {
				 //
				 Auth::logout();
				 $request->session()->forget('auth');
			}
			return response()->json(['URL'=>route('order',$order->reference),'status' => 'SUCCESS','message' => 'Sent Tx Hash:<a target="_blank"  href="'.$this->tx_link($tx_hash).'">'.$tx_hash .'</a>']);
		}elseif($token->family == 'bitFamily'){
				///BTC LTC ETC
			
			$wallet = $user->wallets()->ofToken($token->id)->first();
			try{
				$tx_hash =  $this->coin_send(
					$order->amount+$order->fees,
					$order->address, 
					$wallet, 
					$request->input('password'), 
					$order->id, 
					"high"
			    );
			}catch(\Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $e){
				$message = $e->getMessage();
				$message.=': INVALID PASSWORD';
				return response()->json(['status' => 'ERROR','message' => 'Sent Tx Failed , '.$message]);
			}catch(\Exception $e){
				return response()->json(['status' => 'ERROR','message' => 'Sent Tx Failed , '.$e->getMessage()]);
			}
			$order->txid = $tx_hash;
			$order->txid = $tx_hash;
			if($order->status!='MARKET' ||$order->status!='PARTIAL'){
				$order->status =='CONFIRMING';
			}
			$order->save();
			if ($request->session()->has('auth')) {
				 Auth::logout();
				 $request->session()->forget('auth');
			}
			return response()->json(['status' => 'SUCCESS','message' => 'Sent Tx Hash:<a target="_blank"  href="'.$this->coin_tx_link($tx_hash, $token).'">'.$tx_hash .'</a>']);
		}
		
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
		$this->view =  View::make('gateways.blockchain', compact('order','items','siteOrder'));
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