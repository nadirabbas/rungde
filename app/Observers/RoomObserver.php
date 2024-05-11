<?php

namespace App\Observers;

use App\Events\RoomUpdatedEvent;
use App\Models\Room;

class RoomObserver
{
    public function updated(Room $room)
    {
        event(new RoomUpdatedEvent($room->withEventRelations()));
    }
}
