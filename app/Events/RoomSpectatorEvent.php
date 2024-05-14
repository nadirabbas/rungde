<?php

namespace App\Events;

use App\Models\Room;
use App\Models\RoomSpectator;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoomSpectatorEvent implements ShouldBroadcastNow
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  public function __construct(public int $roomId, public ?RoomSpectator $spectator = null, public ?bool $joined = true, public $leftId = null, public $alert = true)
  {
    $this->roomId = $roomId;
    $this->spectator = $spectator;
    $this->joined = $joined;
    $this->leftId = $leftId;
    $this->alert = $alert;
  }

  /**
   * Get the channels the event should broadcast on.
   *
   * @return array<int, \Illuminate\Broadcasting\Channel>
   */
  public function broadcastOn(): array
  {
    return [
      new PrivateChannel("room.{$this->roomId}"),
    ];
  }

  public function broadcastAs(): string
  {
    return 'spectator-event';
  }
}
