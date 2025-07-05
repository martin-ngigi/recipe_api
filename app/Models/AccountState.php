<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountState extends Model
{
    public $incrementing = false;  // This will prevent  returning "0" when created
    //public $timestamps = false; // Set to false to disable timestamps
    protected $primaryKey = 'user_id';
    protected $keyType ='string';

    protected $casts = [
        // 'state_id' => 'uuid',
        'state_id' => 'string',
        'status'=> AccountStatusEnum::class
    ];

    protected $fillable = [
        'state_id',
        'status',
        'description',
        'open_id'
    ];

     public function status() {
        return $this->belongsTo(AppUser::class, 'open_id', 'open_id');
    }
}
