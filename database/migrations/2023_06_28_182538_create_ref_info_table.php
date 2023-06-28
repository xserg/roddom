<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ref_info', function (Blueprint $table) {
            $table->id();

            $table->string('depth_level');
            $table->integer('percent');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_info');
    }
};
