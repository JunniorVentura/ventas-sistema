<?php

namespace Tests\Feature\Integracion;

use App\Models\Usuario;
use App\Models\Producto;
use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\Permiso;

class DetallePedidoEnLogTest extends TestCase
{
    use RefreshDatabase;

    private function autenticar()
    {
        $usuario = Usuario::factory()->create();
    
        $permisos = ['crear_logs', 'crear_pedidos', 'editar_pedidos', 'eliminar_pedidos'];
        foreach ($permisos as $permiso) {
            $permisoCreado = Permiso::firstOrCreate(['nombre' => $permiso]);
    
            // Asigna permiso al rol del usuario
            $usuario->rol->permisos()->attach($permisoCreado->id, ['estado' => true]);
        }
    
        Sanctum::actingAs($usuario);
    } 

    public function test_detalle_pedido_crea_logs_correctos()
    {
        // Autenticación
        $this->autenticar();

        // Crear producto y pedido
        $producto = Producto::factory()->create();
        $pedido = Pedido::factory()->create(['usuario_id' => auth()->user()->id]);

        // Crear detalle de pedido
        $responseCrear = $this->postJson('/api/detalle-pedidos', [
            'pedido_id' => $pedido->id,
            'producto_id' => $producto->id,
            'cantidad' => 2,
            'precio_unitario' => $producto->precio,
            'subtotal' => 2 * $producto->precio,
        ]);

        $responseCrear->assertStatus(201);
        $detalleId = $responseCrear->json('id');

        // Verificar log de creación
        $this->assertDatabaseHas('logs', [
            'usuario_id' => auth()->user()->id,  // Aquí obtienes el usuario autenticado
            'tabla_afectada' => 'detalle_pedidos',
            'id_registro' => $detalleId,
            'accion' => 'crear',
        ]);

        // Actualizar el detalle
        $responseEditar = $this->putJson("/api/detalle-pedidos/{$detalleId}", [
            'cantidad' => 3,
            'precio_unitario' => 15.00,
        ]);

        $responseEditar->assertStatus(200);

        // Verificar log de edición
        $this->assertDatabaseHas('logs', [
            'usuario_id' => auth()->user()->id,  // Aquí obtienes el usuario autenticado
            'tabla_afectada' => 'detalle_pedidos',
            'id_registro' => $detalleId,
            'accion' => 'editar',
        ]);

        // Eliminar (desactivar) el detalle
        $responseEliminar = $this->deleteJson("/api/detalle-pedidos/{$detalleId}");
        $responseEliminar->assertStatus(200);

        // Verificar log de eliminación
        $this->assertDatabaseHas('logs', [
            'usuario_id' => auth()->user()->id,  // Aquí obtienes el usuario autenticado
            'tabla_afectada' => 'detalle_pedidos',
            'id_registro' => $detalleId,
            'accion' => 'eliminar',
        ]);
    }
}
