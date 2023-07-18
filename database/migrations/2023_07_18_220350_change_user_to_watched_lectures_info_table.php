<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('user_to_watched_lectures', function (Blueprint $table) {
            $table->dropForeign('user_to_watched_lectures_lecture_id_foreign');
            $table->dropIndex('user_to_watched_lectures_lecture_id_foreign');
            $table->dropForeign('user_to_watched_lectures_user_id_foreign');
            $table->dropIndex('user_to_watched_lectures_user_id_foreign');
        });
        Schema::table('user_to_free_watched_lectures', function (Blueprint $table) {
            $table->dropForeign('user_to_free_watched_lectures_lecture_id_foreign');
            $table->dropIndex('user_to_free_watched_lectures_lecture_id_foreign');
            $table->dropForeign('user_to_free_watched_lectures_user_id_foreign');
            $table->dropIndex('user_to_free_watched_lectures_user_id_foreign');
        });
        Schema::table('user_to_free_watched_lectures', function (Blueprint $table) {
            $table->foreign('lecture_id')->references('id')->on('lectures')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('user_id')->references('id')->on('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
        Schema::table('user_to_watched_lectures', function (Blueprint $table) {
            $table->foreign('lecture_id')->references('id')->on('lectures')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('user_id')->references('id')->on('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('user_to_watched_lectures', function (Blueprint $table) {

        });
    }
};
