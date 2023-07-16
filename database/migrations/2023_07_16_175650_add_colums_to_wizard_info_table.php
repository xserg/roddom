<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('wizard_info', function (Blueprint $table) {
            $table->string('readable_key')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('wizard_info', function (Blueprint $table) {
            $table->dropColumn([
                'readable_key',
            ]);
        });
    }
};
