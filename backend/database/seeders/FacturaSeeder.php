<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Factura;
use App\Models\Pedido;

class FacturaSeeder extends Seeder
{
    public function run(): void
    {
        // Asegura que haya pedidos
        if (Pedido::count() === 0) {
            $this->call(PedidoSeeder::class);
        }

        // Obtener pedidos sin factura
        $pedidos = Pedido::doesntHave('factura')->inRandomOrder()->take(10)->get();

        foreach ($pedidos as $pedido) {
            Factura::factory()->create([
                'pedido_id' => $pedido->id,
                'total' => $pedido->total,
                'fecha_emision' => now(),
            ]);
        }
    }
}
