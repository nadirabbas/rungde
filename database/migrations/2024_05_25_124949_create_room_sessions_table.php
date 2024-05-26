<?php

use App\Models\User;
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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('rung');

            $table->unsignedBigInteger('room_creator_id')->nullable();
            $table->foreign('room_creator_id')->references('id')->on('users')->nullOnDelete();

            $table->integer('rung_selector_position');

            $table->enum('victory', ['team_1', 'team_2'])->nullable();
            $table->boolean('is_finished')->default(false);

            $table->timestamps();
        });

        Schema::table('game_participants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('game_id');
            $table->foreign('game_id')->references('id')->on('games')->cascadeOnDelete();

            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();

            $table->integer('position');
            $table->timestamps();
        });

        Schema::table('game_turn', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('game_participant_id');
            $table->foreign('game_participant_id')->references('id')->on('game_participants')->cascadeOnDelete();

            $table->string('card');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
