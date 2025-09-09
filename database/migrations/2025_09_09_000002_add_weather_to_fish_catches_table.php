<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		// No-op: Weather data is stored inside the existing 'environmental_data' JSON column.
		// This migration exists for historical alignment; nothing to change.
	}

	public function down(): void
	{
		// No-op
	}
};

