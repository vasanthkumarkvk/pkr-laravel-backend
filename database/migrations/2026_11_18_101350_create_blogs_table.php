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
    Schema::create('blogs', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->string('category'); // e.g., REAL ESTATE, HOME BUYING
        $table->text('short_description'); // Card la kaatradhuku
        $table->longText('content'); // Full blog content (HTML)
        $table->string('image'); // Thumbnail image path
        $table->string('read_time')->default('5 MIN READ');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
