<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catch_feedback_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catch_feedback_id')->constrained('catch_feedback')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['catch_feedback_id','user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catch_feedback_likes');
    }
};
