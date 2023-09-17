<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('code', 36)->after('status');
        });

        Schema::table('orders', function (Blueprint $table) {
            $orders = \App\Models\Order::all();

            $orders->each(function ($order) {
                if (! $order->code) {
                    $order->code = \Illuminate\Support\Str::uuid();
                    $order->timestamps = false;
                    $order->saveQuietly();
                }
            });
        });

    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'code'
            ]);
        });
    }
};
