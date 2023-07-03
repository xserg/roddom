<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('app_info', function (Blueprint $table) {
            $table->string('ref_system_title')->default('Партнерская программа');
            $table->text('ref_system_description')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('app_info', function (Blueprint $table) {
            $table->dropColumn([
                'ref_system_title',
                'ref_system_description'
            ]);
        });
    }
};
