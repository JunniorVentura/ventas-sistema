<?php

namespace Database\Factories;

use App\Models\Boleta;
use App\Models\Pedido;
use Illuminate\Database\Eloquent\Factories\Factory;

class BoletaFactory extends Factory
{
    protected $model = Boleta::class;

    public function definition(): array
    {
        $pedido = Pedido::inRandomOrder()->first() ?? Pedido::factory()->create();

        return [
            'pedido_id' => $pedido->id,
            'dni_cliente' => $this->faker->numerify('########'), // 8 dígitos para DNI típico
            'nombre_cliente' => $this->faker->name(),
            'fecha_emision' => $this->faker->dateTimeBetween($pedido->fecha ?? '-1 month', 'now'),
            'total' => $pedido->total ?? $this->faker->randomFloat(2, 20, 2000),
            'estado' => true,
        ];
    }
}
