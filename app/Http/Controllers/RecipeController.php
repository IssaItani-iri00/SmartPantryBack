<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Recipe;
use App\Services\RecipeGeneratorService;

class RecipeController extends Controller
{
    protected $recipeGeneratorService;

    public function __construct(RecipeGeneratorService $recipeGeneratorService)
    {
        $this->recipeGeneratorService = $recipeGeneratorService;
    }

    function get($householdId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $belongs = $user->households()->where('household_id', $householdId)->exists();
        if (!$belongs)
            return $this->responseJSON(null, "Unauthorized: You do not belong to this household", 403);
        
        $recipes = Recipe::with('pantryItems', 'user')->where('household_id', $householdId)->get();
        return $this->responseJSON($recipes);
    }

    function generate(Request $request, $householdId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $belongs = $user->households()->where('household_id', $householdId)->exists();
        if (!$belongs)
            return $this->responseJSON(null, "Unauthorized: You do not belong to this household", 403);

        $request->validate([
            'prompt' => 'required|string|max:500',
        ]);

        try {
            $recipe = $this->recipeGeneratorService->generateRecipe(
                $request->prompt,
                $householdId,
                $user->id
            );

            return $this->responseJSON($recipe, "Recipe generated successfully", 201);
        } catch (\Exception $e) {
            return $this->responseJSON(null, $e->getMessage(), 400);
        }
    }

    function delete($id)
    {
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
