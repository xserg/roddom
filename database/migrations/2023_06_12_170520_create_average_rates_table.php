<?php

use App\Models\Lector;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('average_lector_rates', function (Blueprint $table) {
            $table->foreignIdFor(Lector::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->unsignedDecimal('rating')->nullable()->default(null);

            $table->primary('lector_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('average_lector_rates');
    }
};
