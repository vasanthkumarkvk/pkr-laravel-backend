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
    Schema::table('home_contents', function (Blueprint $table) {
        // Puthu 2 columns add pandrom
        $table->json('desktop_banners')->nullable()->after('banners');
        $table->json('mobile_banners')->nullable()->after('desktop_banners');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('home_contents', function (Blueprint $table) {
            //
        });
    }
};
