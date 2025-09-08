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
            if (! Schema::hasColumn('fish_catches', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('location');
            }
            if (! Schema::hasColumn('fish_catches', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fish_catches', function (Blueprint $table) {
            if (Schema::hasColumn('fish_catches', 'longitude')) {
                $table->dropColumn('longitude');
            }
            if (Schema::hasColumn('fish_catches', 'latitude')) {
                $table->dropColumn('latitude');
            }
        });
    }
};
