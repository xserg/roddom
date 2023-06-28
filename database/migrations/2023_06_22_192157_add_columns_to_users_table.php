<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('ref_token')
                ->after('remember_token');
            $table
                ->unsignedBigInteger('referer_id')
                ->nullable()
                ->default(null)
                ->after('remember_token');
            $table->foreign('referer_id')->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('referer_id');
            $table->dropColumn(['ref_token',]);
        });
    }
};
