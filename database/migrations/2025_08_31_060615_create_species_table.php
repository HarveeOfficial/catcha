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
        Schema::create('species', function (Blueprint $table) {
            $table->id();
            $table->string('common_name');
            $table->string('scientific_name')->nullable();
            $table->string('conservation_status')->nullable(); // e.g., LC, NT, VU, EN
            $table->decimal('min_size_cm', 6, 2)->nullable();
            $table->json('seasonal_restrictions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('species');
    }
};
