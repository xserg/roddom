<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users_referrer_id_foreign');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->integer('referrer_id')->default(-1)->change();
            $table->integer('order')->default(0)->index()->after('referrer_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('referrer_id')->nullable()->default(null)->change();
        });
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('referrer_id')->references('id')->on('users');
            $table->dropColumn(['order']);
        });
    }
};
