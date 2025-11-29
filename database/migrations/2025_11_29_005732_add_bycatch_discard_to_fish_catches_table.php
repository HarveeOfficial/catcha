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
            $table->decimal('bycatch_quantity', 10, 2)->nullable()->after('count');
            $table->string('bycatch_species')->nullable()->after('bycatch_quantity');
            $table->decimal('discard_quantity', 10, 2)->nullable()->after('bycatch_species');
            $table->string('discard_reason')->nullable()->after('discard_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fish_catches', function (Blueprint $table) {
            $table->dropColumn(['bycatch_quantity', 'bycatch_species', 'discard_quantity', 'discard_reason']);
        });
    }
};
