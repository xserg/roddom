<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('app_info', function (Blueprint $table) {
            $table->string('buy_all')
                ->default('Купить весь каталог со скидкой');
            $table->string('watch_from')
                ->default('Смотреть от');
            $table->string('chosen_category_contains_lectures')
                ->default('Выбранная категория содержит х лекций.');
            $table->string('your_profit_is_roubles')
                ->default('Ваша экономия составит х рублей.');
        });
    }

    public function down(): void
    {
        Schema::table('app_info', function (Blueprint $table) {
            $table->dropColumn([
                'buy_all',
                'watch_from',
                'chosen_category_contains_lectures',
                'your_profit_is_roubles'
            ]);
        });
    }
};
