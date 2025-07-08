<?php

namespace Database\Factories;

use App\Models\Factura;
use App\Models\Pedido;
use Illuminate\Database\Eloquent\Factories\Factory;

class FacturaFactory extends Factory
{
    protected $model = Factura::class;

    public function definition(): array
    {
        $pedido = Pedido::inRandomOrder()->first() ?? Pedido::factory()->create();

        return [
            'pedido_id' => $pedido->id,
            'ruc_cliente' => $this->faker->numerify('20#########'), // 11 dígitos típicos de RUC
            'razon_social' => $this->faker->company(),
            'fecha_emision' => $this->faker->dateTimeBetween($pedido->fecha ?? '-1 month', 'now'),
            'total' => $pedido->total ?? $this->faker->randomFloat(2, 100, 2000),
            'estado' => true,
        ];
    }
}
