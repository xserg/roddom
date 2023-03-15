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
        Schema::create('category_prices', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('category_id')->unsigned();
            $table->bigInteger('period_id')->unsigned();
            $table->bigInteger('price_for_pack')->unsigned();
            $table->bigInteger('price_for_one_lecture')->unsigned();

            $table->foreign('category_id')->references('id')->on('lecture_categories');
            $table->foreign('period_id')->references('id')->on('subscription_periods');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_prices');
    }
};
