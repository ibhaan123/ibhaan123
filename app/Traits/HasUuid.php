<?php 
namespace App\Traits;
use Webpatser\Uuid\Uuid;
trait HasUuid
{

    /**
     * Boot function from laravel.
     */
    protected static function bootHasUuid()
    {
        static::creating(function ($model) {
            $model->uuid = Uuid::generate()->string;
        });
    }
}