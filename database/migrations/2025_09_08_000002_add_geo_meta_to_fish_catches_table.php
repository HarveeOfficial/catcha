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
            if (! Schema::hasColumn('fish_catches', 'geo_accuracy_m')) {
                $table->decimal('geo_accuracy_m', 8, 2)->nullable()->after('longitude');
            }
            if (! Schema::hasColumn('fish_catches', 'geohash')) {
                $table->string('geohash', 16)->nullable()->after('geo_accuracy_m');
            }
            if (! Schema::hasColumn('fish_catches', 'geo_source')) {
                $table->string('geo_source', 30)->nullable()->after('geohash'); // e.g. html5, manual, drag, watch
            }
        });

        // Indexing geohash for prefix (region) searches.
        Schema::table('fish_catches', function (Blueprint $table) {
            if (Schema::hasColumn('fish_catches', 'geohash')) {
                $table->index('geohash');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fish_catches', function (Blueprint $table) {
            if (Schema::hasColumn('fish_catches', 'geohash')) {
                $table->dropIndex(['geohash']);
            }
        });
        Schema::table('fish_catches', function (Blueprint $table) {
            if (Schema::hasColumn('fish_catches', 'geo_source')) {
                $table->dropColumn('geo_source');
            }
            if (Schema::hasColumn('fish_catches', 'geohash')) {
                $table->dropColumn('geohash');
            }
            if (Schema::hasColumn('fish_catches', 'geo_accuracy_m')) {
                $table->dropColumn('geo_accuracy_m');
            }
        });
    }
};
