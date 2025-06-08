<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChefRate extends Model
{
    //
    public $incrementing = false;  // This will prevent  returning "0" when created
    //public $timestamps = false; // Set to false to disable timestamps
    protected $primaryKey = 'rate_id';
    protected $keyType ='string';
    protected $casts =[
        'rating' => 'double'
    ];

    protected $fillable = [
        'rate_id',
        'chef_id',
        'open_id',
        'rating',
        'comment',
        'created_at',
        'updated_at',
    ];

    public function chef() {
        return $this->belongsTo(Chef::class, 'chef_id', 'chef_id');
    }

    public function rater() {
        return $this->belongsTo(AppUser::class, 'open_id', 'open_id');
    }
}
