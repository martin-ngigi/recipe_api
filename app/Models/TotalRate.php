<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TotalRate extends Model
{
    //
    public $incrementing = false;  // This will prevent  returning "0" when created
    //public $timestamps = false; // Set to false to disable timestamps
    protected $primaryKey = 'rate_id';
    protected $keyType ='string';
    protected $casts =[
        'rating' => 'double',
        'total_ratings'=> 'integer',
    ];

    protected $fillable = [
        'rate_id',
        'open_id',
        'rating',
        'total_ratings',
        'created_at',
        'updated_at',
    ];
}
