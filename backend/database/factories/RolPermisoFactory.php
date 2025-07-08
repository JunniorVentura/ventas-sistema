<?php

namespace Database\Factories;

use App\Models\RolPermiso;
use App\Models\Rol;
use App\Models\Permiso;
use Illuminate\Database\Eloquent\Factories\Factory;

class RolPermisoFactory extends Factory
{
    protected $model = RolPermiso::class;

    public function definition(): array
    {
        return [
            'rol_id' => Rol::inRandomOrder()->first()->id ?? Rol::factory(), // Usa uno existente o crea nuevo
            'permiso_id' => Permiso::inRandomOrder()->first()->id ?? Permiso::factory(),
            'estado' => true,
        ];
    }
}
