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
        Schema::create('air_pollution_leaderboard', function (Blueprint $table) {
            $table->id();
            $table->integer('rank');
            $table->string('station_name');
            $table->string('city');
            $table->string('air_quality_index');
            $table->float('pm10')->nullable();
            $table->float('pm25')->nullable();
            $table->float('no2')->nullable();
            $table->float('so2')->nullable();
            $table->float('o3')->nullable();
            $table->float('co')->nullable();
            $table->timestamp('timestamp');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('air_pollution_leaderboard');
    }
};
