<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $fillable = [
        'name',
        'default_unit',
    ];

    function recipes()
    {
        return $this->belongsToMany(Recipe::class, 'recipe_ingredients')
                    ->withPivot(['quantity', 'unit', 'note'])
                    ->withTimestamps();
    }

    function shoppingListItems()
    {
        return $this->hasMany(ShoppingListItem::class);
    }

    function pantryItems()
    {
        return $this->hasMany(PantryItem::class);
    }
}
