<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('whatsapp_api_logs', function (Blueprint $table) {
            $table->id();
            $table->string('method');
            $table->text('url');
            $table->longText('request_body')->nullable();
            $table->integer('response_status')->nullable();
            $table->longText('response_body')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_api_logs');
    }
};

