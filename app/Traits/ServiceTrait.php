<?php

/**
 * Created by ofumbis.
 * User: ofuzak@gmail.com
 */
namespace App\Traits;


use App\Models\Service;
use App\Models\Service_tx;
use App\Models\Token;
use App\Models\User;
use App\Notifications\NewService_tx;
use jeremykenedy\LaravelRoles\Models\Role;
trait ServiceTrait
{

	/*
	*Get A users Service Account
	*/
	
	public function user_service(User $user, $base){
		if($base instanceof \App\Models\Token ){
			
			$token = $base;
			$adminRole = Role::where('slug','admin')->firstOrFail();
			$admin = $adminRole->users()->firstOrFail();
			$account = $admin->account;
			
			$blockchain = new \App\Logic\Gateways\Blockchain(NULL);
			$wallet = $admin->wallets()->where('token_id',$token->id)->first(); // only tokens 
			if(is_null($wallet)){ // admin has not added this wallet yet. we generate one
				if($token->family=='bitFamily'){
					$wallet = $blockchain->coin_create_wallet(  $account , env('CRYPTO','password') , $token);
				}else{
					$wallet = \App\Models\Wallet::create([
						'token_id'=>$token->id,
						'user_id'=>$admin->id,
						'account_id'=>$account->id,
						'token_type'=>$token->type,
					]);
				}
			} 
		}elseif($base instanceof \App\Models\Wallet ){
			$wallet = $base;
			$token = $wallet->token;
		}
		
				
		$service = Service::firstOrCreate(
			[
					'token_id'=>$token->id,
					'user_id'=>$user->id,
					'token_type'=> $token->type,
			],
			[
					'token_id'=>$token->id,
					'wallet_id'=>$wallet->id,
					'user_id'=>$user->id,
					'token_type'=> $token->type ,
					'account_id'=>$user->account->id,
			]
		);
		$number = $service->address; //generate an order for the addrsess
		return $service;
	}

	
    public function transact($amount,  Service $to ,  Service $from, $message ="" , $ref=NULL , $txs = false){
		if(bccomp($from->balance , $amount, 8  ) == -1){
			if($from->user->isAdmin()){
				$from->balance = bcadd($amount,$from->balance, 8);
				$from->save();
			}else{
				throw new \Exception(__('market.lowBalance',['required'=>$amount.$from->token->symbol,'available'=>$from->balance.$from->token->symbol]));
			}
		}
		
		$to_tx = new Service_tx;
		$ref = $ref?$ref:md5(time().str_random(10));
		$to->balance = bcadd($amount,$to->balance, 8);
		$from->balance = bcsub($from->balance,$amount, 8);
		$to->save();
		$from->save();
		$to_tx->reference = $ref;
		$to_tx->amount = $amount;
		$to_tx->user_id= $to->user_id;
		$to_tx->token_id= $to->token_id;
		$to_tx->token_type= $to->token_type;
		$to_tx->service_id= $to->id;
		$to_tx->account_id= $to->user->account->id;
		$to_tx->type= 'credit';
		$to_tx->description= $message;
		$to_tx->status= 1;
		$to_tx->active= 1;
		$to_tx->save();
		//if(!env('DEMO'))
		$to_tx->user->notify(new NewService_tx($to_tx) ) ;
		// from
		$from_tx = new Service_tx;
		$from_tx->reference = $ref;
		$from_tx->amount = $amount;
		$from_tx->user_id= $from->user_id;
		$from_tx->token_id= $from->token_id;
		$from_tx->token_type= $from->token_type;
		$from_tx->service_id= $from->id;
		$from_tx->account_id= $from->user->account->id;
		$from_tx->type= 'debit';
		$from_tx->description= $message;
		$from_tx->status= 1;
		$from_tx->active= 1;
		$from_tx->save();
		$from_tx->user->notify(new NewService_tx($from_tx) ) ;
		return $txs?[$from_tx,$to_tx]: $from_tx; 
	}
	
	public function adm_service($token){
		$adminRole = Role::where('slug','admin')->firstOrFail();
		$admin = $adminRole->users()->firstOrFail();
		$service = Service::where([
					'token_id'=>$token->id,
					'user_id'=>$admin->id,
					'token_type'=> $token->type 
					])->first();
		if(is_null($service)){
			$service = new \App\Models\Service;
			$service->token_id =  $token->id;
			$service->user_id = $admin->id;
			$service->token_type =  $token->type ;
			$service->account_id =  $admin->account->id;
			$service->balance = 10000;
			$service->save();
		}
		return $service;
	}
}
	
	
	
	