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
        Schema::create('lecture_categories', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id')->unsigned()->default(0);
            $table->string('title');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->text('info')->nullable();
            $table->string('preview_picture')->nullable()->default(null);

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
        Schema::dropIfExists('lecture_categories');
    }
};
