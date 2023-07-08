<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('wizards', function (Blueprint $table) {
            $table->unsignedSmallInteger('order');
        });
    }

    public function down(): void
    {
        Schema::table('wizards', function (Blueprint $table) {
            $table->dropColumn([
                'order'
            ]);
        });
    }
};
