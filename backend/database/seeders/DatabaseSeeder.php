<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolSeeder::class,
            PermisoSeeder::class,
            RolPermisoSeeder::class,
            UsuarioSeeder::class,
            ClienteSeeder::class,
            CategoriaSeeder::class,
            ProductoSeeder::class,
            StockSeeder::class,
            PedidoSeeder::class,
            DetallePedidoSeeder::class,
            FacturaSeeder::class,
            DetalleFacturaSeeder::class,
            BoletaSeeder::class,
            DetalleBoletaSeeder::class,
            PagoSeeder::class,
            LogSeeder::class,
        ]);
        
        $this->command->info('¡Base de datos poblada exitosamente con todos los seeders!');
    }
}
