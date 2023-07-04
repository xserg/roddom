<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ref_points_payments', function (Blueprint $table) {
            $table->string('reason')->after('payer_id')->nullable()->default(null);
        });
    }

    public function down(): void
    {
        Schema::table('ref_points_payments', function (Blueprint $table) {
            $table->dropColumn(['reason']);
        });
    }
};
