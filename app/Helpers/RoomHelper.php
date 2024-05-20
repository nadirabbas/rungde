<?php

use App\Events\RoomUpdatedEvent;
use App\Models\Room;

if (!function_exists('dispatch_room')) {
    function dispatch_room(Room $room, ?bool $closed = null, ?string $leftPos = null, ?string $removedPos = null)
    {
        $roomNew = Room::where('id', $room->id)->with([
            'participants',
            'spectators'
        ])->first();
        event(new RoomUpdatedEvent($roomNew ?? $room, $closed, $leftPos, $removedPos));
    }
}
