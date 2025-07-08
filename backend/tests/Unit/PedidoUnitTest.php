<?php

namespace Tests\Unit;

use App\Models\Pedido;
use App\Models\Usuario;
use App\Models\Cliente;
use App\Models\DetallePedido;
use App\Models\Factura;
use App\Models\Boleta;
use App\Models\Pago;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PedidoUnitTest extends TestCase
{
    use RefreshDatabase;

    
    public function test_un_pedido_pertenece_a_un_usuario()
    {
        $usuario = Usuario::factory()->create();
        $pedido = Pedido::factory()->create(['usuario_id' => $usuario->id]);

        $this->assertInstanceOf(Usuario::class, $pedido->usuario);
        $this->assertEquals($usuario->id, $pedido->usuario->id);
    }

    
    public function test_un_pedido_pertenece_a_un_cliente()
    {
        $cliente = Cliente::factory()->create();
        $pedido = Pedido::factory()->create(['cliente_id' => $cliente->id]);

        $this->assertInstanceOf(Cliente::class, $pedido->cliente);
        $this->assertEquals($cliente->id, $pedido->cliente->id);
    }

    
    public function test_un_pedido_puede_tener_muchos_detalles()
    {
        $pedido = Pedido::factory()->create();
        $pedido->detalle_pedidos()->createMany(
            DetallePedido::factory()->count(3)->make(['pedido_id' => null])->toArray()
        );

        $this->assertCount(3, $pedido->detalle_pedidos);
        $this->assertInstanceOf(DetallePedido::class, $pedido->detalle_pedidos->first());
    }

    
    public function test_un_pedido_puede_tener_factura()
    {
        $pedido = Pedido::factory()->create();
        $factura = Factura::factory()->create(['pedido_id' => $pedido->id]);

        $this->assertInstanceOf(Factura::class, $pedido->factura);
        $this->assertEquals($factura->id, $pedido->factura->id);
    }

    
    public function test_un_pedido_puede_tener_boleta()
    {
        $pedido = Pedido::factory()->create();
        $boleta = Boleta::factory()->create(['pedido_id' => $pedido->id]);

        $this->assertInstanceOf(Boleta::class, $pedido->boleta);
        $this->assertEquals($boleta->id, $pedido->boleta->id);
    }

    
    public function test_un_pedido_puede_tener_pago()
    {
        $pedido = Pedido::factory()->create();
        $pago = Pago::factory()->create(['pedido_id' => $pedido->id]);

        $this->assertInstanceOf(Pago::class, $pedido->pago);
        $this->assertEquals($pago->id, $pedido->pago->id);
    }
}
