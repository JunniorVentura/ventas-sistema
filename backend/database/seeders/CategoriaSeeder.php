<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        // Categorías fijas (opcional)
        $categoriasFijas = [
            ['nombre' => 'Tecnología', 'descripcion' => 'Equipos electrónicos y accesorios'],
            ['nombre' => 'Hogar', 'descripcion' => 'Productos para el hogar y cocina'],
            ['nombre' => 'Ropa', 'descripcion' => 'Vestimenta y accesorios de moda'],
        ];

        foreach ($categoriasFijas as $cat) {
            Categoria::firstOrCreate(
                ['nombre' => $cat['nombre']],
                [
                    'descripcion' => $cat['descripcion'],
                    'estado' => true,
                    'created_at' => now()
                ]
            );
        }

        // Categorías aleatorias con factory
        Categoria::factory()->count(7)->create();
    }
}
