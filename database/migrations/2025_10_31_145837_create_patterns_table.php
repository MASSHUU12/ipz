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
        Schema::create('patterns', function (Blueprint $table) {
            $table->id();
            $table->text('description')->nullable();
            $table->string('group')->nullable()->index();
            $table->string('pattern');
            $table->json('responses');
            $table->enum('access_level', ['public', 'authenticated', 'super_admin'])
                  ->default('public')
                  ->index();
            $table->string('callback')->nullable();
            $table->string('severity')->default('low');
            $table->integer('priority')->default(0);
            $table->boolean('enabled')->default(true);
            $table->unsignedBigInteger('hit_count')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->boolean('stop_processing')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patterns');
    }
};
