<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PantryItem extends Model {
    protected $fillable = [
        'household_id',
        'added_by',
        'name',
        'quantity',
        'unit',
        'expiry_date',
        'location',
        'notes',
    ];

    function household() {
        return $this->belongsTo(HouseHold::class);
    }

    function recipes() {
        return $this->belongsToMany(Recipe::class, 'recipe_pantry_items')
                    ->withPivot('quantity', 'unit', 'note')
                    ->withTimestamps();
    }

    function shoppingListItems() {
        return $this->hasMany(ShoppingListItem::class);
    }
}
