<?php

namespace App\Events;

use App\Models\Room;
use App\Models\RoomUser;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoomUserChangedEvent implements ShouldBroadcastNow
{
  use Dispatchable, InteractsWithSockets, SerializesModels;


  public function __construct(public Room $room, public RoomUser $roomUser)
  {
    $this->room = $room;
    $this->roomUser = $roomUser->load('user');
  }

  public function broadcastOn(): array
  {
    return [
      new PrivateChannel("room.{$this->room->id}"),
    ];
  }

  public function broadcastAs(): string
  {
    return 'userchanged';
  }
}
