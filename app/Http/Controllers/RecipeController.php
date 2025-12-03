<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Recipe;

class RecipeController extends Controller
{
    function get($householdId){
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $belongs = $user->households()->where('household_id', $householdId)->exists();

        if (!$belongs)
            return $this->responseJSON(null, "Unauthorized: You do not belong to this household", 403);
        
        $recipes = Recipe::with('pantryItems', 'user')->where('household_id', $householdId)->get();
        return $this->responseJSON($recipes);
    }

    function create(Request $request, $householdId){
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $belongs = $user->households()->where('household_id', $householdId)->exists();
        if (!$belongs)
            return $this->responseJSON(null, "Unauthorized: You do not belong to this household", 403);

        $request->validate([
            'title' => 'required|string|max:255',
            'instructions' => 'required|string',
            'tags' => 'nullable|array',
            'prep_time_minutes' => 'nullable|integer|min:0',
            'cook_time_minutes' => 'nullable|integer|min:0',
            'servings' => 'nullable|integer|min:1',
            'pantry_items' => 'nullable|array',
            'pantry_items.*.pantry_item_id' => 'required_with:pantry_items|exists:pantry_items,id',
            'pantry_items.*.quantity' => 'required_with:pantry_items|numeric|min:0',
            'pantry_items.*.unit' => 'nullable|string|max:50',
            'pantry_items.*.note' => 'nullable|string',
        ]);

        $recipe = Recipe::create([
            'household_id' => $householdId,
            'user_id' => $user->id,
            'title' => $request->title,
            'instructions' => $request->instructions,
            'tags' => $request->tags,
            'prep_time_minutes' => $request->prep_time_minutes,
            'cook_time_minutes' => $request->cook_time_minutes,
            'servings' => $request->servings,
        ]);

        if ($request->has('pantry_items')) {
            foreach ($request->pantry_items as $pantryItem) {
                $recipe->pantryItems()->attach($pantryItem['pantry_item_id'], [
                    'quantity' => $pantryItem['quantity'],
                    'unit' => $pantryItem['unit'] ?? null,
                    'note' => $pantryItem['note'] ?? null,
                ]);
            }
        }

        return $this->responseJSON($recipe->load('pantryItems', 'user'), "Recipe created successfully", 201);
    }

    function update(Request $request, $id){
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $recipe = Recipe::find($id);
        if (!$recipe)
            return $this->responseJSON(null, "Recipe not found", 404);

        $belongs = $user->households()->where('household_id', $recipe->household_id)->exists();
        if (!$belongs)
            return $this->responseJSON(null, "Unauthorized: You cannot modify this recipe", 403);

        $request->validate([
            'title' => 'nullable|string|max:255',
            'instructions' => 'nullable|string',
            'tags' => 'nullable|array',
            'prep_time_minutes' => 'nullable|integer|min:0',
            'cook_time_minutes' => 'nullable|integer|min:0',
            'servings' => 'nullable|integer|min:1',
            'pantry_items' => 'nullable|array',
            'pantry_items.*.pantry_item_id' => 'required_with:pantry_items|exists:pantry_items,id',
            'pantry_items.*.quantity' => 'required_with:pantry_items|numeric|min:0',
            'pantry_items.*.unit' => 'nullable|string|max:50',
            'pantry_items.*.note' => 'nullable|string',
        ]);

        $recipe->update($request->only('title', 'instructions', 'tags', 'prep_time_minutes', 'cook_time_minutes', 'servings'));

        if ($request->has('pantry_items')) {
            $recipe->pantryItems()->detach();
            foreach ($request->pantry_items as $pantryItem) {
                $recipe->pantryItems()->attach($pantryItem['pantry_item_id'], [
                    'quantity' => $pantryItem['quantity'],
                    'unit' => $pantryItem['unit'] ?? null,
                    'note' => $pantryItem['note'] ?? null,
                ]);
            }
        }

        return $this->responseJSON($recipe->load('pantryItems', 'user'), "Recipe updated successfully");
    }

    function delete($id){
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $recipe = Recipe::find($id);
        if (!$recipe)
            return $this->responseJSON(null, "Recipe not found", 404);

        $belongs = $user->households()->where('household_id', $recipe->household_id)->exists();
        if (!$belongs)
            return $this->responseJSON(null, "Unauthorized: You cannot delete this recipe", 403);

        $recipe->delete();
        return $this->responseJSON(null, "Recipe deleted successfully");
    }
    
}
