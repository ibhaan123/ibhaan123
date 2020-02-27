<?php


namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Scope;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Request;
use Illuminate\Database\Eloquent\Model;
class ActiveScope implements Scope
{
    
    public function apply(Builder $builder, Model $model)
    {
        if (auth()->user() && auth()->user()->hasRole('admin')) {
            return $builder;
        }
        
        return $builder->where('active', 1);
    }
}
