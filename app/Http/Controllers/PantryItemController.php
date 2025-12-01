<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PantryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PantryItemController extends Controller{
    function get($householdId){
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $belongs = $user->households()->where('household_id', $householdId)->exists();
        if (!$belongs)
            return $this->responseJSON(null, "Unauthorized: You do not belong to this household", 403);

        $items = PantryItem::with('ingredient')->where('household_id', $householdId)->get();
        return $this->responseJSON($items);
    }

    function create(Request $request, $householdId){
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $belongs = $user->households()->where('household_id', $householdId)->exists();
        if (!$belongs)
            return $this->responseJSON(null, "Unauthorized: You do not belong to this household", 403);

        $request->validate([
            'ingredients_id' => 'nullable|exists:ingredients,id',
            'name' => 'required_without:ingredients_id|string|max:255',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'location' => 'nullable|string|',
            'expiry_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        if (!$request->ingredients_id && !$request->name)
            return $this->responseJSON(null, "Either ingredients_id or name is required", 400);

        $item = PantryItem::create([
            'household_id' => $householdId,
            'added_by' => $user->name,
            'ingredients_id' => $request->ingredients_id,
            'name' => $request->name,
            'quantity' => $request->quantity,
            'unit' => $request->unit,
            'location' => $request->location,
            'expiry_date' => $request->expiry_date,
            'notes' => $request->notes,
        ]);
        
        return $this->responseJSON($item, "Pantry item added successfully", 201);
    }

    function update(Request $request, $id){
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $item = PantryItem::find($id);
        if (!$item)
            return $this->responseJSON(null, "Pantry item not found", 404);

        $belongs = $user->households()->where('household_id', $item->household_id)->exists();
        if (!$belongs)
            return $this->responseJSON(null, "Unauthorized: You cannot modify this item", 403);

        $request->validate([
            'ingredients_id' => 'nullable|exists:ingredients,id',
            'name' => 'nullable|string|max:255',
            'quantity' => 'sometimes|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'location' => 'nullable|string|in:freezer,fridge,pantry',
            'expiry_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $item->update($request->only('ingredients_id', 'name', 'quantity', 'unit', 'location', 'expiry_date', 'notes'));
        return $this->responseJSON($item->load('ingredient'), "Pantry item updated successfully");
    }
    function delete($id){
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $item = PantryItem::find($id);
        if (!$item)
            return $this->responseJSON(null, "Pantry item not found", 404);

        $belongs = $user->households()->where('household_id', $item->household_id)->exists();
        if (!$belongs)
            return $this->responseJSON(null, "Unauthorized: You cannot delete this item", 403);

        $item->delete();
        return $this->responseJSON(null, "Pantry item deleted successfully");
    }
}
