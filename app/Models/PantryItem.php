<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PantryItem extends Model {
    protected $fillable = [
        'user_id',
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

    function ingredient() {
        return $this->belongsTo(Ingredient::class, 'ingredients_id');
    }
}
