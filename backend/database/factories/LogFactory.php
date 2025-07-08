<?php

namespace Database\Factories;

use App\Models\Log;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

class LogFactory extends Factory
{
    protected $model = Log::class;

    public function definition(): array
    {
        return [
            'usuario_id' => Usuario::inRandomOrder()->first()->id ?? Usuario::factory(),
            'tabla_afectada' => $this->faker->randomElement([
                'usuarios', 'productos', 'pedidos', 'facturas', 'boletas', 'pagos'
            ]),
            'id_registro' => $this->faker->numberBetween(1, 100), // Simula ID del registro afectado
            'accion' => $this->faker->randomElement(['crear', 'editar', 'eliminar', 'login', 'logout']),
            'descripcion' => $this->faker->optional()->sentence(), // Puede ser nula
            'fecha' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
