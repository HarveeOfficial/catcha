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
        Schema::table('fish_catches', function (Blueprint $table) {
            // Drop the foreign key constraints first
            $table->dropForeign(['bycatch_species_id']);
            $table->dropForeign(['discard_species_id']);
            // Drop the old foreign key columns
            $table->dropColumn(['bycatch_species_id', 'discard_species_id']);
            // Add JSON columns for multiple species
            $table->json('bycatch_species_ids')->nullable();
            $table->json('discard_species_ids')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fish_catches', function (Blueprint $table) {
            $table->dropColumn(['bycatch_species_ids', 'discard_species_ids']);
            $table->unsignedBigInteger('bycatch_species_id')->nullable();
            $table->foreign('bycatch_species_id')->references('id')->on('species')->nullOnDelete();
            $table->unsignedBigInteger('discard_species_id')->nullable();
            $table->foreign('discard_species_id')->references('id')->on('species')->nullOnDelete();
        });
    }
};
