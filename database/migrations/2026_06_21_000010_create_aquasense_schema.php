<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AquaSense — Schema completo do banco de dados
 *
 * Tabelas:
 *   sensors             — Cadastro dos sensores instalados nos bueiros
 *   sensor_readings     — Leituras contínuas de cada sensor (ON DELETE CASCADE)
 *   alerts              — Alertas gerados automaticamente por limiar de obstrução
 *   maintenance_records — Histórico de manutenções realizadas por equipes de campo
 *
 * Formas Normais atendidas:
 *   1FN — colunas atômicas, sem grupos repetitivos
 *   2FN — todos os não-chave dependem da chave primária completa
 *   3FN — sem dependências transitivas; sensor_readings referencia sensors via FK
 */
return new class extends Migration
{
    public function up(): void
    {
        // ------------------------------------------------------------------ //
        // sensors
        // ------------------------------------------------------------------ //
        Schema::create('sensors', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();       // AQS-001 … AQS-010
            $table->string('name', 100);
            $table->string('address', 200);
            $table->string('region', 50);               // Norte | Sul | Central | Leste | Oeste
            $table->decimal('latitude',  10, 7);        // precisão geoespacial: ±0.011 m
            $table->decimal('longitude', 10, 7);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // ------------------------------------------------------------------ //
        // sensor_readings
        // ------------------------------------------------------------------ //
        Schema::create('sensor_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sensor_id')
                  ->constrained('sensors')
                  ->onDelete('cascade');

            $table->decimal('obstruction_pct', 5, 2);  // 0,00 – 100,00 %
            $table->decimal('rainfall_mm',     7, 3);  // precipitação em mm
            $table->decimal('flow_lps',        9, 3);  // vazão em L/s

            $table->timestamp('recorded_at')->useCurrent();

            // Índice composto — suporta consultas analíticas de série temporal
            $table->index(['sensor_id', 'recorded_at']);
        });

        // ------------------------------------------------------------------ //
        // alerts
        // ------------------------------------------------------------------ //
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sensor_id')
                  ->constrained('sensors')
                  ->onDelete('cascade');

            // atencao | risco | critico
            $table->string('severity', 20);
            $table->text('message');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['sensor_id', 'resolved_at']);
        });

        // ------------------------------------------------------------------ //
        // maintenance_records
        // ------------------------------------------------------------------ //
        Schema::create('maintenance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sensor_id')
                  ->constrained('sensors')
                  ->onDelete('cascade');

            $table->string('operator_name', 100);
            $table->text('description');
            $table->text('notes')->nullable();
            $table->timestamp('performed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_records');
        Schema::dropIfExists('alerts');
        Schema::dropIfExists('sensor_readings');
        Schema::dropIfExists('sensors');
    }
};
