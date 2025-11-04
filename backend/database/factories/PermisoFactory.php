<?php

namespace Database\Factories;

use App\Models\Permiso;
use Illuminate\Database\Eloquent\Factories\Factory;

class PermisoFactory extends Factory
{
    protected $model = Permiso::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->unique()->word(), // puedes reemplazar por 'ver_productos', 'crear_usuarios', etc. si prefieres algo más real
            'descripcion' => $this->faker->sentence(),
            'estado' => true,
            'created_at' => now(),
        ];
    }
}
