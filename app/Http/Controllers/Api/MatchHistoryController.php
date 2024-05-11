<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MatchHistory;
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
}
