<?php

namespace App\Events;

use App\Models\Room;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoomUpdatedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Room $room;
    public bool $closed;
    public string $leftPos;
    public string $removedPos;

    public function __construct(Room $room, ?bool $closed = null, ?string $leftPos = null, ?string $removedPos = null)
    {
        try {
            $room->event_counter++;
            $room->save();
        } catch (\Throwable $th) {
        }

        $this->room = $room;
        $this->closed = $closed ?? false;
        $this->leftPos = $leftPos ?? '';
        $this->removedPos = $removedPos ?? '';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("room.{$this->room->id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'updated';
    }
}
