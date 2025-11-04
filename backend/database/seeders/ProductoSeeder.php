<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;
use App\Models\Categoria;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        // Asegurarse de que existan categorías
        if (Categoria::count() === 0) {
            $this->call(CategoriaSeeder::class);
        }

        // Crear productos con categorías existentes
        Producto::factory()->count(20)->create();
    }
}
