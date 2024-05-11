<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users',
            'password' => 'required'
        ]);

        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password)
        ]);

        $token = $user->createToken('auth_token')->accessToken;

        return [
            'user' => $user->load('room'),
            'token' => $token
        ];
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.'
            ], 401);
        }

        $token = $user->createToken('auth_token')->accessToken;

        return [
            'user' => $user->load('room'),
            'token' => $token
        ];
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();
        return response()->json(['message' => 'Tokens Revoked']);
    }

    public function oauth(Request $request)
    {
        $request->validate([
            'provider' => 'in:google'
        ]);

        return Socialite::driver($request->provider)->redirect();
    }

    public function callback(Request $request)
    {
        $user = Socialite::driver('google')->user();

        $user = User::firstOrCreate([
            'google_id' => $user->id
        ], [
            'username' => explode('@', $user->email)[0],
        ]);

        auth()->login($user, true);

        return [
            'user' => $user->load('room')
        ];
    }
}
