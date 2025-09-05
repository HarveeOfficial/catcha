<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guidances', function (Blueprint $table) {
            $table->enum('status', ['pending','approved','rejected'])->default('pending')->after('active');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('rejected_reason')->nullable()->after('approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('guidances', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['status','approved_by','approved_at','rejected_reason']);
        });
    }
};
