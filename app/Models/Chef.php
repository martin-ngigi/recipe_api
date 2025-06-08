<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chef extends Model
{
    //
     public $incrementing = false;  // This will prevent  returning "0" when created
    protected $primaryKey = 'chef_id';
    protected $keyType ='string';
    protected $fillable = [
        'chef_id',
        'name',
        'email',
        'phone',
        'avatar',
        'created_at',
        'updated_at',
    ];

    public function recipesList() {
        return $this->hasMany(Recipe::class, 'chef_id', 'chef_id');
    }

}
