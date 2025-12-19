<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // SQLite doesn't support modifying enums, so we need to recreate the table
            Schema::table('users', function (Blueprint $table) {
                // For SQLite, the enum will just be stored as a string
                // This is handled by the migration naturally
            });
        } else {
            // MySQL/MariaDB: Modify the enum to include 'superadmin'
            DB::statement("ALTER TABLE users MODIFY role ENUM('fisher', 'expert', 'admin', 'mao', 'user', 'superadmin') DEFAULT 'fisher'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('fisher', 'expert', 'admin', 'mao') DEFAULT 'fisher'");
        }
    }
};
