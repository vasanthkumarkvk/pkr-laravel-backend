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
    Schema::create('cta_banners', function (Blueprint $table) {
        $table->id();
        $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
        
        // Text Details
        $table->string('title')->nullable()->default('Crafting Your Perfect Space');
        $table->text('description')->nullable();
        
        // Button
        $table->string('button_text')->nullable()->default('Chat With Us');
        $table->string('whatsapp_number')->nullable(); // e.g., +919876543210
        
        // Background Image
        $table->string('background_image')->nullable();
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cta_banners');
    }
};
