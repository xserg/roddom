<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('points')
                ->after('price')
                ->nullable()->default(null);
            $table->unsignedBigInteger('price')
                ->change();
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('points')
                ->after('total_price')
                ->nullable()->default(null);

            $table->unsignedBigInteger('total_price')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['points']);
            $table->decimal('price', 10, 2)
                ->change();
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['points']);
            $table->unsignedDecimal('total_price', 10, 2)
            ->change();
        });
    }
};
