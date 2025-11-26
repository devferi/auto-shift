<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shift_week_pattern_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_week_pattern_id')->constrained('shift_week_patterns');
            $table->integer('order_index');
            $table->integer('duration_weeks');
            $table->foreignId('shift_id')->constrained('shifts');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_week_pattern_items');
    }
};
