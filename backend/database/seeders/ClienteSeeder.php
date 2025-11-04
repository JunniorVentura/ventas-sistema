<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        // Crear 15 clientes con el factory
        Cliente::factory()->count(15)->create();
    }
}
