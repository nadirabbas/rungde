<?php

namespace App\Observers;

use App\Events\RoomUpdatedEvent;
use App\Models\RoomUser;

class RoomUserObserver
{
    protected function broadcast(RoomUser $roomUser)
    {
        broadcast(new RoomUpdatedEvent($roomUser->room->load('participants')));
    }

    public function created(RoomUser $roomUser)
    {
        $this->broadcast($roomUser);
    }

    public function updated(RoomUser $roomUser)
    {
        $this->broadcast($roomUser);
    }

    public function deleted(RoomUser $roomUser)
    {
        $this->broadcast($roomUser);
    }
}
