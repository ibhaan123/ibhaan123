<?php

namespace App\Models;


//use Illuminate\Database\Eloquent\SoftDeletes;


class Country extends Model
{
	
    //use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'countries';

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
		'countries_name',
		'countries_iso',
		'currency',
		'currency_name',
		'exchange_rate',
		'symbol',
		'decimal_digits',
		'rounding',
		
	];


     protected $appends =[
		//'gateways'
	];     
    

    protected $dates = [
        'deleted_at',
    ];

	public function scopeCurrency($query, $currency)
    {
        return $query->where('symbol',$currency);
    }
	public function scopeSite($query)
    {
        return $query->where('symbol',setting('siteCurrency','USD'));
    }
	public function getDecimalsAttribute()
    {
        return $this->decimal_digits;
    }

    public function orders()
    {
		return $this->morphMany(\App\Models\Order::class, 'token');
    }
	
	
	public function services()
    {
        return $this->morphMany(\App\Models\Service::class, 'token');
    }
	
	
	
	
	
	public function getTypeAttribute()
    {
       return 'App\\Models\\Country';
    }
}
