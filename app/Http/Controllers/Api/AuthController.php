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

        auth()->login($user, true);

        return [
            'user' => $user->load('room')
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

        auth()->login($user, true);

        return [
            'user' => $user->load('room')
        ];
    }

    public function logout()
    {
        auth()->logout();
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
