<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HouseHold;
use Illuminate\Support\Facades\Auth;


class HouseHoldController extends Controller{
    function index(){
        $user = Auth::user();
        $households = $user->households;
        if(!$households)
            return $this->responseJSON("No households found", 400);
        
        return $this->responseJSON($households);
    }

    function store(Request $request){
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $request->validate([
            'name' => 'required|string'
        ]);

        $household = HouseHold::create([
            'name' =>$request->name,
        ]);

        $user->households()->attach($household->id);
        
        return $this->responseJSON($user, "Household created successfully", 201);
    }

    function join(Request $request){
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $request->validate([
            'household_code' => 'required|string'
        ]);

        $household = HouseHold::where('invite_code', $request->household_code)->first();
        
        if (!$household) {
            return $this->responseJSON(null, "Invalid household code", 404);
        }

        // checking if the user is already in this household
        if ($user->households()->where('household_id', $household->id)->exists()) {
            return $this->responseJSON(null, "You are already a member of this household", 400);
        }

        $user->households()->attach($household->id);
        
        return $this->responseJSON($user->load('households'), "Successfully joined household", 200);
    }
}
