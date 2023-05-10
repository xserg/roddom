<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lectures', function (Blueprint $table) {
            $table->boolean('show_tariff_1')->default(true);
            $table->boolean('show_tariff_2')->default(true);
            $table->boolean('show_tariff_3')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lectures', function (Blueprint $table) {
            $table->dropColumn([
                'show_tariff_1',
                'show_tariff_2',
                'show_tariff_3',
            ]);
        });
    }
};
