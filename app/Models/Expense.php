<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model {
    protected $fillable = [
        'household_id',
        'amount',
        'date',
        'category',
        'note',
    ];

    protected function casts(): array {
        return [
            'date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    function household() {
        return $this->belongsTo(HouseHold::class);
    }
}
