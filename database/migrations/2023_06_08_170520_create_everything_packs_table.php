<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('everything_pack', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('price')->nullable()->default(null);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('everything_pack');
    }
};
