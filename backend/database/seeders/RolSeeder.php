<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        // Crear roles fijos (opcional)
        Rol::create([
            'nombre' => 'Administrador',
            'descripcion' => 'Control total del sistema.',
            'estado' => true,
            'created_at' => now(),
        ]);

        Rol::create([
            'nombre' => 'Vendedor',
            'descripcion' => 'Realiza ventas, gestiona pedidos, consulta stock y productos.',
            'estado' => true,
            'created_at' => now(),
        ]);

        Rol::create([
            'nombre' => 'Almacenero',
            'descripcion' => 'Gestiona el stock y productos.',
            'estado' => true,
            'created_at' => now(),
        ]);
        
        Rol::create([
            'nombre' => 'Cajero',
            'descripcion' => 'Registra pagos, genera boletas y facturas.',
            'estado' => true,
            'created_at' => now(),
        ]);

        Rol::create([
            'nombre' => 'Reportes',
            'descripcion' => 'Solo puede acceder a reportes y logs.',
            'estado' => true,
            'created_at' => now(),
        ]);

        Rol::create([
            'nombre' => 'Invitado',
            'descripcion' => 'Solo puede visualizar productos, stock (lectura).',
            'estado' => true,
            'created_at' => now(),
        ]);
        // Crear roles aleatorios con factory
        //Rol::factory()->count(3)->create();
    }
}
