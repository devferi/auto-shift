<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendance_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees');
            $table->foreignId('work_place_id')->constrained('work_places');
            $table->foreignId('shift_id')->constrained('shifts');
            $table->date('date');
            $table->string('type');
            $table->string('message');
            $table->timestamp('run_at');
            $table->string('status')->default('pending');
            $table->text('api_url')->nullable();
            $table->longText('api_response')->nullable();
            $table->integer('attempts')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_jobs');
    }
};
