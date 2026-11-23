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
    Schema::create('projects', function (Blueprint $table) {
        $table->id();
        $table->string('name')->default('New Project');
        $table->string('status')->default('inactive'); // Default inactive
        
        // Banner Related Images Only
        $table->string('thumbnail_image')->nullable();
        $table->string('desktop_banner')->nullable();
        $table->string('mobile_banner')->nullable();
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
