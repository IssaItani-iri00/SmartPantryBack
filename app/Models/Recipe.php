<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class Recipe extends Model {
    protected $fillable = [
        'household_id',
        'user_id',
        'title',
        'instructions',
        'tags',
        'prep_time_minutes',
        'cook_time_minutes',
        'servings',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    function household() {
        return $this->belongsTo(HouseHold::class);
    }

    function user() {
        return $this->belongsTo(User::class);
    }

    function ingredients() {
        return $this->belongsToMany(Ingredient::class, 'recipe_ingredients')
                    ->withPivot('quantity', 'unit', 'note')
                    ->withTimestamps();
    }

    function mealPlanEntries() {
        return $this->hasMany(MealPlanEntry::class);
    }
}
