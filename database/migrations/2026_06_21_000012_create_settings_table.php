<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('label', 200)->nullable();    // descrição legível
            $table->timestamps();
        });

        // Valores padrão
        DB::table('settings')->insert([
            ['key' => 'reading_interval_seconds', 'value' => '60',  'label' => 'Intervalo entre leituras dos sensores (segundos)', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'alert_threshold_atencao',  'value' => '10',  'label' => 'Obstrução (%) para alerta Atenção',                'created_at' => now(), 'updated_at' => now()],
            ['key' => 'alert_threshold_risco',    'value' => '40',  'label' => 'Obstrução (%) para alerta Risco',                  'created_at' => now(), 'updated_at' => now()],
            ['key' => 'alert_threshold_critico',  'value' => '70',  'label' => 'Obstrução (%) para alerta Crítico',                'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
