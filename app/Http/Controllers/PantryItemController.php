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

        $items = PantryItem::where('household_id', $householdId)->get();
        return $this->responseJSON($items);
    }

    function create(Request $request, $householdId){
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $belongs = $user->households()->where('household_id', $householdId)->exists();
        if (!$belongs)
            return $this->responseJSON(null, "Unauthorized: You do not belong to this household", 403);

        $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'location' => 'nullable|string|',
            'expiry_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $item = PantryItem::create([
            'household_id' => $householdId,
            'added_by' => $user->name,
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
            'name' => 'nullable|string|max:255',
            'quantity' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'location' => 'nullable|string',
            'expiry_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $item->update($request->only('name', 'quantity', 'unit', 'location', 'expiry_date', 'notes'));
        return $this->responseJSON($item, "Pantry item updated successfully");
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
