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
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id('user_id');
            $table->enum('notice_method', ['SMS', 'E-mail', 'Both'])->default('E-mail');
            $table->string('city');
            $table->boolean('meteorological_warnings')->default(false);
            $table->boolean('hydrological_warnings')->default(false);
            $table->boolean('temperature_warning')->default(false);
            $table->decimal('temperature_check_value', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
