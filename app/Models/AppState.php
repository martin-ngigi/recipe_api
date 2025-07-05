<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppState extends Model
{
    //
    public $incrementing = false;  // This will prevent  returning "0" when created
    //public $timestamps = false; // Set to false to disable timestamps
    protected $primaryKey = 'id';
    protected $keyType ='string';

    protected $fillable = [
        'state',
        'description',
        'image',
        'current'
    ];
}
