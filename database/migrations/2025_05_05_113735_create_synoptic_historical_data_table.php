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
        Schema::create('synoptic_historical_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('station_id');
            $table->string('station_name');
            $table->timestamp('measurement_date');
            $table->integer('measurement_hour');
            $table->float('temperature')->nullable();
            $table->integer('wind_speed')->nullable();
            $table->integer('wind_direction')->nullable();
            $table->float('relative_humidity')->nullable();
            $table->float('rainfall_total')->nullable();
            $table->float('pressure')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('synoptic_historical_data');
    }
};
