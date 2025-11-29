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
            // Add bycatch columns if they don't exist
            if (! Schema::hasColumn('fish_catches', 'bycatch_quantity')) {
                $table->decimal('bycatch_quantity', 10, 2)->nullable()->after('avg_size_cm');
            }
            if (! Schema::hasColumn('fish_catches', 'bycatch_species_ids')) {
                $table->json('bycatch_species_ids')->nullable()->after('bycatch_quantity');
            }
            // Add discard columns if they don't exist
            if (! Schema::hasColumn('fish_catches', 'discard_quantity')) {
                $table->decimal('discard_quantity', 10, 2)->nullable()->after('bycatch_species_ids');
            }
            if (! Schema::hasColumn('fish_catches', 'discard_species_ids')) {
                $table->json('discard_species_ids')->nullable()->after('discard_quantity');
            }
            if (! Schema::hasColumn('fish_catches', 'discard_reason')) {
                $table->string('discard_reason')->nullable()->after('discard_species_ids');
            }
            if (! Schema::hasColumn('fish_catches', 'discard_reason_other')) {
                $table->string('discard_reason_other')->nullable()->after('discard_reason');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fish_catches', function (Blueprint $table) {
            $columns = ['bycatch_quantity', 'bycatch_species_ids', 'discard_quantity', 'discard_species_ids', 'discard_reason', 'discard_reason_other'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('fish_catches', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
