<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerNotification extends Model
{
    //
      use HasFactory;

    public $incrementing = false;  // This will prevent  returning "0" when created
    //public $timestamps = false; // Set to false to disable timestamps
    protected $primaryKey = 'notification_id';
    protected $keyType ='string';


    protected $fillable = [
        'notification_id',
        'open_id',
        'title',
        'message',
        'icon',
        'banner',
        'is_read',
    ];
    // protected $hidden = [
    //     'created_at',
    //     'updated_at',
    // ];
}
