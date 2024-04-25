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
            collect($request->room_users)->each(fn ($users, $position) => $room->participants()->where('position', $position)->first()?->update($users));
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

        $joined = $this->joinRoom($room, $request->user());
        if (!$joined) {
            return response()->json([
                'message' => 'Room is full'
            ], 422);
        }

        return [
            'room' => $room
        ];
    }

    protected function joinRoom(Room $room, User $user)
    {
        // get availabe position
        $position = $room->participants()->pluck('position')->toArray();
        $position = array_diff([1, 2, 3, 4], $position);
        $position = array_values($position);

        if (!count($position)) return false;

        $roomUser = $room->participants()->create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'position' => $position[0]
        ]);

        broadcast(new RoomUpdatedEvent($room->fresh()->load('participants')));

        return $roomUser;
    }

    public function leave(Request $request)
    {
        $user = $request->user();
        $room = $user->room;
        $roomUser = $room->participants()->where('user_id', $user->id)->first();

        if ($room) {
            $room->participants()->where('user_id', $request->user()->id)->delete();
            broadcast(new RoomUpdatedEvent($room->fresh()->load('participants'), false, $roomUser->position));
        }

        return [
            'message' => 'You have left the room'
        ];
    }

    public function close(Request $request)
    {
        $room = $request->user()->room;

        if ($room) {
            $room->delete();
            broadcast(new RoomUpdatedEvent($room, true));
        }

        return [
            'message' => 'Room has been closed'
        ];
    }

    public function kick(Request $request)
    {
        $user = $request->user();
        $room = $user->room;

        if ($room) {
            $roomUser = $room->participants()->where('position', $request->position)->first();
            $roomUser->delete();
            broadcast(new RoomUpdatedEvent($room->fresh()->load('participants'), false, $roomUser->position));
        }

        return [
            'message' => 'User has been kicked'
        ];
    }
}
