<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VoiceToken;
use Illuminate\Http\Request;

class VoiceTokensController extends Controller
{
    public function requestVoiceToken(Request $request)
    {
        $user = $request->user();
        if (!$user->roomUser) {
            return response()->json([
                'message' => 'You are not in a room',
            ], 400);
        }

        $newVoiceToken = $user->voiceTokens()->create([
            'token' => bin2hex(random_bytes(32)),
            'expires_at' => now()->addHour(),
            'room_id' => $user->room->id
        ]);

        return response()->json([
            'token' => $newVoiceToken->token,
            'expires_at' => $newVoiceToken->expires_at,
        ]);
    }

    public function verifyVoiceToken(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'room_id'    => 'required|exists:rooms,id',
            'token'   => 'required|string',
        ]);

        $voiceToken = VoiceToken::where('token', $request->token)
            ->where('user_id', $request->user_id)
            ->where('room_id', $request->room_id)
            ->where('expires_at', '>', now())
            ->first();

        if (!$voiceToken) {
            return response()->json([
                'message' => 'Invalid voice token',
            ], 400);
        }

        return response()->json([
            'message' => 'Valid voice token',
        ]);
    }
}
