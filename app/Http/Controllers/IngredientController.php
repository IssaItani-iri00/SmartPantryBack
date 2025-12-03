<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ingredient;
use Illuminate\Support\Facades\Auth;

class IngredientController extends Controller
{
    function get($householdId){
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $belongs = $user->households()->where('household_id', $householdId)->exists();
        if (!$belongs)
            return $this->responseJSON(null, "Unauthorized: You do not belong to this household", 403);

        $ingredients = Ingredient::all();
        return $this->responseJSON($ingredients);
    }

    function create(Request $request, $householdId){
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $belongs = $user->households()->where('household_id', $householdId)->exists();
        if (!$belongs)
            return $this->responseJSON(null, "Unauthorized: You do not belong to this household", 403);

        $request->validate([
            'name' => 'required|string|max:255|unique:ingredients,name',
            'default_unit' => 'nullable|string|max:50',
        ]);

        $ingredient = Ingredient::create([
            'name' => $request->name,
            'default_unit' => $request->default_unit,
        ]);

        return $this->responseJSON($ingredient, "Ingredient created successfully", 201);
    }

    function update(Request $request, $id){
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $ingredient = Ingredient::find($id);
        if (!$ingredient)
            return $this->responseJSON(null, "Ingredient not found", 404);

        $request->validate([
            'name' => 'nullable|string|max:255|unique:ingredients,name,' . $id,
            'default_unit' => 'nullable|string|max:50',
        ]);

        $ingredient->update($request->only('name', 'default_unit'));
        return $this->responseJSON($ingredient, "Ingredient updated successfully");
    }

    function delete($id){
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $ingredient = Ingredient::find($id);
        if (!$ingredient)
            return $this->responseJSON(null, "Ingredient not found", 404);

        // Checking if ingredient is being used in recipes, pantry items, or shopping lists
        if ($ingredient->recipes()->exists() || $ingredient->pantryItems()->exists() || $ingredient->shoppingListItems()->exists())
            return $this->responseJSON(null, "Cannot delete ingredient: It is being used in recipes, pantry items, or shopping lists", 400);

        $ingredient->delete();
        return $this->responseJSON(null, "Ingredient deleted successfully");
    }
}
