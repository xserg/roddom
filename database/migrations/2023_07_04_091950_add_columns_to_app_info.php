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
        Schema::table('app_info', function (Blueprint $table) {
            $table->string('successful_purchase_image')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_info', function (Blueprint $table) {
            $table->dropColumn([
                'successful_purchase_image',
            ]);
        });
    }
};
