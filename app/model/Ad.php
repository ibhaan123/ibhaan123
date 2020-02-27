<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasUuid;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;

class Ad extends Model
{
    use SoftDeletes,SpatialTrait,HasUuid;
	
	/**
 *  Setup model event hooks
 */
	
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'ads';

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
		'verified_phone' => 'boolean',
		'verified_id' => 'boolean',
    ];
	 /**
    * The location Fields.
    *
    * @var string
    */
	protected $spatialFields = [
        'location',
        'area'
    ];

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
		'user_id', 
		'country_id', 
		'from_symbol', 
		'to_symbol', 
		'rate', 
		'token_id',
		'token_type', 
		'location', 
		'area', 
		'overhead', 
		'city', 
		'slug', 
		'min',
		'max', 
		'min_vol', 
		'min_count', 
		'verified_phone',
		'verified_id', 
		'type',
		'method', 
		'custom_method',
		'custom_type',
		'instructions', 
		'account', 
		'status', 
		'active'
	];

    public function country()
    {
        return $this->belongsTo('App\Models\Country', 'country_id', 'id');
    }
	
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
	
	
	
    public function token()
    {
        return $this->belongsTo('App\Models\Token', 'token_id', 'id');
    }
	
	 public function scopeOpen($query)
    {
        return $query->where('status','open');
    }
	public function scopeClosed($query)
    {
        return $query->where('status','closed');
    }
	public function scopePending($query)
    {
        return $query->where('status','pending');
    }
	
	public function scopeBuy($query)
    {
        return $query->where('type','buy');
    }
	public function scopeSell($query)
    {
        return $query->where('type','sell');
    }
	
	
	
}
