<?php

namespace App\Traits;

use App\Models\User;
use App\Models\Io;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\Token;
use App\Models\Icosale;
use Illuminate\Support\Facades\Validator;
use Ixudra\Curl\Facades\Curl;
trait OrderTrait
{
	
    public function completeOrder(Order $order)
    {
		$order->load('transactions');
		if($order->status == 'COMPLETE')return true;
		if($order->status != 'PARTIAL'){
        	$transactions = $order->transactions()->where('type','credit')->get();
			$total = $transactions->sum('amount');
			if($order->collected < $order->amount && $order->amount > 0){
				$message.='<br>: Order Total Credit ('.$total.') is less than order amount ('.$order->amount.') Please send More '.($order->amount - $total).$order->token->symbol.'  <br><a href="'.route('order', $order->reference).'">'.route('order', $order->reference).'</a>';
				$order->logg .= $message;
				$order->save();
				return false;
			}
			$order->active = 1;
			$order->status = 'COMPLETE';
			$order->save();
		}else{
			if($order->item =='tokensale'){
				return $this->saleToken($order);
			}
		}
		
		if($order->item == 'market_deposit'){ // deposite
			if($order->token instanceof  Token){
				$transaction = $order->transactions()->latest()->first();
				$amount = $transaction->amount;
				$txid = $transaction->tx_hash;
				$order->txid  = $txid ; 
				$order->save();
				$ref = $order->reference;
			}elseif($order->token instanceof \App\Models\Country){
				$amount = $order->amount;
				$txid = $order->txid;
				$ref = $order->reference;
			}
			$oio = Io::where('txid','=', $txid)->first();
			if(!is_null($oio) && $oio->status =='complete' ){
				$order->logg .= 'Payment with Txid '.$txid.' Was Previously Processed.';
				$order->save();
				return $oio;
			}
			$deposit_fees = setting('deposit_fees');
			$fees_percent = 0.00;
			$fees = 0.00;
			$price = 0.00;
			$fees_value = 0.00;
			if(!empty($deposit_fees)){
				$fees_percent = bcdiv(floatval($deposit_fees),100,8);
				$fees = bcmul($fees_percent,$amount,8);
				$price = bccomp($order->token->price,0,8)==1?$order->token->price:1;
				$fees_value = bcmul($fees,$price,8);
				$amount = bcsub($amount,$fees,8);
				$order->fees = $fees;
				$order->save();
			}
			$to = $order->service;
			$io = $oio??new Io;
			$io->txid = $txid;
			$io->fees_percent = $deposit_fees;
			$io->fees = $fees;
			$io->fees_value = $fees_value;
			$io->reference = $ref;
			$io->service_id = $to->id;
			$io->user_id = $order->user_id;
			$io->token_id = $order->token_id;
			$io->token_type = $order->token_type;
			$io->order_id = $order->id;
			$io->type = 'deposit';
			$io->status = 'pending';
			$io->message = now().': New Deposit Transaction';
			$io->amount = $amount;
			$io->symbol = $order->symbol;
			$io->save();
			$sm = new \App\Logic\ServiceManager;
			$from = $sm->adm_service($order->token);
			if(!is_null($to)&&!is_null($from)){
				try{
					list($from,$to) = $sm->transact($amount, $to , $from, $message ="Market Deposit of $amount {$order->symbol} REF: ".$ref,  $ref, true );
				}catch(Exception $e){
					$io->message.= now().': '.$e->getMessage();
					$io->save();
					return false;
				}
			}

			$io->service_tx_id = $to->id; //txto id
			$io->status ="complete";
			$io->message = now().': New Deposit Transaction Completed Successfully';
			$io->save();
			$order->logg .= $io->message;
			if(!$order->token instanceof \App\Models\Token ||  $order->token->family !='ethereum' ){
				$order->status = 'COMPLETE';
			}
			$order->save();
			return $io;
  		}
		
		if($order->item =='api'){
			 $response = Curl::to($order->item_url)
				->withData( $order->toArray() )
				->asJson()
				->post();
			return true;
		}
		if($order->item =='exchange'&&$order->type =='collect'){
			return $this->pay($order->counter);
		}
		return true;
    }
	
	public function saleToken($adm_order){
		$tx = Transaction::find($adm_order->item_id);
		$order = $adm_order->counter;
		$token = $order->token;
		$user = $order->user; // admin
		$account = $order->account; // user
		$amount = $tx->amount * $order->amount  ;
		$tkns = $amount;
		$buytype = false;
		$sold = new Icosale;
		$sold->token_id=$tx->token_id;
		$sold->account_id = $user->account->id;
		$sold->amount= $tkns;
		$sold->ether = $tx->amount;
		$sold->user_id = $account->user->id;
		$sold->symbol = $tx->token->symbol;
			if($token->contract->buy_tokens_function == 'transfer'){
				try{
				   $tx_hash = $this->send(
						$tkns, 
						$account->account, 
						$token->account, 
						$token->ico_pass, 
						$token
				 ) ;
				}catch(\Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $e){
					$message = $e->getMessage();
					$message.=': INVALID PASSWORD';
					$tx->message.='<br> Error: Token Delivery Failed. Send Tx Failed , '.$message;
					$tx->save();
					return false;
				}catch(\Exception $e){
					$tx->message.='<br> Error: Token Delivery Failed. Send Tx Failed, '.$e->getMessage();
					$tx->save();
					return false;
				}
				$sold->token_txhash = $tx_hash;
				$sold->save();
				$tx->message.='<br> SUCCESS: Token Purchase successfull. Tokens Sent! Tx: '.$tx_hash;
				$tx->save();
				return true;
			}else{ 
				try{
					$type = empty($token->contract->contract->mainsale_abi)?'abi':'mainsale_abi';
					$result = $this->resolve($type, $token, $token->contract->buy_tokens_function, $this->reconstruct($token->contract,['uint'=>$amount,'address'=>$order->account->account],'type'), $token->ico_pass, 0, $token->account );
				}catch(\Exception $e){
					$tx->message.='<br> Error: Token Delivery Failed. Send Tx Failed, '.$e->getMessage();
					$tx->save();
					return false;
				}
				$sold->token_txhash = $result;
				$tx->message.='<br> SUCCESS: Call contract Function: '.$token->contract->buy_tokens_function.' Tx: '. $result;
				$tx->save();
				$sold->save();
				return true;
			}
	}
	
	public function buyInputs($contract,$construct =[], $type ="key"){
		$function = $token->contract->buy_tokens_function;
		$data= empty($contract->mainsale_abi)?json_decode($contract->abi):json_decode($contract->mainsale_abi);
		foreach($data as $abi){
			if($abi->type == 'function'&& $abi->name == $function){ 
				if(count($construct) != count($inputs)) return [];
				$return =[];
				foreach($inputs as $k => $input){
					if($type == 'order'){
						if(isset($construct [$k]));
						$return[$input->name ] = $construct [$k];
					}
					if($type == 'key'){
						if(isset($construct [$input->name]))
						$return[$input->name ] = $construct [$input->name];
					}
					if($type == 'type'){
						if(isset($construct [$input->type]))
						$return[$input->name ] = $construct [$input->type];
					}
				}
			}
		}
	}

	public function pay($order){
		return resolve($order->gateway)->boot($order)->payout();
	}
	
	public function collectPayment($order){
		return  resolve($order->gateway)->boot($order)->collect();
	}
	
	public function completeIo(Io $io){
		if($io->type == 'deposit'&&$io->status != 'complete'){
			$amount = $io->amount;
			$to = $io->service;
			$ref = $io->reference;
			$sm = new \App\Logic\ServiceManager;
			$from = $sm->adm_service($io->token);
			if(!is_null($to)&&!is_null($from)){
				try{
					$resp = $sm->transact($amount, $to , $from, $message ="Market Deposit of $amount REF: ".$io->reference ,  $ref, false );
				}catch(Exception $e){
					$io->message.= now().': '.$e->getMessage();
					$io->save();
					return false;
				}
			}

			$io->service_tx_id = $resp[0]; //txto id
			$io->status ="complete";
			$io->message = now().': Deposit Completed';
			$io->save();
			return $io;
		}
		if($io->type == 'withdraw'&&$io->status != 'complete'){
			$order = $io->order;
 			try{
				$resp = $this->pay($order);
			}catch(Exception $e){
				$io->message.= now().': '.$e->getMessage();
				$io->save();
				return false;
			}
			$io->txid = $resp; //txto id
			$io->status ="complete";
			$io->message = now().': Withdraw Completed';
			$io->save();
			return $io;
		}
	}
    
}

		
		
	
