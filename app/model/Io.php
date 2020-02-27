<?php

namespace App\Models;


use Illuminate\Database\Eloquent\SoftDeletes;

class Io extends Model
{
    use SoftDeletes;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'ios';

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
		'service_id', 
		'service_tx_id', 
		'user_id', 
		'order_id', 
		'token_id', 
		'order_id', 
		'status', 
		'type', 
		'fees', 
		'fees_value', 
		'fees_percent', 
		'message', 
		'amount', 
		'symbol'
	];

    public function service()
    {
        return $this->belongsTo('App\Models\Service', 'service_id', 'id');
    }
	
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
	
    public function token()
    {
        return $this->morphTo();
    }
    
}
