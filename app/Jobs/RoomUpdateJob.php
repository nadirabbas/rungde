<?php

namespace App\Jobs;

use App\Events\RoomUpdatedEvent;
use App\Models\Room;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RoomUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $roomId, public array $data)
    {
        $this->roomId = $roomId;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $room = Room::find($this->roomId);
        $room->update($this->data);
        collect($this->data['room_users'])->each(fn ($users, $position) => $room->participants()->where('position', $position)->first()?->update($users));

        event(new RoomUpdatedEvent($room->fresh()->withEventRelations()));
    }
}
