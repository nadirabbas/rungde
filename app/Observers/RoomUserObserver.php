<?php

namespace App\Observers;

use App\Events\RoomUpdatedEvent;
use App\Models\RoomUser;

class RoomUserObserver
{
    public function deleted(RoomUser $roomUser)
    {
        $roomUser->user?->voiceTokens()->delete();
    }
}
