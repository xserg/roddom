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
        Schema::table('promo_pack_prices', function (Blueprint $table) {
            $table->bigInteger('price_for_one_lecture')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promo_pack_prices', function (Blueprint $table) {
            $table->dropColumn(['price_for_one_lecture']);
        });
    }
};
