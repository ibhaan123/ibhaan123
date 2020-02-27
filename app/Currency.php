<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $guarded = [];
    protected $table = "currencies";

    protected $fillable = [
        'name', 'symbol', 'price', 'exchange', 'sell', 'buy','payment_id','available_balance','image','is_coin','status'
    ];
}   