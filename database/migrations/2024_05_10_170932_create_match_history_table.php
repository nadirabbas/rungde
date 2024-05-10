<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::create('match_history', function (Blueprint $table) {
            $u = function ($s) use ($table) {
                $table->unsignedBigInteger($s)->nullable();
                $table->foreign($s)->references('id')->on('users');
            };

            $table->id();

            $u('winner_1_id');
            $u('winner_2_id');
            $u('loser_1_id');
            $u('loser_2_id');

            $table->boolean('is_court')->default(false);
            $table->boolean('is_goon_court')->default(false);

            $table->float('winner_score')->default(0);
            $table->float('loser_score')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_history');
    }
};
