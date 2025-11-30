<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PantryItem extends Model {
    protected $fillable = [
        'household_id',
        'added_by',
        'ingredients_id',
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

    function addedBy() {
        return $this->belongsTo(User::class, 'added_by');
    }

    function ingredient() {
        return $this->belongsTo(Ingredient::class, 'ingredients_id');
    }
}
