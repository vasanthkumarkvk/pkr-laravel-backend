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
    Schema::create('project_layout_headers', function (Blueprint $table) {
        $table->id();
        $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
        
        $table->string('eyebrow')->nullable()->default('FLOOR PLANS');
        $table->string('heading')->nullable()->default('Homes Tailored to Your Needs');
        $table->text('description')->nullable();
        
        // Filters (e.g., "Studio, 1 BHK, 2 BHK")
        $table->json('unit_type_groups')->nullable(); 
        // Blocks (e.g., [{"name": "Block A", "status": "UNDER CONSTRUCTION"}])
        $table->json('blocks')->nullable();
        
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_layout_headers');
    }
};
