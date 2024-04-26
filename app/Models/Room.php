<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'ended_at',
        'rung',
        'turn',
        'card_position_1',
        'card_position_2',
        'card_position_3',
        'card_position_4',
        'folded_deck_count',
        'started_at',
        'rung_selector',
        'turn_rung',
        'total_turns',
        'last_highest_card_position',
        'latest_turn',
        'latest_turn_position',
        'last_winner_id',
        'team_1_3_wins',
        'team_2_4_wins',
    ];

    protected $appends = [
        'is_ended'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function participants()
    {
        return $this->hasMany(RoomUser::class)->with('user');
    }

    public function getIsEndedAttribute()
    {
        return $this->ended_at !== null;
    }
}
