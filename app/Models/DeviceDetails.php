<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceDetails extends Model
{
    use HasFactory;

    public $incrementing = false;  // This will prevent  returning "0" when created
    //public $timestamps = false; // Set to false to disable timestamps
    protected $primaryKey = 'id';
    protected $keyType ='string';

    protected $fillable = [
        'id',
        'device_id',
        'name',
        'model',
        'localized_model',
        'system_name',
        'version',
        'type',
        'open_id'
    ];

}
