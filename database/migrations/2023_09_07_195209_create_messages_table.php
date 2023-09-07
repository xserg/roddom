<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            $table->string('message', 1024)->nullable();
            $table->unsignedBigInteger('thread_id');
            $table->unsignedBigInteger('author_id');

            $table->foreign('thread_id')->references('id')->on('threads')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('author_id')->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
