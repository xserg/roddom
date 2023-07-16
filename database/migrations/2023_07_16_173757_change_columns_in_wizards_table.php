<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('wizards', function (Blueprint $table) {
            $table->dropForeign('wizards_wizard_info_id_foreign');

            $table->dropColumn([
                'wizard_info_id'
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('wizards', function (Blueprint $table) {
            $table->unsignedBigInteger('wizard_info_id')->nullable();

            $table->foreign('wizard_info_id')
                ->references('id')
                ->on('wizard_info')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }
};
