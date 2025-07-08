<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Stock;

class StockSeeder extends Seeder
{
    public function run(): void
    {
        $stocks = [
            [
                'producto_id' => 1, // Leche Gloria
                'cantidad'    => 100,
                'estado'      => true,
                'updated_at'  => now(),
            ],
            [
                'producto_id' => 2, // Queso fresco
                'cantidad'    => 50,
                'estado'      => true,
                'updated_at'  => now(),
            ],
            [
                'producto_id' => 3, // Arroz
                'cantidad'    => 200,
                'estado'      => true,
                'updated_at'  => now(),
            ],
            [
                'producto_id' => 4, // Gaseosa
                'cantidad'    => 120,
                'estado'      => true,
                'updated_at'  => now(),
            ],
            [
                'producto_id' => 5, // Detergente
                'cantidad'    => 75,
                'estado'      => true,
                'updated_at'  => now(),
            ],
        ];

        foreach ($stocks as $stock) {
            Stock::create($stock);
        }
    }
}
