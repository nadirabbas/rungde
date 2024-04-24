<?php

namespace App\Http\Controllers\Api;

use App\Events\RoomUpdatedEvent;
use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RoomsController extends Controller
{
    public function current(Request $request)
    {
        $room = $request->user()->room;

        if ($room) {
            return [
                'room' => $room->load('participants')
            ];
        }

        return response([
            'message' => 'Please join a room first'
        ], 404);
    }

    public function store(Request $request)
    {
        $room = Room::create([
            'user_id' => $request->user()->id,
            'code' => mt_rand(100000, 999999)
        ]);

        $this->joinRoom($room, $request->user());

        return [
            'room' => $room
        ];
    }

    public function update(Request $request, Room $room)
    {
        $room->update($request->all());

        if ($request->input('room_users')) {
            collect($request->room_users)->each(fn ($users, $position) => $room->participants()->where('position', $position)->first()->update($users));
        }

        broadcast(new RoomUpdatedEvent($room->fresh()->load('participants')));
    }

    public function show(Room $room)
    {
        $room->load('user');

        return [
            'room' => $room
        ];
    }

    public function join(Request $request)
    {
        $request->validate([
            'code' => 'required|exists:rooms'
        ]);

        $room = Room::where('code', $request->code)->first();
        if ($room->participants()->whereRelation('user', 'id', $request->user()->id)->exists()) {
            return [
                'room' => $room
            ];
        }

        if ($room->participants()->count() === 4) {
            return response()->json([
                'message' => 'Room is full'
            ], 422);
        }

        $this->joinRoom($room, $request->user());

        return [
            'room' => $room
        ];
    }

    protected function joinRoom(Room $room, User $user)
    {
        $room->participants()->create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'position' => $room->participants()->count() + 1
        ]);

        broadcast(new RoomUpdatedEvent($room->fresh()->load('participants')));
    }
}
