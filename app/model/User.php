<?php

namespace App\Models;
use Laravel\Passport\HasApiTokens;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use jeremykenedy\LaravelRoles\Traits\HasRoleAndPermission;
use Pdazcom\Referrals\Traits\ReferralsMember;
use Pdazcom\Referrals\ReferralLink;
use Pdazcom\Referrals\Models\ReferralProgram;
use Chat;
//use App\Traits\WalletTrait;

class User extends Authenticatable
{

    use HasRoleAndPermission, 
		Notifiable, 
		ReferralsMember, 
		SoftDeletes, 
		HasApiTokens;
	use \Watson\Rememberable\Rememberable;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';
	
	protected $casts =[
		'enable_twofa_sms'=>'boolean',
		'enable_twofa_email'=>'boolean',
		'activated'=>'boolean',
		'can_withdraw'=>'boolean',
	];
    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
	
	protected $hidden = ['password'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'activated',
		'phone_number',
		'twofa',
		'twofa_secret',
		'enable_twofa_sms',
		'enable_twofa_email',
		'phone_number',
		'country_id',
        'token',
        'signup_ip_address',
        'signup_confirmation_ip_address',
        'signup_sm_ip_address',
        'admin_ip_address',
        'updated_ip_address',
        'deleted_ip_address',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    
	
	 protected $visible = [
        'name',
        'first_name',
        'last_name',
		'phone_number',
		'created_at',
		'updated_at',
        'email',
		'id',
		'avatar',
    ];

    protected $dates = [
        'deleted_at',
		'created_at',
		'updated_at',
		'last_seen'
    ];
	
	 protected $appends = [
        'avatar',
    ];
	
	 /**
     * Route notifications for the Nexmo channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return string
     */
	public function routeNotificationForNexmo($notification)
    {
        return $this->phone_number;
    }

    /**
     * Build Social Relationships.
     *
     * @var array
     */
    public function social()
    {
        return $this->hasMany('App\Models\Social');
    }
	
	public function transactions()
    {
        return $this->hasMany('App\Models\Transaction');
    }
	public function wallets()
    {
        return $this->hasMany('App\Models\Wallet');
    }
	public function coins()
    {
        return $this->hasMany('App\Models\Token');
    }
	
	public function bans()
    {
        return $this->hasMany('App\Models\Ban');
    }
		
	public function accounts()
    {
        return $this->hasMany(\App\Models\Account::class);
    }
	
	public function account()
    {
        return $this->accounts()->first();
    }
	
	public function getAccountAttribute()
    {
        return $this->account();
    }
	
	public function alerts(){
		return $this->hasMany('App\Models\Alert');
	}
	
	
	
	public function services(){
		return $this->hasMany('App\Models\Service')->with('wallet');
	}
	
	public function service_txs(){
		return $this->hasMany('App\Models\Service_tx');
	}
	
	public function trades(){
		return $this->hasMany('App\Models\Trade');
	}
	
	public function ads(){
		return $this->hasMany('App\Models\Ad');
	}
	public function feedback(){
		return $this->hasMany('App\Models\Feedback');
	}
	
	

    /**
     * User Profile Relationships.
     *
     * @var array
     */
    public function profile()
    {
        return $this->hasOne('App\Models\Profile');
    }
	
	
	 /**
     * User Country Relationships.
     *
     * @var array
     */
    public function country()
    {
        return $this->belongsTo(\App\Models\Country::class,'country_id','id');
    }
	
	public function notifs()
    {
        return $this->hasMany(\App\Models\Message::class,'user_id','id')->where('is_seen', 0);
    }
	
	/**
     * User KYC Verifcation Relationships.
     *
     * @var array
     */
    public function kyc()
    {
        return $this->hasOne('App\Models\Kyc');
    }
	
	/**
     * User Sessions Relationships.
     *
     * @var array
     */
	
	public function sessions(){
		return $this->hasMany(\App\Models\Session::class, 'user_id','id')->active();
	}
	
	/**
     * User Activities Relationships.
     *
     * @var array
     */
	
	public function activities(){
		return $this->hasMany(\App\Models\Activity::class, 'userId','id');
	}
	
	
	public function isOnline()
	{
		return \Cache::has('user-is-online-' . $this->id);
	}
	
	public function isOffline()
	{
		return !\Cache::has('user-is-online-' . $this->id);
	}
	

    // User Profile Setup - SHould move these to a trait or interface...

    public function profiles()
    {
        return $this->belongsToMany('App\Models\Profile')->withTimestamps();
    }

    public function hasProfile($name)
    {
        foreach ($this->profiles as $profile) {
            if ($profile->name == $name) {
                return true;
            }
        }

        return false;
    }

    public function assignProfile($profile)
    {
        return $this->profiles()->attach($profile);
    }
	
	

    public function removeProfile($profile)
    {
        return $this->profiles()->detach($profile);
    }
	
	
	public function getPercentFeedbackAttribute(){
		
		return \Cache::remember('feedback'.$this->id, 5 , function(){
			$negative = $this->feedback()->negative()->count();
			$all = $this->feedback->count();
			return  $all > 0?(($all-$negative)/$all)*100:100;
		} );
		
	}
	
	
	
	
	public function getAvatarAttribute(){
		return route('image',empty($this->profile->avatar)?'avatar.jpg':$this->profile->avatar).'@80x80';
	}
	
	public function getRefAttribute(){
		$program = ReferralProgram::where('name','tokensale');
		$ref  = "";
		if(!empty($program)){
			$ref  = ReferralLink::getReferral($this, $program);
			if(is_null($ref )){
				ReferralLink::create(['user_id' =>$this->id, 'referral_program_id' => $program->id]);
				$ref  = ReferralLink::getReferral($this, $program);
			}
		}
		return $ref;
	}
	
}
