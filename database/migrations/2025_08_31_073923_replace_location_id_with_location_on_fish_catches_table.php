<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fish_catches', function (Blueprint $table) {
            if (!Schema::hasColumn('fish_catches', 'location')) {
                $table->string('location')->nullable()->after('species_id');
            }
        });

        // Backfill location text from related locations table if present
        if (Schema::hasColumn('fish_catches', 'location_id')) {
            $catches = DB::table('fish_catches')->whereNotNull('location_id')->get();
            foreach ($catches as $c) {
                $locName = DB::table('locations')->where('id', $c->location_id)->value('name');
                if ($locName) {
                    DB::table('fish_catches')->where('id', $c->id)->update(['location' => $locName]);
                }
            }
        }

        Schema::table('fish_catches', function (Blueprint $table) {
            if (Schema::hasColumn('fish_catches', 'location_id')) {
                // Drop foreign key then column (try common constraint names)
                try { $table->dropForeign(['location_id']); } catch (Throwable $e) {}
                try { $table->dropColumn('location_id'); } catch (Throwable $e) {}
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fish_catches', function (Blueprint $table) {
            if (!Schema::hasColumn('fish_catches', 'location_id')) {
                $table->foreignId('location_id')->nullable()->after('species_id');
            }
        });
        // Can't reliably restore FK or data mapping here without ambiguity.
    }
};
