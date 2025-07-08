<?php

namespace Database\Factories;

use App\Models\Stock;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockFactory extends Factory
{
    protected $model = Stock::class;

    public function definition(): array
    {
        return [
            'producto_id' => Producto::inRandomOrder()->first()->id ?? Producto::factory(),
            'cantidad' => $this->faker->numberBetween(1, 100),
            'estado' => true,
            'updated_at' => now(),
        ];
    }
}
