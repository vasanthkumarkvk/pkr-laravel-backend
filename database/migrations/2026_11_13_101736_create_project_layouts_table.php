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
    Schema::create('project_layouts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
        
        $table->string('title'); // e.g., Unit A105
        $table->string('block_name')->nullable(); // e.g., Block A
        $table->string('unit_type'); // e.g., Studio
        $table->string('facing'); // e.g., East
        $table->string('area'); // e.g., 469 Sq.ft
        
        $table->text('description')->nullable();
        $table->string('plan_image')->nullable(); // Layout image
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_layouts');
    }
};
