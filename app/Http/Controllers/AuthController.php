<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Household;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;


class AuthController extends Controller {

    public function login(Request $request) {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $credentials = $request->only('email', 'password');

        $token = Auth::attempt($credentials);
        if (!$token) {
            return $this->responseJSON(null, "Unauthorized", 401);
        }

        $user = Auth::user();
        $user->token = $token;
        return $this->responseJSON($user);

    }

    public function register(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);
        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();

        $token = Auth::login($user);
        $user->token = $token;
        return $this->responseJSON($user);
    }

    public function logout() {
        Auth::logout();
        return $this->responseJSON("Successefully logged out");
    }

    public function refresh() {
        $user = Auth::user();
        $user->token = Auth::refresh();
        return $this->responseJSON($user);
    }
}
