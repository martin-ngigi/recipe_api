<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    public $incrementing = false;  // This will prevent  returning "0" when created
    protected $primaryKey = 'recipe_id';
    //
    protected $fillable = [
        'recipe_id',
        'name',
        'description',
        'ingredients',
        'instructions',
        'image_url',
        'chef_id',
        'created_at',
        'updated_at',
    ];
}
