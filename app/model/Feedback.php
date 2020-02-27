<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LoggerTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class Feedback extends Model
{
    use SoftDeletes;
    
	use LoggerTrait;
	/**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'feedbacks';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'id';
	protected $casts = [
		'active'=>'boolean'
	];

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
		'user_id', 
		'rater_id', 
		'trade_id',
		'feedback_id', 
		'message', 
		'rating', 
		'feedback', 
		'active'
	];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }
    public function rater()
    {
        return $this->belongsTo(\App\Models\User::class, 'rater_id', 'id');
    }
    public function trade()
    {
        return $this->belongsTo(\App\Models\Trade::class, 'trade_id', 'id');
    }
	public function response()
    {
        return $this->hasMAny(\App\Models\Feedback::class, 'feedback_id', 'id');
    }
	
	public function scopeNegative($query)
    {
        return $query->where('feedback' , 'negative');
    }
	public function scopePositive($query)
    {
        return $query->where('feedback' , 'positive');
    }
    
}
