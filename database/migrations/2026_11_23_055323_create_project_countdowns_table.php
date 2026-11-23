<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_countdowns', function (Blueprint $table) {
            $table->id();
            // Project delete aana, countdown um delete aaganum
            $table->foreignId('project_id')->constrained()->onDelete('cascade'); 
            $table->dateTime('completion_date'); // e.g., 2026-12-31 23:59:59
            $table->boolean('is_active')->default(true); // Enable/Disable countdown
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_countdowns');
    }
};