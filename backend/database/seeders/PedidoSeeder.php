<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pedido;
use App\Models\Cliente;
use App\Models\Usuario;

class PedidoSeeder extends Seeder
{
    public function run(): void
    {
        // Verificar que existan clientes y usuarios
        if (Cliente::count() === 0) {
            $this->call(ClienteSeeder::class);
        }

        if (Usuario::count() === 0) {
            $this->call(UsuarioSeeder::class);
        }

        // Crear 20 pedidos con relaciones válidas
        Pedido::factory()->count(30)->create();
    }
}
