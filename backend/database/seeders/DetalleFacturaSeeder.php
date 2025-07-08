<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DetalleFactura;
use App\Models\Factura;
use App\Models\Producto;

class DetalleFacturaSeeder extends Seeder
{
    public function run(): void
    {
        // Asegurar que existan facturas y productos
        if (Factura::count() === 0) {
            $this->call(FacturaSeeder::class);
        }

        if (Producto::count() === 0) {
            $this->call(ProductoSeeder::class);
        }

        // Crear detalles por cada factura
        foreach (Factura::all() as $factura) {
            $detalles = rand(1, 4); // entre 1 y 4 productos por factura

            for ($i = 0; $i < $detalles; $i++) {
                DetalleFactura::factory()->create([
                    'factura_id' => $factura->id,
                ]);
            }
        }
    }
}
