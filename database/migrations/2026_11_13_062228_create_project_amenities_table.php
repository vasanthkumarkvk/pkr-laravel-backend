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
    Schema::create('project_amenities', function (Blueprint $table) {
        $table->id();
        $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
        
        // Main Heading & Desc (Left side)
        $table->string('section_subtitle')->nullable()->default('LIFE AT PRIVANA');
        $table->string('section_title')->nullable()->default('Everyday Comforts, Exceptional Living');
$table->text('section_description')->nullable();        
        // Grid Icons JSON array: [{"name": "Gym", "icon": "path/gym.png"}, ...]
        $table->json('amenities_list')->nullable();
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_amenities');
    }
};
