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
    Schema::table('project_pricing_headers', function (Blueprint $table) {
        // Default ah 'yes' (offer price kaatanum)
        $table->enum('offer_price_status', ['yes', 'no'])->default('yes')->after('validity_message');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_pricing_headers', function (Blueprint $table) {
            //
        });
    }
};
