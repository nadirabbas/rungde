<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class RoomUser extends Pivot
{
    use HasFactory;

    public $table = 'room_users';

    protected $fillable = [
        'room_id',
        'user_id',
        'position',
        'cards',
        'sir_count'
    ];

    protected $casts = [
        'cards' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
