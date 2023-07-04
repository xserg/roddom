<?php

use App\Models\Period;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('full_catalog_prices', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Period::class);
            $table->unsignedBigInteger('price_for_one_lecture');
            $table->unsignedBigInteger('price_for_one_lecture_promo');
            $table->boolean('is_promo')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('full_catalog_prices');
    }
};
