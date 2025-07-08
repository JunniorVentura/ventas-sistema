<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permiso;

class PermisoSeeder extends Seeder
{
    public function run(): void
    {
        // Permisos fijos
        $permisos = [
            'ver_usuarios', 'crear_usuarios', 'editar_usuarios', 'eliminar_usuarios',
            'ver_roles', 'crear_roles', 'editar_roles', 'eliminar_roles', 
            'ver_permisos', 'asignar_permisos','ver_rolpermisos', 'asignar_rolpermisos', 'eliminar_rolpermisos',
            'ver_clientes', 'crear_clientes', 'editar_clientes', 'eliminar_clientes',
            'ver_categorias', 'crear_categorias', 'editar_categorias', 'eliminar_categorias',
            'ver_productos', 'crear_productos', 'editar_productos', 'eliminar_productos',
            'ver_stock', 'actualizar_stock',
            'ver_pedidos', 'crear_pedidos', 'editar_pedidos', 'eliminar_pedidos',
            'ver_detalles_pedidos','emitir_documento',
            'ver_facturas', 'crear_facturas', 'editar_facturas', 'eliminar_facturas',
            'ver_boletas', 'crear_boletas', 'editar_boletas', 'eliminar_boletas',
            'ver_pagos', 'registrar_pagos', 'verificar_pagos',
            'ver_logs',
            'ver_reportes', 'exportar_reportes',
        ];

        foreach ($permisos as $nombre) {
            Permiso::firstOrCreate(['nombre' => $nombre], [
                'descripcion' => ucfirst(str_replace('_', ' ', $nombre)),
                'estado' => true,
                'created_at' => now(),
            ]);
        }
        // Crear permisos adicionales con el factory
        //Permiso::factory()->count(5)->create();
    }
}
