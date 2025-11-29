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
            $table->dropColumn(['bycatch_species', 'discard_quantity']);
            $table->unsignedBigInteger('bycatch_species_id')->nullable()->after('bycatch_quantity');
            $table->foreign('bycatch_species_id')->references('id')->on('species')->nullOnDelete();
            $table->unsignedBigInteger('discard_species_id')->nullable()->after('discard_reason');
            $table->foreign('discard_species_id')->references('id')->on('species')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fish_catches', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Species::class, 'bycatch_species_id');
            $table->dropForeignIdFor(\App\Models\Species::class, 'discard_species_id');
            $table->dropColumn(['bycatch_species_id', 'discard_species_id']);
            $table->string('bycatch_species')->nullable()->after('bycatch_quantity');
            $table->decimal('discard_quantity', 10, 2)->nullable()->after('bycatch_species');
        });
    }
};
