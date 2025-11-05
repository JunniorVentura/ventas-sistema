<?php

namespace Tests\Feature\Integracion;

use App\Models\Usuario;
use App\Models\Cliente;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\Permiso;

class PedidoCompletoTest extends TestCase
{
    use RefreshDatabase;

    private function autenticar()
    {
        $usuario = Usuario::factory()->create();
    
        $permisos = ['crear_pedidos'];
        foreach ($permisos as $permiso) {
            $permisoCreado = Permiso::firstOrCreate(['nombre' => $permiso]);
    
            // Asigna permiso al rol del usuario
            $usuario->rol->permisos()->attach($permisoCreado->id, ['estado' => true]);
        }
    
        Sanctum::actingAs($usuario);
    } 

    public function test_usuario_puede_crear_pedido_completo_con_detalles_y_log()
    {
        // Autenticación
        $this->autenticar();

        // Crear cliente y productos
        $cliente = Cliente::factory()->create();
        $producto1 = Producto::factory()->create(['precio' => 10]);
        $producto2 = Producto::factory()->create(['precio' => 20]);

        // Datos del pedido (sin detalles por ahora)
        $datosPedido = [
            'cliente_id' => $cliente->id,
            'usuario_id' => auth()->user()->id,
            'total' => 40.00,
            'fecha' => now()->toDateString(),
            'estado_pedido' => 'pendiente'
        ];

        // Enviar solicitud para crear pedido
        $response = $this->postJson('/api/pedidos', $datosPedido);

        // Validar respuesta
        $response->assertStatus(201)
                 ->assertJsonFragment(['estado_pedido' => 'pendiente']);

        // Obtener el ID del pedido creado
        $pedidoId = $response->json('id');

        // Crear detalles
        $detalles = [
            ['pedido_id' => $pedidoId, 'producto_id' => $producto1->id, 'cantidad' => 2, 'precio_unitario' => $producto1->precio],
            ['pedido_id' => $pedidoId, 'producto_id' => $producto2->id, 'cantidad' => 1, 'precio_unitario' => $producto2->precio]
        ];

        // Enviar detalles
        foreach ($detalles as $detalle) {
            $this->postJson('/api/detalle-pedidos', $detalle)->assertStatus(201);
        }

        // Verificaciones
        $this->assertDatabaseHas('pedidos', [
            'id' => $pedidoId,
            'cliente_id' => $cliente->id,
            'estado_pedido' => 'pendiente',
            'estado' => true,
        ]);

        $this->assertDatabaseCount('detalle_pedidos', 2);

        $this->assertDatabaseHas('logs', [
            'usuario_id' => auth()->user()->id,
            'tabla_afectada' => 'pedidos',
            'accion' => 'crear',
        ]);
    }
}
