<?php

namespace App\Observers;

use App\Events\RoomUpdatedEvent;
use App\Models\Room;

class RoomObserver
{
    public function updating(Room $room)
    {
        // $room->event_counter++;
    }
}
