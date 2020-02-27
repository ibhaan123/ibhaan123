<?php
namespace App\Traits;
use App\Observers\LogObserver;
use Illuminate\Database\Eloquent\Relations\MorphMany;
trait LoggerTrait
{
    public static function bootLoggerTrait()
    {
        static::observe(new LogObserver);
    }
	
	public function activity(): MorphMany
    {
        return $this->morphMany(\App\Models\Activity::class, 'subject');
    }
	
}