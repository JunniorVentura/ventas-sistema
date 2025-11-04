<?php

namespace Database\Factories;

use App\Models\Pago;
use App\Models\Pedido;
use Illuminate\Database\Eloquent\Factories\Factory;

class PagoFactory extends Factory
{
    protected $model = Pago::class;

    public function definition(): array
    {
        $pedido = Pedido::inRandomOrder()->first() ?? Pedido::factory()->create();

        return [
            'pedido_id' => $pedido->id,
            'metodo_pago' => $this->faker->randomElement(['efectivo', 'yape', 'transferencia']),
            'estado_pago' => $this->faker->randomElement(['pendiente', 'verificado', 'rechazado']),
            'fecha_pago' => $this->faker->dateTimeBetween($pedido->fecha ?? '-1 week', 'now'),
            'estado' => true,
        ];
    }
}
