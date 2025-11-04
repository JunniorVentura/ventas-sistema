<?php

namespace Tests\Unit;

use App\Models\Pago;
use App\Models\Pedido;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagoUnitTest extends TestCase
{
    use RefreshDatabase;

    
    public function test_un_pago_pertenece_a_un_pedido()
    {
        $pedido = Pedido::factory()->create();
        $pago = Pago::factory()->create(['pedido_id' => $pedido->id]);

        $this->assertInstanceOf(Pedido::class, $pago->pedido);
        $this->assertEquals($pedido->id, $pago->pedido->id);
    }

    
    public function test_metodo_de_pago_es_valido()
    {
        $pago = Pago::factory()->create([
            'metodo_pago' => 'yape'
        ]);

        $this->assertContains($pago->metodo_pago, ['efectivo', 'yape', 'transferencia']);
    }

    
    public function test_estado_de_pago_es_valido()
    {
        $pago = Pago::factory()->create([
            'estado_pago' => 'verificado'
        ]);

        $this->assertContains($pago->estado_pago, ['pendiente', 'verificado', 'rechazado']);
    }

    
    public function test_estado_es_true_por_defecto()
    {
        $pago = Pago::factory()->create();

        $this->assertTrue($pago->estado);
    }
}
