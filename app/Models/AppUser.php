<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AppUser extends Model
{
    //
    public $incrementing = false;  // This will prevent  returning "0" when created
    //public $timestamps = false; // Set to false to disable timestamps
    protected $primaryKey = 'user_id';
    protected $keyType ='string';

    protected $casts = [
        // 'user_id' => 'uuid',
        'user_id' => 'string',
    ];

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'type',
        'open_id',
        'avatar',
        'token',
        'access_token',
        'created_at',
        'updated_at',
    ];

    //  protected static function boot()
    // {
    //     parent::boot();

    //     static::creating(function ($model) {
    //         if (empty($model->user_id)) {
    //             $model->user_id = (string) Str::uuid();
    //         }
    //     });
    // }
}
