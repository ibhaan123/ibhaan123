<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cookie;
use Webpatser\Uuid\Uuid;
trait ReferralTrait
{
    public function getReferralLinkAttribute()
    {
		if(empty($this->affiliate_id)){
			$this->affiliate_id = Uuid::generate()->string;
			$this->save();
		}
        return url('/').'/?voucher='.$this->affiliate_id;
    }

    public static function scopeReferralExists(Builder $query, $referral)
    {
        return $query->whereAffiliateId($referral)->exists();
    }
	
	public function referrals(){
		return $this->hasMany(\App\Models\User::class, 'referred_by','affiliate_id' );
	}
	
	public function referrer(){
		return $this->hasOne(\App\Models\User::class, 'affiliate_id','referred_by' );
	}

    protected static function bootReferralTrait()
    {
        static::creating(function ($model) {
            if ($referredBy = Cookie::get('referral')) {
                $model->referred_by = $referredBy;
            }
            $model->affiliate_id = Uuid::generate()->string;
        });
    }

    
}
