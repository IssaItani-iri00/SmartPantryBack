<?php

namespace App\Services;

use App\Models\PantryItem;
use App\Models\Recipe;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;

class RecipeGeneratorService
{
    public function generateRecipe(string $userInput, int $householdId, int $userId): Recipe
    {
        $pantryItems = PantryItem::where('household_id', $householdId)->get();

        if ($pantryItems->isEmpty()) {
            throw new \Exception("No pantry items available in this household");
        }

        $context = $this->buildAIContext($pantryItems);
        $recipeData = $this->callOpenAI($userInput, $context);
        $recipe = $this->createRecipe($recipeData, $householdId, $userId, $pantryItems);

        return $recipe->load('pantryItems', 'user');
    }

    private function buildAIContext($pantryItems): string
    {
        $context = "Available pantry items:\n";
        
        foreach ($pantryItems as $item) {
            $context .= "- {$item->name} ({$item->quantity} {$item->unit})";
            if ($item->location) {
                $context .= " [Location: {$item->location}]";
            }
            if ($item->expiry_date) {
                $context .= " [Expires: {$item->expiry_date}]";
            }
            $context .= "\n";
        }

        return $context;
    }

    private function callOpenAI(string $userInput, string $context): array
    {
        try {
            $systemPrompt = "You are a helpful cooking assistant. Generate recipes based on available pantry items. " .
                "Your response MUST be a valid JSON object with the following structure:\n" .
                "{\n" .
                '  "title": "Recipe Name",' . "\n" .
                '  "instructions": "Detailed step-by-step cooking instructions",' . "\n" .
                '  "tags": ["tag1", "tag2"],' . "\n" .
                '  "prep_time_minutes":,' . "\n" .
                '  "cook_time_minutes":,' . "\n" .
                '  "servings": ,' . "\n" .
                '  "ingredients": [' . "\n" .
                '    {"name": "ingredient1", "quantity": 2, "unit": "cups", "note": "optional note"},' . "\n" .
                '    {"name": "ingredient2", "quantity": 1, "unit": "tbsp"}' . "\n" .
                '  ]' . "\n" .
                "}\n\n" .
                "Important guidelines:\n" .
                "- Use ingredients from the available pantry items whenever possible\n" .
                "- Match ingredient names exactly as they appear in the pantry list\n" .
                "- You can suggest complementary ingredients from the pantry that would work well\n" .
                "- If the user mentions specific ingredients in their request, prioritize those\n" .
                "- Keep quantities realistic and appropriate for the recipe\n" .
                "- Instructions should be clear, detailed, and easy to follow\n" .
                "- Tags should describe the recipe (e.g., 'dinner', 'quick', 'healthy', 'vegetarian')\n" .
                "- Return ONLY valid JSON, no additional text or explanation";

            $userPrompt = $context . "\n\nUser request: " . $userInput;

            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'temperature' => 0.7,
                'max_tokens' => 2000,
            ]);

            $content = $response->choices[0]->message->content;

            $content = preg_replace('/```json\s*/', '', $content);
            $content = preg_replace('/```\s*$/', '', $content);
            $content = trim($content);

            $recipeData = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Failed to parse OpenAI response', [
                    'error' => json_last_error_msg(),
                    'response' => $content
                ]);
                throw new \Exception("Failed to generate recipe. Please try again.");
            }

            $requiredFields = ['title', 'instructions', 'ingredients'];
            foreach ($requiredFields as $field) {
                if (!isset($recipeData[$field])) {
                    throw new \Exception("Generated recipe is missing required field: {$field}");
                }
            }

            return $recipeData;

        } catch (\Exception $e) {
            Log::error('OpenAI API error', ['message' => $e->getMessage()]);
            throw new \Exception("Failed to generate recipe: " . $e->getMessage());
        }
    }

    private function createRecipe(array $recipeData, int $householdId, int $userId, $pantryItems): Recipe
    {
        $recipe = Recipe::create([
            'household_id' => $householdId,
            'user_id' => $userId,
            'title' => $recipeData['title'],
            'instructions' => $recipeData['instructions'],
            'tags' => $recipeData['tags'] ?? [],
            'prep_time_minutes' => $recipeData['prep_time_minutes'] ?? null,
            'cook_time_minutes' => $recipeData['cook_time_minutes'] ?? null,
            'servings' => $recipeData['servings'] ?? null,
        ]);

        if (isset($recipeData['ingredients']) && is_array($recipeData['ingredients'])) {
            $pantryItemsMap = $pantryItems->keyBy(function ($item) {
                return strtolower($item->name);
            });

            foreach ($recipeData['ingredients'] as $ingredient) {
                $ingredientName = strtolower($ingredient['name']);
                $pantryItem = $pantryItemsMap->get($ingredientName);
                
                if ($pantryItem) {
                    $recipe->pantryItems()->attach($pantryItem->id, [
                        'quantity' => $ingredient['quantity'] ?? 0,
                        'unit' => $ingredient['unit'] ?? null,
                        'note' => $ingredient['note'] ?? null,
                    ]);
                }
            }
        }

        return $recipe;
    }
}
