<?php

namespace App\Models;


use Illuminate\Database\Eloquent\SoftDeletes;

class Kyc extends Model
{
    use SoftDeletes;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'kycs';

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
		'user_id', 
		'image', 
		'id_card', 
		'passport', 
		'pdf',
		'status',
		'image_message', 
		'id_card_message', 
		'passport_message', 
		'pdf_message'
		];
	 protected $visible = [
		'user_id', 
		'image', 
		'id_card', 
		'passport', 
		'pdf',
		'status',
		'created_at',
		'image_message', 
		'id_card_message', 
		'passport_message', 
		'pdf_message'
		];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
	
	public function getbadgeAttribute(){
		$colour = ['primary','success','danger','warning','primary','default'];
		$status = ['Pending','Verified','Rejected','Revision','Unverified','Accepted'];
		return '<span class="badge badge-'.$colour[$this->status].'">'.$status[$this->status].'</span>';
	}
	
    
}
