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
        Schema::create('promo_lectures_prices', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('lecture_id')->unsigned();
            $table->bigInteger('promo_id')->unsigned();
            $table->bigInteger('period_id')->unsigned();
            $table->bigInteger('price')->unsigned();

            $table->foreign('lecture_id')->references('id')->on('lectures');
            $table->foreign('promo_id')->references('id')->on('promos');
            $table->foreign('period_id')->references('id')->on('subscription_periods');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promo_lectures_prices');
    }
};
