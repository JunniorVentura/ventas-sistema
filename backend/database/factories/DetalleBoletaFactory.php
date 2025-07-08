<?php

namespace Database\Factories;

use App\Models\DetalleBoleta;
use App\Models\Boleta;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

class DetalleBoletaFactory extends Factory
{
    protected $model = DetalleBoleta::class;

    public function definition(): array
    {
        $producto = Producto::inRandomOrder()->first() ?? Producto::factory()->create();
        $cantidad = $this->faker->numberBetween(1, 10);
        $precio = $producto->precio ?? $this->faker->randomFloat(2, 5, 500);

        return [
            'boleta_id' => Boleta::inRandomOrder()->first()->id ?? Boleta::factory(),
            'producto_id' => $producto->id,
            'cantidad' => $cantidad,
            'precio_unitario' => $precio,
            'subtotal' => round($cantidad * $precio, 2),
            'estado' => true,
        ];
    }
}
