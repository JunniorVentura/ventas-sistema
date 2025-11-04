<?php

namespace Database\Factories;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Stock;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->unique()->words(2, true),
            'descripcion' => $this->faker->optional()->sentence(),
            'precio' => $this->faker->randomFloat(2, 10, 1000),
            'categoria_id' => Categoria::inRandomOrder()->first()->id ?? Categoria::factory(),
            'estado' => true,
            'created_at' => now(),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Producto $producto) {
            Stock::factory()->create([
                'producto_id' => $producto->id,
            ]);
        });
    }
}
