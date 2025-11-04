<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Boleta;
use App\Models\Pedido;

class BoletaSeeder extends Seeder
{
    public function run(): void
    {
        // Verificar que existan pedidos
        if (Pedido::count() === 0) {
            $this->call(PedidoSeeder::class);
        }

        // Crear boletas para algunos pedidos (por ejemplo, 10)
        $pedidos = Pedido::inRandomOrder()->take(10)->get();

        foreach ($pedidos as $pedido) {
            Boleta::factory()->create([
                'pedido_id' => $pedido->id,
                'total' => $pedido->total,
                'fecha_emision' => now(),
            ]);
        }
    }
}
