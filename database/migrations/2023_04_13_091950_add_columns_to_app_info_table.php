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
            $table->string('validation_wrong_credentials')->default('Неправильный логин/пароль. Повторите попытку.');
            $table->string('reset_code_sent')->default('Код подтверждения отправлен');
            $table->string('added_to_saved')->default('Добавили в «Сохранённые»');
            $table->string('removed_from_saved')->default('Удалили из «Сохранённых»');
            $table->string('added_to_watched')->default('Добавили в «Просмотренные»');
            $table->string('removed_from_watched')->default('Удалили из «Просмотренных»');
            $table->string('message_sent')->default('Ваше сообщение успешно отправлено.');
            $table->string('message_sent_error')->default('Во время отправки сообщения произошла ошибка.');
            $table->string('thanks_for_rate')->default('Спасибо за вашу оценку!');
            $table->string('thanks_for_feedback')->default('Спасибо за обратную связь! Ваше сообщение успено отправлено.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_info', function (Blueprint $table) {
            $table->dropColumn([
                'validation_wrong_credentials',
                'reset_code_sent',
                'added_to_saved',
                'removed_from_saved',
                'added_to_watched',
                'removed_from_watched',
                'message_sent',
                'message_sent_error',
                'thanks_for_rate',
                'thanks_for_feedback',
            ]);
        });
    }
};
