<?php

namespace App\Http\Controllers\Api;

use App\Events\RoomUserChangedEvent;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UsersController extends Controller
{
    public function me(Request $request)
    {
        $user = $request->user();

        return [
            'user' => $user->load('room')->load('roomSpectator')
        ];
    }

    public function updateMe(Request $request)
    {
        $request->validate([
            'username' => 'sometimes|string|max:20|unique:users,username,' . $request->user()->id,
            'avatar'   => 'sometimes|image|mimes:jpeg,png,jpg,webp|max:5048',
        ]);

        $user = $request->user();

        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $avatarName = $user->id . '.' . $avatar->getClientOriginalExtension();
            $avatar->storeAs('public/avatars', $avatarName);
            $user->avatar = '/storage/avatars/' . $avatarName;
        }

        if ($request->has('username')) {
            $user->username = $request->username;
        }

        $user->save();

        return [
            'user' => $user
        ];
    }

    public function getByUsername(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:20|exists:users,username'
        ]);

        $user = User::firstWhere('username', $request->username);

        return [
            'user' => $user
        ];
    }
}
