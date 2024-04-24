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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->enum('rung', ['c', 's', 'h', 'd'])->nullable();
            $table->enum('turn_rung', ['c', 's', 'h', 'd'])->nullable();
            $table->integer('rung_selector')->nullable();
            $table->integer('turn')->nullable();
            $table->string('card_position_1')->nullable();
            $table->string('card_position_2')->nullable();
            $table->string('card_position_3')->nullable();
            $table->string('card_position_4')->nullable();
            $table->integer('total_turns')->default(0);
            $table->integer('folded_deck_count')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->integer('last_highest_card_position')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
