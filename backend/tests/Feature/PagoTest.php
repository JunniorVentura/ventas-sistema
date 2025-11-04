<?php

namespace Tests\Feature;

use App\Models\Pago;
use App\Models\Pedido;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\Permiso;

class PagoTest extends TestCase
{
    use RefreshDatabase;

    private function autenticar()
    {
        $usuario = Usuario::factory()->create();
    
        $permisos = ['ver_pagos', 'registrar_pagos', 'verificar_pagos', 'eliminar_pagos'];
        foreach ($permisos as $permiso) {
            $permisoCreado = Permiso::firstOrCreate(['nombre' => $permiso]);
    
            // Asigna permiso al rol del usuario
            $usuario->rol->permisos()->attach($permisoCreado->id, ['estado' => true]);
        }
    
        Sanctum::actingAs($usuario);
    } 

    
    public function test_puede_listar_pagos()
    {
        $this->autenticar();

        Pago::factory()->count(3)->create();

        $response = $this->getJson('/api/pagos');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     '*' => ['id', 'pedido_id', 'metodo_pago', 'estado_pago', 'fecha_pago', 'estado']
                 ]);
    }

    
    public function test_puede_crear_pago()
    {
        $this->autenticar();

        $pedido = Pedido::factory()->create();

        $response = $this->postJson('/api/pagos', [
            'pedido_id' => $pedido->id,
            'metodo_pago' => 'efectivo',
            'estado_pago' => 'pendiente',
            'fecha_pago' => now()->toDateTimeString(),
            'estado' => true,
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment([
                     'pedido_id' => $pedido->id,
                     'metodo_pago' => 'efectivo',
                     'estado_pago' => 'pendiente',
                 ]);

        $this->assertDatabaseHas('pagos', [
            'pedido_id' => $pedido->id,
            'metodo_pago' => 'efectivo',
        ]);
    }

    
    public function test_puede_ver_un_pago()
    {
        $this->autenticar();

        $pago = Pago::factory()->create();

        $response = $this->getJson("/api/pagos/{$pago->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $pago->id]);
    }

    
    public function test_puede_actualizar_pago()
    {
        $this->autenticar();

        $pago = Pago::factory()->create(['estado_pago' => 'pendiente']);

        $response = $this->putJson("/api/pagos/{$pago->id}", [
            'pedido_id' => $pago->pedido_id,
            'metodo_pago' => 'transferencia',
            'estado_pago' => 'verificado',
            'fecha_pago' => now()->toDateTimeString(),
            'estado' => true,
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['estado_pago' => 'verificado']);

        $this->assertDatabaseHas('pagos', ['id' => $pago->id, 'estado_pago' => 'verificado']);
    }

    
    public function test_puede_eliminar_pago()
    {
        $this->autenticar();
    
        $pago = Pago::factory()->create(['estado' => true]);
    
        $response = $this->deleteJson("/api/pagos/{$pago->id}");
    
        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Pago desactivado']);
    
        $this->assertDatabaseHas('pagos', [
            'id' => $pago->id,
            'estado' => false,
        ]);
    }
    
}
