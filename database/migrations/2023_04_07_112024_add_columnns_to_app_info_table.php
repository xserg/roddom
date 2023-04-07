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
        Schema::table('app_info', function (Blueprint $table) {
            $table->string('tarif_title_1')->default('tarif-1');
            $table->string('tarif_title_2')->default('tarif-2');
            $table->string('tarif_title_3')->default('tarif-3');
            $table->integer('free_lecture_hours')->unsigned()->default(24);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_info', function (Blueprint $table) {
            $table->dropColumn([
                'tarif_title_1',
                'tarif_title_2',
                'tarif_title_3',
                'free_lecture_hours'
            ]);
        });
    }
};
