<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->json('exclude')->nullable()->after('period');
        });
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->json('exclude')->nullable()->after('period_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['exclude']);
        });
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['exclude']);
        });
    }
};
