<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class HouseHold extends Model
{
    protected $fillable = [
        'name',
        'invite_code',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($household) {
            if (!$household->invite_code) {
                $code = strtoupper(Str::random(8));
                $household->invite_code = $code;
            }
        });
    }

    function users()
    {
        return $this->belongsToMany(User::class)
                    ->withTimestamps();
    }

    function pantryItems()
    {
        return $this->hasMany(Recipe::class);
    }

    function meanlPlans()
    {
        return $this->hasMany(MealPlan::class);
    }

    function shoppingLists()
    {
        return $this->hasMany(ShoppingList::class);
    }

    function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
