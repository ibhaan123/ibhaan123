<?php

namespace App\Models;



class Service_tx extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'service_txs';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
		'user_id', 
		'token_id', 
		'token_type', 
		'service_id', 
		'account_id', 
		'margin', 
		'leverage',
		'amount',
		'type', 
		'description', 
		'status'];

    public function account()
    {
        return $this->belongsTo('App\Models\Account', 'account_id', 'id');
    }
	
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
	
	public function service()
    {
        return $this->belongsTo('App\Models\Service', 'service_id', 'id');
    }
	
    public function ledger()
    {
        return $this->hasOne('App\Models\Ledger', 'service_tx_id', 'id');
    }
	
	 public function io()
    {
        return $this->hasOne('App\Models\Io', 'service_tx_id', 'id');
    }
	
	public function token()
    {
        return $this->morphTo();
    }
	
	public function scopeCredit($query)
    {
        return $query->where('type','credit');
    }
	
	public function scopeDebit($query)
    {
        return $query->where('type','debit');
    }
	
}
