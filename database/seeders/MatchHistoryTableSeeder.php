<?php

namespace Database\Seeders;

use App\Models\MatchHistory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MatchHistoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MatchHistory::factory(20)->create();
    }
}
