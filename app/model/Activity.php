<?php

namespace App\Models;

use jeremykenedy\LaravelLogger\App\Models\Activity as Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;
class Activity extends Model
{
	
	/**
     * Fillable fields for a Profile.
     *
     * @var array
     */
    protected $fillable = [
        'description',
        'userType',
        'userId',
        'route',
		'subject_id',
		'subject_type',
        'ipAddress',
        'userAgent',
        'locale',
        'referer',
        'methodType',
    ];
	
    
    public function subject(): MorphTo
    {
        return $this->morphTo()->withTrashed();
    }
	
	 /**
     * Scope a query to only include activities for a given subject.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Database\Eloquent\Model $subject
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForSubject(Builder $query, Model $subject): Builder
    {
        return $query
            ->where('subject_type', $subject->getMorphClass())
            ->where('subject_id', $subject->getKey());
    }
    
}
