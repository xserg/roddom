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
        Schema::create('lectures_prices', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('lecture_id')->unsigned();
            $table->bigInteger('period_id')->unsigned();
            $table->bigInteger('price')->unsigned();

            $table->foreign('lecture_id')->references('id')->on('lectures')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('period_id')->references('id')->on('subscription_periods')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lectures_prices');
    }
};
