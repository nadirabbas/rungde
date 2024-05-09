<?php

namespace App\Http\Controllers\Api;

use App\Events\RoomUpdatedEvent;
use App\Events\RoomUserChangedEvent;
use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;

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

        try {
            if ($request->input('ended_at')) {
                $participants = $room->participants()->get();

                foreach ($participants as $participant) {
                    $data = [
                        'games_played' => $participant->user->games_played + 1,
                        'sirs' => $participant->sir_count + $participant->user->sirs
                    ];

                    $teammatePosition = $participant->position + 2;
                    $teammatePosition = $teammatePosition > 4 ? $teammatePosition - 4 : $teammatePosition;
                    $teammate = $participants->where('position', $teammatePosition)->first();
                    $isWinner = $participant->user_id === $request->input('last_winner_id') || $teammate->user_id === $request->input('last_winner_id');

                    if ($isWinner) {
                        $data['games_won'] = $participant->user->games_won + 1;
                        $ourScore = $participant->sir_count + $teammate->sir_count;
                        $didWeSelectRung = $room->rung_selector === $participant->position || $room->rung_selector === $teammatePosition;

                        if ($ourScore === 13) {
                            if ($didWeSelectRung) {
                                $data['courts'] = $participant->user->courts + 1;
                            } else {
                                $data['goon_courts'] = $participant->user->goon_courts + 1;
                            }
                        }
                    }

                    $participant->user->update($data);
                }
            }
        } catch (\Throwable $th) {
            //throw $th;
        }

        event(new RoomUpdatedEvent($room->fresh()->load('participants')));
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

        event(new RoomUpdatedEvent($room->fresh()->load('participants')));

        return $roomUser;
    }

    public function leave(Request $request)
    {
        $user = $request->user();
        $room = $user->room;
        $roomUser = $room->participants()->where('user_id', $user->id)->first();

        if ($room) {
            $room->participants()->where('user_id', $request->user()->id)->delete();
            event(new RoomUpdatedEvent($room->fresh()->load('participants'), false, $roomUser->position));
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
            event(new RoomUpdatedEvent($room, true));
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
            event(new RoomUpdatedEvent($room->load('participants'), false, '', $roomUser->position));
        }

        return [
            'message' => 'User has been kicked'
        ];
    }
}
