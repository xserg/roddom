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
            $table->bigInteger('lector_id')->unsigned();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('preview_picture')->nullable();
            $table->string('video');

            $table->timestamps();

            $table->foreign('lector_id')->references('id')->on('lectors');
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
