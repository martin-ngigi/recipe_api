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
        'rating',
        'total_ratings',
        'created_at',
        'updated_at',
    ];

    //protected $appends = ['chef_rate']; //MARK: This will be calculated dynamically. i.e. check the chefRateList() method below.

    public function recipesList() {
        return $this->hasMany(Recipe::class, 'chef_id', 'chef_id');
    }

    public function chefRateList() {
        return $this->hasMany(ChefRate::class, 'chef_id', 'chef_id');
    }

    /*
    public function getChefRateAttribute()
    {
        return $this->chefRateList()->avg('rating') ?? 0.0;
    }
    */

}
