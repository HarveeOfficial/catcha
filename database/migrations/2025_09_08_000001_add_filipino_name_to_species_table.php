<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('species', function (Blueprint $table) {
            if (! Schema::hasColumn('species', 'filipino_name')) {
                $table->string('filipino_name')->nullable()->after('common_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('species', function (Blueprint $table) {
            if (Schema::hasColumn('species', 'filipino_name')) {
                $table->dropColumn('filipino_name');
            }
        });
    }
};
