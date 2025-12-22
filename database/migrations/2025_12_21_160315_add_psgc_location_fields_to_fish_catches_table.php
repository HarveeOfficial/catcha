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
            $table->string('psgc_region')->nullable()->after('location');
            $table->string('psgc_municipality')->nullable()->after('psgc_region');
            $table->string('psgc_barangay')->nullable()->after('psgc_municipality');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fish_catches', function (Blueprint $table) {
            $table->dropColumn(['psgc_region', 'psgc_municipality', 'psgc_barangay']);
        });
    }
};
