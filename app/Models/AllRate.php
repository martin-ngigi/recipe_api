<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AllRate extends Model
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
        'ratee_id',
        'rater_id',
        'rating',
        'comment',
        'created_at',
        'updated_at',
    ];

    public function chef() {
        return $this->belongsTo(AppUser::class, 'chef_id', 'open_id');
    }

    public function rater() {
        return $this->belongsTo(AppUser::class, 'rater_id', 'open_id');
    }
}
