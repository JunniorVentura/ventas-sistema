<?php

namespace Tests\Feature;

use App\Models\Pedido;
use App\Models\Usuario;
use App\Models\Cliente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\Permiso;

class PedidoTest extends TestCase
{
    use RefreshDatabase;

    private function autenticar()
    {
        $usuario = Usuario::factory()->create();
    
        $permisos = ['ver_pedidos', 'crear_pedidos', 'editar_pedidos', 'eliminar_pedidos'];
        foreach ($permisos as $permiso) {
            $permisoCreado = Permiso::firstOrCreate(['nombre' => $permiso]);
    
            // Asigna permiso al rol del usuario
            $usuario->rol->permisos()->attach($permisoCreado->id, ['estado' => true]);
        }
    
        Sanctum::actingAs($usuario);
    } 


    public function test_puede_listar_pedidos()
    {
        $this->autenticar();

        Pedido::factory()->count(5)->create();

        $response = $this->getJson('/api/pedidos');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     '*' => [
                         'id',
                         'cliente',
                         'usuario',
                         'total',
                         'estado_pedido',
                         'boleta_emitida',
                         'factura_emitida',
                         'pagos',
                         'detalle_pedidos'
                     ]
                 ]);
        
    }

    public function test_puede_crear_un_pedido()
    {
        $this->autenticar();

        $usuario = Usuario::factory()->create();
        $cliente = Cliente::factory()->create();

        $response = $this->postJson('/api/pedidos', [
            'usuario_id' => $usuario->id,
            'cliente_id' => $cliente->id,
            'fecha' => now(),
            'total' => 120.50,
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment([
                     'cliente_id' => $cliente->id,
                     'total' => 120.50
                 ]);
    }


    public function test_puede_ver_un_pedido()
    {
        $this->autenticar();

        $pedido = Pedido::factory()->create();

        $response = $this->getJson("/api/pedidos/{$pedido->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'id' => $pedido->id
                 ]);
    }


    public function test_puede_actualizar_un_pedido()
    {
        $this->autenticar();

        $pedido = Pedido::factory()->create();

        $response = $this->putJson("/api/pedidos/{$pedido->id}", [
            'cliente_id' => $pedido->cliente_id,
            'usuario_id' => $pedido->usuario_id,
            'fecha' => now(),
            'total' => 200.00,
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'total' => 200.00
                 ]);
    }

    
    public function test_puede_eliminar_un_pedido() 
    {
        $this->autenticar();
    
        $pedido = Pedido::factory()->create(['estado' => true]);
    
        $response = $this->deleteJson("/api/pedidos/{$pedido->id}");
    
        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Pedido desactivado']);
    
        $this->assertDatabaseHas('pedidos', [
            'id' => $pedido->id,
            'estado' => false, // verificación de eliminación lógica
        ]);
    }
    
}
