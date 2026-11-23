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
    Schema::create('project_pricing_headers', function (Blueprint $table) {
        $table->id();
        $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
        
        // Left Side Texts
        $table->string('eyebrow')->nullable()->default('PRE-LAUNCH OFFER');
        $table->string('heading')->nullable()->default('Homes & Pricing');
        $table->text('description')->nullable();
        
        // 3 Icons
        $table->string('icon_1_image')->nullable();
        $table->string('icon_1_title')->nullable()->default('Clear Titles');
        $table->string('icon_1_desc')->nullable()->default('Hassle-free ownership');
        
        $table->string('icon_2_image')->nullable();
        $table->string('icon_2_title')->nullable()->default('Competitive Pricing');
        $table->string('icon_2_desc')->nullable()->default('Great value for your investment');
        
        $table->string('icon_3_image')->nullable();
        $table->string('icon_3_title')->nullable()->default('Future Growth');
        $table->string('icon_3_desc')->nullable()->default('A location with lasting potential');
        
        // Bottom CTA Card
        $table->string('cta_title')->nullable()->default('A Brighter Tomorrow Awaits');
$table->text('cta_description')->nullable();
        $table->string('cta_image')->nullable();
        $table->string('cta_button_text')->nullable()->default('Enquire Now');

        // Top Right Info
        $table->string('validity_message')->nullable()->default('Pre-launch offer valid till Nov 8th 2026');
        $table->json('filter_tabs')->nullable(); // e.g. ["All Units", "Studio", "1 BHK"]
        
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_pricing_headers');
    }
};
