<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users',
        ]);

        $user = User::create([
            'username' => $request->username,
        ]);

        auth()->login($user, true);

        return [
            'user' => $user->load('room')
        ];
    }

    public function logout()
    {
        auth()->logout();
    }
}
