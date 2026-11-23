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
    Schema::create('project_connectivities', function (Blueprint $table) {
        $table->id();
        $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
        
        // Left side Text & Button
        $table->string('title')->nullable()->default('Well Connected for a Brighter You');
        $table->text('description')->nullable();
        $table->string('button_text')->nullable()->default('Get Directions');
        
        // Map Link (Google Maps Embed URL illa iframe source)
        $table->text('map_link')->nullable();
        
        // Right Side List (Location + Time)
        // Example: [{"location": "Guduvancheri", "time": "5 mins"}, ...]
        $table->json('locations')->nullable();
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_connectivities');
    }
};
