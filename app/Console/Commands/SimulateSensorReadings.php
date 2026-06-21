<?php

namespace App\Console\Commands;

use App\Models\Alert;
use App\Models\Sensor;
use App\Models\SensorReading;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Simulates continuous insertion of sensor readings.
 *
 * Single batch:       php artisan sensor:simulate
 * Loop (DB interval): php artisan sensor:simulate --loop=auto
 * Loop every 30s:     php artisan sensor:simulate --loop=30
 * Seed 24h back:      php artisan sensor:simulate --backfill=24
 */
class SimulateSensorReadings extends Command
{
    protected $signature = 'sensor:simulate
                            {--loop=0     : Seconds between batches, or "auto" to read from DB settings}
                            {--backfill=0 : Insert N hours of historical readings at 1-min intervals}';

    protected $description = 'Inserts simulated sensor readings into the database';

    /** Tracks last obstruction per sensor to produce gradual drift. */
    private array $state = [];

    public function handle(): int
    {
        $loopOpt  = $this->option('loop');
        $loop     = $loopOpt === 'auto'
            ? (int) Setting::get('reading_interval_seconds', 60)
            : (int) $loopOpt;
        $backfill = (int) $this->option('backfill');

        $sensors = Sensor::where('active', true)->get();

        if ($sensors->isEmpty()) {
            $this->error('No active sensors found. Run: php artisan db:seed');
            return self::FAILURE;
        }

        // Initialise drift state from latest DB reading (or sensible defaults)
        foreach ($sensors as $sensor) {
            $last = $sensor->latestReading;
            $this->state[$sensor->id] = [
                'obstruction' => $last?->obstruction_pct ?? random_int(5, 30),
                'flow_base'   => $last?->flow_lps        ?? random_int(200, 500),
            ];
        }

        if ($backfill > 0) {
            $this->backfill($sensors, $backfill);
            return self::SUCCESS;
        }

        do {
            $this->insertBatch($sensors);

            if ($loop > 0) {
                $this->line("  Next batch in {$loop}s… (Ctrl+C to stop)");
                sleep($loop);
            }
        } while ($loop > 0);

        return self::SUCCESS;
    }

    // -------------------------------------------------------------------------

    /** Insert one reading per sensor for the current moment. */
    private function insertBatch(\Illuminate\Support\Collection $sensors): void
    {
        $now  = Carbon::now();
        $rows = [];

        foreach ($sensors as $sensor) {
            $reading = $this->generateReading($sensor->id, $now);
            $rows[]  = $reading;

            $this->evaluateAlerts($sensor, $reading);
        }

        SensorReading::insert($rows);

        $this->info(
            "[{$now->format('H:i:s')}] Inserted " . count($rows) . " readings"
        );
    }

    /** Insert historical readings from N hours ago up to now (1-min intervals). */
    private function backfill(\Illuminate\Support\Collection $sensors, int $hours): void
    {
        $start  = Carbon::now()->subHours($hours);
        $end    = Carbon::now();
        $cursor = $start->copy();
        $total  = 0;

        $this->info("Backfilling {$hours}h of history from {$start->format('Y-m-d H:i')} …");
        $bar = $this->output->createProgressBar((int) ($hours * 60));

        while ($cursor->lte($end)) {
            $rows = [];
            foreach ($sensors as $sensor) {
                $rows[] = $this->generateReading($sensor->id, $cursor->copy());
            }
            SensorReading::insert($rows);
            $total  += count($rows);
            $cursor->addMinute();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Done. Inserted {$total} historical readings.");
    }

    // -------------------------------------------------------------------------

    /**
     * Generates a realistic reading for one sensor at a given moment.
     * Behaviour:
     *  - Rainfall peaks 14:00–18:00 (afternoon storms typical of MG interior)
     *  - Obstruction drifts ±5 % per cycle; capped at [0, 100]
     *  - Flow decreases as obstruction increases (inverse linear model)
     */
    private function generateReading(int $sensorId, Carbon $at): array
    {
        $hour = (int) $at->format('H');

        // Rainfall model (mm)
        $isStorm = $hour >= 14 && $hour <= 18;
        $base    = $isStorm ? mt_rand(12, 20) : mt_rand(1, 4);
        $rainfall = round($base + (mt_rand(-100, 100) / 100), 3);

        // Obstruction drift
        $prev  = $this->state[$sensorId]['obstruction'];
        $delta = (mt_rand(0, 100) / 100 - 0.45) * 5;   // slightly upward bias
        $obs   = max(0.0, min(100.0, $prev + $delta + ($isStorm ? 1.5 : 0)));
        $this->state[$sensorId]['obstruction'] = $obs;

        // Flow model: max flow at 0% obstruction, near-zero at 100%
        $flowMax = $this->state[$sensorId]['flow_base'];
        $flowFactor = max(0.02, 1.0 - ($obs / 100) * 0.95);
        $flow = round($flowMax * $flowFactor + (mt_rand(-50, 50) / 10), 3);

        return [
            'sensor_id'       => $sensorId,
            'obstruction_pct' => round($obs, 2),
            'rainfall_mm'     => $rainfall,
            'flow_lps'        => max(0.0, $flow),
            'recorded_at'     => $at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Creates or resolves alerts based on current obstruction level.
     * Only one active alert per sensor per severity is kept.
     */
    private function evaluateAlerts(Sensor $sensor, array $reading): void
    {
        $obs = $reading['obstruction_pct'];

        // Limiares configuráveis via tabela settings
        $tCritico = (float) Setting::get('alert_threshold_critico', 70);
        $tRisco   = (float) Setting::get('alert_threshold_risco',   40);
        $tAtencao = (float) Setting::get('alert_threshold_atencao', 10);

        $severity = match (true) {
            $obs >= $tCritico => 'critico',
            $obs >= $tRisco   => 'risco',
            $obs >= $tAtencao => 'atencao',
            default           => null,
        };

        // Resolve all active alerts if sensor is back to normal
        if ($severity === null) {
            Alert::where('sensor_id', $sensor->id)
                 ->whereNull('resolved_at')
                 ->update(['resolved_at' => now()]);
            return;
        }

        $messages = [
            'critico' => "Obstrução de {$obs}%. Risco iminente de transbordamento. Intervenção urgente necessária.",
            'risco'   => "Obstrução de {$obs}%. Inspeção recomendada em até 2 horas.",
            'atencao' => "Obstrução de {$obs}%. Monitoramento contínuo recomendado.",
        ];

        // Resolve alerts for lower severities
        Alert::where('sensor_id', $sensor->id)
             ->whereNull('resolved_at')
             ->where('severity', '!=', $severity)
             ->update(['resolved_at' => now()]);

        // Open a new alert only if none active for this severity
        $exists = Alert::where('sensor_id', $sensor->id)
                       ->where('severity', $severity)
                       ->whereNull('resolved_at')
                       ->exists();

        if (! $exists) {
            Alert::create([
                'sensor_id' => $sensor->id,
                'severity'  => $severity,
                'message'   => $messages[$severity],
            ]);
        }
    }
}
