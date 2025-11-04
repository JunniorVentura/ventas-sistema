<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClienteFactory extends Factory
{
    protected $model = Cliente::class;

    public function definition(): array
    {
        $nombre = $this->faker->name();

        return [
            'nombre' => $nombre,
            'dni' => $this->faker->unique()->numerify('########'), // 8 dígitos
            'ruc' => $this->faker->unique()->numerify('20#########'), // 11 dígitos válidos para Perú
            'razon_social' => strtoupper("EMPRESA DE {$nombre} S.A.C."), // Ejemplo: EMPRESA DE JUAN PEDRO S.A.C.
            'direccion' => $this->faker->address(),
            'telefono' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'estado' => true,
            'created_at' => now(),
        ];
    }
    
}
