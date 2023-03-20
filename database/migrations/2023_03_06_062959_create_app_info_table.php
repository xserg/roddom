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
        Schema::create('app_info', function (Blueprint $table) {
            $table->id();

            $table->string('agreement_title')->default('Прочтите соглашение');
            $table->text('agreement_text')->nullable();
            $table->string('recommended_title')->default('Рекомендуем');
            $table->string('recommended_subtitle')->default('Не пропустите новые лекции!');
            $table->string('lectures_catalog_title')->default('Каталог лекций');
            $table->string('lectures_catalog_subtitle')->default('Выберите тему, которая вас интересует');
            $table->string('out_lectors_title')->default('Наши лекторы');
            $table->string('not_viewed_yet_title')->default('Вы ещё не смотрели');
            $table->string('more_in_the_collection')->default('Ещё в подборке');
            $table->string('about_lector_title')->default('О лекторе');
            $table->string('diplomas_title')->default('Дипломы и сертификаты');
            $table->string('lectors_videos')->default('Видео от лектора');

            $table->string('app_title')->default('Школа мам и пап «Нежность»');
            $table->text('about_app')->nullable();
            $table->string('app_author_name')->default('Сергей Тарасов');
            $table->string('app_link_share_title')->default('Поделиться ссылкой');
            $table->string('app_link_share_link')->default('https://xn--80axb4d.online');
            $table->string('app_show_qr_title')->default('Показать QR-код');
            $table->string('app_show_qr_link')->default('https://api.мамы.online/storage/images/app/qr.jpeg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_info');
    }
};
