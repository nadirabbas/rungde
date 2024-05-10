<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MatchHistory>
 */
class MatchHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isCourt = $this->faker->boolean();
        $isGoonCourt = $isCourt ? false : $this->faker->boolean();

        $winnerScore = $this->faker->numberBetween(0, 13);
        $loserScore = 13 - $winnerScore;

        return [
            'winner_1_id' => User::inRandomOrder()->first()->id,
            'winner_2_id' => User::inRandomOrder()->first()->id,
            'loser_1_id' => User::inRandomOrder()->first()->id,
            'loser_2_id' => User::inRandomOrder()->first()->id,
            'is_court' => $isCourt,
            'is_goon_court' => $isGoonCourt,
            'winner_score' => $winnerScore,
            'loser_score' => $loserScore,
        ];
    }
}
