<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    //
    public $incrementing = false;  // This will prevent  returning "0" when created
    protected $primaryKey = 'ingredient_id';
    //
    protected $fillable = [
        'ingredient_id',
        'name',
        'image',
        'quantity',
        'recipe_id',
        'created_at',
        'updated_at',
    ];

    public function chef() {
        return $this->belongsTo(Chef::class, 'chef_id', 'chef_id');
    }

    public function recipe() {
        return $this->belongsTo(Recipe::class, 'recipe_id', 'recipe_id');
    }
}
