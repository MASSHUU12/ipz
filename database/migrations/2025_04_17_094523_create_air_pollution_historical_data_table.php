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
        Schema::create('air_pollution_historical_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('station_id')->nullable();
            $table->float('latitude', 8, 6);
            $table->float('longitude', 8, 6);
            $table->string('station_name');
            $table->string('air_quality_index')->nullable();
            $table->float('pm10')->nullable();
            $table->float('pm25')->nullable();
            $table->float('no2')->nullable();
            $table->float('so2')->nullable();
            $table->float('o3')->nullable();
            $table->float('co')->nullable();
            $table->json('measurements')->nullable();
            $table->json('forecasts')->nullable();
            $table->timestamps();
            $table->index('station_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('air_pollution_historical_data');
    }
};
