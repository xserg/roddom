<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_to_watched_lectures', function (Blueprint $table) {
            $table->primary(['user_id', 'lecture_id']);
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('lecture_id')->unsigned();

            $table->foreign('lecture_id')->references('id')->on('lectures')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_to_watched_lectures');
    }
};
