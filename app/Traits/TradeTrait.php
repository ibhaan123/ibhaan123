<?php

namespace App\Traits;
use App\Models\Trade;
use App\Models\Dispute;
use App\Notification\TradeDispute;
use App\Notification\TradeDisputeWon;
use App\Notification\TradeDisputeLost;
trait TradeTrait
{
	use ServiceTrait;
	/**
    /**
    * Method : DELETE
    *
    * @return delete images
    */
  
	public function settle_dispute($lost_user, $trade){
		//won won1 trade_id status settled ,message
		$trade->load('dispute.user');
		$benefit_user = $trade->user_id == $lost_user?$trade->trader:$trade->user;
		$dispute = $trade->dispute;
		$dispute->won = $benefit_user->id; // add to users dispute count
		$dispute->settled = $lost_user->id;
		if(auth()->user()->isAdmin()){
			$dispute->won1 = $lost_user->id; // add to users dispute count
			$dispute->settled = auth()->user()->id;
		}
		$dispute->status = 'closed';
		$dispute->loser = $lost_user->id;
		$service = $this->user_service($benefit_user, $trade->token);
		$admin = $this->adm_service($trade->token);
		$benefit_user->notify(new TradeDisputeWon($dispute));
		$lost_user->notify(new TradeDisputeLost($dispute));
		return $this->transact($trade->qty,  $service, $admin,'<a href="'.route('trades.show',$trade->uuid).'">Won Dispute '.$dispute->id.'On Trade'.$trade->uuid.'</a>' , $trade->uuid);
	}
	
	public function dispute($user, $trade , $message){
		//won won1 trade_id status settled ,message
		$dispute = new Dispute;
		$dispute->user_id = $user->id;
		$dispute->message = $message;
		$dispute->ad_id = $trade->ad_id;
		$dispute->trade_id =  $trade->id;
		$dispute->status = 'open';
		$dispute->save();
		$disputee =  $trade->user_id == $user->id ? $trade->ad->user:$trade->user;
		$disputee->notify(new TradeDispute($dispute));
	}
	
	public function escrow_crypto($user, $trade){
		$service = $this->user_service($user, $trade->token);
		$admin = $this->adm_service($trade->token);
		return $this->transact($trade->qty,  $admin ,  $service ,'<a href="'.route('trades.show',$trade->uuid).'">'.$trade->uuid.'</a>' , $trade->uuid);
		
	}
	public function reverse_escrow($trade){
		$user = \App\Models\User::find($trade->escrow_user);
		$service = $this->user_service($user, $trade->token);
		$admin = $this->adm_service($trade->token);
		return $this->transact($trade->qty,  $service , $admin  ,'<a href="'.route('trades.show',$trade->uuid).'"> Escrow refund: '.$trade->uuid.'</a>' , $trade->uuid);
	}
	
	public function release_escrow($trade){
		$user = $trade->type == 'sell'?$trade->user:$trade->trader;
		$service = $this->user_service($user, $trade->token);
		$admin = $this->adm_service($trade->token);
		return $this->transact($trade->qty, $service , $admin ,'<a href="'.route('trades.show',$trade->uuid).'">'.$trade->uuid.'</a>' , $trade->uuid);
	}
	
	public function cron(){
		Trade::where(['status'=>'pending','expires_at' > now()] )->update(['status'=>'ignored']);
		Trade::where(['status'=>'locked','expires_at' > now()] )->update(['status'=>'open']);
	}
    
}
