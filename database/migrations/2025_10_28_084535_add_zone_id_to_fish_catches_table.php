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
            if (! Schema::hasColumn('fish_catches', 'zone_id')) {
                $table->foreignId('zone_id')->nullable()->after('species_id')->constrained('zones')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fish_catches', function (Blueprint $table) {
            if (Schema::hasColumn('fish_catches', 'zone_id')) {
                $table->dropForeign(['zone_id']);
                $table->dropColumn('zone_id');
            }
        });
    }
};
