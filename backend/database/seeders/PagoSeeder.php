<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pago;
use App\Models\Pedido;

class PagoSeeder extends Seeder
{
    public function run(): void
    {
        // Asegurar que existan pedidos
        if (Pedido::count() === 0) {
            $this->call(PedidoSeeder::class);
        }

        // Crear pagos para algunos pedidos que no tienen pago aún
        $pedidos = Pedido::doesntHave('pago')->inRandomOrder()->take(15)->get();

        foreach ($pedidos as $pedido) {
            Pago::factory()->create([
                'pedido_id' => $pedido->id,
            ]);
        }
    }
}
