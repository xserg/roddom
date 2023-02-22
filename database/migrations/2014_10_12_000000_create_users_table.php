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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->date('birthdate')->nullable()->default(null);
            $table->string('phone', 20)->nullable()->default(null);
            $table->boolean('is_mother')->default(0);
            $table->date('pregnancy_start')->nullable()->default(null);
            $table->date('baby_born')->nullable()->default(null);
            $table->string('photo')->nullable()->default(null);
            $table->boolean('to_delete')->default(false);
            $table->dateTime('free_lecture_watched')->nullable()->default(null);

            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
};
