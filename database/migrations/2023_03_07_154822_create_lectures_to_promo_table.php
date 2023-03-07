<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lectures_to_promo', function (Blueprint $table) {
            $table->primary(['promo_id', 'lecture_id']);
            $table->bigInteger('promo_id')->unsigned();
            $table->bigInteger('lecture_id')->unsigned();

            $table->foreign('promo_id')->references('id')->on('promos');
            $table->foreign('lecture_id')->references('id')->on('lectures');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lectures_to_promo');
    }
};
