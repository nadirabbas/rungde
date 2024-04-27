<?php

namespace App\Http\Controllers\Api;

use App\Events\RoomChatEvent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RoomChatController extends Controller
{
    public function newChat(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'msg' => 'required|max:500',
        ]);

        if (!$user->room) {
            return response([
                'message' => 'You are not in a room'
            ], 400);
        }

        broadcast(new RoomChatEvent($user->room->id, $request->msg, $user->username));
    }
}
