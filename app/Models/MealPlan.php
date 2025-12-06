<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealPlan extends Model {
    protected $fillable = [
        'household_id',
        'week_start_date',
        'week_end_date',
        'title',
        'status',
    ];

    function household() {
        return $this->belongsTo(HouseHold::class);
    }

    function entries() {
        return $this->hasMany(MealPlanEntry::class);
    }

    function shoppingLists() {
        return $this->hasMany(ShoppingList::class, "generated_from_meal_plan_id");
    }
}
