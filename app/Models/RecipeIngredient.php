<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecipeIngredient extends Model {
    protected $table = 'recipe_ingredients';

    protected $fillable = [
        'recipe_id',
        'ingredient_id',
        'quantity',
        'unit',
        'note',
    ];

    function recipe() {
        return $this->belongsTo(Recipe::class);
    }

    function ingredient() {
        return $this->belongsTo(Ingredient::class);
    }
}
