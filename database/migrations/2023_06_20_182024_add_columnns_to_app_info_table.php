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
            $table->string('buy_page_under_btn_description')
                ->default('Выбранный материал будет доступен Вам для просмотра в течение x дней с момента покупки.');
            $table->string('buy_page_description', 510)
                ->default('Вы можете приобрести доступ к этому материалу на необходимый Вам промежуток времени.');
            $table->string('buy_category')
                ->default('Купить категорию со скидкой');
            $table->string('buy_subcategory')
                ->default('Купить подкатегорию со скидкой');
            $table->string('view_schedule')
                ->default('График просмотра');
            $table->string('watched_already')
                ->default('Вы уже посмотрели материал на сегодня');
            $table->string('next_free_lecture_available_at')
                ->default('Следующий бесплатный будет доступен через');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_info', function (Blueprint $table) {
            $table->dropColumn([
                'buy_page_under_btn_description',
                'buy_page_description',
                'buy_category',
                'buy_subcategory',
                'view_schedule',
                'watched_already',
                'next_free_lecture_available_at',
            ]);
        });
    }
};
