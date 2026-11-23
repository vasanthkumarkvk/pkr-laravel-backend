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
    Schema::create('three_sixty_details', function (Blueprint $table) {
        $table->id();
        $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
        
        // Left Side Text
        $table->string('eyebrow')->nullable()->default('EXPLORE BEFORE YOU BELONG');
        $table->string('title')->nullable()->default('Experience Privana in 360°');
        $table->text('description')->nullable();
        
        // Images
        $table->string('cover_image')->nullable(); // Play button irukkura image
        $table->string('panorama_image')->nullable(); // 360-degree rotate aagura image
        
        // Button
        $table->string('button_text')->nullable()->default('Start 360° Tour');
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('three_sixty_details');
    }
};
