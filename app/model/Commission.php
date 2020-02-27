<?php

namespace App\Models;


use Illuminate\Database\Eloquent\SoftDeletes;

class Commission extends Model
{
    use SoftDeletes;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'commissions';

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
		'icosale_id', 
		'user_id', 
		'token_id', 
		'status', 
		'message', 
		'amount', 
		'symbol'
	];

    public function sale()
    {
        return $this->belongsTo('App\Models\Icosale', 'icosale_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
    public function token()
    {
        return $this->belongsTo('App\Models\Token', 'token_id', 'id');
    }
    
}
