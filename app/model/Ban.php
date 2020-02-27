<?php

namespace App\Models;


use Illuminate\Database\Eloquent\SoftDeletes;

class Ban extends Model
{
	
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'bans';

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
	
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
		'chat',
		'login',
		'market',
		'wallet'
    ];

    

	
	protected $hidden = [
        'user_id',
    ];
	
	
    
	 public function scopeChat($query)
    {
        return $query->where('chat',1);
    }
	 public function scopeWallet($query)
    {
        return $query->where('wallet',1);
    }
	public function scopeLogin($query)
    {
        return $query->where('login',1);
    }
	public function scopeMarket($query)
    {
        return $query->where('market',1);
    }
	

   
}
