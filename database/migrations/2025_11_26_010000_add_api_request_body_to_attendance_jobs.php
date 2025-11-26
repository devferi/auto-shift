<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('attendance_jobs', function (Blueprint $table) {
            $table->longText('api_request_body')->nullable()->after('api_url');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_jobs', function (Blueprint $table) {
            $table->dropColumn('api_request_body');
        });
    }
};

