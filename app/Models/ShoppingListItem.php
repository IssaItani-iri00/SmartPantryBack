<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShoppingListItem extends Model {
    protected $fillable = [
        'shopping_list_id',
        'ingredient_id',
        'name',
        'quantity',
        'unit',
        'is_checked',
        'notes',
    ];

    function shoppingList() {
        return $this->belongsTo(ShoppingList::class);
    }

    function ingredient() {
        return $this->belongsTo(Ingredient::class);
    }
}
