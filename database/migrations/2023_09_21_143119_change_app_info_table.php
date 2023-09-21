<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('app_info', function (Blueprint $table) {
            $table->string('category_special_price_text')->default('Вы также можете приобрести полностью категорию по специальной цене');
            $table->unsignedInteger('credit_minimal_sum')->default(3000);
        });
    }

    public function down(): void
    {
        Schema::table('app_info', function (Blueprint $table) {
            $table->dropColumn(['category_special_price', 'credit_minimal_sum']);
        });
    }
};
