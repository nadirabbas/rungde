<?php

namespace App\Http\Controllers\Api;

use App\Events\RoomReactionEvent;
use App\Events\RoomSpectatorEvent;
use App\Events\RoomToastEvent;
use App\Events\RoomUpdatedEvent;
use App\Events\RoomUserChangedEvent;
use App\Http\Controllers\Controller;
use App\Jobs\RoomUpdateJob;
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
                'room' => $user->roomSpectator->room->withEventRelations()->load('spectators')
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
        $delayedUpdateData = $request->input('delayed_update');
        $newRoomData = $request->all();
        if (isset($newRoomData['delayed_update'])) {
            unset($newRoomData['delayed_update']);
        }

        $room->update($newRoomData);

        if ($request->input('room_users')) {
            collect($request->room_users)->each(fn ($users, $position) => $room->participants()->where('position', $position)->first()?->update($users));
        }


        event(new RoomUpdatedEvent($room->fresh()->withEventRelations()));
        if ($delayedUpdateData && count($delayedUpdateData)) {
            sleep(2);

            try {
                if (isset($delayedUpdateData['ended_at']) && $delayedUpdateData['ended_at']) {
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

                        $isSelfWinner = $participant->user_id === $delayedUpdateData['last_winner_id'];
                        $isTeammateWinner = $teammate->user_id === $delayedUpdateData['last_winner_id'];

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

            $room->update($delayedUpdateData);
            collect($delayedUpdateData['room_users'])->each(fn ($users, $position) => $room->participants()->where('position', $position)->first()?->update($users));

            event(new RoomUpdatedEvent($room->fresh()->withEventRelations()));
        }
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

    protected function joinRoom(Room $room, User $user, $spectate = false, $preferredPosition = null)
    {
        $fn = fn () => event(new RoomUpdatedEvent($room->fresh()->withEventRelations()));

        if ($spectate) {
            $spectator = $room->spectators()->create([
                'room_id' => $room->id,
                'user_id' => $user->id
            ]);
            event(new RoomSpectatorEvent(
                $room->id,
                $spectator->load('user'),
                true
            ));
            return $spectator;
        }

        $position = $preferredPosition;

        // get availabe position
        if (!$position) {
            $position = $room->participants()->pluck('position')->toArray();
            $position = array_diff([1, 2, 3, 4], $position);
            $position = array_values($position);
            if (!count($position)) {
                // Room full, add as spectator
                return false;
            }
            $position = $position[0];
        }

        $roomUser = $room->participants()->create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'position' => $position
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
                null,
                false,
                $user->id
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

    public function kickSpectator(Request $request)
    {
        $request->validate([
            'spectator_id' => 'required|numeric|exists:room_spectators,id'
        ]);

        $spectator = $request->user()->room->spectators()->where('id', $request->spectator_id)->first();
        if (!$spectator) {
            return response()->json([
                'message' => 'Spectator not found'
            ], 404);
        }
        $room = $spectator->room;

        $spectator->delete();
        event(new RoomSpectatorEvent(
            $room->id,
            null,
            false,
            $spectator->user_id
        ));

        return [
            'message' => 'Spectator has been kicked'
        ];
    }

    public function swapPlaces(Request $request)
    {
        $request->validate([
            'spectator_id' => 'required|exists:room_spectators,user_id'
        ]);

        $user = $request->user();
        if (!$user->room) {
            return response()->json([
                'message' => 'Not in room'
            ], 403);
        }
        $room = $user->room;

        $spectator = $room->spectators()->where('user_id', $request->spectator_id)->first();
        if (!$spectator) {
            return response()->json([
                'message' => 'Spectator not found'
            ], 404);
        }

        $spectatorUserId = $spectator->user_id;
        if ($room->user_id === $user->id) {
            $room->update([
                'user_id' => $spectatorUserId
            ]);
        }


        $user->roomUser->update([
            'user_id' => $spectatorUserId
        ]);

        $spectator->update([
            'user_id' => $user->id
        ]);

        event(new RoomUpdatedEvent($room->fresh()->withEventRelations()));
        event(new RoomToastEvent($room, "{$user->username} swapped places with {$spectator->user->username}"));

        event(new RoomSpectatorEvent(
            $room->id,
            null,
            false,
            $spectatorUserId,
            false
        ));

        // user joined spectators
        event(new RoomSpectatorEvent(
            $room->id,
            $spectator->fresh()->load('user'),
            true,
            null,
            false
        ));
    }

    public function switchToSpectator(Request $request)
    {
        $user = $request->user();
        if (!$user->room) {
            return response()->json([
                'message' => 'Not in room'
            ], 403);
        }
        $room = $user->room;

        $spectator = $room->spectators()->create([
            'room_id' => $room->id,
            'user_id' => $user->id
        ]);

        $user->roomUser->delete();

        $room->reset();

        event(new RoomUpdatedEvent($room->fresh()->withEventRelations()));
        event(new RoomToastEvent($room, "{$user->username} switched to spectator"));
        event(new RoomSpectatorEvent(
            $room->id,
            $spectator->load('user'),
            true,
            null,
            false
        ));

        return [
            'message' => 'You are now a spectator'
        ];
    }

    public function switchToPlayer(Request $request)
    {
        $request->validate([
            'position' => 'required|numeric'
        ]);


        $user = $request->user();
        if (!$user->roomSpectator) {
            return response()->json([
                'message' => 'Not a spectator'
            ], 403);
        }
        $room = $user->roomSpectator->room;

        $userWithPos = $room->participants()->where('position', $request->position)->first();
        if ($userWithPos) {
            return response()->json([
                'message' => 'Position already taken'
            ], 422);
        }

        $user->roomSpectator->delete();
        $this->joinRoom($room, $user, false, $request->position);

        event(new RoomToastEvent($room, "{$user->username} joined the game"));
        event(new RoomSpectatorEvent(
            $room->id,
            null,
            false,
            $user->id,
            false
        ));

        return [
            'message' => 'You are now a room user'
        ];
    }
}
