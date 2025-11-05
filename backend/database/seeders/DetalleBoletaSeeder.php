<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DetalleBoleta;
use App\Models\Boleta;
use App\Models\Producto;

class DetalleBoletaSeeder extends Seeder
{
    public function run(): void
    {
        // Asegurar que existan boletas y productos
        if (Boleta::count() === 0) {
            $this->call(BoletaSeeder::class);
        }

        if (Producto::count() === 0) {
            $this->call(ProductoSeeder::class);
        }

        // Crear detalles por cada boleta
        foreach (Boleta::all() as $boleta) {
            $detallesCount = rand(1, 4);

            for ($i = 0; $i < $detallesCount; $i++) {
                DetalleBoleta::factory()->create([
                    'boleta_id' => $boleta->id,
                ]);
            }
        }
    }
}
