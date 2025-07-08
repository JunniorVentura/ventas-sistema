<?php

namespace Database\Factories;

use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoriaFactory extends Factory
{
    protected $model = Categoria::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->unique()->word(), // Ejemplo: "Tecnología"
            'descripcion' => $this->faker->sentence(),  // Ejemplo: "Productos electrónicos y gadgets"
            'estado' => true,
            'created_at' => now(),
        ];
    }
}
