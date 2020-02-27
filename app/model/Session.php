<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Session extends Model
{ 
	
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sessions';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'id';

   
	public $incrementing=false;
	 
    protected $fillable = [ ];
	
	
	
	public function getPlatformAttribute(){
		$agent = new \Jenssegers\Agent\Agent();
		$agent->setUserAgent($this->user_agent);
		return $agent->platform();
	}
	
	public function getBrowserAttribute(){
		$agent = new \Jenssegers\Agent\Agent();
		$agent->setUserAgent($this->user_agent);
		return $agent->browser();
	}
	
	public function getDeviceAttribute(){
		$agent = new \Jenssegers\Agent\Agent();
		$agent->setUserAgent($this->user_agent);
		return $agent->device();
	}
	
	public function getLastAttribute(){
		return  \Carbon\Carbon::createFromTimestamp($this->last_activity);
	}
	
	
	public function user(){
		return $this->belongsTo(\App\Models\User::class, 'user_id','id');
	}
	
	 /**
     * Scope a query to only include active sessions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
		$active = now()->subMinutes(config('session.lifetime'))->timestamp;
        return $query->where('last_activity', '>=', $active);
    }

    
}
