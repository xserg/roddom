<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('user_to_watched_lectures');

        Schema::create('user_to_watched_lectures', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('lecture_id');

            $table->foreign('lecture_id')->references('id')->on('lectures')->cascadeOnUpdate();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnUpdate();

            $table->timestamps();
        });

        Schema::create('user_to_free_watched_lectures', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('lecture_id');

            $table->foreign('lecture_id')->references('id')->on('lectures')->cascadeOnUpdate();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnUpdate();

            $table->dateTime('available_until');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_to_watched_lectures');
    }
};
