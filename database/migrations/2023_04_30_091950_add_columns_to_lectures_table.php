<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lectures', function (Blueprint $table) {
            $table->unsignedBigInteger('content_type_id')->default(1);
            $table->unsignedBigInteger('payment_type_id')->default(1);
            $table->renameColumn('video_id', 'content');
            $table->dropColumn('is_free');

            $table->foreign('content_type_id')->references('id')->on('lecture_content_types')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('payment_type_id')->references('id')->on('lecture_payment_types')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lectures', function (Blueprint $table) {
            $table->boolean('is_free')->default(false);
            $table->renameColumn('content', 'video_id');
            $table->dropForeign(['content_type_id']);
            $table->dropForeign(['payment_type_id']);
            $table->dropColumn(['content_type_id', 'payment_type_id']);
        });
    }
};
