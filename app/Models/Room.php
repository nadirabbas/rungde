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
        'deck',
        'team_1_3_goon_courts',
        'team_2_4_goon_courts',
        'team_1_3_courts',
        'team_2_4_courts',
        'new_deck',
        'event_counter'
    ];

    protected $appends = [
        'is_ended'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'deck' => 'array',
        'new_deck' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function participants()
    {
        return $this->hasMany(RoomUser::class)->with('user');
    }

    public function spectators()
    {
        return $this->hasMany(RoomSpectator::class)->with('user');
    }

    public function getIsEndedAttribute()
    {
        return $this->ended_at !== null;
    }

    public function withEventRelations()
    {
        return $this->load([
            'participants',
            'spectators'
        ]);
    }

    public function reset()
    {
        // same as reset room from Game.vue
        $this->update([
            "latest_turn" => null,
            "latest_turn_position" => null,
            "ended_at" => null,
            "started_at" => null,
            "rung" => null,
            "rung_selector" => null,
            "turn" => null,
            "card_position_1" => null,
            "card_position_2" => null,
            "card_position_3" => null,
            "card_position_4" => null,
            "total_turns" => 0,
            "folded_deck_count" => 0,
            "turn_rung" => null,
            "last_highest_card_position" => null,
            "team_1_3_wins" => 0,
            "team_2_4_wins" => 0,
            "team_1_3_goon_courts" => 0,
            "team_2_4_goon_courts" => 0,
            "team_1_3_courts" => 0,
            "team_2_4_courts" => 0,
            "deck" => [],
        ]);

        $this->participants()->update([
            'sir_count' => 0,
            'cards' => []
        ]);
    }
}
