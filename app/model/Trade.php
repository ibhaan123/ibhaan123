<?php

namespace App\Models;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trade extends Model
{
    use SoftDeletes,HasUuid;
	
	 /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'active' => 'boolean',
    ];
	
	 protected $dates = [
        'expires_at',
    ];
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'trades';

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
		'ad_id', 
		'user_id', 
		'ad_user_id', 
		'chat_id', 
		'country_id',
		'price', 
		'qty', 
		'total', 
		'token', 
		'type', 
		'status', 
		'active', 
		'account_name', 
		'account'
	];

    public function ad()
    {
        return $this->belongsTo('App\Models\Ad', 'ad_id', 'id');
    }
	
	public function dispute()
    {
        return $this->hasOne('App\Models\Dispute', 'trade_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
    public function feedback()
    {
        return $this->hasMAny('App\Models\Feedback', 'trade_id', 'id');
    }
	public function trader()
    {
        return $this->belongsTo('App\Models\User', 'ad_user_id', 'id');
    }
	public function token()
    {
        return $this->morphTo();
    }
	
	 public function country()
    {
        return $this->belongsTo('App\Models\Country', 'country_id', 'id');
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
	
	public function scopePaid($query)
    {
        return $query->where('status','paid');
    }
	
	public function scopeCancelled($query)
    {
        return $query->where('status','cancelled');
    }
	
	public function scopeLocked($query)
    {
        return $query->where('status','locked');
    }
	
	public function scopeRejected($query)
    {
        return $query->where('status','rejected');
    }
	
	public function scopeSuccess($query)
    {
        return $query->where('status','success');
    }
	
	public function scopeDisputed($query)
    {
        return $query->where('status','disputed');
    }
	
	public function scopeIgnored($query)
    {
        return $query->where('status','ignored');
    }

	
	public function getIsTraderAttribute(){
		return $this->ad_user_id == auth()->user()->id;
	}
	
	public function getBadgeAttribute(){
		$status= $this->statusMatrix();
		if(isset($status[$this->status])){
			$bg = $status[$this->status]; 
			return '<span data-toggle="tooltip" title="'.$bg['tip'].'" class="badge badge-'.$bg['badge'].' bg-'.$bg['badge'].'">'.$this->status.'</span>';
		}
		return 'unknown';
	}
	
	public function statusMatrix(){
		$seller = $this->seller_statusMatrix();
		$buyer = $this->buyer_statusMatrix();
		return [
				'open'=>[
					'tip'=>$this->type=='sell'?$seller->open->tip:$buyer->open->tip,
					'badge'=>__('ads.open.badge'),
					'cancel'=> true,
					'dispute'=> false,
					],
				'paid'=>[
					'tip'=>$this->type=='sell'?$seller->paid->tip:$buyer->paid->tip,
					'badge'=>__('ads.paid.badge'),
					'cancel'=> false,
					'dispute'=> true,
					],
				'cancelled'=>[
					'tip'=>$this->type=='sell'?$seller->cancelled->tip:$buyer->cancelled->tip,
					'badge'=>__('ads.cancelled.badge'),
					'cancel'=> false,
					'dispute'=> false,
					],
				'closed'=> [
					'tip'=>$this->type=='sell'?$seller->closed->tip:$buyer->closed->tip,
					'badge'=>__('ads.closed.badge'),
					'cancel'=> false,
					'dispute'=> false,
					],
				'locked'=> [
					'tip'=> $this->type=='sell'?$seller->locked->tip:$buyer->locked->tip, 
					'cancel'=> false,
					'dispute'=>false,
					'badge'=>__('ads.locked.badge'),
					], 
				'pending'=> [
					'tip'=>$this->type=='sell'?$seller->pending->tip:$buyer->pending->tip,
					'badge'=>__('ads.pending.badge'),
					'badge'=>'secondary',
					'cancel'=> true,
					'dispute'=> false,
					],  // 
				'rejected'=> [
					'tip'=>$this->type=='sell'?$seller->rejected->tip:$buyer->rejected->tip,
					'badge'=>__('ads.rejected.badge'),
					'cancel'=> false,
					'dispute'=> false,
					],   
				'success'=> [
					'tip'=>$this->type=='sell'?$seller->success->tip:$buyer->success->tip,
					'badge'=>__('ads.success.badge'),
					'cancel'=> false,
					'dispute'=> false,
					],
				'disputed'=>[
					'tip'=>$this->type=='sell'?$seller->disputed->tip:$buyer->disputed->tip,
					'cancel'=> false,
					'dispute'=> false,
					'badge'=>__('ads.disputed.badge'),
					],// issue has been raised.
				'ignored'=>[
					'tip'=>$this->type=='sell'?$seller->ignored->tip:$buyer->ignored->tip,
					'badge'=>__('ads.ignored.badge'),
					'cancel'=> true,
					'dispute'=> false,
				]
		];
	}
	
	public function seller_statusMatrix(){
		return json_decode(json_encode( [
				'open'=>[
					'tip'=>$this->isTrader?__('ads.open.st_tip'):__('ads.open.bu_tip'),
					 
					],
				'paid'=>[
					'tip'=>$this->isTrader?__('ads.paid.st_tip'):__('ads.paid.bu_tip'),
					 
					],
				'cancelled'=>[
					'tip'=>$this->isTrader?__('ads.cancelled.st_tip'):__('ads.cancelled.bu_tip'),
					 
					],
				'closed'=> [
					'tip'=>$this->isTrader?__('ads.closed.st_tip'):__('ads.closed.bu_tip'),
					 
					],
				'locked'=> [
					'tip'=> $this->isTrader?__('ads.locked.st_tip'):__('ads.locked.bu_tip'),
					 
					], 
				'pending'=> [
					'tip'=>$this->isTrader?__('ads.pending.st_tip'):__('ads.pending.bu_tip'),
					 
					],  // 
				'rejected'=> [
					'tip'=>$this->isTrader?__('ads.rejected.st_tip'):__('ads.rejected.bu_tip'),
					 
					],   
				'success'=> [
					'tip'=>$this->isTrader?__('ads.success.st_tip'):__('ads.success.bu_tip'),
					 
					],
				'disputed'=>[
					'tip'=>$this->isTrader?__('ads.disputed.t_tip'):__('ads.disputed.bu_tip'),
					 
					],// issue has been raised.
				'ignored'=>[
					'tip'=>$this->isTrader?__('ads.ignored.st_tip'):__('ads.ignored.bu_tip'),
					 
				]
		]));
	}
	
	public function buyer_statusMatrix(){
		return json_decode(json_encode( [
				'open'=>[
					'tip'=>$this->isTrader?__('ads.open.bt_tip'):__('ads.open.su_tip'),
					 
					],
				'paid'=>[
					'tip'=>$this->isTrader?__('ads.paid.bt_tip'):__('ads.paid.su_tip'),
					 
					],
				'cancelled'=>[
					'tip'=>$this->isTrader?__('ads.cancelled.bt_tip'):__('ads.cancelled.su_tip'),
					 
					],
				'closed'=> [
					'tip'=>$this->isTrader?__('ads.closed.bt_tip'):__('ads.closed.su_tip'),
					 
					],
				'locked'=> [
					'tip'=>($this->isTrader?__('ads.locked.bt_tip'):__('ads.locked.su_tip')),
					 
					], 
				'pending'=> [
					'tip'=>$this->isTrader?__('ads.pending.bt_tip'):__('ads.pending.su_tip'),
					 
					],  // 
				'rejected'=> [
					'tip'=>$this->isTrader?__('ads.rejected.bt_tip'):__('ads.rejected.su_tip'),
					 
					],   
				'success'=> [
					'tip'=>$this->isTrader?__('ads.success.bt_tip'):__('ads.success.su_tip'),
					 
					],
				'disputed'=>[
					'tip'=>$this->isTrader?__('ads.disputed.bt_tip'):__('ads.disputed.su_tip'),
					 
					],// issue has been raised.
				'ignored'=>[
					'tip'=>$this->isTrader?__('ads.ignored.bt_tip'):__('ads.ignored.su_tip'),
				 
				]
		]));
	}
	
	

	
	public function getAlertAttribute(){
		$status = $this->statusMatrix();
		if(isset($status[$this->status])){
			$bg = $status[$this->status];
			$html='
			<div class="alert alert-'.$bg['badge'].' alert-dismissible">
			  <button data-dismiss="alert" class="close"></button>
			  <h4>'.__('ads.ad_status').': '.strtoupper($this->status).'</h4>
			  <p>
				'.$bg['tip'].'
			  </p>';
			  
			  
			 if($bg['cancel']||$bg['dispute'])
			 	$html .='<div class="btn-list">';
			 if($this->status =='pending' && $this->isTrader){
				 $html .='<a class="ajax_link authorize" href="'.route('trades.update',$this->id).'" data-_method="PUT" data-action="locked"><button class="btn btn-secondary" type="button">'.__('ads.accept_trade').'</button></a>';
			 	 $html .='<a class="ajax_link authorize" href="'.route('trades.update',$this->id).'" data-_method="PUT" data-action="rejected"><button class="btn btn-danger reject" type="button">'.__('ads.reject_trade').'</button><a>';
			 }elseif($bg['cancel'])
			 	$html .='<a class="ajax_link authorize" href="'.route('trades.update',$this->id).'" data-_method="PUT" data-action="cancelled"><button class="btn btn-danger cancel" type="button">'.__('ads.cancel_trade').'</button></a>';
			 if($bg['dispute'])
			 	$html .='<a class="ajax_link authorize" href="'.route('trades.update',$this->id).'" data-_method="PUT" data-action="disputed"><button class="btn btn-secondary dispute" type="button">'.__('ads.dispute_trade').'</button></a>';
			 
			 if($bg['cancel']||$bg['dispute']||($this->status =='open' && $this->isTrader))
			 	$html .='</div>';
			$html .='</div>';
			return $html;
		}
		
		return null;
	}
	
	public function getHeadAttribute(){
		$colour =$this->escrow?'text-green':'text-red';
		$escrow =  $this->escrow?$this->escrow:'0.000000';
		return '<h3 class="card-title '.$colour.' ">ESCROW : '.$escrow.$this->token->symbol.'</h3>
			<span  class="ml-2 mr-2">Expires:</span><span  datetime="'.$this->expires_at->toIso8601ZuluString().'" class="timeago">'.$this->expires_at.'</span>
			<div class="card-options">
			'.$this->badge .'
			</div>';
	}
	

    
}
