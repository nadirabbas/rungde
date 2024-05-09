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
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable();
            $table->integer('games_played')->default(0);
            $table->integer('games_won')->default(0);
            $table->integer('sirs')->default(0);
            $table->integer('goon_courts')->default(0);
            $table->integer('courts')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('avatar');
            $table->dropColumn('games_played');
            $table->dropColumn('games_won');
            $table->dropColumn('sirs');
            $table->dropColumn('goon_courts');
            $table->dropColumn('courts');
        });
    }
};
