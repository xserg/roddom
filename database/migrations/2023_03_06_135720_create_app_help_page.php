<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('app_help_page', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('text');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_help_page');
    }
};
