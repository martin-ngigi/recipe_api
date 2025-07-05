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
        //'ingredients',
        'instructions',
        'image',
        'open_id',
        'created_at',
        'updated_at',
    ];

    public function chef() {
        return $this->belongsTo(AppUser::class, 'open_id', 'open_id');
    }

    public function ingredients_list() {
        return $this->hasMany(Ingredient::class, 'recipe_id', 'recipe_id');
    }
}
