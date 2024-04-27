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
        Schema::table('rooms', function (Blueprint $table) {
            $table->float('team_1_3_goon_courts')->default(0);
            $table->float('team_2_4_goon_courts')->default(0);
            $table->float('team_1_3_courts')->default(0);
            $table->float('team_2_4_courts')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('team_1_3_goon_courts');
            $table->dropColumn('team_2_4_goon_courts');
            $table->dropColumn('team_1_3_courts');
            $table->dropColumn('team_2_4_courts');
        });
    }
};
