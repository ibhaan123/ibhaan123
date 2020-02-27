<?php

namespace App\Models;


use jeremykenedy\LaravelRoles\Models\Role;

class Service extends Model
{
	public static function boot()
	{
		parent::boot();
		static::creating(function($item)
		{
				$characters = 'ABCDEFGHJKMNPQRSTUVWXYZ';
				$pin =   mt_rand(1000000, 9999999)
					   . mt_rand(1000000, 9999999)
					   . $characters[rand(0, strlen($characters) - 1)];
				$item->number = str_shuffle($pin);
		});
	}

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'services';

    /**
    * The database primary key value.
    *
    * @var string
    */
	
    protected $primaryKey = 'id';
	 /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'active' => 'boolean',
    ];
    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
		'user_id', 
		'token_id', 
		'token_type', 
		'account_id',
		'wallet_id',
		'address', 
		'margin', 
		'leverage',
		'collateral',
		'balance', 
		'credit', 
		'status'
		];
	protected $visible = [
		'id', 
		'balance',
		'token_id', 
		'token_type',  
		'status',
		'address',
		'number',
		'totalDeposit', 
		'totalWithdraw',
		'symbol',
		'wallet',
		'token'
		];
	protected $appends = [
		'address',
		'totalDeposit', 
		'symbol',
		'siteBalance',
		];
	

	

    public function account()
    {
        return $this->belongsTo('App\Models\Account', 'account_id', 'id');
    }
	
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
	 
	public function wallet()
    {
        return $this->hasOne('App\Models\Wallet', 'token_id', 'token_id');
    }
	
	public function adminWallet(){
		return $this->hasOne('App\Models\Wallet', 'wallet_id', 'id');
	}
	
	public function order()
    {
        return $this->hasOne(\App\Models\Order::class)
					->where('status','PARTIAL')
					->where('item','market_deposit')
					->where('type','market');
    }
	
	public function api_orders()
    {
        return $this->hasMany(\App\Models\Order::class)->where('type','api');
    }
	
	public function service_txs()
    {
        return $this->hasMany('App\Models\Service_tx', 'service_id', 'id');
    }
	
	public function token()
    {
        return $this->morphTo();
    }
	
	public function getAddressAttribute()
    {
		$order = \App\Models\Order::where(
		[
			['service_id','=', $this->id],
			['item','=','market_deposit'],
			['type','=','market'],
			['status','=', 'PARTIAL'],
			
		])->first();
		if($order)
		return $order->address;
		//if($this->token instanceof \App\Models\Country)
		//return $this->number;
		$token = $this->token;
		$adminRole = Role::where('slug','admin')->firstOrFail();
		$admin = $adminRole->users()->firstOrFail();
		$account = $admin->account; 
		$wm = new \App\Logic\WalletManager;
		$order = new \App\Models\Order;
		if($this->token_type == 'App\\Models\\Token'){
			if(is_null($this->wallet_id)){
				if($this->token->family=='bitFamily'){
					$wallet = \App\Models\Wallet::where('token_id',$token->id)
												->where('user_id',$admin->id)
												->first();
					$wallet = $wallet??$wm->coin_create_wallet(  $account , env('CRYPTO','password') , $token);
				}else{
					$wallet = \App\Models\Wallet::firstOrCreate([
						'token_id'=>$token->id,
						'user_id'=>$admin->id 
					],[
						'account_id'=>$account->id,
						'token_type'=>$token->type,
					]);
				}
				$this->wallet_id = $wallet->id;
				$this->save();
			}
			if($this->token->family=='bitFamily'){
				$wallet = $wallet??$this->wallet;
				$add = $wm->coin_deriveAddress($wallet);
				$order->idx = $add->idx;
				$order->address = $add->address;
				$order->start = $wm->api($this->token->symbol)->currentBlock()->blocks;
			}else{
				$wallet = $wallet??$this->wallet;
				$singleschema = env('USE_SINGLE_ETHEREUM_ACCOUNT_PER_USER',false);
				$index = $singleschema?$this->user_id:$account->orders()->count()+1;
				list($order->idx,$order->address) = $wm->deriveAddress($wallet->account, $index);
				$order->start = $wm->web3()->eth->blockNumber()->getInt();
			}
		}else{
			
			$order->idx = NULL;
			$order->address = $this->number;
		}
		$order->item = 'market_deposit'; 
		//$order->gateway = NULL; 
		$order->reference = md5(time().str_random('100'));
		$order->item_url = route('services.show',$this->token->symbol );
		$order->expires_at = \Carbon\Carbon::now()->addMinutes((int)1410000);
		$order->account_id = $account->id; // seller
		$order->user_id = $this->user_id; // user 
		$order->wallet_id = $this->wallet_id; // user 
		$order->service_id = $this->id; // user 
		$order->pair_id = NULL;
		$order->type='market';
		$order->token_type = $this->token_type;
		$order->rate_id = NULL;
		$order->token_id = $this->token_id;
		$order->item_data = [];
		$order->amount = 0;
		$order->fees = 0;
		$order->status = 'partial';
		$order->symbol = $this->token->symbol;
		$order->save();
		return $order->address;
    }
	
	public function getTotalDepositAttribute(){
		return number_format($this->service_txs()->where('type','credit')->where('status',1)->sum('amount'),4);
	}
	
	public function getTotalWithdrawAttribute(){
		return number_format($this->service_txs()->where('type','debit')->where('status',1)->sum('amount'),4);
	}
	public function getSymbolAttribute(){
		return $this->token->symbol;
	}
	
	public function getSiteBalanceAttribute(){
		$country = \App\Models\Country::where('symbol',setting('siteCurrency','USD'))->first();
		return  number_format($this->token->price*$this->balance,$country->decimals);
	}
	
	
	
	

	
	
    
}
