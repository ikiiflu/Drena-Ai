<?php

namespace Database\Seeders;

use App\Models\Sensor;
use Illuminate\Database\Seeder;

class SensorSeeder extends Seeder
{
    public function run(): void
    {
        $sensors = [
            ['code' => 'AQS-001', 'name' => 'Bueiro Central',    'address' => 'Av. Principal, 1500',      'region' => 'Central', 'latitude' => -19.7880000, 'longitude' => -42.1400000],
            ['code' => 'AQS-002', 'name' => 'Galeria Norte',     'address' => 'Rua das Flores, 420',      'region' => 'Norte',   'latitude' => -19.7850000, 'longitude' => -42.1420000],
            ['code' => 'AQS-003', 'name' => 'Bueiro Leste',      'address' => 'Av. Brasil, 2300',         'region' => 'Leste',   'latitude' => -19.7900000, 'longitude' => -42.1360000],
            ['code' => 'AQS-004', 'name' => 'Galeria Sul',       'address' => 'Rua Comércio, 88',         'region' => 'Sul',     'latitude' => -19.7940000, 'longitude' => -42.1410000],
            ['code' => 'AQS-005', 'name' => 'Bueiro Oeste',      'address' => 'Av. Perimetral, 100',      'region' => 'Oeste',   'latitude' => -19.7830000, 'longitude' => -42.1480000],
            ['code' => 'AQS-006', 'name' => 'Galeria Centro',    'address' => 'Praça da Matriz',          'region' => 'Central', 'latitude' => -19.7910000, 'longitude' => -42.1390000],
            ['code' => 'AQS-007', 'name' => 'Bueiro Nordeste',   'address' => 'Rua Amazonas, 55',         'region' => 'Norte',   'latitude' => -19.7810000, 'longitude' => -42.1370000],
            ['code' => 'AQS-008', 'name' => 'Galeria Sudeste',   'address' => 'Av. Independência, 700',   'region' => 'Leste',   'latitude' => -19.7970000, 'longitude' => -42.1330000],
            ['code' => 'AQS-009', 'name' => 'Bueiro Rodoviária', 'address' => 'Terminal Urbano',          'region' => 'Sul',     'latitude' => -19.7860000, 'longitude' => -42.1450000],
            ['code' => 'AQS-010', 'name' => 'Galeria Marginal',  'address' => 'Av. Beira Rio, 3200',      'region' => 'Oeste',   'latitude' => -19.7930000, 'longitude' => -42.1440000],
        ];

        foreach ($sensors as $data) {
            Sensor::firstOrCreate(['code' => $data['code']], $data);
        }
    }
}
