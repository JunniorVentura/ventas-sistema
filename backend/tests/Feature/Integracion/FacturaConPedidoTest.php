<?php

namespace Tests\Feature\Integracion;

use App\Models\Usuario;
use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\Factura;
use App\Models\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\Permiso;

class FacturaConPedidoTest extends TestCase
{
    use RefreshDatabase;

    private function autenticar()
    {
        $usuario = Usuario::factory()->create();
    
        $permisos = ['crear_facturas', 'ver_logs', 'ver_facturas'];
        foreach ($permisos as $permiso) {
            $permisoCreado = Permiso::firstOrCreate(['nombre' => $permiso]);
    
            // Asigna permiso al rol del usuario
            $usuario->rol->permisos()->attach($permisoCreado->id, ['estado' => true]);
        }
    
        Sanctum::actingAs($usuario);
    } 

    public function test_usuario_puede_generar_factura_para_un_pedido_y_se_registra_log()
    {
        // Autenticación
        $this->autenticar();

        // Crear cliente y pedido
        $cliente = Cliente::factory()->create();
        $pedido = Pedido::factory()->create(['cliente_id' => $cliente->id]);

        // Crear factura asociada al pedido
        $response = $this->postJson('/api/facturas', [
            'pedido_id' => $pedido->id,
            'ruc_cliente' => $cliente->ruc,
            'razon_social' => 'Empresa S.A.C.',
            'total' => $pedido->total,
        ]);

        // Validar respuesta
        $response->assertStatus(201)
                 ->assertJsonFragment(['pedido_id' => $pedido->id]);

        // Verificar que se guardó en la base de datos
        $this->assertDatabaseHas('facturas', [
            'pedido_id' => $pedido->id,
            'ruc_cliente' => $cliente->ruc,
            'razon_social' => 'Empresa S.A.C.',
            'estado' => true,
        ]);

        // Verificar que se creó un log
        $this->assertDatabaseHas('logs', [
            'usuario_id' => auth()->user()->id,
            'tabla_afectada' => 'facturas',
            'accion' => 'crear',
        ]);
    }
}
