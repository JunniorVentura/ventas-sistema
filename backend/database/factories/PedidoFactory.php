<?php

namespace Database\Factories;

use App\Models\Pedido;
use App\Models\Cliente;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

class PedidoFactory extends Factory
{
    protected $model = Pedido::class;

    public function definition(): array
    {
        return [
            'cliente_id' => Cliente::inRandomOrder()->first()->id ?? Cliente::factory(),
            'usuario_id' => Usuario::inRandomOrder()->first()->id ?? Usuario::factory(),
            'fecha' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'total' => $this->faker->randomFloat(2, 50, 5000),
            'estado_pedido' => $this->faker->randomElement(['pendiente', 'pagado', 'cancelado']),
            'estado' => true,
        ];
    }
}
