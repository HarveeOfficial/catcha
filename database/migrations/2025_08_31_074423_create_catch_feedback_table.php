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
        Schema::create('catch_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fish_catch_id')->constrained('fish_catches')->cascadeOnDelete();
            $table->foreignId('expert_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating')->nullable(); // 1-5 sustainability / compliance rating
            $table->boolean('approved')->default(false);
            $table->text('comments')->nullable();
            $table->json('flags')->nullable(); // structured issues (undersized, season, etc.)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catch_feedback');
    }
};
