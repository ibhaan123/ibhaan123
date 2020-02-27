<?php

namespace App\Http\Controllers\Admin;

use Auth;
use \App\Models\Token;
use \App\Models\Wallet;
use \App\Models\Transaction;
use \App\Models\User;
use  Yajra\DataTables\DataTables;
use Illuminate\Http\Request;
use App\Traits\WalletTrait;
use Illuminate\Support\Facades\Hash;
use Pdazcom\Referrals\Models\ReferralProgram;
use Pdazcom\Referrals\Models\ReferralLink;
use App\Http\Controllers\Controller;

class WalletController extends Controller
{
	use WalletTrait;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		
		$user = Auth::user();
		$tkn = $ethToken = Token::symbol('ETH')->first();
		$tokenWallet = $this->getOrMakeAdminWallet($tkn);
		$TokensList = Token::whereNotIn('id',$user->wallets()->pluck('token_id'))->get()->pluck('name','id');
	
		$bit = Token::where('family','bitFamily')->get()->pluck('id')->all();
		$bitwallets = $user->wallets()->whereIn('token_id',$bit)->get();
		//
        return view('_admin.wallets.index', compact(
			'user',
			'tkn',
			'ethToken',
			'TokensList',
			'tokenWallet',
			'bitwallets'
		));
    
	}
	
	public function txTable()
    {
		
		$tx = Transaction::with('token')->get()->makeVisible('created_at');
        return DataTables::of($tx)
		    ->rawColumns(['tx_hash','from_address','to_address'])
			->addColumn('symbol', function ($tx) {
				return  $tx->token->symbol;
			})
			->editColumn('amount', function ($tx) {
				return  $tx->amount.' '.$tx->token->symbol;
			})
			->editColumn('tx_hash', function ($tx) {
				if($tx->token->family =='bitFamily')
				$link = $this->coin_tx_link($tx->tx_hash, $tx->token->symbol);
				else
				$link = $this->tx_link($tx->tx_hash); 
				
				return ' <a target="_blank"  href="'.$link.'" data-toggle="tooltip" class="tooltips" data-original-title="Explore at the Blockchain" title="Explore at the Blockchain">'.substr($tx->tx_hash,0,12).'.....
                         </a>';
			})
			->editColumn('from_address', function ($tx){
				if($tx->token->family =='bitFamily')
				$link = $this->coin_address_link(__($tx->from_address),__( $tx->token->symbol));
				else
				$link = $this->address_link(__($tx->from_address)); 
				return $tx->type=="query"?'<span style="cursor:pointer" data-toggle="tooltip" class="tooltips" data-original-title="'.$tx->description.'" title="'.$tx->description.'">'.str_limit($tx->description,20).'</span>':' <a target="_blank"  href="'.$link.'" data-toggle="tooltip" class="tooltips" data-original-title="View at Etherscan" title="Explore at the Blockchain">'.substr(__($tx->from_address),0,10).'.....
                         </a>';
			})
			->editColumn('to_address', function ($tx){
				if($tx->token->family =='bitFamily')
				$link = $this->coin_address_link(__($tx->from_address), __($tx->token->symbol));
				else
				$link = $this->address_link(__($tx->from_address)); 
				return ' <a target="_blank"  href="'.$link.'" data-toggle="tooltip" class="tooltips" data-original-title="View at Etherscan" title="View at Etherscan">'.substr(__($tx->to_address),0,10).'.....
                         </a>';
			})->toJson();
    }
	
	 public function walletsTable()
    {
		$wallets = auth()->user()->wallets()->has('token')->get();
        return DataTables::of($wallets)
		  	->rawColumns(['action','symbol','usd_balance'])
			->setRowClass(function ($wallet) {
				$ut =  bccomp($wallet->service_balance , $wallet->balance ,8)==1 ? 'danger' : 'success';
				return  bccomp( $wallet->service_balance ,$wallet->balance ,8) ==0 ? '' : $ut;
			})
			->addColumn('symbol', function ($wallet) {
				return  '<a style="color:green" href="'.route("admin.tokens.show",$wallet->token->id).'">'.$wallet->token->name.' ( '.__($wallet->token->symbol).') </a>'; 
			})
			->editColumn('ubalance', function ($wallet) {
				return $wallet->service_balance.$wallet->token->symbol ;
			})
			->editColumn('available', function ($wallet) {
				return bcsub($wallet->service_balance , $wallet->balance, 8).$wallet->token->symbol ;
			})
			->editColumn('balance', function ($wallet) {
				return $wallet->balance??"0.0000";
			})
			->editColumn('action', function ($wallet) {
				return ' <a class="ajax_link authorize confirm" data-confirm="Are you sure you want to delete this wallet?" table ="walletsTable" data-table ="walletsTable" href="'.route("admin.wallets.remove",$wallet->id).'"><i class="fa fa-close"></i></a>';
			})
			->toJson();
    }
	

	
	public function addWallet(Request $request){
		$request->validate([
			'token_id'=>'required|numeric',
			'password'=>'required'
		]);
		$token_id = $request->input('token_id');
		$password = $request->input('password');
		$user = auth()->user();
		$password = $request->input('password');
		if (!Hash::check($password, $user->password)) {
			return response()->json(['status' => 'ERROR','message' => 'Invalid Password. Please Input your Login Password. Your private Keys are encrypted']);
		}
		$token = \App\Models\Token::findOrFail($token_id);
		if($token->family == "bitFamily"){
			$coldKey = setting($token->symbol.'_coldKey',false);
			if(empty($coldKey)){
				return response()->json(['status' => 'ERROR','message' => 'Wallet SetUp Failed. Admin Multisig Setup Incomplete']);
			}
			try{
				$this->coin_create_wallet( $user->account , $password , $token);
			}catch(\Exception $e){
				return response()->json(['status' => 'ERROR','message' => $e->getMessage()]);
			}
			return response()->json(['status' => 'SUCCESS','message' => $token->name.' Wallet Initialized Successfully']);
		}
		$wallet = auth()->user()->wallets()->ofToken($token_id)->firstOrNew([
			'user_id'=>auth()->user()->id,
			'account_id'=>auth()->user()->account->id,
			'token_id'=>$token->id,
			'token_type'=>$token->type,
		]);
		$wallet->save();
		//$wallet->updateBalance();
		return response()->json(['status' => 'SUCCESS','message' =>  $token->name.' Wallet Added Successfully']);
	}
	
	/*Remove Wallet From the Database*/
	public function removeWallet(Request $request, $token_id){
		
		$wallet = Wallet::findOrFail($token_id);
		$message = $wallet->token->name;
		$wallet->forceDelete(); 
		return response()->json(['URL'=>url('admin/wallets'),'status' => 'SUCCESS','message' =>' Your '. $message.' Wallet Was Removed Successfully']);
	}
	
	
	public function backupKey(Request $request){
		$user = auth()->user();
		$password = $request->input('password');
		if (Hash::check($password, $user->password)) {
			list($mnc,$Acc) = $this->unlocPrivateKey($user->account,$password);
			$resp ='###YOUR BACKUP DATA###'.PHP_EOL;
			$resp .='##MNEMONIC:'.PHP_EOL;
			$resp .=$mnc.PHP_EOL;
			$resp .='##PASSWORD:'.PHP_EOL;
			$resp .=$password.PHP_EOL;
			$resp .='##MASTER PRIVATE KEY:'.PHP_EOL;
			$resp .= $Acc->getMasterXpriv().PHP_EOL;
			$resp .='##ETHEREUM PUBLIC KEY at m/44\'/60\'/0\'/0:'.PHP_EOL;
			$resp .= $Acc->getXpub().PHP_EOL;
			return response()->json(['filename'=>$user->account->account,'file'=>$resp,'status' => 'SUCCESS','message' => 'Backup Generated successfully. Download Has started']);
		}
		return response()->json(['status' => 'ERROR','message' => 'Invalid Password. Please Check your password']);
		
		
	}
	
	public function sendToken(Request $request){
		
		 
		$request->validate([
            'amount'=> 'required|numeric',
            'token_id'  => 'required|numeric',
            'to'  => 'required|alpha_num',
            'password' => 'required|min:3',
        ]);
		$user = auth()->user();
		$token = Token::findOrFail($request->input('token_id'));
		
		if($token->family == 'ethereum'&& strlen($request->to) !=42){
			
			
			return response()->json(['status' => 'ERROR','message' => 'Invalid Ethereum Address']);
		}
		
		if($token->family == 'bitFamily'){
			$wallet = $user->wallets()->ofToken($token->id)->first();
			
			try{
				$tx_hash =  $this->coin_send($request->input('amount'), $request->input('to'), $wallet, $request->input('password'), NULL , "high") 	;
			}catch(\Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $e){
				$message = $e->getMessage();
				$message.=': INVALID PASSWORD';
				return response()->json(['status' => 'ERROR','message' => 'Sent Tx Failed , '.$message]);
			}catch(\Exception $e){
				return response()->json(['status' => 'ERROR','message' => 'Sent Tx Failed , '.$e->getMessage()]);
			}
			return response()->json(['status' => 'SUCCESS','message' => 'Sent Tx Hash:<a target="_blank"  href="'.$this->coin_tx_link($tx_hash, $token).'">'.$tx_hash .'</a>']);
		}elseif($token->family == 'ethereum'){
			$gasLimit = empty($request->input('gasLimit'))?NULL:$request->input('gasLimit');
			$gasPrice = empty($request->input('gasPrice'))?NULL:$request->input('gasPrice');
			$account = $user->account;
			try{
				$tx_hash = $this->send(
					$request->input('amount'), 
					$request->input('to'), 
					$account, 
					$request->input('password'), 
					$token->symbol =='ETH'?NULL:$token,
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
			return response()->json(['status' => 'SUCCESS','message' => 'Sent Tx Hash:<a target="_blank"  href="'.$this->tx_link($tx_hash).'">'.$tx_hash .'</a>']);
		}
		
	}
	
	
	
	
	
	
}
