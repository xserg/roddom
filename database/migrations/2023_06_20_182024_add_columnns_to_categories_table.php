<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lecture_categories', function (Blueprint $table) {
            $table->boolean('is_promo')
                ->default(false)
                ->after('preview_picture');
        });
        Schema::table('category_prices', function (Blueprint $table) {
            $table->unsignedBigInteger('price_for_one_lecture_promo')
                ->nullable()
                ->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lecture_categories', function (Blueprint $table) {
            $table->dropColumn([
                'is_promo'
            ]);
        });
        Schema::table('category_prices', function (Blueprint $table) {
            $table->dropColumn([
                'price_for_one_lecture_promo'
            ]);
        });
    }
};
