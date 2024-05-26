<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameTurn extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_participant_id',
        'card',
    ];

    public function participant()
    {
        return $this->belongsTo(GameParticipant::class);
    }

    public function game()
    {
        return $this->belongsToThrough(Game::class, GameParticipant::class);
    }

    public function user()
    {
        return $this->belongsToThrough(User::class, GameParticipant::class);
    }
}
