<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function me()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load('households');
        return $this->responseJSON($user);
    }
}
