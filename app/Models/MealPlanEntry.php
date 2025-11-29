<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealPlanEntry extends Model {
    protected $fillable = [
        'meal_plan_id',
        'date',
        'meal_type',
        'recipe_id',
    ];

    function mealPlan() {
        return $this->belongsTo(MealPlan::class);
    }

    function recipe() {
        return $this->belongsTo(Recipe::class);
    }
}
