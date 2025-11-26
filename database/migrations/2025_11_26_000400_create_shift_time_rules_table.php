<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shift_time_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_id')->constrained('shifts');
            $table->unsignedTinyInteger('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_time_rules');
    }
};
