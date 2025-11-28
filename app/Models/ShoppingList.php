<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShoppingList extends Model
{
    protected $fillable = [
        'household_id',
        'created_by',
        'name',
        'is_active',
        'generated_from_meal_plan_id',
    ];

    function household()
    {
        return $this->belongsTo(HouseHold::class);
    }

    function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    function items()
    {
        return $this->hasMany(ShoppingListItem::class);
    }

    function mealPlan()
    {
        return $this->belongsTo(MealPlan::class, 'generated_from_meal_plan_id');
    }
}
