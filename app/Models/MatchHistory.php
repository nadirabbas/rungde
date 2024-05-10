<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchHistory extends Model
{
    use HasFactory;

    public $table = 'match_history';

    protected $fillable = [
        'winner_1_id',
        'winner_2_id',
        'loser_1_id',
        'loser_2_id',
        'is_court',
        'is_goon_court',
        'winner_score',
        'loser_score',
    ];

    protected $casts = [
        'is_court' => 'boolean',
        'is_goon_court' => 'boolean',
    ];

    protected function loadUserRelation(string $key)
    {
        return $this->belongsTo(User::class, $key)->select(['id', 'username']);
    }

    public function winner_1()
    {
        return $this->loadUserRelation('winner_1_id');
    }

    public function winner_2()
    {
        return $this->loadUserRelation('winner_2_id');
    }

    public function loser_1()
    {
        return $this->loadUserRelation('loser_1_id');
    }

    public function loser_2()
    {
        return $this->loadUserRelation('loser_2_id');
    }
}
