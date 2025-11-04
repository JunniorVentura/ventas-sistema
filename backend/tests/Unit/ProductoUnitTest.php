<?php

namespace Tests\Unit;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\DetallePedido;
use App\Models\DetalleFactura;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductoUnitTest extends TestCase
{
    use RefreshDatabase;

    
    public function test_un_producto_pertenece_a_una_categoria()
    {
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create([
            'categoria_id' => $categoria->id
        ]);

        $this->assertInstanceOf(Categoria::class, $producto->categoria);
        $this->assertEquals($categoria->id, $producto->categoria->id);
    }

    
    public function test_un_producto_puede_tener_detalles_de_pedido()
    {
        $producto = Producto::factory()->create();
        $producto->detallePedidos()->createMany(
            DetallePedido::factory()->count(2)->make(['producto_id' => null])->toArray()
        );

        $this->assertCount(2, $producto->detallePedidos);
        $this->assertInstanceOf(DetallePedido::class, $producto->detallePedidos->first());
    }

    
    public function test_un_producto_puede_tener_detalles_de_factura()
    {
        $producto = Producto::factory()->create();
        $producto->detalleFacturas()->createMany(
            DetalleFactura::factory()->count(2)->make(['producto_id' => null])->toArray()
        );

        $this->assertCount(2, $producto->detalleFacturas);
        $this->assertInstanceOf(DetalleFactura::class, $producto->detalleFacturas->first());
    }
}
