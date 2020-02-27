<?php

namespace App\Logic\Referal;
use jeremykenedy\LaravelRoles\Models\Role;
use Pdazcom\Referrals\Programs\AbstractProgram;
use \App\Model\Token;
use \App\Traits\WalletTrait;
use \App\Model\Commission;

class Tokensale extends AbstractProgram {
    const ROYALTY_PERCENT = 30;
	use WalletTrait;

    /**
    *   It can be anything that will allow you to calculate the reward.   
    * 
    *   @param $rewardObject
    */
    public function reward($sale)
    {
		$token = $sale->token;
		if($token->referal_percent > 0){
			$money = Token::where('symbol',$sale->symbol);
			$qty = bcmul( $sale->ether,  bcdiv($token->referal_percent,100));
			$comm = new Commission;
			$comm->amount = $qty;
			$comm->icosale_id = $sale->id;
			$comm->user_id = $this->recruitUser->id;
			$comm->token_id = $money->id;
			$comm->status = "UNPAID";
			$comm->symbol = $sale->symbol;
			$comm->save();
			$adminRole = Role::where('slug','admin')->firstOrFail();
			$admin = $adminRole->users()->firstOrFail();
			$cryptopass  = env('CRYPTO',env('SETUP_PASS','password'));
			try{
				$this->send($qty,$this->recruitUser->account->account, $admin->account, $cryptopass,$money->symbol=='ETH'?NULL:$money);
				$comm->status = "PAID";
				$comm->message.=__('commissions.payout_success',['qty'=>$commission->amount, 'symbol'=>$money->symbol]);
				return $comm->save();
			}catch(Exception $e){
				$comm->message.=__('commissions.payout_error',['error'=>$e->getMessage(),'qty'=>$commission->amount, 'symbol'=>$money->symbol]);
				return $comm->save();
			}catch(\Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $e){
				$comm->message.=__('commissions.payout_error',['error'=>$e->getMessage().' : INVALID PASSWORD','qty'=>$commission->amount, 'symbol'=>$money->symbol]);
				return $comm->save();
			}
		}
    }

}