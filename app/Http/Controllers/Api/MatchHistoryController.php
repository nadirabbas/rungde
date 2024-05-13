<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MatchHistory;
use App\Models\User;
use Illuminate\Http\Request;

class MatchHistoryController extends Controller
{
    public function index(Request $request)
    {
        $history = MatchHistory::with([
            'winner_1',
            'winner_2',
            'loser_1',
            'loser_2',
        ])->where(function ($q) use ($request) {
            if ($request->input('only_self')) {
                $q->where('winner_1_id', auth()->id())
                    ->orWhere('winner_2_id', auth()->id())
                    ->orWhere('loser_1_id', auth()->id())
                    ->orWhere('loser_2_id', auth()->id());
            }
        })->latest()->paginate(50);

        return $history;
    }

    public function leaderboard(Request $request)
    {
        $request->validate([
            'sort_by' => 'required|in:goon_courts,courts,games_won,games_played,tricks'
        ]);

        $sortBy = [
            'goon_courts' => 'goon_courts',
            'courts' => 'courts',
            'games_won' => 'games_won',
            'games_played' => 'games_played',
            'tricks' => 'sirs'
        ][$request->input('sort_by')];

        $leaderboard = User::orderBy($sortBy, 'desc')->paginate(10);
        return $leaderboard;
    }
}
