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
        Schema::create('boats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('registration_number')->unique();
            $table->string('name');
            $table->string('owner_name');
            $table->string('owner_contact')->nullable();
            $table->enum('boat_type', ['motorized', 'non-motorized'])->default('motorized');
            $table->decimal('length_m', 8, 2)->nullable();
            $table->decimal('width_m', 8, 2)->nullable();
            $table->decimal('gross_tonnage', 8, 2)->nullable();
            $table->string('engine_type')->nullable();
            $table->integer('engine_horsepower')->nullable();
            $table->string('home_port')->nullable();
            $table->string('psgc_region')->nullable();
            $table->string('psgc_municipality')->nullable();
            $table->string('psgc_barangay')->nullable();
            $table->date('registration_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->enum('status', ['active', 'expired', 'suspended', 'decommissioned'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boats');
    }
};
