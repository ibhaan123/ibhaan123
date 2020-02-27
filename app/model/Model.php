<?php

namespace App\Models;


use Watson\Rememberable\Rememberable;
use Illuminate\Database\Eloquent\Model as Eloquent;

abstract class Model extends Eloquent
{
    use Rememberable;
	public function scopeActive($query)
    {
		return $query->where('active', 1);
    }
}
