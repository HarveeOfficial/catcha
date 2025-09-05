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
        Schema::create('fish_catches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('species_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('caught_at');
            $table->decimal('quantity', 10, 2)->default(0); // in kg
            $table->unsignedInteger('count')->nullable();
            $table->decimal('avg_size_cm', 6, 2)->nullable();
            $table->string('gear_type')->nullable();
            $table->string('vessel_name')->nullable();
            $table->json('environmental_data')->nullable(); // temp, weather, etc.
            $table->json('notes')->nullable();
            $table->boolean('flagged')->default(false);
            $table->string('flag_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fish_catches');
    }
};
