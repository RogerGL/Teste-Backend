<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Vinkla\Hashids\Facades\Hashids;

class Redirect extends Model
{
    use SoftDeletes;

    protected $fillable = ['destination_url', 'status', 'code'];

    protected static function booted()
    {
        static::created(function ($redirect) {
            $redirect->code = Hashids::encode($redirect->id);
            $redirect->save();
        });
    }
}
