<?php

namespace Database\Factories;

use App\Models\Rol;
use Illuminate\Database\Eloquent\Factories\Factory;

class RolFactory extends Factory
{
    protected $model = Rol::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->words(3, true), // Puedes usar 'Administrador', 'Vendedor', etc. si prefieres valores fijos
            'descripcion' => $this->faker->sentence(),
            'estado' => true,
            'created_at' => now(),
        ];
    }
}
