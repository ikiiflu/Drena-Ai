<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'name'  => 'Operador Defesa Civil',
            'email' => 'operador@defesacivil.caratinga.mg.gov.br',
        ]);

        $this->call([
            SensorSeeder::class,
        ]);
    }
}
