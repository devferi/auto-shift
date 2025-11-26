<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        DB::table('system_settings')->insert([
            ['key' => 'wa_server_url', 'value' => 'https://wa.posyandudigital.my.id/', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'wa_session', 'value' => 'waiskak', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'wa_target_number', 'value' => '6281333994823', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};

