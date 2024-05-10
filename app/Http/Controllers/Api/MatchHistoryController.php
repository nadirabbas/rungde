<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MatchHistory;
use Illuminate\Http\Request;

class MatchHistoryController extends Controller
{
    public function index()
    {
        $history = MatchHistory::with([
            'winner_1',
            'winner_2',
            'loser_1',
            'loser_2',
        ])->latest()->paginate(50);

        return $history;
    }
}
