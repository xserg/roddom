<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('wizard_info', function (Blueprint $table) {
            $table->string('key')->nullable();
            $table->string('value')->nullable();
        });
        Schema::table('wizard_info', function (Blueprint $table) {
            $table->dropColumn([
                'subtitle',
                'description'
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('wizard_info', function (Blueprint $table) {
            $table->dropColumn([
                'key',
                'value'
            ]);
            $table->string('subtitle')->nullable();
            $table->string('description')->nullable();
        });
    }
};
