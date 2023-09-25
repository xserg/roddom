<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedSmallInteger('lectures_count')->change();
        });
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->unsignedSmallInteger('lectures_count')->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedTinyInteger('lectures_count')->change();
        });
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->unsignedTinyInteger('lectures_count')->change();
        });
    }
};
