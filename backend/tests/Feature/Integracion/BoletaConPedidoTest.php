<?php

namespace Tests\Feature\Integracion;

use App\Models\Usuario;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\Boleta;
use App\Models\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\Permiso;

class BoletaConPedidoTest extends TestCase
{
    use RefreshDatabase;

    private function autenticar()
    {
        $usuario = Usuario::factory()->create();
    
        $permisos = ['crear_boletas', 'crear_logs'];
        foreach ($permisos as $permiso) {
            $permisoCreado = Permiso::firstOrCreate(['nombre' => $permiso]);
    
            // Asigna permiso al rol del usuario
            $usuario->rol->permisos()->attach($permisoCreado->id, ['estado' => true]);
        }
    
        Sanctum::actingAs($usuario);
    } 

    public function test_usuario_puede_generar_boleta_para_un_pedido_y_se_registra_log()
    {
        // Autenticación
        $this->autenticar();

        // Crear cliente y pedido
        $cliente = Cliente::factory()->create();
        $pedido = Pedido::factory()->create(['cliente_id' => $cliente->id]);

        // Crear boleta para ese pedido
        $response = $this->postJson('/api/boletas', [
            'pedido_id' => $pedido->id,
            'dni_cliente' => $cliente->dni,
            'nombre_cliente' => $cliente->nombre,
            'total' => $pedido->total,
        ]);
        
        // Validar respuesta
        $response->assertStatus(201)
                 ->assertJsonFragment(['pedido_id' => $pedido->id]);

        // Validar que la boleta fue registrada
        $this->assertDatabaseHas('boletas', [
            'pedido_id' => $pedido->id,
            'total' => $pedido->total,
            'estado' => true,
        ]);

        // Validar que se registró un log
        $this->assertDatabaseHas('logs', [
            'usuario_id' => auth()->user()->id,  // Aquí obtienes el usuario autenticado
            'tabla_afectada' => 'boletas',
            'accion' => 'crear', 
        ]);
    }
}
