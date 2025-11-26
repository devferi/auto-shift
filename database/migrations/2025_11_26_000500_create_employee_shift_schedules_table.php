<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employee_shift_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees');
            $table->foreignId('work_place_id')->constrained('work_places');
            $table->foreignId('shift_id')->constrained('shifts');
            $table->date('date');
            $table->string('login_message')->nullable();
            $table->string('logout_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_shift_schedules');
    }
};
