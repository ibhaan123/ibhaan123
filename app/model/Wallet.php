<?php

namespace App\Models;


use Illuminate\Database\Eloquent\SoftDeletes;

class Wallet extends Model
{
	
	
    use SoftDeletes;
	use \App\Traits\BitcoinTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'wallets';

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
	
	protected $hidden = [
		'deleted_at',
		'xpub',
		'xpriv',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
		'account_id',
		'token_id',
		'token_type',
		'balance'
    ];
	
	protected $visible = [
		'id',
        'user_id',
		'account_id',
		'token_id',
		'balance',
		'symbol',
		'name',
		'decimals',
		'freeAddress',
		'siteBalance',
		'totalSent',
		'totalRecieved',
    ];
	protected $appends = [
		'freeAddress',
		'siteBalance',
		'totalSent',
		'symbol',
		'name',
		'decimals'
    ];

    

    protected $dates = [
        'deleted_at',
    ];
	
	
    /**
     * Build account Relationships.
     *
     * @var array
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
	
	public function transactions()
    {
        return $this->hasMany(\App\Models\Transaction::class, 'wallet_id');
    }
	public function addresses()
    {
        return $this->hasMany(\App\Models\Address::class, 'wallet_id');
    }
	
	public function token()
    {
        return $this->belongsTo(\App\Models\Token::class, 'token_id');
    }
	
	public function account()
    {
        return $this->belongsTo(\App\Models\Account::class, 'account_id');
    }
	
	public function services()
    {
        return $this->hasMany(\App\Models\Service::class, 'wallet_id');
    }
	
	 public function scopeOfToken($query, $tk_id)
    {
        return $query->where('token_id',$tk_id)->where('token_type','App\\Models\\Token');
    }
	
	 public function scopeOfAccount($query, $acc_id)
    {
        return $query->where('account_id',$acc_id);
    }
	
	public function getFreeAddressAttribute(){
		if ($this->token->family == 'ethereum')
		return $this->account->account;
		$address = \App\Models\Address::where([['type','=','external'],['wallet_id','=',$this->id],['active','=',1]])->first();
		if(empty($address)){
			$address = $this->coin_deriveAddress($this);
		}
		return $address->address;

	} 
	
	public function getSiteBalanceAttribute(){
		$country = \App\Models\Country::where('symbol',setting('siteCurrency','USD'))->first();
		return  number_format($this->token->price*$this->balance,$country->decimals);
	}
	
	public function getTotalSentAttribute(){
		return  number_format($this->transactions()->where('type','debit')->sum('amount'),8);
	}
	public function getTotalRecievedAttribute(){
		return  number_format($this->transactions()->where('type','credit')->sum('amount'),8);
	}
	
	public function getSymbolAttribute(){
		
		return \Cache::remember($this->token->name.'_symbol', 30, function(){
			return $this->token->symbol;
		}) ;
	}
	public function getDecimalsAttribute(){
		return \Cache::remember($this->token->name.'_decimals', 30, function(){
			return $this->token->decimals;
		}) ;
	}
	public function getNameAttribute(){
		return \Cache::remember($this->token->name."_name", 30, function(){
			return $this->token->name;
		}) ;
	}
	
	public function getServiceBalanceAttribute(){
		return $this->services()->sum('balance');
	}
	

   
}
