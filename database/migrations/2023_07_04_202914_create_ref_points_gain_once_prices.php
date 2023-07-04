<?php

use App\Models\Period;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ref_points_gain_onces', function (Blueprint $table) {
            $table->id();

            $table->string('user_type')->unique();
            $table->unsignedInteger('points_gains');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_points_gain_onces');
    }
};
