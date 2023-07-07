<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('app_info', function (Blueprint $table) {
            $table->string('user_invites_you_to_join')->nullable()->default('x приглашает Вас присоединиться к интересным материалам в Школе Мам и Пап');
        });
    }

    public function down(): void
    {
        Schema::table('app_info', function (Blueprint $table) {
            $table->dropColumn([
                'user_invites_you_to_join',
            ]);
        });
    }
};
