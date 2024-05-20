<?php

use App\Events\RoomUpdatedEvent;
use App\Models\Room;

if (!function_exists('dispatch_room')) {
    function dispatch_room(Room $room)
    {
        event(new RoomUpdatedEvent($room->fresh()->withEventRelations()));
    }
}
