<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Log;
use App\Models\Usuario;

class LogSeeder extends Seeder
{
    public function run(): void
    {
        // Asegurar que existan usuarios
        if (Usuario::count() === 0) {
            $this->call(UsuarioSeeder::class);
        }

        // Generar 50 logs aleatorios
        Log::factory()->count(50)->create();
    }
}
