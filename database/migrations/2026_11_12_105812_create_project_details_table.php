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
    Schema::create('project_details', function (Blueprint $table) {
        $table->id();
        // Connect to projects table
        $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
        
        // Hero Section Details
        $table->string('hero_subtitle')->nullable()->default('MORE THAN JUST A HOME');
        $table->string('hero_title')->nullable()->default('Designed for a Better Way of Life');
$table->text('hero_description')->nullable();
        $table->string('hero_image')->nullable();
        
        // Call Button
        $table->string('call_button_text')->nullable()->default('Call Us');
        $table->string('call_button_number')->nullable()->default('+919876543210');
        
        // Property Details
        $table->string('prop_type')->nullable()->default('Studio, 1, 2 & 3 BHK');
        $table->string('prop_size')->nullable()->default('1.41 Acres');
        $table->string('prop_units')->nullable()->default('186');
        $table->string('prop_price')->nullable()->default('₹ 5,100 / Sq.Ft');
        $table->string('prop_floors')->nullable()->default('Stilt + 5');
        
        // RERA Details
        $table->string('rera_number')->nullable()->default('TNRERA/35/BLG/0317/2026');
        $table->string('rera_pdf')->nullable(); 
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_details');
    }
};
