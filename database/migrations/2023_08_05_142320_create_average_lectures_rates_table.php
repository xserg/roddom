<?php

use App\Models\Lecture;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('average_lecture_rates', function (Blueprint $table) {
            $table->foreignIdFor(Lecture::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->unsignedDecimal('rating')->nullable()->default(null);

            $table->primary('lecture_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('average_lecture_rates');
    }
};
