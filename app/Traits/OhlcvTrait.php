<?php

/**
 * Created by ofumbis.
 * User: ofuzak@gmail.com
 */
namespace App\Traits;
use Illuminate\Support\Facades\Storage;
use \Illuminate\Support\Facades\DB;
use \App\Models\Service;
use \App\Models\Ledger;
use \App\Models\Margin;
use \App\Models\Margin_asset;
use \App\Models\Asset;
use \App\Models\Trade;
use \App\Models\Service_tx;
use \App\Notifications\NewMargin;
use \App\Notifications\NewLedger;
use \App\Events\LedgerEvent;
use jeremykenedy\LaravelRoles\Models\Role;
trait OhlcvTrait
{
	//tt
	/** 
     * acquire a margin trading Margin
     * @return $reference string // 
     *
	*/
	
	use ServiceTrait;
	
	public function getMargin($amount, $from , $ref , $marginType){
		if(bccomp(bcmul($from->balance*$from->leverage) ,$amount, 8) == -1)
		throw new \Exception(__('market.lowBalance',['required'=>bcdiv($amount,$from->leverage,8 ).$from->token->symbol,'available'=>$from->balance.$from->token->symbol]) );
		$query =  Assets::where([
					['token_id','=',$from->token->id],
					['token_type','=',$from->token_type],
					['status','=', 1]
					])
					->orderBy('interest','asc');
		if(setting('adminLending') == "admin"){
			$adminRole = Role::where('slug','admin')->firstOrFail();
			$admin = $adminRole->users()->firstOrFail();
			$query->where('user_id', $admin->id);
		}
		$assets = $query->latest()->get();
		if(bccomp($assets->sum('unfilled'), $amount ,8) == -1 );
		throw new \Exception(__('market.adminWalletLow',['symbol'=>$from->token->symbol.' Assets ']));
		$margin = new Margin;
		if($marginType == 'sell'){
			$margin->threshold_type = 'up';
		}elseif($marginType == 'buy'){
			$margin->threshold_type = 'down';
		}
		$callloss = bcmul( 15*100, $amount);
		$callamount = bcmul( 15*100, $amount);
		$margin->threshold = 
		$margin->amount = $amount;
		$margin->service_id = $from->id;
		$margin->reference = $ref;
		$margin->leverage = $from->leverage;
		$margin->invested =bcdiv($amount, $from->leverage);
		$margin->period = $period;
		$margin->collect = \Carbon\Carbon::now()->addDays($from->margin_days);
		$margin->user_id = $from->user_id;
		$margin->token_id = $from->id;
		$margin->token_type = $from->token_type;
		$margin->save();
		$filled = 0;
		foreach($assets as $ast){
			$filled  = bcadd($filled , $asset->unfilled , 8);
			$unfilled = $asset->unfilled;
			if(bccomp($filled , $amount , 8) == 0){
				$asset->filled  = bcadd($asset->filled , $asset->unfilled , 8);
				$asset->unfilled  = 0 ;
				$asset->save();
				$this->createMarginAsset($asset, $margin ,$unfilled );
				break;
			}if(bccomp($filled , $amount , 8) == 1){
				$required = bcsub($amount, bcsub($filled,$asset->unfilled, 8), 8);
				$asset->filled = bcadd($required , $asset->filled , 8);
				$asset->unfilled  = bcsub($asset->unfilled , $required , 8); 
				$asset->save();
				$this->createMarginAsset($asset, $margin , $required );
				break;
			}
			$asset->filled  = bcadd($asset->filled , $asset->unfilled , 8);
			$asset->unfilled  = 0 ;
			$asset->save();
			$this->createMarginAsset($asset, $margin , $unfilled  );
		}
		$margin->save();
		$dec = $margin->token->decimals;
		$margin->load('assets');
		$margin->interest = $margin->margin_assets()->max('interest'); // apply the highest;
		$margin->payback = bcadd(bcmul(bcdiv( $margin->interest , 100 ,2),$margin->amount, 8),$margin->amount, 8);
		$margin->save();
		 
		foreach($margin->margin_assets as $marginAsset){ // update the payback
			$amt = $marginAsset->amount;
			$marginAsset->applied_interest = $margin->interest;
			$marginAsset->admin_amount = bcadd(bcmul($margin->interest/100,$amt,$dec),$amt,$dec);
			$marginAsset->admin_profit = bcsub($marginAsset->admin_amount , $marginAsset->payback_amount);
			$marginAsset->save();
		}
		if(!env('DEMO'))
		$margin->user->notify(new NewMargin($margin));
		return $margin;
	}
	
	public function createMarginAsset($asset, $margin , $amt ){
		$dec = $margin->token->decimals;
		$marginAsset = new Margin_asset;
		$marginAsset->asset_id = $asset->id;
		$marginAsset->margin_id  = $margin->id;
		$marginAsset->user_id  = $margin->user_id;
		$marginAsset->service_id  = $asset->service_id;
		$marginAsset->amount 	 = $amt;
		$marginAsset->fee 	 = $asset->fee;
		$marginAsset->collect  = now()->addDays($asset->period);;
		$marginAsset->interest = $asset->interest;
		$marginAsset->interest_amount =  bcmul($asset->interest/100,$amt,$dec);
		$marginAsset->fees 	 =  bcmul($asset->fee/100,$marginAsset->interest_amount,$dec);
		$marginAsset->tax 	 =  bcmul($asset->tax/100,$marginAsset->interest_amount,$dec);
		$marginAsset->applied_interest = 0;
		$marginAsset->payback_amount  = bcsub( bcadd($marginAsset->interest_amount ,$amt,$dec),bcadd($marginAsset->fees,$marginAsset->tax,$dec),$dec);
		$marginAsset->admin_amount = 0;
		$marginAsset->admin_profit = 0;
		$marginAsset->save();
		return $marginAsset;
	}
	
	
	
	
	
	/** 
     * record a transaction to payout margin trades
     * @return $reference string // 
     */
	 
	 public function margin_transact($amount,  Service $from , $ref , $margin ){
		$leverage =  0 ;
		$mamount =  0 ;
		$marginId = NULL;
		$ref = $ref?$ref:md5(time().str_random(10));
		$to->margin_balance = bcadd($amount,$to->margin_balance, 8);
		$from->balance = bcsub($from->balance,$amount, 8);
		$to->save();
		$from->save();
		
		$to_tx = new Service_tx;
		$to_tx->reference = $ref;
		$to_tx->amount = $amount;
		$to_tx->margin = $mamount;
		$to_tx->margin_id = $margin->id;
		$to_tx->leverage = NULL;
		$to_tx->user_id = $to->user_id;
		$to_tx->token_id = $to->token_id;
		$to_tx->token_type = $to->token_type;
		$to_tx->service_id = $to->id;
		$to_tx->account_id = $to->user->account->id;
		$to_tx->type= 'credit';
		$to_tx->description= $message;
		$to_tx->status= 1;
		$to_tx->active= 1;
		$to_tx->save();
		// from
		$from_tx = new Service_tx;
		$from_tx->reference = $ref;
		$from_tx->amount = $amount;
		$from_tx->margin = $mamount;
		$from_tx->margin_id = NULL;
		$from_tx->leverage = NULL;
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
		return $from_tx; 
	
		}
	
    public function service_transact($amount,  Service $to ,  Service $from, $message ="" , $ref=NULL, $margin=false ){
		
	
		
		if(!$margin && bccomp($from->balance , $amount, 8  ) == -1 ){
			if($from->user->isAdmin()){
				$from->balance = bcadd($amount,$from->balance, 8);
				$from->save();
			}else{
				throw new \Exception(__('market.lowBalance',['required'=>$amount.$from->token->symbol,'available'=>$from->balance.$from->token->symbol]));
			}
		}
		
		$leverage =  0 ;
		$mamount =  0 ;
		$marginId = NULL;
		$to_tx = new Service_tx;
		$ref = $ref?$ref:md5(time().str_random(10));
		if($margin =="sell"||$margin =="buy"){ // get a marginposition
			if(bccomp(bcmul($from->balance*$from->leverage) ,$amount, 8) == -1)
			throw new \Exception(__('market.lowBalance',['required'=>bcdiv($amount,$from->leverage,8 ).$from->token->symbol,'available'=>$from->collateral.$from->token->symbol]) );
			$leverage =  $from->leverage ;
			$mamount = $amount;
			$amount =  bcdiv($amount,$from->leverage,8);
			$margin = $this->getMargin($mamount,$from, $ref ,$margin);
			$marginId = $margin->id;
		}
		
		
		if($margin instanceof Margin){ // settle to margin account
			$to->margin_balance = bcadd($amount,$to->margin_balance, 8);
			$to_tx->margin_id = $margin->id;
		}else{
			$to->balance = bcadd($amount,$to->balance, 8);
			$to_tx->margin_id = NULL;
		}
		if(is_numeric($margin) && $margin > 0){
			$to_tx->margin_id = $margin;
		}else{
			$from->balance = bcsub($from->balance,$amount, 8);
		}
		$to->save();
		$from->save();
		$to_tx->reference = $ref;
		$to_tx->amount = $amount;
		$to_tx->margin = $mamount;
		$to_tx->leverage =NULL;
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
		$to_tx->user->notify(new \App\Notifications\NewService_tx($to_tx) ) ;
		
		// from
		$from_tx = new Service_tx;
		$from_tx->reference = $ref;
		$from_tx->amount = $amount;
		$from_tx->margin = $mamount;
		$from_tx->margin_id = $marginId;
		$from_tx->leverage = $leverage;
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
		//if(!env('DEMO'))
		$from_tx->user->notify(new \App\Notifications\NewService_tx($from_tx) ) ;
		return $from_tx; 
	}
	
	
	
	/** 
     *Extend a Margin position
     * @return null // 
     */
	public function  marginsExtend(){
		$expireds = Margin_asset::with('margin.token')->where('collect','>',now())->get()->groupBy('margin_id');
		$adminRole = Role::where('slug','admin')->firstOrFail();
		$admin = $adminRole->users()->firstOrFail();
		foreach( $expireds as  $expired ){
			$margin =  $expired->first()->margin;
			$interest =  $margin->interest;
			$amount = $expired->sum('amount');
			$query =  Assets::where([
					['token_id','=',$margin->token->id],
					['token_type','=',$margin->token_type],
					['status','=', 1]
					])
					->orderBy('interest','asc');
			if(setting('adminLending') == "admin"){
				$query->where('user_id', $admin->id);
			}
			$assets = $query->latest()->get();
			if(bccomp($assets->sum('unfilled'), $amount ,8) == -1 ){
				if(!env('DEMO'))
				$admin->notify(new \App\Notifications\AdminError(__('market.adminMarginWalletLow',['symbol'=>$from->token->symbol.' Assets '])));
				return false;
			}
			
			foreach($expired as $exp ){
				if($exp->status == 'withdrawn'){ // expired 
					continue;
				}
				$service = $exp->service;
				$tx = $this->service_transact($exp->payback_amount,  $service, $adm_service, $message = __('market.assetpayout') );
				$exp->payout_id = $tx->id;
				$exp->status = 'withdrawn';
				$exp->save();
			}
			$filled = 0;
			foreach($assets as $asset){
				$filled  = bcadd($filled , $asset->unfilled , 8);
				$unfilled = $asset->unfilled;
				if(bccomp($filled , $amount , 8) == 0){
					$asset->filled  = bcadd($asset->filled , $asset->unfilled , 8);
					$asset->unfilled  = 0 ;
					$asset->save();
					$this->createMarginAsset($asset, $margin ,$unfilled );
					break;
				}if(bccomp($filled , $amount , 8) == 1){
					$required = bcsub($amount, bcsub($filled,$asset->unfilled, 8), 8);
					$asset->filled = bcadd($required , $asset->filled , 8);
					$asset->unfilled  = bcsub($asset->unfilled , $required , 8); 
					$asset->save();
					$this->createMarginAsset($asset, $margin , $required );
					break;
				}
				$asset->filled  = bcadd($asset->filled , $asset->unfilled , 8);
				$asset->unfilled  = 0 ;
				$asset->save();
				$this->createMarginAsset($asset, $margin , $unfilled  );
			}
			$margin->save();
			$margin->load('margin_assets');
			$margin->interest = $margin->margin_assets()->max('interest'); // apply the highest;
			$margin->payback = bcadd(bcmul(bcdiv( $margin->interest , 100 ,2),$margin->amount, 8),$margin->amount, 8);
			$margin->save();
			foreach($margin->margin_assets as $marginAsset){ // update the payback
				$amt = $marginAsset->amount;
				$marginAsset->applied_interest = $margin->interest;
				$marginAsset->admin_amount = bcadd(bcmul($margin->interest/100,$amt,$dec),$amt,$dec);
				$marginAsset->admin_profit = bcsub($marginAsset->admin_amount , $marginAsset->payback_amount);
				$marginAsset->save();
			}
			if($margin->interest != $interest)
			if(!env('DEMO'))
			$margin->user->notify(new InterestRecalculation($margin));
			return $margin;
		
		}
	}
	
	public function MarginClaim(Margin $margin){
		// unclosed or called margin
		if($margin->status == 'complete')
			throw new Exception(__('market.margin_complete_error'));
		
		if($margin->ledgercall()->count() < 1){
			if(bccomp($margin->payback , $margin->service->balance ,8)== 1){
				$market = $margin->ledger->market;
				$excess = bcsub($margin->payback, $margin->service->balance,8);
				$price =$margin-> threshold_type=='up' ?$market->buy_price.$market->qoute->symbol:$market->sell_price.$market->qoute->symbol;
				$qty = bcdiv( $excess ,  $price, 8);
				$type = $margin-> threshold_type=='up' ?'buy':'sell';
				throw new Exception(__('market.insufficent_balance_for_margin',['type'=>$type, 'qty'=>$qty.' '.$market->base->name ])); 
			}
			$required  = bcsub($margin->payback, $margin->service->balance ,8);
			// collect required 
			$adm_service = $this->adm_service($margin->token);
			$tx = $this->service_transact( $required, $adm_service, $margin->service,__('market.claim_margin'), $margin->ref, false);
			$ledger = $margin->ledger;
			if($ledger->type == 'buy'){ // send qty
				$token = $ledger->market->base;
				$amt = $ledger->qty;
			}elseif($ledger->type == 'sell'){ // send totalfee
				$token = $ledger->market->quote;
				$amt = $ledger->totalfee;
			}
			$adm_service = $this->adm_service($token);
			$service = $margin->user->services()->where([['token_id','=',$token->id],['token_type','=',$token->type]])->first();
			$tx = $this->service_transact( $amt , $adm_service, $service,__('market.claim_credit'), $margin->ref, false);
			return $this->settleAssets($margin);
		}else{
			$ledger = $margin->ledgercall;
			$mpay = $ledger->type == "sell"?$ledger->filled : $ledger->totalfilled;
			$avialable = bcadd($margin->service->balance, $mpay);
			if(bccomp($margin->payback , $avialable ,0)==1){
				$market = $margin->ledger->market;
				$excess = bcsub($margin->payback, $avialable, 8 );
				$price =$margin-> threshold_type=='up' ?$market->buy_price.$market->qoute->symbol:$market->sell_price.$market->qoute->symbol;
				$qty = bcdiv( $excess ,  $price, 8);
				$type = $margin-> threshold_type=='up' ?'buy':'sell';
				throw new Exception(__('market.insufficent_balance_for_margin',['type'=>$type, 'qty'=>$qty.' '.$market->base->name ])); 
			}
			$required  = bcsub($margin->payback - $mpay );
			$adm_service = $this->adm_service($margin->token);
			$tx = $this->service_transact( $required, $adm_service, $margin->service,__('market.claim_margin'), $margin->ref, false);
			
			$margin->service->margin_balance = bcsub($margin->service->margin_balance,$mpay,8);
			$ledger = $margin->ledger;
			if($ledger->type == 'buy'){ // send qty
				$token = $ledger->market->base;
				$amt = $ledger->qty;
			}elseif($ledger->type == 'sell'){ // send totalfee
				$token = $ledger->market->quote;
				$amt = $ledger->totalfee;
			}
			$adm_service = $this->adm_service($token);
			$service = $margin->user->services()->where([['token_id','=',$token->id],['token_type','=',$token->type]])->first();
			$tx = $this->service_transact( $amt , $adm_service, $service,__('market.claim_credit'), $margin->ref, false);
			$this->settleAssets($margin);
			$ledger->unfilled = 0;
			$ledger->filled = $ledger->qty;
			$ledger->totalunfilled = 0;
			$ledger->status = 'closed';
			$ledger->totalfilled = $ledger->totalfee;
			$ledger->save();
			return true;
		}
	}
	
	
	public function settleAssets($margin){
		if($margin->status != 'complete'){
			$token = $margin->token;
			$assets = $margin->margin_assets;
			$adm_service = $this->adm_service($token);
			$margin->profit = 0;
			$margin->net_profit = 0;
			$margin->earned =  0 ;
			$margin->status ='complete';
			$margin->save();
			foreach($assets as $asset){
				if($asset->status == 'withdrawn'){ // expired 
					continue;
				}
				$service = $asset->service;
				$tx = $this->service_transact($asset->payback_amount,  $service, $adm_service, $message =__('market.assetpayout') );
				$asset->payout_id = $tx->id;
				$asset->status = 'withdrawn';
				$asset->save();
			}
			return true;
		}
		throw new Exception(__('market.margin_complete_error'));
	}
	
	
	 
	 /** 
     * Close a Margin position
     * @return null // 
     */
	 public function  marginsCall(){ 
		 $margins = Margin::with('ledger.market','ledgercall')
		 					->orWhere('status','closed')
							->orWhere('status','active')
							->get();
		 foreach( $margins as $margin){
			$market = $margin->legder->market;
			if(($margin->threshold_type == 'up' && $market->latest >= $margin->threshold)||($margin->threshold_type == 'down' && $market->latest <= $margin->threshold)){
				$this->marginCall( $margin );
			}
		 }
	 }
	 

	 public function marginCall(Margin $margin, $price = false ){ // for now only force close
		 $qty = $margin->qty;
		 $limit = $price?$price: $margin->legder->market->latest;
		 $order_type = $price? 'limit':'market';
		 if($margin->ledgercall()->count()< 1){
			 $ledger = $margin->ledger;
			 $type = $ledger->type=="buy" ?"sell":"buy";
			 $margin->status =  $price ? 'closed':'called';
			 $margin->save();
			 try{
				 $lg = $this->ledgerRecord($margin->user,$margin->ledger->market, $type, $limit, $qty , $order_type, $stop="0", $margin );
			 }catch(Exception $e){ // nothing happaend reverse status
				 $margin->status = 'active';
				 $margin->save();
				 if(!env('DEMO'))
				 $admin->notify(new \App\Notifications\AdminError($e->getMessage()));
				 throw $e;
			 }
			 return $margin;
		 }elseif($margin->ledgercall()->count()> 0){ // call a previously closed margin
			 $ledger = $margin->ledgercall;
			 $this->liquidate( $ledger );
		 }
	 }
	 
	 
	 
	 public function liquidate($ledger){
		$user = $ledger->user;
		$lg  = $ledger;
		$market  = $ledger->market;
		if($ledger->type =="buy" ){ 
			$token = $market->quote;
			$fitting =  Ledger::where([
					['pair','=', $market->pair],
					['type','=', 'sell'],
					['status','=', 'open'],
					['unfilled','>', 0]
				])
				->orderBy('price','asc')->latest(); // first come first serve
			$all = $fitting ->get();
			$total = $lg->totalunfilled;
			$collected = 0;
			$selected = [];
			foreach ($all as $value){
					if($collected < $total){
						$collected += $value->unfilled;
						$selected [] = $value;
						continue;
					}
					break;
				}
			$selected = collect($selected);
			$limit = $selected->max('price');
			$lg->price = $limit;
			$lg->total = bcdiv(bcmul($lg->totalfee , 100+$lg->fee),100);
			$lg->fees = bcmul($lg->fee , $lg->total, 8 ); 
			$lg->qty = bcdiv($lg->total, $lg->price, 8); 

			####
			
			if($selected->count() && $selected->sum('qty') >= $qty){
				$lg->maker = 0; 
				if($market->fee_type=="maker" ){ // waiver taker fees
					$lg->totalfee = $total;
					$lg->fees = 0;
					$lg->fee = 0;
				}
			}else{
				throw new Exception(__('market.smallsize'));
			}
			//$lg->save();
			$filled = 0;
			$qty = $lg->unfilled;
			foreach($selected as $book){
				$filled  = bcadd($filled , $book->unfilled , 8);
				$unfilled = $book->unfilled;
				if(bccomp($filled , $qty , 8) == 0){ //exact filled
					$this->executeTrade($lg, $book, $qty );
					if($book->sales()->count()){
						$book->filled  = $book->sales()->sum('qty');
						$book->unfilled  = 0 ;
						$book->status= 'closed';
						$book->active = 2;
						$book->totalfilled = ($book->filled / $book->qty)*$book->totalfee;
						$book->totalunfilled = number_format(($book->unfilled / $book->qty)*$book->totalfee,8);
					}
					$book->save();
					event(new  LedgerEvent($book));
					break;
				}if(bccomp($filled , $qty , 8) == 1){ // excess
					$required = bcsub($qty, bcsub($filled,$book->unfilled, 8),8);
					$this->executeTrade($lg, $book , $required );
					if($book->sales()->count()){
						$book->filled =  $book->sales()->sum('qty');
						$book->unfilled  = bcsub($book->unfilled , $required , 8); 
						$book->totalfilled = ($book->filled / $book->qty)*$book->totalfee;
						$book->totalunfilled = number_format(($book->unfilled / $book->qty)*$book->totalfee,8);
					}
					$book->save();
					event(new  LedgerEvent($book));
					break;
				}
				$this->executeTrade($lg, $book , $unfilled );
				if($book->sales()->count()){
					$book->filled  = $book->sales()->sum('qty');
					$book->unfilled  = 0 ;
					$book->status= 'closed';
					$book->active = 2;
					$book->totalfilled = ($book->filled / $book->qty)*$book->totalfee;
					$book->totalunfilled = number_format(($book->unfilled / $book->qty)*$book->totalfee,8);
				}
				$book->save();
				event(new  LedgerEvent($book));
			}
			if($lg->buys()->count()){
				$lg->filled = $lg->buys()->sum('qty');
				$lg->unfilled = bcsub($lg->unfilled,$lg->filled,8);
				$lg->totalfilled = number_format(($lg->filled / $lg->qty)*$lg->totalfee,8);
				$lg->totalunfilled =  number_format(($lg->unfilled / $lg->qty)*$lg->totalfee,8);
				if($lg->unfilled == 0){
					$lg->status= 'closed';
					$lg->active = 2;
				}
			}
			$lg->save();
			assert($lg->unfilled >= 0);
			// update margin 
			event(new  LedgerEvent($lg));
			if(!env('DEMO'))
			$lg->user->notify(new NewLedger($lg));
  		}elseif( $type =="sell" ){ 
			$fitting =  Ledger::where([
					['pair','=', $market->pair],
					['type','=', 'buy'],
					['status','=', 'open']
				])->orderBy('price','desc');
			$all = $fitting ->get();
			$min = $margin->payback; // liquidation total
			$gathered = 0;
			$items  =[];
			foreach ($all as $value){
				if($gathered < $min){
					$gathered += $value->totalunfilled;
					$items [] = $value;
					continue;
				}
				break;
			}
			$picked = collect($items);
			$mprice = $picked->min('price'); // price to guarantee liquidation
			$lg->qty = $margin->qty;
			$total = $lg->qty;
			$collected = 0;
			$selected = [];
			foreach ($all as $value){
					if($collected < $total){
						$collected += $value->unfilled;
						$selected [] = $value;
						continue;
					}
					break;
				}
			$selected  = collect($selected );
			$sprice = $selected->min('price');
			$limit = $mprice < $sprice ? $mprice:$sprice;
			$lg->price = $limit;
			$lg->fee = $market->seller_fees/100;
			$lg->total = bcmul($limit,$lg->qty,8);
			$lg->fees = bcmul($lg->fee , $lg->total, 8 ); 
			$lg->totalfee =  bcsub($lg->total, $lg->fees, 8); 
			if($selected->sum('qty') >= $qty){ // fillable
				$lg->maker = 0; 
				if($market->fee_type=="maker" ){
					$lg->totalfee = $total;
					$lg->fees = 0;
					$lg->fee = 0;
				}
			}else{
				if(($margin instanceof Margin && $margin->status =="called")|| $order_type == 'fillOrKill'){  // kill 
					throw new Exception(__('market.smallsize'));
				}
			}
		
			$filled = 0;
			$qty = $lg->unfilled;
			foreach($selected as $book){
				$filled  = bcadd($filled , $book->unfilled , 8);
				$unfilled = $book->unfilled;
				if(bccomp($filled , $qty , 8) == 0){ //exact filled
					$this->executeTrade($lg, $book , $unfilled );
					if($book->buys()->count()){
						$book->filled  = $book->buys()->sum('qty');
						$book->unfilled  = 0 ;
						$book->totalfilled = ($book->filled / $book->qty)*$book->totalfee;
						$book->totalunfilled = number_format(($book->unfilled/$book->qty)*$book->totalfee,8);
						$book->status= 'closed';
						$book->active = 2;
					}
					$book->save();
					event(new  LedgerEvent($book));
					break;
				}if(bccomp($filled , $qty , 8) == 1){ // excess
					$required = bcsub($qty, bcsub($filled,$book->unfilled, 8), 8);
  					$this->executeTrade($lg, $book , $required );
					if($book->buys()->count()){
						$book->filled =  $book->buys()->sum('qty');
						$book->unfilled  = bcsub($book->unfilled , $required , 8 ); 
						$book->totalfilled = ($book->filled / $book->qty)*$book->totalfee;
						$book->totalunfilled = number_format(($book->unfilled / $book->qty)*$book->totalfee,8);
					}
					$book->save();
					event(new  LedgerEvent($book));
					break;
				}
				$this->executeTrade($lg, $book , $unfilled );
				if($book->buys()->count()){
					$book->filled  = $book->buys()->sum('qty');
					$book->unfilled  = 0 ;
					$book->totalfilled = number_format(($book->filled / $book->qty)*$book->totalfee,8);
					$book->totalunfilled = number_format(($book->unfilled / $book->qty)*$book->totalfee,8);
					$book->status= 'closed';
					$book->active = 2;
				}
				$book->save();
				event(new  LedgerEvent($book));
			}
			if($lg->sales()->count()){
				$lg->filled = $lg->sales()->sum('qty');
				$lg->unfilled = bcsub($lg->unfilled,$lg->filled,8);
				$lg->totalfilled = number_format(($lg->filled / $lg->qty)*$lg->totalfee,8);
				$lg->totalunfilled = number_format(($lg->unfilled / $lg->qty)*$lg->totalfee,8);
				if($lg->unfilled == 0){
					$lg->status= 'closed';
					$lg->active = 2;
				}
			}
			$lg->save();
			event(new  LedgerEvent($lg));
		}
		return $lg;
	 }
	
	
	/**create a new market position
     * @param $datas 
     *
     * @return $reference string // 
     */
	
    public function ledgerRecord(\App\Models\User $user, \App\Models\Market $market, $type, float $limit, float $qty , $order_type ='limit', $stop=NULL, $margin=false){
	
	
		if($qty <= 0 || $limit <= 0) return false;
		$margincalled = $margin instanceof \App\Models\Margin &&$margin->status == 'called'?true:false;
		$marginopen = $margin =="open"?true:false;
		$price = $limit;
		$total = bcmul($limit,$qty,8);
		$ref = md5(time().str_random(10));
		$lg = new Ledger;
		$lg->user_id = $user->id;
		$lg->market_id = $market->id; 
		$lg->type  = $type;
		$lg->reference  = $ref;
		$lg->pair = $market->pair;
		$lg->price = $limit;
		$lg->stop = $stop;
		$lg->qty = $qty;
		$lg->total = bcmul($limit,$qty,8);
		$lg->order_type = $order_type;  //'stopLimit'
		$lg->maker = 1;
		$lg->status = 'paused'; 
		$lg->active = 1;
		$lg->filled = 0; 
		$lg->unfilled = $qty;
		
		$selected = collect([]); 
		$triggered_type = NULL;
		if($type =="buy" ){ 
			## buyorder order##########
			$lg->fee = $market->buyer_fees/100;
			$lg->fees = bcmul($lg->fee , $lg->total, 8 ); 
			$lg->totalfee = bcadd($lg->total, $lg->fees, 8); 
			$token = $market->quote;
			try{
				$adm_service =  $this->adm_service($token);
				}catch( \Exception $e){
				
				throw new \Exception(__('market.noAdminAccount') );
			}
			try{
				$service =  $token->services()->where('user_id', $user->id)->firstOrFail();
				}catch( \Exception $e){
				throw new \Exception(__('market.noUserAccount') );
			}
			$fitting =  Ledger::where([
					['pair','=', $market->pair],
					['type','=', 'sell'],
					['status','=', 'open'],
					['unfilled','>', 0]
				])
				->orderBy('price','asc')->latest(); // first come first serve
				if($order_type != 'market' && !$margin ){
					$fitting->where(function($query)use($limit, $stop){ 
						$query->where(function($query)use($limit, $stop){//stop limit sells
							$query->where('order_type','stop_limit')
								  ->where('stop','>=',$limit)
								  ->where('price','<=',$limit);
						})
						->orWhere(function($query)use($limit, $stop){ //limit
							$query->where('triggered_type','limit')
								  ->where('price','<=',$limit);
						})
						->orWhere(function($query)use($limit, $stop){ //limit
							$query->where('order_type','limit')
								  ->where('price','<=',$limit);
						})
						->orWhere(function($query)use($limit, $stop){ //stop
							$query->where('order_type','stop')
								  ->where('stop','>=', $limit);
						})->orWhere('order_type','trailing_stop');
						   
					});
				}
			
			if($order_type == 'stop_limit'){ 
				$selected = $fitting->whereBetween('price', [$stop, $limit])->get();
				if($selected->count() > 0 )
				$triggered_type = 'limit';
			}
			if($order_type == 'limit' ||$order_type == 'fillOrKill' ){ //matcch price lesser then $limit
				$selected = $fitting -> where('price', '<=' ,$limit )->get();
				
			}
			$trigger_price = NULL;
			if($order_type == 'stop'){ // check if there are prices greater than stop is triggered
				$selected = $fitting ->where('price', '>=', $stop )->get();
				if($selected->count()>0){
					$triggered_type = 'market';
					$order_type = 'market'; // disburse this order as a mrket order
					$trigger_price = $selected->min('price');
				}
			}
			$lg->triggered_type = $triggered_type;
			$lg->trigger_price = $trigger_price;
			if($order_type == 'market' || $marginopen || $margincalled ){ // we shall not constrain this one
				    $all = $fitting ->get();
					$qty = $lg->qty;
					if($margin instanceof Margin ){ // required is the QTY
						$qty  = $margin->payback;
						$lg->qty = $margin->payback;
					}
					$collected = 0;
					$selected = [];
					if($all->count()){
						foreach ($all as $value){
							if($collected < $total){
								$collected += $value->unfilled;
								$selected [] = $value;
								continue;
							}
							break;
						}
						$limit = collect($selected)->max('price');
						if($limit == 0)
						$limit = $price > 0?$price:$market->last;
						$lg->price = $limit;
					}
					$selected = collect($selected );
 					$lg->total = bcmul($lg->qty , $lg->price ,8);
					$lg->fees = bcmul($lg->fee , $lg->total, 8 ); 
					$lg->totalfee = bcadd($lg->total, $lg->fees, 8); 
					$lg->totalunfilled = $lg->totalfee; 
					
			}
			
			
			
			// adjust market order prices to be the cheapest.
			$mledger= Ledger::where([
					['pair','=', $market->pair],
					['type','=', 'sell'],
					['status','=', 'open'],
					['unfilled','>', 0]
				])->where(function($query){
					$query->where('order_type','market')
					->orWhere('triggered_type','market');
				});
			
			if($mledger->count()> 0){
				$newprice = $selected->count()?$selected->min('price'):$lg->price;
				$markets  = $mledger->get()->map(function($mkt)use($newprice){
					if($mkt->price > $newprice ){
						$mkt->price = $newprice;
						$mkt->unfilled = bcdiv($mkt->totalunfilled,$newprice);
						$mkt->save();
					}
					return $mkt;
				});
				$selected = $markets->concat($selected);
			}
			
			
			 
			if($selected->count()){
				$selected = $selected->reject(function($item)use($limit,$lg){ 
					if($item->order_type =='stop'){
						$item->triggered_type ="market";
						$item->trigger_price = $lg->price;
						$item->save();
					}
					if($item->order_type =='stop_limit'){
						$item->triggered_type ="limit";
						$item->trigger_price = $lg->price;
						$item->save();
					}
					if($item->triggered_type == 'limit'||$item->triggered_type == 'market') return false;
					if($item->order_type != 'trailing_stop') return false;
					$trade = Trade::where([
						['pair','=', $item->pair],
						['status','=', 1],
						['created_at','>', $item->created_at]
					])->latest()->orderBy('price','desc')->first(); // hightest since
					if(is_null($trade ))return false;
					if( abs($trade->price - $limit ) > $item->stop ){
						$item->triggered_type ="market";
						$item->trigger_price = $lg->price;
						$item->save();
						return false;
					}
					return true; 
				});
			}
	
			####
			
			if($selected->count() && $selected->sum('qty') >= $qty){
				$lg->maker = 0; 
				if($market->fee_type=="maker" ){ // waiver taker fees
					$lg->totalfee = $total;
					$lg->fees = 0;
					$lg->fee = 0;
				}
			}else{
				if( $margincalled || $order_type == 'fillOrKill'){  // kill 
					throw new Exception(__('market.smallsize'));
				}
				$lg->maker = 1; 
				if($market->fee_type=="taker" ){ // waiver maker fees
					$lg->totalfee = $total;
					$lg->fees = 0;
					$lg->fee = 0;
				}
			}
			if($order_type == 'trailing_stop'){  // maker // nop need to match orders
				$selected = collect([]);
				$lg->maker = 1; 
				if($market->fee_type=="taker" ){
					$lg->totalfee = $total;
					$lg->fees = 0;
					$lg->fee = 0;
				}
			}
		
			$marginType = $margin == "open"? "buy": false;
			if($margin instanceof Margin ){ // closinga margin postion
				$marginType = $margin->id;
			}
			try{
				$tx = $this->service_transact($lg->totalfee, $adm_service , $service , __('market.Market').' Listing: '.__('market.'.$type).' '.__('market.of').' '.$market->quote->name, $ref , $marginType);
			}catch(\Exception $e){
				throw $e;
			}
			
			$lg->service_tx_id  = $tx->id;
			$lg->service_id  = $service->id;
			$lg->margin_id = $tx->margin_id;
			$lg->save();
			
			if($margin instanceof Margin ){
				$margin->ledger_id = $lg->id;
				$margin->save();
			}
			$filled = 0;
			if($selected->count() > 0)
			//Storage::disk('local')->put(str_random(8).'lg.txt', $lg->toJson());
			foreach($selected as $book){
				$filled  = bcadd($filled , $book->unfilled , 8);
				$unfilled = $book->unfilled;
				if(bccomp($filled , $qty , 8) == 0){ //exact filled
					//Storage::disk('local')->put($book->id.'_'.str_random(8).'book.txt', $book->toJson().PHP_EOL.$lg->toJson());
					$this->executeTrade($lg, $book, $qty );
					if($book->sales()->count()){
						$book->filled  = $book->sales()->sum('qty');
						$book->unfilled  = 3 ;
						$book->status= 'closed';
						$book->active = 2;
						$book->totalfilled = bcmul(bcdiv($book->filled , $book->qty,8),$book->totalfee,8);
						$book->totalunfilled = bcmul(bcdiv($book->unfilled,$book->qty,8),$book->totalfee,8);
					}
					$book->save();
					event(new  LedgerEvent($book));
					break;
				}if(bccomp($filled , $qty , 8) == 1){ // excess
				//Storage::disk('local')->put($book->id.'_'.str_random(8).'book.txt', $book->toJson().PHP_EOL.$lg->toJson());
					//$required = bcsub($qty, bcsub($filled,$book->unfilled, 8),8);
					$excess =  bcsub($filled,$qty,8);
					$required = bcsub($book->unfilled , $excess, 8);
					$this->executeTrade($lg, $book , $required );
					if($book->sales()->count()){
						$book->filled =  $book->sales()->sum('qty');
						$book->unfilled  = bcsub($book->unfilled , $required , 8); 
						$book->totalfilled = bcmul(bcdiv($book->filled , $book->qty,8),$book->totalfee,8);
						$book->totalunfilled = bcmul(bcdiv($book->unfilled,$book->qty,8),$book->totalfee,8);
					}
					$book->save();
					event(new  LedgerEvent($book));
					break;
				}
				//Storage::disk('local')->put($book->id.'_'.str_random(8).'book.txt', $book->toJson().PHP_EOL.$lg->toJson());
				$this->executeTrade($lg, $book , $unfilled );
				if($book->sales()->count()){
					$book->filled  = $book->sales()->sum('qty');
					$book->unfilled  = 0 ;
					$book->status= 'closed';
					$book->active = 4;
					$book->totalfilled = bcmul(bcdiv($book->filled , $book->qty,8),$book->totalfee,8);
					$book->totalunfilled = bcmul(bcdiv($book->unfilled,$book->qty,8),$book->totalfee,8);
				}
				$book->save();
				event(new  LedgerEvent($book));
			}
			$lg->status= 'open';
			$lg->active = 1;
			if($selected->count() > 0 && $lg->buys()->count() > 0){
				$lg->filled = $lg->buys()->sum('qty');
				$lg->unfilled = bcsub($lg->qty,$lg->filled,8);
				if($lg->unfilled < 0)
				Storage::disk('local')->put(str_random(8).$lg->type.'lg.txt', $lg->toJson().PHP_EOL.PHP_EOL.$selected->toJson().PHP_EOL.PHP_EOL.$lg->sales->toJson());
				$lg->totalfilled = bcmul(bcdiv($lg->filled , $lg->qty,8),$lg->totalfee,8);
				$lg->totalunfilled = bcmul(bcdiv($lg->unfilled,$lg->qty,8),$lg->totalfee,8);
				if($lg->unfilled == 0){
					$lg->status= 'closed';
					$lg->active = 2;
				}
			}
			$lg->save();
			assert($lg->unfilled >= 0);
			// update margin call price;
			if(!empty($lg->margin_id)){
				$margin = $lg->margin;
				$dist= bcmul($margin->payback, 15/100 , 8); // paybak is totalinfee
				$margin->threshold = bcdiv( bcsub($margin->payback, $dist , 8), $lg->qty, 8 );
				$margin->qty =  $lg->qty;
				$margin->total =  $lg->totalfee;
				$margin->price =  $price;
				if($price < $lg->price ){ // dont auto list loss margins
					$margin->price = NULL;
				}
				$margin->save();
			}
			
			$lg->load('margin','margincall');
			event(new  LedgerEvent($lg));
			if(!env('DEMO'))
			$lg->user->notify(new NewLedger($lg));
			
  		}elseif( $type =="sell" ){ 
		
			## sell order##########
			$lg->fee = $market->seller_fees/100;
			$lg->fees = bcmul($lg->fee , $lg->total, 8 ); 
			$lg->totalfee =  bcsub($lg->total, $lg->fees, 8); 
			$token = $market->base;
			try{
				$adm_service =  $this->adm_service($token);
				}catch( \Exception $e){
				throw new \Exception(__('market.noAdminAccount') );
			}
			try{
				$service =  $token->services()->where('user_id', $user->id)->firstOrFail();
				}catch( \Exception $e){
				throw new \Exception(__('market.noUserAccount') );
			}
			// will the user take?
			
			$fitting =  Ledger::where([
					['pair','=', $market->pair],
					['type','=', 'buy'],
					['status','=', 'open']
				])
				->orderBy('price','desc')
				->where(function($query)use($limit, $stop ){ 
					$query->where(function($query)use($limit, $stop){//stop limit buys
						$query->where('order_type','stop_limit')
							  ->where('stop','<=',$limit)
							  ->where('price','>=',$limit);
					})
					->orWhere(function($query)use($limit, $stop){ //limit
							$query->where('triggered_type','limit')
								  ->where('price','>=',$limit);
						})
			
					->orWhere(function($query)use($limit, $stop){ //limit
						$query->where('order_type','limit')
							  ->where('price','>=',$limit);
					})
					->orWhere(function($query)use($limit, $stop){ //stop
						$query->where('order_type','stop')
							  ->where('stop','<=',$limit);
					})->orWhere('order_type','trailing_stop');
					
				});
				
		
			if($order_type == 'stop_limit'){ // if price is less greater than $limit and less tha Stop
				$selected = $fitting->whereBetween('price', [$limit, $stop])->get();
				if($selected->count() > 0 )
				$triggered_type = 'limit';
			}
			if($order_type == 'limit'||$order_type == 'fillOrKill'){ //matcch price greater then $limit
				$selected = $fitting -> where('price','>=', $limit )->get();
			}
			$trigger_price = NULL;
			if($order_type == 'stop'){ // check if there are prices less than stop is triggered
				$selected = $fitting -> where('price' ,'<=', $stop )->get();
				if($selected->count()>0){
					$triggered_type = 'market';
					$trigger_price = $selected->max('price');
					$order_type ='market';
				}
			}
			$lg->triggered_type = $triggered_type;
			$lg->trigger_price = $trigger_price;
			if($order_type =='market'|| $marginopen || $margincalled){ 
				$all = $fitting ->get();
				if($margincalled){ // force close postion
					$total = $margin->payback; // liquidation total
					$collected = 0;
					$items  =[];
					foreach ($all as $value){
						if($collected < $total){
							$collected += $value->totalunfilled;
							$items [] = $value;
							continue;
						}
						break;
					}
					$picked = collect($items);
					$mprice = $picked->min('price'); // price to guarantee liquidation
					$lg->qty = $margin->qty;
				}
				$total = $lg->qty;
				$collected = 0;
				$selected = [];
				foreach ($all as $value){
						if($collected < $total){
							$collected += $value->unfilled;
							$selected [] = $value;
							continue;
						}
						break;
					}
				$selected  = collect($selected );
				$limit = isset($mprice)? $mprice: $selected->min('price');
				if($limit == 0)
				$limit = $market->last;
				$lg->price = $limit;
				$lg->fee = $market->seller_fees/100;
				$lg->total = bcmul($limit,$lg->qty,8);
				$lg->fees = bcmul($lg->fee , $lg->total, 8 ); 
				$lg->totalfee =  bcsub($lg->total, $lg->fees, 8); 
			}
			$newprice = $selected->count()?$selected->min('price'):$lg->price;
		    // adjust market order prices to be the best asks.
			$mledger= Ledger::where([
					['pair','=', $market->pair],
					['type','=', 'buy'],
					['status','=', 'open'],
					['unfilled','>', 0]
				])->where(function($query){
					$query->where('order_type','market')
					->orWhere('triggered_type','market');
				});
			if($mledger->count()){
				$markets  = $mledger->get()->map(function($mkt)use($newprice){
					if($mkt->price < $newprice ){
						$mkt->price = $newprice;
						$mkt->unfilled = bcdiv($mkt->totalunfilled ,$newprice, 8);
						$mkt->save();
					}
					return $mkt;
				});
				$selected = $markets->concat($selected);
			}
			
			
			
			//reject unfit trailing stop orders
			if($selected->count()){
				$selected = $selected->reject(function($item)use($limit, $newprice, $lg){ 
					if($item->order_type =='stop'){
						$item->triggered_type ="market";
						$item->trigger_price = $lg->price;
						$item->save();
					}
					if($item->order_type =='stop_limit'){
						$item->triggered_type ="limit";
						$item->trigger_price = $lg->price;
						$item->save();
					}
					if($item->triggered_type == 'limit'||$item->triggered_type == 'market') return false;
					if($item->order_type != 'trailing_stop') return false;
					$trade = Trade::where([
						['pair','=', $item->pair],
						['status','=', 1],
						['created_at','>', $item->created_at]
					])->latest()->orderBy('price','asc')->first(); // lowest offer since
					if(is_null($trade ))return false;
					if( abs($trade->price - $limit ) < $item->stop ){
						$item->triggered_type ="market";
						$item->trigger_price = $lg->price;
						if($item->price < $newprice ){
							$item->price = $newprice;
							$item->unfilled = bcdiv($item->totalunfilled ,$newprice, 8);
						}
						$item->save();
						return false;
					}
					return true; 
				});
			}
			
			if($selected->count() && $selected->sum('qty') >= $qty){ // fillable
				$lg->maker = 0; 
				if($market->fee_type=="maker" ){
					$lg->totalfee = $total;
					$lg->fees = 0;
					$lg->fee = 0;
				}
			}else{
				if(($margin instanceof Margin && $margin->status =="called")|| $order_type == 'fillOrKill'){  // kill 
					throw new Exception(__('market.smallsize'));
				}
				$lg->maker = 1; 
				if($market->fee_type=="taker" ){
					$lg->totalfee = $total;
					$lg->fees = 0;
					$lg->fee = 0;
				}
			}
			
			if($order_type == 'trailing_stop'){  // maker // nop need to match orders
				$selected = collect([]);
				$lg->maker = 1; 
				if($market->fee_type=="taker" ){
					$lg->totalfee = $total;
					$lg->fees = 0;
					$lg->fee = 0;
				}
			}
			$marginType = $margin == "open"? "sell": false;
			if($margin instanceof Margin ){
				$marginType = $margin->id;
			}
			try{
				$tx = $this->service_transact($qty, $adm_service , $service ,__('market.Market').' Listing '.__('market.'.$type).' '.__('market.of').' '.$market->base->name,$ref, $marginType);
			}catch(\Exception $e){
				throw $e;
			}
			$lg->margin_id  = $tx->margin_id;
			$lg->service_tx_id  = $tx->id;
			$lg->service_id  = $service->id;
			$lg->save();
			if($margin instanceof Margin ){
				$margin->ledger_id = $lg->id;
				$margin->save();
			}
			$filled = 0;
			foreach($selected as $book){
				$filled  = bcadd($filled , $book->unfilled , 8);
				$unfilled = $book->unfilled;
				if(bccomp($filled , $lg->qty , 8) == 0){ //exact filled
					$this->executeTrade($lg, $book , $unfilled );
					if($book->buys()->count()){
						$book->filled  = $book->buys()->sum('qty');
						$book->unfilled  = 0 ;
						$book->totalfilled = ($book->filled / $book->qty)*$book->totalfee;
						$book->totalunfilled = number_format(($book->unfilled / $book->qty)*$book->totalfee,8);
						$book->status= 'closed';
						$book->active = 2;
					}
					$book->save();
					event(new  LedgerEvent($book));
					break;
				}if(bccomp($filled , $lg->qty , 8) == 1){ // excess
					$excess =  bcsub($filled,$lg->qty,8);
					$required = bcsub($book->unfilled , $excess, 8);
  					$this->executeTrade($lg, $book , $required );
					if($book->buys()->count()){
						$book->filled =  $book->buys()->sum('qty');
						$book->unfilled  = bcsub($book->unfilled , $required , 8 ); 
						$book->totalfilled = ($book->filled / $book->qty)*$book->totalfee;
						$book->totalunfilled = number_format(($book->unfilled / $book->qty)*$book->totalfee,8);
					}
					$book->save();
					event(new  LedgerEvent($book));
					break;
				}
				if(bccomp($filled , $lg->qty , 8) == -1){
					$this->executeTrade($lg, $book , $unfilled );
					if($book->buys()->count()){
						$book->filled  = $book->buys()->sum('qty');
						$book->unfilled  = 0 ;
						$book->totalfilled = number_format(($book->filled / $book->qty)*$book->totalfee,8);
						$book->totalunfilled = number_format(($book->unfilled / $book->qty)*$book->totalfee,8);
						$book->status= 'closed';
						$book->active = 2;
					}
					$book->save();
					event(new  LedgerEvent($book));
				}
			}
		
			$lg->filled = $lg->sales()->sum('qty');
			$lg->status= 'open';
			$lg->active = 1;
			if($lg->sales()->count()){
				$lg->unfilled = bcsub($lg->qty,$lg->filled,8);
				if($lg->unfilled < 0)
				Storage::disk('local')->put(str_random(8).$lg->type.'lg.txt', $lg->toJson().PHP_EOL.PHP_EOL.$selected->toJson().PHP_EOL.PHP_EOL.$lg->sales->toJson());
				$lg->totalfilled = number_format(($lg->filled / $lg->qty)*$lg->totalfee,8);
				$lg->totalunfilled = number_format(($lg->unfilled / $lg->qty)*$lg->totalfee,8);
				if($lg->unfilled <= 0){
					$lg->status= 'closed';
					$lg->active = 2;
				}
			}
			
			$lg->save();
			if(!is_null($lg->margin_id)&& $margin =="open"){ // open a margin positio 
				$margin = $lg->margin;
				$rqty = $margin->payback;
				$dist= bcmul($margin->payback, 15/100 , 8); // payback is in qty
				$margin->threshold = bcdiv($lg->totalfee, bcsub($margin->payback, $dist , 8), 8 );
				$margin->qty =  $lg->qty;
				$margin->total =  $lg->totalfee;
				$margin->price =  $price; //user set price
				$margin->save();
			}
			event(new  LedgerEvent($lg));
			if(!env('DEMO'))
			$lg->user->notify(new NewLedger($lg));
		}
		
		return $lg;

	}
	
	
	public function executeTrade($lg, $book , $qty ){
		
		// $margin =
		$market = $lg->market;
		$proft = $lg->type == "buy"? $lg->price - $book->price:$book->price - $lg->price;
		$trade = new Trade;
		$trade->buy_id = $lg->type =="buy"? $lg->id:$book->id;
		$trade->sell_id = $lg->type =="buy"? $book->id:$lg->id;
		$trade->market_id= $lg->market->id ;
		$trade->ledger_id = $lg->id;
		$trade->price = $lg->price;
		$trade->paid = $book->price;
		$trade->profit = $proft > 0 ? bcmul($proft,$qty,8):0;
		$trade->qty = $qty;
		$trade->reference = $lg->reference;
		$trade->maker = $lg->maker;
		$trade->pair = $lg->pair;
		$trade->type = $lg->type;
		$trade->status= 1;
		$trade->active=1;
		$trade->minimum= 0;
		//$trade->save();
		// update User balances
		$sellerMargin = false;
		$buyerMargin = false;
		$ref = $trade->reference;
		if($lg->type == "buy"){
			$sellToken = $lg->market->quote;
			$sellUser = $book->user;
			$sellType  = $book->type;
			if($book->margincall()->count()){
				$sellerMargin = $book->margincall;
			}
			$amount = bcmul($trade->qty , $book->price, 8 );
			$fee = bcmul($amount, $book->fee, 8);
			$sellAmount = bcsub($amount, $fee , 8);
			$trade->fee = $fee ;
			$trade->total = $amount;
			$trade->totalfee = $sellAmount;
			$buyToken = $lg->market->base;
			$buyUser = $lg->user;
			$buyType = $lg->type;
			$buyAmount =  $trade->qty;
			if($lg->margin()->count()){
				$buyerMargin = $lg->margin;
			}elseif($lg->margincall()->count()){
				$buyerMargin = $lg->margincall;
			}
			
			try{
				$sellService =  $sellToken->services()->where('user_id', $sellUser->id)->firstOrFail();
				$trade->quote_service_id =  $sellService->id;
				}catch( \Exception $e){
	
					throw new \Exception(__('market.noUserAccount') );
			
			}
			
			try{
				$buyService =  $buyToken->services()->where('user_id', $buyUser->id)->firstOrFail();
				$trade->base_service_id =  $buyService->id;
				}catch( \Exception $e){
					
				throw new \Exception(__('market.noUserAccount') );
			}
			
			
		}elseif($lg->type == "sell"){
			$sellToken = $lg->market->quote;
			$sellUser = $lg->user;
			$sellType = $lg->type;
			if($lg->margin()->count()){
				$sellerMargin = $lg->margincall;
			}elseif($lg->margincall()->count()){
				$sellerMargin = $lg->margincall;
			}
			$amount = bcmul($trade->qty , $lg->price, 8 );
			$fee = bcmul($amount, $lg->fee, 8);
			$sellAmount = bcsub($amount, $fee , 8);
			$trade->fee = $fee ;
			$trade->total = $amount;
			$trade->totalfee = $sellAmount;
			$buyToken = $lg->market->base;
			$buyUser = $book->user;
			$buyType = $book->type;
			if($book->margincall()->count()){ // cant have a margin
				$buyerMargin = $book->margincall;
			}
			$buyAmount =  $trade->qty;
			try{
				$sellService =  $sellToken->services()->where('user_id', $sellUser->id)->firstOrFail();
				$trade->base_service_id =  $sellService->id;
				}catch( \Exception $e){
				throw new \Exception(__('market.noUserAccount') );
			}
			try{
				$buyService = $buyToken->services()->where('user_id', $buyUser->id)->firstOrFail();
				$trade->quote_service_id =  $buyService->id;
				}catch( \Exception $e){
				throw new \Exception(__('market.noUserAccount') );
			}
		}else{
			throw new Exception('Unknow Trade Type '.$lg->type);
		}
		
		
		$trade->save();
		try{
			$adm_sell =  $this->adm_service($sellToken);
			}catch( \Exception $e){
			throw new \Exception(__('market.noAdminAccount') );
		}
		
		try{
			$adm_buy = $this->adm_service($buyToken);
			}catch( \Exception $e){
			throw new \Exception(__('market.noAdminAccount') );
		}
		
		//buyers
		
		
		try{
			$tx = $this->service_transact( $sellAmount,  $sellService , $adm_sell , __('market.Market').' '.__('market.'.$sellType  ).' '.__('market.of').' '.$lg->market->base->name , $ref ,$sellerMargin );
		}catch(\Exception $e){
			throw $e;
		}
		try{
			$tx = $this->service_transact($buyAmount, $buyService , $adm_buy  ,__('market.Market').' '.__('market.'.$buyType ).' '.__('market.of').' '.$lg->market->base->name,$ref, $buyerMargin);
		}catch(\Exception $e){
			throw $e;
		}
		return $trade;
	}
	
	
	public function removeLedger($ledger){
		if($ledger->margincall()->count()){ // margin ledger
			try{
				$this->liquidate($ledger);
			}catch(Exception $e){
				throw $e;
			}
		}else{
			if($ledger->type == 'sell'){
				$amt = $ledger->unfilled;
			}elseif($ledger->type == 'buy'){
				$amt = $ledger->unfilledtotal;
			}
			
			$token = $ledger->service->token;
			try{
				$adm =  $this->adm_service($token);
				}catch( \Exception $e){
				throw new \Exception(__('market.noAdminAccount') );
			}
			
			
			try{
				$tx = $this->service_transact( $amt, $ledger->service , $adm ,'DELETE: '.__('market.Market').' '.__('market.'.$ledger->type ).' '.__('market.of').' '.$token->name , $ledger->reference , false);
			}catch(\Exception $e){
				throw $e;
			}
			Ledger::destroy($ledger->id);
			return true;
		}
	}
	
	
	
	
    /**
     * @param $datas
     *
     * @return array
     */
    public function organizePairData($datas, $limit=999)
    {
        $ret = array();
        foreach ($datas as $data) {
            $ret[$data->market_id]['timestamp'][]   = $data->buckettime;
            $ret[$data->market_id]['date'][]   = gmdate("j-M-y", $data->buckettime);
            $ret[$data->market_id]['low'][]    = $data->low;
            $ret[$data->market_id]['high'][]   = $data->high;
            $ret[$data->market_id]['open'][]   = $data->open;
            $ret[$data->market_id]['close'][]  = $data->close;
            $ret[$data->market_id]['volume'][] = $data->basevolume;
			$ret[$data->market_id]['quotevolume'][] = $data->quotevolume;
        }
        foreach($ret as $ex => $opt) {
            foreach ($opt as $key => $rettemmp) {
                $ret[$ex][$key] = array_reverse($rettemmp);
                $ret[$ex][$key] = array_slice($ret[$ex][$key], 0, $limit, true);
            }
        }
        return $ret;
    }

    /** Get the Graph Data Using Timeslicing 
	
	 *  The time slicing queries in various databases are done differently.
	 *  none of these queries can be done through our eloquent models unfortunately.
	 *
     * @param string $pair
     * @param int    $limit
     * @param string $periodSize
     * @param bool   $dontFormat
     *
     * @return array
     */
    public function OHLCV($pair='BTC-USD', $limit=168, $periodSize='1m', $dontFormat=false)
    {
       
	   
        $connection_name = config('database.default');
        $key = 'recent::'.$pair.'::'.$limit."::$periodSize::$connection_name";
        if(\Cache::has($key)) {
            return \Cache::get($key);
        }

        $timeslice = 120;
        switch($periodSize) {
            case '1m':
                $timescale = '1';
                $timeslice = 60;
                break;
            case '5m':
                $timescale = 60*5;
                $timeslice = 1;
                break;
            case '10m':
                $timescale = 60*10; 
                $timeslice = 2;
                break;
            case '15m':
                $timescale = 60*15;
                $timeslice = 10;
                break;
            case '30m':
                $timescale =  60*30;
                $timeslice =60;
                break;
            case '1h':
                $timescale = 60*60;
                $timeslice = 60;
                break;
            case '4h':
                $timescale = 60*60*4;
                $timeslice = 60*2;
                break;
			case '12h':
                $timescale = 60*60*12;
                $timeslice = 60*20;
                break;
            case '1d':
                $timescale = 60*60*24;
                $timeslice =  60*30;
                break;
            case '7d':
                $timescale =  60*60*24*7; 
                $timeslice = 60*60*12;
                break;
			 case '31d':
                $timescale =  60*60*24*30; 
                $timeslice = 60*60*12;
                break;
			case '186d':
                $timeslice = 60*60*24;
                $timescale = 60*60*24*30*6;
                break;
        }
        $current_time = time();
        $offset = ($current_time - $timescale);
 		//$timeslice = 60;
       
        if ($connection_name == 'mysql') {
			$results = DB::select(DB::raw("
              SELECT 
                market_id,
				ROUND((CEILING(UNIX_TIMESTAMP(`created_at`) / $timeslice) * $timeslice)) AS buckettime,
                SUBSTRING_INDEX(GROUP_CONCAT(CAST(price AS CHAR) ORDER BY created_at), ',', 1 ) AS `open`,
                SUBSTRING_INDEX(GROUP_CONCAT(CAST(price AS CHAR) ORDER BY price DESC), ',', 1 ) AS `high`,
                SUBSTRING_INDEX(GROUP_CONCAT(CAST(price AS CHAR) ORDER BY price), ',', 1 ) AS `low`,
                SUBSTRING_INDEX(GROUP_CONCAT(CAST(price AS CHAR) ORDER BY created_at DESC), ',', 1 ) AS `close`,
                SUM(qty) AS basevolume, 
				SUM(total) AS quotevolume,
               	ROUND(AVG(price),8) AS avgprice,
                AVG(qty) AS avgvolume
				
              FROM trades
              WHERE pair = '$pair'
              AND UNIX_TIMESTAMP(`created_at`) > ($offset)
              GROUP BY market_id, buckettime 
              ORDER BY buckettime ASC
          "));
		} else {
			throw new \Exception(__('market.mysql_only'));
		}

        if ($dontFormat) {
            $ret = $results;
        } else {
            $ret = $this->organizePairData($results, $limit);
        }

        \Cache::put($key, $ret, 3);
        return $ret;
    }
	
	
}
