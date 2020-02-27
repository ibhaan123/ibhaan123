<?php

namespace App\Models;


use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
    use SoftDeletes;
	
	public static function boot()
	{
		parent::boot();
		static::creating(function($item)
		{
				$characters = 'ABCDEFGHJKMNPQRSTUVWXYZ';
				$pin =   mt_rand(1000000, 9999999)
					   . mt_rand(1000000, 9999999)
					   . $characters[rand(0, strlen($characters) - 1)];
				$item->code = str_shuffle(str_replace('0','8',$pin));			
				$item->serial = str_random(18);
		});
	}
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'vouchers';

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
		'value', 
		'status', 
	];
	
	public function token(){
		return $this->morphTo();
	}
	
	public function user(){
		return $this->belongsTo(\App\Models\User::class);
	}
	
}
