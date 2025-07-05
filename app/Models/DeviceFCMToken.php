<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceFCMToken extends Model
{
    //
        protected $fillable = [
        'device_fcm_id',
        'open_id',
        'android_token',
        'ios_token',
        'web_token',
    ];
}
