<?php

namespace App\Http\Controllers\Api;

use App\Events\RoomUserChangedEvent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function me(Request $request)
    {
        $user = $request->user();

        return [
            'user' => $user->load('room')
        ];
    }

    public function setStreamId(Request $request)
    {
        $request->validate([
            'stream_id' => 'required|string'
        ]);

        $user = $request->user();
        if (!$user->roomUser) {
            return response([
                'message' => 'Please join a room first'
            ], 404);
        }

        $user->roomUser->update([
            'stream_id' => $request->stream_id
        ]);

        $user = $user->fresh();

        event(new RoomUserChangedEvent($user->room, $user->roomUser));
    }
}
