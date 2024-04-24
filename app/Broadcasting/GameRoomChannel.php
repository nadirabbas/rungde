<?php

namespace App\Broadcasting;

use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class GameRoomChannel
{
    public function join(User $user, Room $room)
    {
        return true;
    }
}
