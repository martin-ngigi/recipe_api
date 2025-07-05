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
        'auth_type'=> AuthTypeEnum::class, // e.g. Email, Google, Apple, Facebook, Twitter, Microsoft
        'role' => UserRoleEnum::class,
        'gender'=> GenderEnum::class,
    ];

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'auth_type', // e.g. Email, Google, Apple, Facebook, Twitter, Microsoft
        'open_id',
        'avatar',
        'role', // e.g. customer, chef, admin
        'gender', // e.g. Male, Female, Other, Prefer not to say
        'date_of_birth',
        'phone',
        'phone_complete', // e.g. +1234567890
        'country_code', // e.g. +1, +44, +91
        'token',
        'access_token',
        'created_at',
        'updated_at',
    ];

    public function recipesList() {
        return $this->hasMany(Recipe::class, 'open_id', 'open_id');
    }

    public function allRates() {
        return $this->hasMany(AllRate::class, 'ratee_id', 'open_id');
    }
}
