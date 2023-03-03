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
        Schema::create('promo_pack_prices', function (Blueprint $table) {
            $table->primary(['promo_id', 'period_id']);

            $table->bigInteger('promo_id')->unsigned();
            $table->bigInteger('period_id')->unsigned();
            $table->bigInteger('price')->unsigned();

            $table->foreign('period_id')->references('id')->on('subscription_periods');
            $table->foreign('promo_id')->references('id')->on('promos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promo_pack_prices');
    }
};
