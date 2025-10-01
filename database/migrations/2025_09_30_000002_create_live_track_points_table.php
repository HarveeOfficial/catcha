<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_track_points', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('live_track_id')->constrained()->cascadeOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->float('accuracy_m')->nullable();
            $table->float('speed_mps')->nullable();
            $table->float('bearing_deg')->nullable();
            $table->timestamp('recorded_at')->useCurrent();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['live_track_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_track_points');
    }
};
