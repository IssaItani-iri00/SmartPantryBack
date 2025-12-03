<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShoppingListItem extends Model {
    protected $fillable = [
        'shopping_list_id',
        'pantry_item_id',
        'name',
        'quantity',
        'unit',
        'is_checked',
        'notes',
    ];

    function shoppingList() {
        return $this->belongsTo(ShoppingList::class);
    }

    function pantryItem() {
        return $this->belongsTo(PantryItem::class);
    }
}
