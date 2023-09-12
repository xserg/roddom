<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('app_info', function (Blueprint $table) {
            $table->dropColumn([
                'app_show_qr_link'
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('app_info', function (Blueprint $table) {
            $table->string('app_show_qr_link')->default('images/app/qr.jpeg');
        });
    }
};
