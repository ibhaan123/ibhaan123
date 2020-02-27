<?php

namespace App\Traits;

use App\Logic\Activation\ActivationRepository;
use App\Models\User;
use App\Models\Account;
use jeremykenedy\LaravelRoles\Models\Role;
trait WalletTrait

{
	use BitcoinTrait;
	
		public function isOffNet($token){
			if(!isset($token->net)||empty($token->net)) return false;
			$chains = ['olympic'=>0,'frontier'=>1,'mainnet'=>1,'homestead'=>1,'metropolis'=>1,'classic'=>1,'expanse'=>1,'morden'=>2,'ropsten'=>3,'rinkeby'=>4,'kovan'=>42];
			$set = setting('ETHEREUMNETWORK','mainnet');
			$chain = isset($chains[$set])?$chains[$set]:1;
			$net = isset($chains[$token->net])?$chains[$token->net]:1;
			return $net != $chain;
		}
	 	
		public function web3(){
			$infuraToken =  env('INFURATOKEN', setting('INFURATOKEN',''));
        	$etherscanToken  = env('ETHERSCANTOKEN', setting('ETHERSCANTOKEN',''));
		 	$parityIp  =  env('PARITYIP', setting('PARITYIP', '127.0.0.1:8545'));
        	$ethereumNetwork   = env('ETHEREUMNETWORK', setting('ETHEREUMNETWORK','mainnet'));
        	$ethereumProvider  = env('ETHEREUMPROVIDER', setting('ETHEREUMPROVIDER','infura'));
			
			if($ethereumProvider == 'infura'){
				if(empty($infuraToken))throw new \Exception('Please Add infura Token in .env File');
				$provider = new \phpEther\Web3\Providers\Infura($infuraToken , $ethereumNetwork); 
				return new \phpEther\Web3($provider);
			}
			if($ethereumProvider == 'etherscan'){
				if(empty($etherscanToken))throw new \Exception('Please Add etherscanToken in .env File');
				$provider = new \phpEther\Web3\Providers\Etherscan($etherscanToken , $ethereumNetwork); 
				return new \phpEther\Web3($provider);
			}
			if($ethereumProvider == 'parity'){
				if(empty($parityIp))throw new \Exception('Please Add  Geth IP address in .env File');
				$provider = new \phpEther\Web3\Providers\Geth($parityIp , $ethereumNetwork); 
				return new \phpEther\Web3($provider);
			}
			
			throw new \Exception('No provider Selected');
		}
	
		public function tx_link($tx_id){
			$ethereumNetwork   = env('ETHEREUMNETWORK', setting('ETHEREUMNETWORK','mainnet'));
			$api = in_array($ethereumNetwork,['frontier', 'homestead', 'metropolis','mainnet'])?'':$ethereumNetwork.'.';
			return "https://{$api}etherscan.io/tx/".$tx_id; 
		}
		
		public function address_link($address){
			$ethereumNetwork   = env('ETHEREUMNETWORK', setting('ETHEREUMNETWORK','mainnet'));
			$api = in_array($ethereumNetwork,['frontier', 'homestead', 'metropolis','mainnet'])?'':$ethereumNetwork.'.';
			return "https://{$api}etherscan.io/address/".$address;
		}
		
		protected function convertIntegers($construction){
			foreach($construction as $k => $v){
				if( 
					stripos(strtolower($k),'start')!==false||
					stripos(strtolower($k),'end')!==false||
					stripos(strtolower($k),'time')!==false
				)
				{
					$construction[$k] = \Carbon\Carbon::parse($construction[$k])->timestamp;
				}
				
				if(
					stripos(strtolower($k),'teamBonus')!==false||
					stripos(strtolower($k),'cap')!==false||
					stripos(strtolower($k),'price')!==false||
					stripos(strtolower($k),'rate')!==false||
					stripos(strtolower($k),'amount')!==false||
					stripos(strtolower($k),'value')!==false||
					stripos(strtolower($k),'bid')!==false||
					stripos(strtolower($k),'asksize')!==false||
					stripos(strtolower($k),'supply')!==false
				)
				{
					$construction[$k] = $this->web3()->toWei($construction[$k]);
				}
			}
			return $construction;
		}
	
    
		public function getOrMakeAdminWallet($token, $password =NULL){
			$user = $token->user;
			$account = $user->accounts()->first();
			if($token->family == "bitFamily"){
				$wallet = auth()->user()->wallets()->where('token_id', $token->id)->first();
				if($wallet)return $wallet;
				$password = !is_null($password)?$password:$user->isAdmin()?env('CRYPTO'):$token->ico_pass;
				if(empty($password))$password = env('SETUP_PASSWORD');
				if(empty($password)){
					throw new Exception('Wallet SetUp Failed. Admin Multisig Setup Incomplete');
				}
				$coldKey = setting($token->symbol.'_coldKey',false);
				if(empty($coldKey)){
					throw new Exception('Wallet SetUp Failed. Admin Multisig Setup Incomplete');
				}
				try{
					return $this->coin_create_wallet( $account , $password , $token);
				}catch(\Exception $e){
					throw $e;
				}
				return $wallet;
			}
			$wallet = auth()->user()->wallets()->ofToken($token->id)->firstOrCreate([
				'user_id'=>auth()->user()->id,
				'account_id'=>$account->id,
				'token_id'=>$token->id,
			]);
			return $wallet;
		} 

	
	
		protected function decodeInteger($method, $val){
			if(
				stripos(strtolower($method),'volume')!==false||
				stripos(strtolower($method),'supply')!==false||
				stripos(strtolower($method),'balance')!==false||
				stripos(strtolower($method),'tokens')!==false
				
			)
			{
				return $this->web3()->fromWei($val);
			}
			
			return $val;
			
		}
		
	
		
		protected function resolve($contract,\App\Models\Token $token,$func,$construction=[],$password=NULL,$eth=0,$account = NULL)
		{
			$account = $account?$account:auth()->user()->account;
			$web3 =  $this->web3();
			$construct = empty($construction)?[]:$this->convertIntegers($construction);
			$abi = $contract == "mainsale"?$token->contract->mainsale_abi:$token->contract_ABI_array;
			$address = $contract == "mainsale"?$token->mainsale_address:$token->contract_address;
			try{
				$result = $this->Query($address, $abi,$func, $token, $account, $construct, $password ,$eth);
			}catch(Exception $e){
				throw $e;
			}
			return $this->get_response($func,$result);
		}
		
		private function get_response($func, $res){
		if(is_numeric($res)){
			$nbr =  (string)$this->decodeInteger($func,$res);
			return strpos($nbr,'.')!==false ? rtrim(rtrim($nbr,'0'),'.') : $nbr;
		}
		if(!is_array($res))return $res;
		$result = "";
		foreach($res as $k => $v )
		{
			if(!is_array($v))
			$v  = $this->get_response($func,$v);
			$result.= " ".$k .' = '.$v.'<br>';
		}
		return $result;
	}
		
		
		public function unlockAccount($account,$password){
			$protected_key =\Defuse\Crypto\KeyProtectedByPassword::loadFromAsciiSafeString($account->cypher);
		    $key  = $protected_key->unlockKey($password);
			$master_xpriv = \Defuse\Crypto\Crypto::decrypt($account->xpriv,$key);
			$HD = new \phpEther\HD();
			$index = $account->idx?$account->idx:0;
			return $HD->masterSeed($master_xpriv)->getAccount($index);
		}
		
		public function unlocPrivateKey($account,$password){
			$protected_key =\Defuse\Crypto\KeyProtectedByPassword::loadFromAsciiSafeString($account->cypher);
		    $key  = $protected_key->unlockKey($password);
			$master_xpriv = \Defuse\Crypto\Crypto::decrypt($account->xpriv,$key);
			$HD = new \phpEther\HD();
			return [ \Defuse\Crypto\Crypto::decrypt($account->mnemonic,$key),$HD->masterSeed($master_xpriv)];
			
		}
		 
		
		public function deploy(\App\Models\Account $from, string $password, string $contractABI,string $contractBIN, array $construction){
			
			$web3 = $this->web3();
			$account = $this->unlockAccount($from,$password);
			$last = $from->transactions()->orderBy('nonce','dec')->first();
			$nonce = empty($last)?0:$last->nonce+1; // last tx in not paid
			$tx = new \phpEther\Transaction($account, NULL, 0 , NULL ,$nonce); 
			$contract = $web3->eth->contract($contractABI)->deploy($contractBIN , $tx);
			// send to the blockchain by calling the contructor
			$res = $contract->constructor($construction);
			$token = \App\Models\Token::where('symbol','ETH')->first();
			if($res instanceof \phpEther\Transaction){
				$tx = $res->getTx();
				$transaction = new \App\Models\Transaction(); 
				$transaction->confirmations = 0;
				$transaction->from_address  = $from->account;
				$transaction->to_address  = "SMART_CONTRACT";
				$transaction->type ='debit';
				$transaction->account_id =$from->id;
				$transaction->user_id =$from->user_id;
				$transaction->token_id = $token->id;
				$transaction->token_type = '\App\Models\Token';
				$transaction->amount = $tx->value;
				$transaction->tx_hash = $tx->hash;
				$transaction->tx_hash_link = $this->tx_link($transaction->tx_hash);
				$transaction->description = "You Deployed a Smart Contract";
				$transaction->nonce = $tx->nonce ;
				$transaction->gas_price = $web3->fromWei($tx->gasPrice) ;
				$transaction->gas_limit = $web3->fromWei($tx->gasLimit) ;
				$transaction->save();
				return $transaction->tx_hash;
			}
			return  $res; 
		}
		
		
		public function Query($address, $abi, string $func, \App\Models\Token $token, \App\Models\Account $from,  array $construct = [] ,$password = NULL, $eth=0 ){
		
			$web3 = $this->web3();
			$ether = NULL;
			if($this->isOffNet($token))throw new \Exception('<p class="red">INVALID NETWORK. Please contact Admin. This Token is for '.$token->net.' But Network is set to '.setting('ETHEREUMNETWORK').' </p>');
			
			$contract = $web3->eth->contract($abi)->at($address)->decode();
			if($password){
				try{
					$account = $this->unlockAccount($from,$password);
				}catch(\Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $e){
					throw new \Exception('<p class="red">INVALID PASSWORD</p> <p>'.$e->getMessage().'</p>');
				}
				
				if(empty($eth)){ 
					$ether = 0;
				}else{
					$ether = $web3->toWei($eth);
					
				}
				$last = $from->transactions()->orderBy('nonce','dec')->first();
				$nonce = empty($last)?0:$last->nonce+1; // last tx in not paid
				$tx = new \phpEther\Transaction($account, NULL, $ether , NULL ,$nonce);
				$construct[] = $tx; 
			}
			
			//dd($construct);
			$res = call_user_func_array(array($contract, $func), $construct);
			//$res = $contract->$func($construct);
			if($res instanceof \phpEther\Transaction){
				$tx = $res->getTx();
				$ethereum = \App\Models\Token::where('symbol','ETH')->first();
				$transaction = new \App\Models\Transaction(); 
				$transaction->confirmations = 0;
				$transaction->from_address  = $from->account;
				$transaction->to_address  = $token->contract_address;
				$transaction->type ='query';
				$transaction->account_id =$from->id;
				$transaction->user_id =$from->user_id;
				$transaction->token_id = $ethereum->id;
				$transaction->token_type = '\App\Models\Token';
				$transaction->amount = $eth; 
				$transaction->tx_hash = $tx->hash; 
				$transaction->tx_hash_link = $this->tx_link($transaction->tx_hash);
				$transaction->description = "You Queried { $token->name } {$token->symbol}=>{$func}() Contract ";
				$transaction->nonce = $tx->nonce ;
				$transaction->gas_price = $web3->fromWei($tx->gasPrice) ;
				$transaction->gas_limit = $web3->fromWei($tx->gasLimit) ;
				$transaction->save();
				return $transaction->tx_hash;
			}
			return $res;
			
		}
		
		public function deriveAddress(\App\Models\Account $account, $index){
			$HD = new \phpEther\HD();
			$index = $index??$account->orders()->count()+1;
			return [$index , $HD->publicSeed($account->xpub)->getAddress($index)];
		}
	
		public function create_account( User $user , $password , $idx = 0 , $name ='Default' ){
			$GN = new \phpEther\HD();
			$HD = $GN->randomSeed($password); // get random HD Keys
			$account = $HD->getAccount($idx);
			$locked_key = \Defuse\Crypto\KeyProtectedByPassword::createRandomPasswordProtectedKey($password);
			$locked_key_encoded = $locked_key->saveToAsciiSafeString();// now in db
  			$protected_key = \Defuse\Crypto\KeyProtectedByPassword::loadFromAsciiSafeString($locked_key_encoded);
		    $key = $protected_key->unlockKey($password);
			//$token = $user->createToken('Merchant-Key')->accessToken;
			$acc = $user->accounts()->create([
				'account'=>$account->getAddress(),
				'api_key'=>str_random('28'),
				'user_id'=>$user->id,
				'xpub'=>$HD->getXpub(),
				'mnemonic'=>\Defuse\Crypto\Crypto::encrypt($HD->getMnemonic(), $key),
				'xpriv'=> \Defuse\Crypto\Crypto::encrypt($HD->getMasterXpriv(), $key),
				'cypher'=>$locked_key_encoded,
				]);
			$acc->save();
			return true;
		}
		
	  public function balance($address, \App\Models\Token $token = NULL){
		  if(!empty($token) && $token->family != "ethereum") return 0;
		  	$web3 = $this->web3();
		   if(is_null($token)||$token->symbol == 'ETH'){
				$res = $web3->eth->getBalance($address);
				if(empty($res->getHex()))return 0;
				return $web3->fromWei($res->getInt());
			}elseif(!empty($token->contract_address)){// token
				if($this->isOffNet($token))return 0;
				$token = $web3->eth->contract($this->abi)->at($token->contract_address);
				$res = $token->balanceOf($address);
				if(empty($res->getHex()))return 0;
				return $web3->fromWei($res->getInt());
			}
		}
	 
	 public function getERC20CoinInfo(\App\Models\Token $token)
		{
			
			if(!empty($this->abi)){// token
				$web3 = $this->web3();
				$abiArray = json_decode($this->abi, true) ;
				$token = $web3->eth->contract($this->abi)->at($token->contract_address);
				$name = $token->name()->getBinary();
				$symbol = $token->symbol()->getBinary();
				$decimals = $token->decimals()->getInt();
				
				//dd($name , $symbol, $decimals);
				if(stripos($this->abi,'totalSupply')!==false)
					$totalSupply = $token->totalSupply()->getInt();
					
				if (!$symbol) {
					throw new \Exception('This is not ERC20 coin');
				}
				if (!$name) {
					$name = $symbol;
				}
				return [$name,$symbol,$decimals,$totalSupply];
			}
		} 
		
		public function setERC20CoinInfo(\App\Models\Token $token)
		{
			$web3 = $this->web3();
			$abi = $this->abi;
			if($token->contract()->count()){
				$abi = $token->contract->abi;
				$mainsale = $token->contract->mainsale_abi;
				if(!empty($mainsale)){
					$mainsale_contract = $web3->eth->contract($mainsale)->at($token->contract_address);
					$token->mainsale_address =  $token->contract_address;
					$token->contract_address = '0x'. $mainsale_contract->token()->getHex();
					try{
						$mainsale_contract->getMethodBin('hardcap',  []);
						$tt = $mainsale_contract->hardcap();
						$total_supply = empty($tt->getHex())?0:$tt->getInt();
						$token->total_supply =$total_supply?(int)$web3->fromWei($total_supply):0 ;
					}catch (\Exception $e ){
					}
					try{
						$mainsale_contract->getMethodBin('rate',  []);
						$tr = $mainsale_contract->rate();
						$price = $token->token_price  = empty($tr->getHex())?0:$tt->getInt();
						$token->price = $token->token_price  = $price ?(int)$web3->fromWei($price):0;
					}catch (\Exception $e ){
					}
				}
			}
			$contract = $web3->eth->contract($abi)->at($token->contract_address);
			$token->name = $contract->name()->getBinary();
			$token->symbol = $contract->symbol()->getBinary();
			$token->decimals = $contract->decimals()->getInt();
			try{
				$contract->getMethodBin('hardcap',  []);
				$tt = $contract->hardcap();
				$total_supply = empty($tt->getHex())?0:$tt->getInt();
				$token->total_supply = $total_supply?(int)$web3->fromWei($total_supply):0 ;
			}catch (\Exception $e ){
			}
			try{
				$contract->getMethodBin('rate',  []);
				$tr = $contract->rate();
				$price = $token->token_price  = empty($tr->getHex())?0:$tt->getInt();
				$token->price = $token->token_price  = $price ?(int)$web3->fromWei($price):0;
			}catch (\Exception $e ){
			}
			try{
				$contract->getMethodBin('totalSupply',  []);
				$tt = $contract->totalSupply();
				$total_supply = empty($tt->getHex())?0:$tt->getInt();
				$token->total_supply = $total_supply?(int)$web3->fromWei($total_supply):0 ;
			}catch (\Exception $e ){
			}
			
			if (!$token->symbol) {
				throw new \Exception('This is not ERC20 coin');
			}
			if (!$token->name) {
				$token->name = $token->symbol;
			}
			$token->save();
			return $token;
		} 
		
		public function send($amt, $to, \App\Models\Account $from, $password, \App\Models\Token $token = NULL, $order_id = NULL) 	
		{
			
			$account = $this->unlockAccount($from,$password);
			$web3 =  $this->web3();
			$value = $web3->toWei($amt, 'ether'); 
			if(!is_null($token)&&$token->symbol!="ETH"){
				$balance = $this->balance($from->account , $token);
				if(bccomp($amt , $balance , 8) == 1)
				throw new \Exception('Low Balance: ('.$balance.$token->symbol.') '.$amt.$token->symbol.'Required' );
				$contract = $web3->eth->contract($this->abi)->at($token->contract_address);
				try{
					$res = $contract->transfer($to, $value, new \phpEther\Transaction($account));
				}catch( Exception $e ){
					throw $e;
				}
			}else{
				$tx = new \phpEther\Transaction(
					$account, 
					$to,
					(int)$value
				);
				
				try{
					$res = $tx->setWeb3($web3)->prefill()->send();
				}catch( Exception $e ){
					throw $e;
				}
			}
			if(is_null($token))
			$token = \App\Models\Token::symbol('ETH')->first();
			$tx = $res->getTx();
			$transaction = new \App\Models\Transaction();
			$transaction->confirmations = 0;
			$transaction->from_address  = $from->account;
			$transaction->to_address  = $to;
			$transaction->type ='debit';
			$transaction->order_id = $order_id;
			$transaction->account_id =$from->id;
			$transaction->user_id =$from->user_id;
			$transaction->token_id = $token->id;
			$transaction->token_type = '\App\Models\Token';
			$transaction->amount = bcmul($amt , 1 , 8);
			$transaction->tx_hash = $tx->hash; 
			$transaction->tx_hash_link = $this->tx_link($tx->hash);
			$transaction->description = "You Sent {$amt} {$token->symbol}";
			$transaction->nonce = $tx->nonce ;
			$transaction->gas_price = $web3->fromWei($tx->gasPrice) ;
			$transaction->gas_limit = $web3->fromWei($tx->gasLimit) ;
			$transaction->save();
  			return $tx->hash;
		}
		
		
		public function refresh_hash($hash , \App\Models\Token $token =NULL){
			$web3 =  $this->web3();
			$TX = $web3->eth->getTransactionByHash($hash);
			$now = $ether->eth_blockNumber();
			$confirms  = gmp_strval( gmp_sub($now->getGmp(),$TX->blockNumber->getGmp()));
			$TX->confirmations =  $confirms;
			$TX->address = is_null($token)?NULL:$token;
			$txs = [];
			if(!empty($TX)){
				if(!is_null($token)){
					if($this->isOffNet($token))return false;
					$filter = new \phpEther\Filter($TX->blockNumber->getInt(),$now->getInt(),$TX->to,["0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef"]);
					$logs =  $web3->eth->getLogs($filter);
					$txs = array_map(function($log)use($TX,$confirms){
						$tx = new stdClass;
						list(,$tx->from, $$tx->to) = $log->topics;
						if('0x'.$log->transactionHash->getHex() != $TX->hash );
						return NULL;
						$tx->value = $log->data;
						$tx->hash =  $log->transactionHash ;
						$tx->address = $log->address;
						//$tx->blockHash = $log->blockHash ;
						$tx->blockNumber = $log->blockNumber;
						$tx->confirmations = $confirms;
						$tx->transactionIndex = $log->transactionIndex;
						return $tx;
					}, $logs);
				}
				$txs[] = $TX;
				foreach($txs as $txn){
					if(is_null($txn))continue;
				 	$this->processtx($txn ,$token);
				}
				 
			}
		}
		
		
		public function processtx($tx , \App\Models\Token $token =NULL ,\App\Models\Order $order =NULL){
			
			$web3 = $this->web3();
			if(is_null($token))
			$token = \App\Models\Token::symbol('ETH')->first();
			\DB::table('transactions')
            	->where('tx_hash', '0x'.$tx->hash->getHex())
            	->update(['confirmations'=>$tx->confirmations]);
			$event = \App\Models\Transaction::where('type','credit')->where('tx_hash','0x'.$tx->hash->getHex())->get();
			
			if($event->count() < 1){  
				$to = \App\Models\Account::where('account','0x'.$tx->to->getHex())->first();
				$symbol = $token->symbol;
				if(!empty($order))
				$to = $order->account;
				if(empty($to))return false;
			    $transaction = new \App\Models\Transaction();
				$amt = number_format((float)$web3->fromWei($tx->value->getInt()),8, '.', '');
				$transaction->confirmations = isset($tx->confirmations)?$tx->confirmations: 5;
				$transaction->from_address  = '0x'.$tx->from->getHex();
				$transaction->to_address  = '0x'.$tx->to->getHex();
				$transaction->type ='credit';
				$transaction->account_id =$to->id;
				$transaction->user_id =$to->user_id;
				$transaction->token_id = $token->id;
				$transaction->token_type = '\App\Models\Token';
				$transaction->amount = $amt;
				$transaction->tx_hash = '0x'.$tx->hash->getHex();
				$transaction->tx_hash_link = $this->tx_link($transaction->tx_hash);
				$transaction->description = "You Recieved {$amt}{$token->symbol}";
				$transaction->save(); 
				if(empty($order)){
					return  $transaction->id;   
				}
				$transaction->order_id = $order->id;
				$transaction->save();
				if($tx->confirmations > setting('minConf', 3) && $transaction->processed == 0){
					$transaction->processed = 1;
					$transaction->save();
					try{
						 return $order->completeOrder($order);
					}catch(Exception $e){
						return false;
					}
				}
				return true;
 			}
			foreach($event as $txn ) { // out going or incoming Transaction
				$order = $txn->order;
				if(empty($order)) continue;
				if($txn->confirmations > setting('minConf', 3) && $txn->processed == 0){
					$txn->processed = 1;
					$txn->save();
					try{
						 return $order->completeOrder($order);
					}catch(Exception $e){
						return false;
					}
				}
			}
			return true;
		 }
		 
		 
		public function refresh_tx(\Illuminate\Support\Collection $accounts , \App\Models\Token $token = NULL)
		{
			
			
			
			$rid = is_null($token)?'ETH':$token->symbol;
			$web3 = $this->web3();
			$end = $web3->eth->blockNumber();
			$last = \App\Models\Last::where('rid', $rid)->first();
			$startBlockNumber =  $last->start_block;
			$endBlockNumber = $last->end_block;
			$mine = [];
			
			$addresses = $accounts->pluck('account')->map(function($v,$k){
								return strtolower($v);
							})->all() ;
			if($token->symbol =="ETH"){
					for($i = $startBlockNumber; $i <= $endBlockNumber;  $i++){
						$block = $web3->eth->getBlockByNumber( $i, true);
						if(!isset($block->transactions))continue;
						$found = array_filter($block->transactions,function($tx)use($addresses){
							return (in_array('0x'.$tx->from->getHex(), $addresses)||in_array('0x'.$tx->to->getHex(), $addresses));
							
						});
						$mine = array_merge($mine,$found);
					}
					
					foreach($mine as $tx){
						$tx->confirmations = gmp_strval(gmp_sub($end->getGmp(),$tx->blockNumber->getGmp()));
						$this->processtx($tx);
					}
					return true;
			}elseif(!empty(trim($token->contract_address))){
				if($this->isOffNet($token))return false;
					$filter = new \phpEther\Filter($startBlockNumber,$endBlockNumber,$token->contract_address,["0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef"]);
					$logs =  $web3->eth->getLogs($filter);
					$txs = array_map(function($log)use($end,$addresses){
						$tx = new \stdClass;
						list(,$tx->from, $tx->to) = $log->topics;
						if(!in_array('0x'.$tx->to->getHex(),$addresses)&&!in_array('0x'.$tx->from->getHex(),$addresses))
						return NULL;
						$tx->value =  $log->data;
						$tx->hash = $log->transactionHash;
						$tx->address = $log->address;
						$tx->confirmations = gmp_strval(gmp_sub($end->getGmp(),$log->blockNumber->getGmp()));
						$tx->blockNumber = $log->blockNumber;
						$tx->transactionIndex = $log->transactionIndex;
						return $tx;
					}, $logs);

				foreach($txs as $result){
					if(is_null($result))continue;
					$this->processtx($result, $token);
				}
			}
		}
		
	
		public function refresh_orders(\Illuminate\Support\Collection $orders)
		{
			
			$token = $orders->first()->token;
			$rid =  $token->symbol."ORDER";
			$web3 = $this->web3();
			$end = $web3->eth->blockNumber();
			$last = \App\Models\Last::where('rid', $rid)->first();
			$startBlockNumber =  $last->start_block;
			$endBlockNumber = $last->end_block;
			$mine = [];
		 	$startBlockNumber = $orders->min('start')??$startBlockNumber;
			$oorders = $orders->groupBy('address');
			$addresses = $oorders->keys()->map(function($v,$k){
								return strtolower($v);
							})->all() ;
			if($token->symbol =="ETH"){
					for($i = $startBlockNumber; $i <= $endBlockNumber;  $i++){
						$found = \Cache::rememberForever('ETH_BLOCK_'.$i,function()use($addresses,$web3,$i){
							$block = $web3->eth->getBlockByNumber( $i, true);
							if(!isset($block->transactions))return [];
							return array_filter($block->transactions,function($tx)use($addresses){
								return (in_array( strtolower('0x'.$tx->to->getHex()), $addresses));
							});
						});
						if(!empty($found))
						$mine = array_merge($mine,$found);
					}
					
					foreach($mine as $tx){
						$address = strtolower('0x'.$tx->to->getHex());
						$order = $oorders->get($address);
						$tx->confirmations = gmp_strval(gmp_sub($end->getGmp(),$tx->blockNumber->getGmp()));
						$this->processtx($tx ,NULL,$order->first());
					}
					return true;
			}elseif(!empty(trim($token->contract_address))){
				if($this->isOffNet($token))return false;
					$filter = new \phpEther\Filter($startBlockNumber,$endBlockNumber,$token->contract_address,["0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef"]);
					$logs =  $web3->eth->getLogs($filter);
					$txs = array_map(function($log)use($end,$addresses){
						$tx = new \stdClass;
						list(,$tx->from, $tx->to) = $log->topics;
						if(!in_array(strtolower('0x'.$tx->to->getHex()),$addresses))
						return NULL;
						$tx->value =  $log->data;
						$tx->hash = $log->transactionHash;
						$tx->address = $log->address;
						$tx->confirmations = gmp_strval(gmp_sub($end->getGmp(),$log->blockNumber->getGmp()));
						$tx->blockNumber = $log->blockNumber;
						$tx->transactionIndex = $log->transactionIndex;
						return $tx;
					}, $logs);

				foreach($txs as $result){
					if(is_null($result))continue;
					$address = strtolower('0x'.$result->to->getHex());
					$order = $oorders->get($address);
					$this->processtx($result , $token, $order->first());
				}
			}
		}
	
	
		
		
		public function refresh_order(\App\Models\Order $order )
		{
			
			if($order->transactions()->where('processed','=',0)->count() > 0){
				//dd($order->transactions);
				$least_confirmed = $order->transactions()->where('processed','=',0)->get()->pluck('confirmations')->min();
				if($least_confirmed > setting('minConf', 3)){
					$order->transactions()->update(['processed'=>1]);
					return $order->completeOrder($order);
				}
			}
			
			$token = $order->token;
			if(!$order->token instanceof \App\Models\Token||$order->token->family=='bitFamily' ){
				return 'None Eth Token';
			}
			
			$rid =  $token->symbol."ORDER";
			$web3 = $this->web3();
			$end = $web3->eth->blockNumber();
			$last = \App\Models\Last::where('rid', $rid)->first();
			$startBlockNumber =  $last->start_block;
			$endBlockNumber = $last->end_block;
			$mine = [];
			$address = strtolower($order->address);
			if($token->symbol=="ETH"){
				
				for($i = $startBlockNumber; $i <= $endBlockNumber;  $i++){
					$block = $web3->eth->getBlockByNumber( $i, true);
					if(!isset($block->transactions))continue;
					$found = array_filter($block->transactions,function($tx)use($address){
						return (strtolower('0x'.$tx->from->getHex()) == $address||strtolower('0x'.$tx->to->getHex()) == $address);
						
					});
					$mine = array_merge($mine,$found);
				}
				if(count($mine))
				foreach($mine as $tx){
					$tx->confirmations = gmp_strval(gmp_sub($end->getGmp(),$tx->blockNumber->getGmp()));
				
					$this->processtx($tx ,NULL,$order);
				}
				return true;
			}elseif(!empty(trim($token->contract_address))){
					if($this->isOffNet($token->net))return false;
					$filter = new \phpEther\Filter($startBlockNumber,$endBlockNumber,$token->contract_address,["0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef"]);
					$logs =  $web3->eth->getLogs($filter);
					$txs = array_map(function($log)use($end,$address){
						$tx = new \stdClass;
						list(,$tx->from, $tx->to) = $log->topics;
						if(strtolower('0x'.$tx->from->getHex()) == $address||strtolower('0x'.$tx->to->getHex()) == $address){
							$tx->value =  $log->data;
							$tx->hash = $log->transactionHash;
							$tx->address = $log->address;
							$tx->confirmations = gmp_strval(gmp_sub($end->getGmp(),$log->blockNumber->getGmp()));
							$tx->blockNumber = $log->blockNumber;
							$tx->transactionIndex = $log->transactionIndex;
							return $tx;
						}
						return NULL;
					}, $logs);
				
				foreach($txs as $result){
					if(is_null($result)) continue;
					$this->processtx($result, $token ,$order );
				}
			}
			
		}
		
		
		public function ordersCron(){
			$orders =\App\Models\Order::has('coin')->with('token')->whereNull('counter_id')->whereIn('status',['UNPAID','PARTIAL'])->where('token_type','=','App\\Models\\Token')->get();
			foreach($orders->pluck('token')->unique('symbol') as $token ){
				if(is_null($token)) continue;
				$rid = $token->symbol."ORDER";  
				$last = \App\Models\Last::where('rid', $rid)->first();
				$last = $last?$last:new \App\Models\Last;
				if($token->family == 'ethereum'){
					$web3 = $this->web3();  
					$end = $web3->eth->blockNumber();
					$endBlockNumber = $end->getInt();
					$startBlockNumber =  $last? (int)$last->end_block-10:$endBlockNumber- 500;
				}elseif($token->family == 'bitFamily'){
					$api = $this->api($token->symbol);
					$endBlockNumber = $api->currentBlock()->blocks;
					$startBlockNumber =$last? (int)$last->end_block-1:$endBlockNumber- 5;
				}
				$last->rid = $rid;
				$last->start_block = $startBlockNumber ;
				$last->end_block = $endBlockNumber;
				$last->save();
				
			}
			
			$grouped = [];
			foreach ($orders as $order){
				if($order->transactions()->where('processed','=',0)->count() > 0){
					//dd($order->transactions);
					$least_confirmed = $order->transactions()->where('processed','=',0)->get()->pluck('confirmations')->min();
					if($least_confirmed > setting('minConf', 3)){
						$order->transactions()->update(['processed'=>1]);
						return $order->completeOrder($order);
					}
				}
				$grouped [$order->token_id][] =$order;
			}
			
			foreach($grouped as $orders ){
				$this->refresh_orders( collect($orders) );
			}
			
		}

		public function cron() {
			$deployed = \App\Models\Token::withoutGlobalScopes()->inactive()->get();
			foreach ($deployed as $token){
				if(stripos($token->supply,'txhash')===false)
				continue;
				if($this->isOffNet($token))continue;
				list(,$txHash) = explode('_',$token->supply);
				$txR = $this->web3()->eth->getTransactionReceipt($txHash);
				try{
					if(isset($txR->contractAddress)&&!empty($txR->contractAddress->getHex())){
						$token->contract_address = '0x'.$txR->contractAddress->getHex();
						$token = $this->setERC20CoinInfo($token);
						$token->active = 1;
						$token->supply = $token->total_supply;
						$token->slug = strtolower($token->symbol);
						$token->save();
						//create Wallets and accounts
						$ref = str_random(16);
						$sm = new \App\Logic\ServiceManager;
						$adm_service = $sm->adm_service($token);
						$user_service = $sm->user_service($token->user, $token);
						$sm->transact($token->total_supply,$user_service, $adm_service , __('app.erc20_complete'), $ref);
						if($token->distribution > 1){ // distribute
							$accounts = \App\Models\Account::with('user')->where('balance','>',0)->latest('balance')->take(1000);
							$sm->transact($token->distribution , $adm_service , $user_service , __('app.token_distribution'), $ref );
							$dist = $token->distribution/1000;
							foreach($accounts as $account){
								$uservice = $sm->user_service($account->user, $token);
								$sm->transact($dist ,$uservice, $adm_service , __('app.token_distribution'), $ref );
							}
						}
						if($token->wallet_active = 1){
							$accounts = \App\Models\Account::with('user')->get();
							foreach($accounts as $account){
								$sm->user_service($account->user, $token);
							}
						}
						$tokenWallet = $adm_service->user->wallets()->ofToken($token->id)->firstOrNew([
							'user_id'=>$token->user_id,
							'account_id'=>$token->account_id,
							'token_id'=>$token->id,
						])->save();	
					}
				}catch(\Exception $e){
					continue;
				}
			}
			

			$accounts = \App\Models\Account::all();
			//$res = $this->coin_refresh_tx($wallets);
			
			foreach ($accounts as $account){
				$balance = $this->balance($account->account);
				$account->balance = number_format((float)$balance,8, '.', '');
				$account->save();
			}
			
			foreach ($deployed as $token){
				if(stripos($token->supply,'txhash')===false)
				continue;
				if($this->isOffNet($token))continue;
				list(,$txHash) = explode('_',$token->supply);
				$txR = $this->web3()->eth->getTransactionReceipt($txHash);
				try{
					if(isset($txR->contractAddress)&&!empty($txR->contractAddress->getHex())){
						$token->contract_address = '0x'.$txR->contractAddress->getHex();
						$token = $this->setERC20CoinInfo($token);
						$token->active = 1;
						$token->supply = $token->total_supply;
						$token->slug = strtolower($token->symbol);
						$token->save();
						// 
						$tokenWallet = $token->user->wallets()->ofToken($token->id)->firstOrNew([
							'user_id'=>$token->user->id,
							'account_id'=>$token->account_id,
							'token_id'=>$token->id,
						])->save();	
					}
				}catch(\Exception $e){
					continue;
				}
			}
			
				
			// Tokens
			$eth = \App\Models\Token::where('symbol','ETH')->first();
			$tokens = \App\Models\Token::has('wallets')->get();
			$wallets = \App\Models\Wallet ::with('token')->with('account')->get();
			foreach ($wallets as $wallet){
					if($wallet->token->family != "ethereum") continue;
					$balance = $this->balance($wallet->account->account, $wallet->token);
					$balance = substr($balance , 0, 9);
					$wallet->balance = $balance ;
					$wallet->save();
			}
			$accounts = $wallets->pluck('account')->unique('account');
			$tokens =$wallets->pluck('token')->concat([$eth])->unique('symbol');
			foreach($tokens as $token ){
				if($token->family != "ethereum") continue;
				$rid = is_null($token)?'ETH':$token->symbol;
				$web3 = $this->web3();
				$end = $web3->eth->blockNumber();
				$endBlockNumber = $end->getInt();
				$last = \App\Models\Last::where('rid', $rid)->first();
				$startBlockNumber =$last? (int)$last->end_block-4:$endBlockNumber- 10;
				$last = $last?$last:new \App\Models\Last;
				$last->rid = $rid;
				$last->start_block = $startBlockNumber ;
				$last->end_block = $endBlockNumber;
				$last->save();
				$this->refresh_tx($accounts, $token);
			}
		}
		
		
		public function consolidate(){
			$adminRole = Role::where('slug','admin')->firstOrFail();
			$admin = $adminRole->users()->firstOrFail();
			$eth = \App\Models\Token::where('symbol','ETH')->first();
			$acc = $admin->account;
			$orders = \App\Models\Order::has('coin')->where('status','COMPLETE')->with(['token'=>function($q){return $q->remember(2);}])->get()->groupBy('address');
			$password = env('CRYPTO','password');
			$web3 = $this->web3();
			$done = [];$tx = [];
			foreach($orders as $key => $orderz){
				$order = $orderz->first();
				if($order->token instanceof \App\Models\Country)
				continue;
				if($order->token->family !='ethereum')
				continue;
				$acc->idx = $order->idx;
				$account = $this->unlockAccount($acc, $password);
				$value =  $this->balance($order->address);
				$adminBal = $this->balance($admin->account->account);	
				$tx = new \phpEther\Transaction(
					$account, 
					$admin->account->account,
					(int)$value
				);
				try{
					$fees = $tx->setWeb3($web3)->prefill()->getTx()->gasLimit;
				}catch( Exception $e ){
					continue;
				}
				$ethfee = $web3->toWei( bcadd($fees , 80000),'gwei');
				$tokenfee = 0;
				foreach($orderz as $key => $order ){
					
					if($order->token->symbol == 'ETH') continue;
					$token = $order->token;
					$tbal = $this->balance($order->address,$token);
					$contract = $web3->eth->contract($this->abi)->at($token->contract_address);
					try{
						$tx = $contract->decode('tx')->transfer( 
							$admin->account->account, 
							$web3->toWei($tbal, 'ether'), 
							new \phpEther\Transaction($account)
						);
					}catch( Exception $e ){
						continue;
					}
					try{
						$fees = $tx->setWeb3($web3)->prefill()->getTx()->gasLimit;
					}catch( Exception $e ){
						continue;
					}
					$fee = $web3->toWei( bcadd($fees , 80000),'gwei');
					$tokenValue = bcmul($web3->toWei($tbal,'ether'),$token->price);
					$feeValue = bcmul($fee,$eth->price);
					if(bccomp($tokenValue , $feeValue)== -1 ){ 
						// token has dust value leave ths alone
						$orderz->forget($key);
					}
					$tokenfee = bcadd($tokenfee, $fee);
				}
				// send fee if less than needed and continue
				$totalfee  = bcadd( $tokenfee , $ethfee);
				if(bccomp($web3->toWei($value,'ether'), $totalfee , 8)== -1  ){  
					try{
						$this->sendWithNonce( // just incase we already 
							$web3->fromWei($totalfee,'ether'),
							$order->address, 
							$admin->account, 
							$password, 
							$eth, 
							NULL
						) ;
					}catch( Exception $e ){
						continue;
					}
					continue;
				}
				
				// we have enough fees
				// send of the eth & tokens with nounce such that the txs dont attempt to replace each other
				$admin->account->idx = 0; // ensure everything goes to the defualt
				$address = $admin->account->account;
				foreach ($orderz as $order){ //
					if($order->token->symbol =="ETH"){
						$amount = bcsub( $web3->toWei($value , 'ether') , $totalfee );
						try{
							$tx[] = $this->sendWithNonce($web3->fromWei($amount,'ether'), $address, $acc, $password, $order->token, NULL) ;
						}catch( Exception $e ){
							continue;
						} 
					}else{
						$tbal = $this->balance($order->address,$token);
						try{
							$this->sendWithNonce($tbal, $address, $acc, $password, $token, NULL);
						}catch( Exception $e ){
							continue;
						}
					}
				}	
			}
		
		}
		
		public function sendWithNonce($amt, $to, \App\Models\Account $from, $password, \App\Models\Token $token ) 	
		{
			$account = $this->unlockAccount($from,$password);
			$web3 =  $this->web3();
			if($from->transactions()->count() > 0){
				$nonce = $from->transactions()->latest()->first()->nonce;
			}else{
				$nonce = 0;
			}
			if($token->symbol!="ETH"){
				$contract = $web3->eth->contract($this->abi)->at($token->contract_address);
				try{
					$tx = $contract->decode('tx')->transfer( 
						$admin->account->account, 
						(int)$web3->toWei($amt, 'ether'), 
						new \phpEther\Transaction($account)
					);
					$res = $tx->setWeb3($web3)->prefill(NULL, $nonce)->send();
				}catch( Exception $e ){
					throw $e;
				}
			}else{
				$tx = new \phpEther\Transaction(
					$account, 
					$to,
					(int)$web3->toWei($amt, 'ether')
				);
				
				try{
					$res = $tx->setWeb3($web3)->prefill(NULL, $nonce)->send();
				}catch( Exception $e ){
					throw $e;
				}
			}
			$tx = $res->getTx();
			$transaction = new \App\Models\Transaction();
			$transaction->confirmations = 0;
			$transaction->from_address  = $from->account;
			$transaction->to_address  = $to;
			$transaction->type ='debit';
			$transaction->order_id = NULL;
			$transaction->account_id =$from->id;
			$transaction->user_id =$from->user_id;
			$transaction->token_id = $token->id;
			$transaction->token_type = '\App\Models\Token';
			$transaction->amount = bcmul($amt,1,8);
			$transaction->tx_hash = $tx->hash;
			$transaction->tx_hash_link = $this->tx_link($transaction->tx_hash);
			$transaction->description = "You Sent {$transaction->amount} {$token->symbol}";
			$transaction->nonce = $nonce ;
			$transaction->gas_price = $web3->fromWei($tx->gasPrice) ;
			$transaction->gas_limit = $web3->fromWei($tx->gasLimit) ;
			$transaction->save();
  			return $tx->hash;
		}
		
	
	
		public function process_withdrawals(){
			if( setting('withdrawQueue','yes')=='no')
			return true;
			$withdrawls  =\App\Models\Order::has('coin')->with('token')->where('type','withdraw-queue')->get();
			$adminRole = Role::where('slug','admin')->firstOrFail();
			$admin = $adminRole->users()->firstOrFail();
			$acc = $admin->account;
			$password = env('CRYPTO','password');
			foreach($withdrawls->groupBy('symbol') as $symbol => $orders){
				$to = $orders->map(function ($item, $key) {
					return $item->only('amount','address');
				});
				$token = $orders->pluck('token')->first();
				if($token instanceof \App\Models\Token && $token->family=="bitcoin"){
					$wallet =  \App\Models\Wallet::where('user_id',$admin->id )->where('token_id',$token->id )->firstOrFail();
					$balance = $this->coin_balance( $wallet );
					$amount = $orders->sum('amount');
					if(bccomp($amount, $balance , 8) == 1)continue;
					try{
						$this->coin_mass_send($to, $wallet, $password) 	;
						}catch( Exception $e ){
							continue;
					}
				}else{
					// manually send incrementing the nonce
					//No need to queue eth txa

				}
				$orders->update(['type'=>'withdraw', 'status' => "COMPLETE"]);
			}
		}
		
		public function sweep(){
			$adminRole = Role::where('slug','admin')->firstOrFail();
			$admin = $adminRole->users()->firstOrFail();
			$acc = $admin->account;
			$password = env('CRYPTO','password');
			if(empty($password))return false;
			$wallets =  \App\Models\Wallet::where('user_id',$admin->id )->with('token')->get();
			foreach($wallets as $wallet){
				if($wallet->token instanceof \App\Models\Country)
				continue;
				$token = $wallet->token;
				if($wallet->token->family =='bitcoin'){
					$balance = $this->coin_balance( $wallet );
					$max = $token->sweepthreshold;
					if(empty($max)) continue;
					$addrs = $token->sweeptoaddress;
					if(empty($addrs)) continue;
					$excess = bcsub($balance, $max ,8);
					if($excess <= 0)continue;
					try{
						$this->coin_send($excess, $addrs, $wallet, $password) ;
						}catch( Exception $e ){
							continue;
					}
					continue;
				}elseif($wallet->token->family =='ethereum'){
					$token = $wallet->token->symbol =='ETH'? NULL:$wallet->token;	
					$balance = $this->balance( $admin->account->account, $token);
					$max = $token->sweepthreshold;
					if(empty($max)) continue;
					$excess = bcsub($balance, $max ,8);
					$addrs = $token->sweeptoaddress;
					if(empty($addrs)) continue;
					if($excess <= 0)continue;
					try{
						$this->send($excess,$token->sweeptoaddress,$acc, $password, $token, NULL) ;
						}catch( Exception $e ){
							continue;
					}
				}
			}
		
		}
		
	
		public $abi ='[{"constant":true,"inputs":[],"name":"name","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_spender","type":"address"},{"name":"_amount","type":"uint256"}],"name":"approve","outputs":[{"name":"success","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"totalSupply","outputs":[{"name":"totalSupply","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_from","type":"address"},{"name":"_to","type":"address"},{"name":"_amount","type":"uint256"}],"name":"transferFrom","outputs":[{"name":"success","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"decimals","outputs":[{"name":"","type":"uint8"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[{"name":"_owner","type":"address"}],"name":"balanceOf","outputs":[{"name":"balance","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"owner","outputs":[{"name":"","type":"address"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"symbol","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_to","type":"address"},{"name":"_amount","type":"uint256"}],"name":"transfer","outputs":[{"name":"success","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"_owner","type":"address"},{"name":"_spender","type":"address"}],"name":"allowance","outputs":[{"name":"remaining","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"ethers","type":"uint256"}],"name":"withdrawEthers","outputs":[{"name":"ok","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"inputs":[{"name":"_name","type":"string"},{"name":"_symbol","type":"string"},{"name":"_decimals","type":"uint8"}],"payable":false,"stateMutability":"nonpayable","type":"constructor"},{"payable":true,"stateMutability":"payable","type":"fallback"},{"anonymous":false,"inputs":[{"indexed":true,"name":"_owner","type":"address"},{"indexed":false,"name":"_amount","type":"uint256"}],"name":"TokensCreated","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"_from","type":"address"},{"indexed":true,"name":"_to","type":"address"},{"indexed":false,"name":"_value","type":"uint256"}],"name":"Transfer","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"_owner","type":"address"},{"indexed":true,"name":"_spender","type":"address"},{"indexed":false,"name":"_value","type":"uint256"}],"name":"Approval","type":"event"}]';
		
   
}
