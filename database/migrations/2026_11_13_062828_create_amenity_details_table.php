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
    Schema::create('amenity_details', function (Blueprint $table) {
        $table->id();
        $table->foreignId('project_amenity_id')->constrained('project_amenities')->onDelete('cascade');
        
        $table->string('tag')->nullable(); // GATHER & CELEBRATE
        $table->string('title'); // Club House
        $table->text('description')->nullable();
        
        $table->json('tags')->nullable(); // ["Events", "Community", "Social"]
        $table->json('images')->nullable(); // Slider images paths
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amenity_details');
    }
};
