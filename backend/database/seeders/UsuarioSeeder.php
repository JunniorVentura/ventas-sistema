<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $usuariosFijos = [
            [
                'nombre' => 'Admin Principal',
                'email' => 'admin@empresa.com',
                'password' => 'admin123',
                'rol' => 'Administrador',
            ],
            [
                'nombre' => 'Vendedor Prueba',
                'email' => 'vendedor@empresa.com',
                'password' => 'vendedor123',
                'rol' => 'Vendedor',
            ],
            [
                'nombre' => 'Almacenero',
                'email' => 'almacenero@empresa.com',
                'password' => 'almacenero123',
                'rol' => 'Almacenero',
            ],
            [
                'nombre' => 'Reportes Viewer',
                'email' => 'reportes@empresa.com',
                'password' => 'reportes123',
                'rol' => 'Reportes',
            ],
        ];

        foreach ($usuariosFijos as $datos) {
            // Asegurar que el rol exista
            $rol = Rol::firstOrCreate(
                ['nombre' => $datos['rol']],
                ['descripcion' => 'Rol autogenerado', 'estado' => true, 'created_at' => now()]
            );

            // Crear o actualizar usuario
            Usuario::firstOrCreate(
                ['email' => $datos['email']],
                [
                    'nombre' => $datos['nombre'],
                    'password' => Hash::make($datos['password']),
                    'rol_id' => $rol->id,
                    'estado' => true,
                    'created_at' => now(),
                ]
            );
        }

        /*// Crear usuarios de prueba aleatorios si aún no hay muchos
        if (Usuario::count() < 10) {
            Usuario::factory()->count(10)->create();
        }*/
    }
}
