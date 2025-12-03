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
        
        $recipes = Recipe::with('ingredients', 'user')->where('household_id', $householdId)->get();
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
            'ingredients' => 'nullable|array',
            'ingredients.*.ingredient_id' => 'required_with:ingredients|exists:ingredients,id',
            'ingredients.*.quantity' => 'required_with:ingredients|numeric|min:0',
            'ingredients.*.unit' => 'nullable|string|max:50',
            'ingredients.*.note' => 'nullable|string',
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

        if ($request->has('ingredients')) {
            foreach ($request->ingredients as $ingredient) {
                $recipe->ingredients()->attach($ingredient['ingredient_id'], [
                    'quantity' => $ingredient['quantity'],
                    'unit' => $ingredient['unit'] ?? null,
                    'note' => $ingredient['note'] ?? null,
                ]);
            }
        }

        return $this->responseJSON($recipe->load('ingredients', 'user'), "Recipe created successfully", 201);
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
            'ingredients' => 'nullable|array',
            'ingredients.*.ingredient_id' => 'required_with:ingredients|exists:ingredients,id',
            'ingredients.*.quantity' => 'required_with:ingredients|numeric|min:0',
            'ingredients.*.unit' => 'nullable|string|max:50',
            'ingredients.*.note' => 'nullable|string',
        ]);

        $recipe->update($request->only('title', 'instructions', 'tags', 'prep_time_minutes', 'cook_time_minutes', 'servings'));

        if ($request->has('ingredients')) {
            $recipe->ingredients()->detach();
            foreach ($request->ingredients as $ingredient) {
                $recipe->ingredients()->attach($ingredient['ingredient_id'], [
                    'quantity' => $ingredient['quantity'],
                    'unit' => $ingredient['unit'] ?? null,
                    'note' => $ingredient['note'] ?? null,
                ]);
            }
        }

        return $this->responseJSON($recipe->load('ingredients', 'user'), "Recipe updated successfully");
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
