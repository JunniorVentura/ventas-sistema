<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;

class DetallePedidoSeeder extends Seeder
{
    public function run(): void
    {
        // Asegurar que existan pedidos y productos
        if (Pedido::count() === 0) {
            $this->call(PedidoSeeder::class);
        }

        if (Producto::count() === 0) {
            $this->call(ProductoSeeder::class);
        }

        // Agregar entre 1 a 3 detalles por pedido
        foreach (Pedido::all() as $pedido) {
            $cantidadDetalles = rand(1, 3);

            for ($i = 0; $i < $cantidadDetalles; $i++) {
                DetallePedido::factory()->create([
                    'pedido_id' => $pedido->id,
                ]);
            }
        }
    }
}
