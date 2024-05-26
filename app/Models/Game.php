<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'rung',
        'room_creator_id',
        'rung_selector_position',
        'victory',
        'is_finished',
    ];

    protected $casts = [
        'is_finished' => 'boolean',
    ];

    public function roomCreator()
    {
        return $this->belongsTo(User::class, 'room_creator_id');
    }

    public function participants()
    {
        return $this->hasMany(GameParticipant::class);
    }
}
