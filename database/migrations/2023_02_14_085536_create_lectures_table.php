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
        Schema::create('lectures', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('preview_picture')->nullable();
            $table->integer('video_id')->unsigned()->unique();
            $table->bigInteger('lector_id')->unsigned();
            $table->bigInteger('category_id')->unsigned();
            $table->boolean('is_free')->default(false);

            $table->timestamps();

            $table->foreign('lector_id')->references('id')->on('lectors');
            $table->foreign('category_id')->references('id')->on('lecture_categories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lectures');
    }
};
