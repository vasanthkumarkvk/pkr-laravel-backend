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
    Schema::create('home_contents', function (Blueprint $table) {
        $table->id();
        
        // Popup Banner Image
        $table->string('popup_image')->nullable();
        
        // Main Banners (JSON array-a store pannuvom, multiple images irukkum)
        $table->json('banners')->nullable(); 
        
        // Legacy Stats Section
        $table->string('legacy_years')->default('9+');
        $table->string('legacy_years_text')->default('YEARS OF EXCELLENCE');
        $table->string('legacy_space')->default('1L+');
        $table->string('legacy_space_text')->default('SQ.FT. SPACE DELIVERED');
        $table->string('legacy_landmark')->default('3+');
        $table->string('legacy_landmark_text')->default('LANDMARK');
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_contents');
    }
};
