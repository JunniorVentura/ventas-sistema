<?php

namespace Tests\Feature;

use App\Models\Pedido;
use App\Models\Usuario;
use App\Models\Cliente;
use App\Models\Producto;
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
    
        $permisos = ['ver_pedidos', 'crear_pedidos', 'editar_pedidos', 'eliminar_pedidos', 'registrar_pagos', 'editar_pagos'];
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

    /*public function test_puede_ver_detalles_completos_de_un_pedido()
    {
        // Autenticar al usuario
        $this->autenticar();
    
        // Crear un cliente
        $cliente = Cliente::factory()->create();
    
        // Crear productos
        $producto1 = Producto::factory()->create();
        $producto2 = Producto::factory()->create();
    
        // Crear un pedido con detalles y productos
        $pedido = Pedido::factory()->create([
            'cliente_id' => $cliente->id,
            'total' => 200.00
        ]);
    
        // Crear detalles de productos para el pedido
        $pedido->detalle_pedidos()->create([
            'producto_id' => $producto1->id,
            'cantidad' => 2,
            'precio_unitario' => 50.00,
            'subtotal' => 50.00 * 2,
            'precio' => 100.00
        ]);
        $pedido->detalle_pedidos()->create([
            'producto_id' => $producto2->id,
            'cantidad' => 1,
            'precio_unitario' => 100.00,
            'subtotal' => 100.00 * 1,
            'precio' => 100.00
        ]);
    
        // Hacer la solicitud GET al endpoint que muestra los detalles del pedido
        $response = $this->getJson("/api/pedidos/{$pedido->id}");
    
        // Imprimir la respuesta para revisar la estructura
        dd($response->json()); // Esto te permitirá ver la estructura completa de la respuesta
    
        // Verificar que la respuesta contiene los detalles correctos
        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'id' => $pedido->id,
                     'cliente_id' => $cliente->id,
                     'total' => '200.00',
                     'estado_pedido' => $pedido->estado_pedido,
                 ])
                 ->assertJsonFragment([
                     'producto_id' => $producto1->id,
                     'cantidad' => 2,  // Asegúrate de que este fragmento se encuentra en la respuesta
                     'precio_unitario' => 50.00,
                     'subtotal' => 100.00,
                     'precio' => 100.00
                 ])
                 ->assertJsonFragment([
                     'producto_id' => $producto2->id,
                     'cantidad' => 1,
                     'precio_unitario' => 100.00,
                     'subtotal' => 100.00,
                     'precio' => 100.00
                 ])
                 ->assertJsonStructure([
                     'id',
                     'cliente' => ['id', 'nombre', 'email'],
                     'total',
                     'estado_pedido',
                     'detalle_pedidos' => [
                         '*' => ['producto_id', 'cantidad', 'precio_unitario', 'subtotal', 'precio']
                     ],
                 ]);
    }*/
    
    public function test_puede_registrar_pago_de_un_pedido()
    {
        // Autenticar al usuario
        $this->autenticar();
    
        // Crear un cliente
        $cliente = Cliente::factory()->create();
    
        // Crear un pedido
        $pedido = Pedido::factory()->create([
            'cliente_id' => $cliente->id,
            'total' => 150.00,
            'estado_pedido' => 'pendiente', // Estado inicial del pedido
        ]);
    
        // Enviar la solicitud para actualizar el pedido
        $response = $this->putJson("/api/pedidos/{$pedido->id}", [
            'estado_pedido' => 'pagado', // Actualizamos el estado del pedido
        ]);
    
        // Verificar que el estado del pedido haya sido actualizado a 'pagado'
        $response->assertStatus(200) // Esperamos un código de estado 200
                 ->assertJsonFragment([
                     'estado_pedido' => 'pagado', // Aseguramos que el estado sea 'pagado'
                 ]);
    
        // Verificar que el estado real del pedido en la base de datos también se haya actualizado
        $pedido->refresh(); // Refrescar el pedido desde la base de datos
        $this->assertEquals('pagado', $pedido->estado_pedido); // Verificar que el estado en la BD sea 'pagado'
    
        // Registrar el pago en pagos 
        $responsePagos = $this->postJson('/api/pagos', [
            'pedido_id' => $pedido->id,
            'metodo_pago' => 'efectivo',
            'estado_pago' => 'pendiente',
            'fecha_pago' => now()->toDateTimeString(),
            'estado' => true,
        ]);
    
        // Verificar que el pago se haya registrado correctamente
        $responsePagos->assertStatus(201)
                     ->assertJsonFragment([
                         'pedido_id' => $pedido->id,
                         'metodo_pago' => 'efectivo',
                         'estado_pago' => 'pendiente',
                     ]);
    }  

    public function test_puede_cancelar_un_pedido()
    {
        $this->autenticar();

        $pedido = Pedido::factory()->create();

        $response = $this->putJson("/api/pedidos/{$pedido->id}", [
            'estado_pedido' => 'cancelado',
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'estado_pedido' => 'cancelado',
                 ]);
    }

    public function test_validar_integridad_entre_detalles_del_pedido_y_su_pedido_principal()
    {
        // Autenticación
        $this->autenticar();

        // Crear un cliente
        $cliente = Cliente::factory()->create();

        // Crear un pedido
        $pedido = Pedido::factory()->create([
            'cliente_id' => $cliente->id,
            'total' => 0.00,  // Inicializamos el total en 0.00
            'estado_pedido' => 'pendiente',
        ]);

        // Crear productos
        $producto1 = Producto::factory()->create(['precio' => 50.00]);
        $producto2 = Producto::factory()->create(['precio' => 100.00]);

        // Crear detalles de pedido
        $pedido->detalle_pedidos()->create([
            'producto_id' => $producto1->id,
            'cantidad' => 2,
            'precio_unitario' => 50.00,
            'subtotal' => 100.00,
        ]);

        $pedido->detalle_pedidos()->create([
            'producto_id' => $producto2->id,
            'cantidad' => 1,
            'precio_unitario' => 100.00,
            'subtotal' => 100.00,
        ]);

        // Ahora, deberíamos actualizar el total del pedido
        $pedido->refresh();  // Refrescar para obtener el total actualizado
        $pedido->total = $pedido->detalle_pedidos->sum('subtotal');
        $pedido->save();  // Guardar el total actualizado

        // Verificar la integridad del pedido: Total debe ser la suma de los subtotales de los detalles
        $this->assertEquals(200.00, $pedido->total); // Verificamos que el total del pedido es correcto

        // Verificar que el pedido tenga los detalles asociados correctamente
        $this->assertCount(2, $pedido->detalle_pedidos); // Verificamos que el pedido tiene 2 detalles asociados
        $this->assertDatabaseHas('detalle_pedidos', [
            'pedido_id' => $pedido->id,
            'producto_id' => $producto1->id,
            'cantidad' => 2,
            'precio_unitario' => 50.00,
        ]);
        $this->assertDatabaseHas('detalle_pedidos', [
            'pedido_id' => $pedido->id,
            'producto_id' => $producto2->id,
            'cantidad' => 1,
            'precio_unitario' => 100.00,
        ]);

    }

}
