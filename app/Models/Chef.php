<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chef extends Model
{
    //
     public $incrementing = false;  // This will prevent  returning "0" when created
    protected $primaryKey = 'chef_id';
    protected $fillable = [
        'chef_id',
        'name',
        'email',
        'phone',
        'avatar',
        'created_at',
        'updated_at',
    ];
}
