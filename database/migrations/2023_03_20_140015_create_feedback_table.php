<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('lecture_id')->unsigned();
            $table->bigInteger('lector_id')->unsigned();
            $table->text('content');

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('lecture_id')->references('id')->on('lectures');
            $table->foreign('lector_id')->references('id')->on('lectors');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
