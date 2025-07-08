<?php

namespace Database\Factories;

use App\Models\DetalleFactura;
use App\Models\Factura;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

class DetalleFacturaFactory extends Factory
{
    protected $model = DetalleFactura::class;

    public function definition(): array
    {
        $producto = Producto::inRandomOrder()->first() ?? Producto::factory()->create();
        $cantidad = $this->faker->numberBetween(1, 10);
        $precio = $producto->precio ?? $this->faker->randomFloat(2, 10, 1000);

        return [
            'factura_id' => Factura::inRandomOrder()->first()->id ?? Factura::factory(),
            'producto_id' => $producto->id,
            'cantidad' => $cantidad,
            'precio_unitario' => $precio,
            'subtotal' => round($cantidad * $precio, 2),
            'estado' => true,
        ];
    }
}
