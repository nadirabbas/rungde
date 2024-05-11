<?php

namespace App\Http\Controllers\Api;

use App\Events\RoomReactionEvent;
use App\Events\RoomSpectatorEvent;
use App\Events\RoomUpdatedEvent;
use App\Events\RoomUserChangedEvent;
use App\Http\Controllers\Controller;
use App\Models\MatchHistory;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RoomsController extends Controller
{
    public function current(Request $request)
    {
        $user = $request->user();
        $room = $user->room()->first();

        if ($room) {
            return [
                'room' => $room->withEventRelations()
            ];
        }

        if ($user->roomSpectator) {
            return [
                'room' => $user->roomSpectator->room->withEventRelations()
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
                $winner_1_id = null;
                $winner_2_id = null;
                $loser_1_id = null;
                $loser_2_id = null;
                $is_goon_court = false;
                $is_court = false;
                $winner_score = 0;
                $loser_score = 0;

                $participants = $room->participants()->get();

                foreach ($participants as $participant) {
                    $data = [
                        'games_played' => $participant->user->games_played + 1,
                        'sirs' => $participant->sir_count + $participant->user->sirs
                    ];

                    $teammatePosition = $participant->position + 2;
                    $teammatePosition = $teammatePosition > 4 ? $teammatePosition - 4 : $teammatePosition;
                    $teammate = $participants->where('position', $teammatePosition)->first();

                    $isSelfWinner = $participant->user_id === $request->input('last_winner_id');
                    $isTeammateWinner = $teammate->user_id === $request->input('last_winner_id');

                    $isWinner = $isSelfWinner || $isTeammateWinner;

                    if ($isSelfWinner) {
                        $winner_1_id = $participant->user_id;
                    } else if ($isTeammateWinner) {
                        $winner_2_id = $participant->user_id;
                    } else {
                        $loser_1_id = $loser_1_id ?: $participant->user_id;
                        $loser_2_id = $loser_2_id ?: $teammate->user_id;
                    }

                    if ($isWinner) {
                        $winner_score += $participant->sir_count;
                    } else {
                        $loser_score += $participant->sir_count;
                    }

                    if ($isWinner) {
                        $data['games_won'] = $participant->user->games_won + 1;
                        $ourScore = $participant->sir_count + $teammate->sir_count;
                        $didWeSelectRung = $room->rung_selector === $participant->position || $room->rung_selector === $teammatePosition;

                        if ($ourScore === 13) {
                            if ($didWeSelectRung) {
                                $is_court = true;
                                $data['courts'] = $participant->user->courts + 1;
                            } else {
                                $is_goon_court = true;
                                $data['goon_courts'] = $participant->user->goon_courts + 1;
                            }
                        }
                    }

                    $participant->user->update($data);
                }

                MatchHistory::create([
                    'winner_1_id' => $winner_1_id,
                    'winner_2_id' => $winner_2_id,
                    'loser_1_id' => $loser_1_id,
                    'loser_2_id' => $loser_2_id,
                    'is_court' => $is_court,
                    'is_goon_court' => $is_goon_court,
                    'winner_score' => $winner_score,
                    'loser_score' => $loser_score,
                ]);
            }
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
            //throw $th;
        }

        event(new RoomUpdatedEvent($room->fresh()->withEventRelations()));
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
        $user = $request->user();
        $request->validate([
            'code' => 'required|exists:rooms',
            'as_spectator' => 'sometimes|numeric'
        ]);

        Log::info($request->input('as_spectator'));

        $room = Room::where('code', $request->code)->first();
        if ($room->participants()->whereRelation('user', 'id', $request->user()->id)->exists()) {
            return [
                'room' => $room
            ];
        }

        $joined = $this->joinRoom($room, $user, $request->input('as_spectator'));
        if (!$joined) {
            return response()->json([
                'message' => 'Room is full'
            ], 422);
        }

        return [
            'room' => $room
        ];
    }

    protected function joinRoom(Room $room, User $user, $spectate = false)
    {
        $fn = fn () => event(new RoomUpdatedEvent($room->fresh()->withEventRelations()));

        if ($spectate) {
            $spectator = $room->spectators()->create([
                'room_id' => $room->id,
                'user_id' => $user->id
            ]);
            $fn();

            event(new RoomSpectatorEvent(
                $room->id,
                "{$user->username} started spectating."
            ));
            return $spectator;
        }

        // get availabe position
        $position = $room->participants()->pluck('position')->toArray();
        $position = array_diff([1, 2, 3, 4], $position);
        $position = array_values($position);

        if (!count($position)) {
            // Room full, add as spectator
            return false;
        }

        $roomUser = $room->participants()->create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'position' => $position[0]
        ]);

        $fn();

        return $roomUser;
    }

    public function leave(Request $request)
    {
        $user = $request->user();

        if ($user->room) {
            $roomUser = $user->room->participants()->where('user_id', $user->id)->first();
            $user->room->participants()->where('user_id', $request->user()->id)->delete();
            event(new RoomUpdatedEvent($user->room->fresh()->withEventRelations(), false, $roomUser->position));
        }

        if ($user->roomSpectator) {
            $user->roomSpectator->delete();
            event(new RoomSpectatorEvent(
                $user->roomSpectator->room_id,
                "{$user->username} stopped spectating."
            ));
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
            event(new RoomUpdatedEvent($room->withEventRelations(), false, '', $roomUser->position));
        }

        return [
            'message' => 'User has been kicked'
        ];
    }

    public function sendReaction(Request $request)
    {
        $request->validate([
            'reaction' => 'required',
        ]);

        event(new RoomReactionEvent(
            $request->user()->room->id,
            $request->reaction,
            $request->user()->id
        ));
    }
}
