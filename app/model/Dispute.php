<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Dispute extends Model
{
    use SoftDeletes;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'disputes';

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
		'trade_id', 
		'ad_id', 
		'won', 
		'won1', 
		'settled', 
		'message', 
		'status', 
		'active'
	];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
    public function trade()
    {
        return $this->belongsTo('App\Models\Trade', 'trade_id', 'id');
    }
    public function ad()
    {
        return $this->belongsTo('App\Models\Ad', 'ad_id', 'id');
    }
    public function winner()
    {
        return $this->belongsTo('App\Models\User', 'won', 'id');
    }
    public function winner1()
    {
        return $this->belongsTo('App\Models\User', 'won1', 'id');
    }
    public function settler()
    {
        return $this->belongsTo('App\Models\User', 'settled', 'id');
    }
	public function loser()
    {
        return $this->belongsTo('App\Models\User', 'loser', 'id');
    }
    
}
