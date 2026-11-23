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
    Schema::create('project_pricings', function (Blueprint $table) {
        $table->id();
        $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
        
        $table->string('flat_no'); // A101
        $table->string('floor'); // 1st floor
        $table->string('type'); // 2 BHK + 2T
        $table->string('facing'); // East
        $table->string('area'); // 1259 sq.ft
        
        $table->string('original_price')->nullable(); // ₹75.52L
        $table->string('offer_price'); // ₹65.45L
        $table->boolean('is_pre_launch')->default(true); // Pre-launch tag
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_pricings');
    }
};
