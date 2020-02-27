<?php
namespace App\Traits;
use App\Logic\Activation\ActivationRepository;
use App\Models\User;
use App\Models\Account;
use Illuminate\Database\Eloquent\Collection;

trait BitcoinTrait
{
		public function api ($coin){
			if($coin instanceof \App\Models\Token){
				$crypto = '\\App\\Logic\\Crypto\\'.$coin->symbol;
			
				if(!class_exists($crypto)&&(!isset($coin->chain_params)||is_null($coin->chain_params))){
					throw new \Exception('Unsupported Coin');
				}

				if(class_exists($crypto))
				   return new\ofumbi\Api(new $crypto());
				if(isset($coin->chain_params)&&!is_null($coin->chain_params)){
					$url = $coin->chain_params->rpc_url ;
					$username = $coin->chain_params->rpc_username;
					$password = $coin->chain_params->rpc_password;
					$chain = new ofumbi\Api\Multichain($url,$username,$password);
					$chain->addressByte = $coin->chain_params->p2pkh_bypte;
					$chain->p2shByte=$coin->chain_params->p2sh_byte;
					$chain->privByte =$coin->chain_params->priv_byte;
					$chain->hdPubByte =$coin->chain_params->hd_pub_byte;
					$chain->hdPrivByte = $coin->chain_params->hd_priv_byte;
					$chain->p2pMagic = $coin->chain_params->p2p_magic;
					$chain->SegwitBech32Prefix = NULL;
					$chain->signedMessagePrefix = $coin->chain_params->signed_message_prefix;
					$chain->bip44index = $coin->chain_params->bip44index;
					return $chain;
				}
	
			}
			$crypto = '\\App\\Logic\\Crypto\\'.$coin;
				
			if (!class_exists($crypto)){
				dd($crypto);
				throw new \Exception('Unsupported Coin'.$crypto);
			}
				
			return new\ofumbi\Api(new $crypto());	
		}
	
	
		public function coin_tx_link($tx_id,$symbol){
			$symbol = $symbol instanceof \App\Models\Token?strtolower($symbol->symbol): strtolower($symbol);
			if(in_array($symbol,['btc','ltc','dash','doge']))
			return 'https://live.blockcypher.com/'.$symbol.'/tx/'.$tx_id;
			if(in_array($symbol,['bch','zec','btg','btgtestnet']))
			return 'https://'.$symbol.'-bitcore3.trezor.io/tx/'.$tx_id;
			if( $symbol == 'btgtestnet' )
			return 'https://test-explorer.bitcoingold.org/insight/tx/'.$tx_id;
			if( $symbol == 'btctestnet' )
			return 'https://test-insight.bitpay.com/tx/'.$tx_id;
			if( $symbol == 'bchtestnet' )
			return 'https://test-bch-insight.bitpay.com/tx/'.$tx_id;
			if( $symbol == 'ctg' ){
				$expl = env('CTG_EXPLORER','http://68.183.131.196:7897');
				return $expl.'/'.$symbol.'/tx/'.$tx_id;
			}
		}
		
		
		public function coin_address_link($address, $symbol = NULL){
			if($address instanceof \App\Models\Address){
				$symbol =  $address->token->symbol;
				$address  = $address->address;
			}
			$symbol = strtolower($symbol);
			if(in_array($symbol,['btc','ltc','dash','doge']))
			return 'https://live.blockcypher.com/'.$symbol.'/address/'.$address;
			if(in_array($symbol,['bch','zec','btg']))
			return 'https://'.$symbol.'-bitcore3.trezor.io/address/'.$address;
			if( $symbol == 'btgtestnet' )
			return 'https://test-explorer.bitcoingold.org/insight/address/'.$address;
			if( $symbol == 'btctestnet' )
			return 'https://test-insight.bitpay.com/address/'.$address;
			if( $symbol == 'bchtestnet' )
			return 'https://test-bch-insight.bitpay.com/address/'.$address;
			if( $symbol == 'ctg' ){
				$expl = env('CTG_EXPLORER','http://18.191.160.247:7667');
				return $expl.'/address/'.$address;
			}
		}
	
	
		//return multisig
		public function coin_unlockWallet(\App\Models\Wallet $wallet,$password){
			$api = $this->api($wallet->token->symbol);
			$symbol = strtolower($wallet->token->symbol);
			$protected_key =\Defuse\Crypto\KeyProtectedByPassword::loadFromAsciiSafeString($wallet->account->cypher);
		    $key  = $protected_key->unlockKey($password);
			$wallet_xpriv = \Defuse\Crypto\Crypto::decrypt($wallet->xpriv,$key);
			//app
			$apppriv = $this->getAppPrivateKey($wallet->token->symbol)->getXpriv();
			$hd = new \ofumbi\HD($api);
			$hd1 = $hd->privateSeed($wallet_xpriv);
			$hd = new \ofumbi\HD($api);
			$hd2 = $hd->privateSeed($apppriv);
			$hd = new \ofumbi\HD($api);
			$hd3 = $hd->publicSeed($wallet->cold_key);
			$return = array();
			foreach($wallet->addresses as $address){
				$multisig = new \ofumbi\Multisig($hd1,$hd2,$hd3);
				$sig = $multisig->at($address->idx);
				assert($address->address == $sig->address);
				$return[]= $sig->getAddress(true);
			}
			return collect($return);
		}
		
	
		public function coin_deriveAddress(\App\Models\Wallet $wallet , $index =false , $change =false){
			$token = $wallet->token;
			if($token->family !='bitFamily') throw new \Exception('Invalid Coin. Only BTC derivatives!!');
			$api = $this->api($token->symbol);
			$apppub =  $this->getAppPrivateKey($token->symbol)->getXpub();
			$hd = new \ofumbi\HD($api);
			$hd1 = $hd->publicSeed($wallet->xpub);
			$hdx = new \ofumbi\HD($api);
			$hd2 = $hdx->publicSeed($apppub);
			$hdy = new \ofumbi\HD($api);
			$hd3 = $hdy->publicSeed($wallet->cold_key);
			$type = $change?'change':'external';
			$index = $index?$index:$wallet->addresses()->where('type', $type)->count()+1;
			$index = $change?'1/'.$index:$index;
			$multisig = new \ofumbi\Multisig($hd1,$hd2,$hd3);
			$address = new \App\Models\Address;
			$address->address = $multisig->get($index)->address;
			$address->idx =  $index ;
			$address->type =  $type ;
			$address->wallet_id =  $wallet->id ;
			$address->account_id = $wallet->account_id;
			$address->user_id = $wallet->user_id;
			$address->token_id = $wallet->token_id;
			$address->symbol = $wallet->token->symbol;
			$address->address_link = $this->coin_address_link($address);
			$address->save();
			$api->importaddress($address->address,null,true);
			return $address;
		}
		
		// make a private key for each netowrk
		public function makeColdKey(){
			$ret = new \stdClass;
			$api = $this->api('BTC');
			$hd = new \ofumbi\HD($api);
			$hd1 = $hd->randomSeed();
			$ret->HD = $hd1;
			$ret->btc = $hd1;
			$ret->ltc =  $this->convertMasterKey($hd1,'LTC');
			$ret->btcTestnet = $this->convertMasterKey($hd1,'BTCTESTNET');
			$ret->bchTestnet = $this->convertMasterKey($hd1,'BCHTESTNET');
			$ret->btgTestnet = $this->convertMasterKey($hd1,'BTGTESTNET');
			$ret->dash = $this->convertMasterKey($hd1,'DASH');
			$ret->zec = $hd1;
			$ret->btg = $this->convertMasterKey($hd1,'BTG');
			$ret->bch = $this->convertMasterKey($hd1,'BCH');
			$ret->ctg = $this->convertMasterKey($hd1,'CTG');
			return $ret;
		}
		
		public function getAppPrivateKey($coin){
			$priv = setting('apppriv','');
			$api = $this->api('BTC');
			$hd = new \ofumbi\HD($api);
			if(empty($priv)){
				$hd1 = $hd->randomSeed();
 				$pass = str_random(30);
				\Setting::set('applock', \Crypt::encryptString($pass)); 
				$locked_key = \Defuse\Crypto\KeyProtectedByPassword::createRandomPasswordProtectedKey($pass);
				$locked_key_encoded = $locked_key->saveToAsciiSafeString();// now in db
				\Setting::set('appcypher',$locked_key_encoded);
				$protected_key = \Defuse\Crypto\KeyProtectedByPassword::loadFromAsciiSafeString($locked_key_encoded);
				$key = $protected_key->unlockKey($pass);
				$xpriv = $hd1->getMasterXpriv();
				\Setting::set('apppriv', \Defuse\Crypto\Crypto::encrypt($xpriv, $key));
				\Setting::save();
			}else{
				$appcypher = setting('appcypher');
				$pass = \Crypt::decryptString(setting('applock'));
				$protected_key = \Defuse\Crypto\KeyProtectedByPassword::loadFromAsciiSafeString($appcypher);
				$key = $protected_key->unlockKey($pass);
				$xpriv = \Defuse\Crypto\Crypto::decrypt($priv, $key);
			}
			
			$btc =  $hd->masterSeed($xpriv);
			if($coin == "BTC")return $btc ;
			return $this->convertMasterKey($btc,$coin); 
		}
		
		
		
		public function convertMasterKey( \ofumbi\HD $master, $to){
			$api = $this->api($to);
			$key = $master->getMasterXprivKey()->toExtendedPrivateKey($api->network);
			$HD = new \ofumbi\HD($api);
			return $HD->masterSeed($key);
		}
		
		public function coin_create_wallet( \App\Models\Account $account , $password ,  \App\Models\Token $token  ){
			if($token->family !='bitFamily') throw new \Exception('Invalid Coin. Only BTC derivatives!!');
			$protected_key =\Defuse\Crypto\KeyProtectedByPassword::loadFromAsciiSafeString($account->cypher);
			$key  = $protected_key->unlockKey($password);
			$bitcoinKey = \Defuse\Crypto\Crypto::decrypt($account->xpriv,$key);
			$api = $this->api('BTC');
			$hd = new \ofumbi\HD($api);
			$coinKey = $hd->masterSeed($bitcoinKey);
			if($token->symbol!=="BTC")
			$coinKey = $this->convertMasterKey($coinKey , $token->symbol);
			$wallet  = new \App\Models\Wallet ;
			$wallet->xpriv = \Defuse\Crypto\Crypto::encrypt($coinKey->getXpriv(), $key);
			$wallet->xpub = $coinKey->getXpub();
			$wallet->cold_key = setting($token->symbol.'_coldKey');
			$wallet->user_id = $account->user->id;
			$wallet->account_id = $account->id;
			$wallet->token_id = $token->id;
			$wallet->save();
			return $wallet;
		}
		
	  public function coin_balance($wallet , $rescan = false){
		   if($wallet->token->family !='bitFamily') throw new \Exception('Invalid Coin. Only BTC derivatives!!');
		   $api = $this->api($wallet->token->symbol);
		   $addresses = $rescan?$wallet->all_addresses:$wallet->addresses;
		   $check = $addresses->pluck('address')->all();
		   $minconf = setting('minConf');
		   $balance = $api->getBalance($check, $minconf??3);
		   return $balance;
		}
	 
	 
		
		public function coin_send($amt, $toaddress, \App\Models\Wallet $wallet, $password, $order_id = NULL , $fees ="high") 	
		{
			$token = $wallet->token;
			$balance = $this->coin_balance($wallet); 
			if(bccomp($amt , $balance , 8) == 1)
				throw new \Exception('Low Balance: ('.$balance.$token->symbol.') '.$amt.$token->symbol.'Required' );
			$from = $this->coin_unlockWallet($wallet,$password);
			$api =  $this->api($wallet->token->symbol);
			$to = collect([['amount'=>$api->toSatoshi($amt),'address'=>$toaddress]]);
			$changeAddress = $this->coin_deriveAddress($wallet,NULL,true);
			if( in_array($wallet->token->symbol,["BCH","BCHTESNET","BTG","BTGTESTNET"]))
			$trans = new \ofumbi\BitcoinCashTx($to, $from, $api, $changeAddress->address, $fees);
			else
			$trans= new \ofumbi\BitcoinTx($to, $from, $api, $changeAddress->address, $fees);
			$tx = $trans->send();
			$transaction = new \App\Models\Transaction();
			$transaction->confirmations = 0;
			$transaction->from_address  = $tx->source[0];
			$transaction->to_address  = $toaddress;
			$transaction->type ='debit';
			$transaction->order_id = $order_id;
			$transaction->account_id =$wallet->account->id;
			$transaction->user_id = $wallet->user_id;
			$transaction->wallet_id = $wallet->id;
			$transaction->token_id = $wallet->token->id;
			$transaction->token_type = "\\App\\Models\\Token";
			$transaction->amount = $amt;
			$transaction->tx_hash = $tx->txHash;
			$transaction->description = "You Sent {$amt} {$wallet->token->symbol}";
			$transaction->nonce = NULL ;
			$transaction->gas_price = NULL ;
			$transaction->gas_limit =NULL ;
			$transaction->save();
  			return $tx->txHash ;
		}
		
		
		
		public function coin_mass_send(Collection $to, \App\Models\Wallet $wallet, $password, $order_id = NULL , $fees ="high") 	
		{

			$from = $this->coin_unlockWallet($wallet,$password);
			$api =  $this->api($wallet->token->symbol);
			//$to = collect([['amount'=>$api->toSatoshi($amt),'address'=>$toaddress]]);
			$changeAddress = $this->coin_deriveAddress($wallet,NULL,true);
			if( in_array($wallet->token->symbol,["BCH","BCHTESNET","BTG","BTGTESTNET"]))
			$trans = new \ofumbi\BitcoinCashTx($to, $from, $api, $changeAddress->address, $fees);
			else
			$trans= new \ofumbi\BitcoinTx($to, $from, $api, $changeAddress->address, $fees);
			$tx = $trans->send();
			$transaction = new \App\Models\Transaction();
			$transaction->confirmations = 0;
			$transaction->from_address  = $tx->source[0];
			$transaction->to_address  = $toaddress;
			$transaction->type ='debit';
			$transaction->order_id = $order_id;
			$transaction->account_id =$wallet->account->id;
			$transaction->user_id = $wallet->user_id;
			$transaction->wallet_id = $wallet->id;
			$transaction->token_id = $wallet->token->id;
			$transaction->token_type = "\\App\\Models\\Token";
			$transaction->amount = $amt;
			$transaction->tx_hash = $tx->txHash;
			$transaction->description = "You Sent {$amt} {$wallet->token->symbol}";
			$transaction->nonce = NULL ;
			$transaction->gas_price = NULL ;
			$transaction->gas_limit =NULL ;
			$transaction->save();
  			return $tx->txHash ;
		}
		
		public function coin_refresh_hash($hash , \App\Models\Token $token =NULL){
			
		}
		
		
		public function coin_processtx($tx , \App\Models\Token $token =NULL){
			
			\DB::table('transactions')
            	->where('tx_hash', $tx->hash)
            	->update(['confirmations'=>$tx->confirmations]);
			$event = \App\Models\Transaction::whereIn('type',['credit','change'])->where('tx_hash',$tx->hash)->get();

			if($event->count() < 1){  
				$done = \App\Models\Transaction::where('tx_hash',$tx->hash)->get();
				$to = $tx->address;
				$to->active = 0;
				$to->save();
				$symbol = $token->symbol;
				$order = \App\Models\Order::where('address',$tx->to)->where('symbol',$token->symbol)->first();
				$wallet = \App\Models\Wallet::where('token_id',$token->id)->where('user_id',$to->user_id)->first();
			    $transaction = new \App\Models\Transaction();
				$transaction->confirmations = isset($tx->confirmations)?$tx->confirmations: 5;
				$transaction->from_address  =  $tx->from ;
				$transaction->to_address  = $tx->to;
				$transaction->blockheight  = $tx->blockheight;
				$transaction->type =$to->type=='change'?'change':'credit';
				$transaction->account_id = $to->account_id;
				$transaction->user_id =$to->user_id;
				$transaction->tx_hash_link = $this->coin_tx_link($tx->hash, $symbol);
				$transaction->wallet_id =$to->wallet_id;
				$transaction->token_id = $token->id;
				$transaction->token_type = "\\App\\Models\\Token";
				$transaction->amount = $tx->amount;
				$transaction->tx_hash = $tx->hash;
				$transaction->description = "You Recieved {$tx->amount}{$token->symbol}";
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
		 
		 
		public function coin_refresh_tx(\Illuminate\Support\Collection $Wallets, \App\Models\Token $token )
		{
			set_time_limit(0);
			foreach( $Wallets as $wallet ){
				$api = $this->api($token->symbol);
				$last = \App\Models\Last::where('rid', $token->symbol)->first();
				$startBlockNumber = $last->start_block ;
				$endBlockNumber = $last->end_block ;
				$blocks = [];
				for($i = $startBlockNumber ; $i == $endBlockNumber  ; $i++){
					$blocks[] = $i;
				}
				$addresses = $wallet->addresses()->get();
				$txs = $api->addressTx($addresses, $blocks);
				foreach($txs as $tx){
					$this->coin_processtx($tx , $token);
				}
				
			}
			
		}
		
		
		public function coin_refresh_order( $orders )
		{
			
			$token = $orders->first()->token;
			$rid =  $token->symbol."ORDER";
			$api = $this->api($token->symbol);
			$last = \App\Models\Last::where('rid', $rid)->first();
			$startBlockNumber =  $last->start_block;
			$endBlockNumber = $last->end_block;
			$start = $orders->min('start');
			if(is_null($start )){
				$orders->update(['start'=>$endBlockNumber]);
			}
			$blocks =[];
			for($i = $start??$startBlockNumber ; $i == $endBlockNumber ; $i++){
				$blocks[] = $i;
			}
		 	//$addresses = collect([$order]);
			$txs = $api->addressTx($orders, $blocks);
			foreach($txs as $tx){
				$this->coin_processtx($tx , $token);
			}
		}
		
		/*
		*ltc 3 mins
		*zec 3 mins
		*btc 10 mins
		*bch 10 mins
		*btg 10 mins
		*dash 3 mins
		*/
		public function coin_cron($sym) {
		
			$token = \App\Models\Token::where('family','bitFamily')->where('symbol',$sym)->first();
			$wallets = $token->wallets()->get();
			$orders = $token->orders()->whereNull('counter_id')->whereIn('status',['UNPAID','PARTIAL'])->where('token_type','=','App\\Models\\Token')->where('expires_at','>',\Carbon\Carbon::now())->get();
			$rid =$token->symbol;
			$last = \App\Models\Last::where('rid', $rid)->first();
			$last = $last?$last:new \App\Models\Last;
			if($token->family == 'ethereum') return false;
				
			$api = $this->api($token->symbol);
			
			$endBlockNumber = $api->currentBlock()->blocks;
			$startBlockNumber =$last? (int)$last->end_block-1:$endBlockNumber- 5;
			$last->rid = $rid;
			$last->start_block = $startBlockNumber ;
			$last->end_block = $endBlockNumber;
			$last->save();
			$this->coin_refresh_order( $orders );
			/*foreach($orders as $order ){
				if($order->transactions()->where('processed','=',0)->count() > 0){
				$least_confirmed = $order->transactions()->where('processed','=',0)->get()->pluck('confirmations')->min();
				if($least_confirmed > setting('minConf', 3)){
					$order->transactions()->update(['processed'=>1]);
					if($order->completeOrder($order))return;
				}
			}
				$response = $this->coin_refresh_order( $order);
			}*/
			
			
			$res = $this->coin_refresh_tx($wallets,$token);
		
			foreach ($wallets as $wallet){
				$balance = $this->coin_balance($wallet);
				$wallet->balance = number_format((float)$balance,8);
				$wallet->save();
			}
		
		
			
		}
		
		
   
}
