<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;
use App\Models\Permiso;
use App\Models\RolPermiso;

class RolPermisoSeeder extends Seeder
{
    public function run(): void
    { 
        // Lista de permisos por rol
        $permisosPorRol = [
            'Administrador' => Permiso::pluck('id')->toArray(), // Todos los permisos

            'Vendedor' => Permiso::whereIn('nombre', [
                'crear_clientes', 'editar_clientes', 'ver_clientes', 'eliminar_clientes', 
                'ver_productos', 
                'crear_pedidos', 'ver_pedidos', 'eliminar_pedidos', 'editar_pedidos', 'ver_detalles_pedidos',
                'crear_facturas', 'ver_facturas', 'editar_facturas', 'eliminar_facturas',
                'crear_boletas', 'ver_boletas', 'editar_boletas',  'eliminar_boletas',
                'registrar_pagos', 'verificar_pagos', 'ver_pagos',
                'ver_stock',
                'ver_reportes',
                'ver_categorias',
                'emitir_documento',
            ])->pluck('id')->toArray(),

            'Almacenero' => Permiso::whereIn('nombre', [
                'ver_categorias',
                'ver_productos',
                'crear_productos',
                'editar_productos',
                'eliminar_productos',
                'ver_stock',
                'actualizar_stock',
                'ver_pedidos',
                'ver_detalles_pedidos',
                'ver_reportes',
                'exportar_reportes',
            ])->pluck('id')->toArray(),

            'Cajero' => Permiso::whereIn('nombre', [
                'ver_pagos', 'registrar_pagos', 'verificar_pagos',
                'ver_facturas', 'crear_facturas',
                'ver_boletas', 'crear_boletas',
            ])->pluck('id')->toArray(),

            'Reportes' => Permiso::whereIn('nombre', [
                'ver_reportes', 'exportar_reportes', 'ver_logs',
            ])->pluck('id')->toArray(),

            'Invitado' => Permiso::whereIn('nombre', [
                'ver_productos', 'ver_stock',
            ])->pluck('id')->toArray(),
        ];

        foreach ($permisosPorRol as $nombreRol => $idsPermisos) {
            $rol = Rol::where('nombre', $nombreRol)->first();

            if ($rol) {
                foreach ($idsPermisos as $permisoId) {
                    RolPermiso::firstOrCreate([
                        'rol_id' => $rol->id,
                        'permiso_id' => $permisoId,
                    ], [
                        'estado' => true,
                    ]);
                }
            }
        }
    }
}

/*
// Usando roles y permisos aleatorios
class RolPermisoSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Rol::all();
        $permisos = Permiso::all();

        foreach ($roles as $rol) {
            // Asigna entre 2 a 5 permisos aleatorios por rol
            //$permisosAleatorios = $permisos->random(rand(2, 5));

            foreach ($permisosAleatorios as $permiso) {
                // Evita duplicados si ya existen en la tabla
                RolPermiso::firstOrCreate([
                    'rol_id' => $rol->id,
                    'permiso_id' => $permiso->id,
                ], [
                    'estado' => true,
                ]);
            }
        }

        // Opcional: genera más combinaciones aleatorias
        //RolPermiso::factory()->count(10)->create();
    }
}*/