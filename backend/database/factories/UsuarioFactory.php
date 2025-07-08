<?php

namespace Database\Factories;

use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('12345678'), // Contraseña fija para pruebas
            //'rol_id' => Rol::inRandomOrder()->first()->id ?? Rol::factory(), // Usa rol existente o crea uno nuevo
            'rol_id' => Rol::factory()->create()->id,
            'estado' => true,
            'token_expiration' => now()->addMinutes(60), // o el tiempo que uses por defecto
            'created_at' => now(),
        ];
    }
}
