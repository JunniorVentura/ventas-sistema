<?php

namespace Tests\Feature;

use App\Models\Boleta;
use App\Models\Pedido;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\Permiso;

class BoletaTest extends TestCase
{
    use RefreshDatabase;

    private function autenticar()
    {
        $usuario = Usuario::factory()->create();
    
        $permisos = ['ver_boletas', 'crear_boletas', 'editar_boletas', 'eliminar_boletas'];
        foreach ($permisos as $permiso) {
            $permisoCreado = Permiso::firstOrCreate(['nombre' => $permiso]);
    
            // Asigna permiso al rol del usuario
            $usuario->rol->permisos()->attach($permisoCreado->id, ['estado' => true]);
        }
    
        Sanctum::actingAs($usuario);
    }    
    
    public function test_puede_listar_boletas()
    {
        $this->autenticar();

        Boleta::factory()->count(3)->create();

        $response = $this->getJson('/api/boletas');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     '*' => ['id', 'pedido_id', 'dni_cliente', 'nombre_cliente', 'fecha_emision', 'total', 'estado']
                 ]);
    }

    
    public function test_puede_crear_boleta()
    {
        $this->autenticar();

        $pedido = Pedido::factory()->create();

        $response = $this->postJson('/api/boletas', [
            'pedido_id' => $pedido->id,
            'dni_cliente' => '12345678',
            'nombre_cliente' => 'Junnior César',
            'total' => 250.00,
            'estado' => true,
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment([
                     'dni_cliente' => '12345678',
                     'nombre_cliente' => 'Junnior César',
                     'total' => 250.00,
                 ]);

        $this->assertDatabaseHas('boletas', ['pedido_id' => $pedido->id, 'dni_cliente' => '12345678']);
    }

    
    public function test_puede_ver_una_boleta()
    {
        $this->autenticar();

        $boleta = Boleta::factory()->create();

        $response = $this->getJson("/api/boletas/{$boleta->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $boleta->id]);
    }

   
    public function test_puede_actualizar_boleta()
    {
        $this->autenticar();

        $boleta = Boleta::factory()->create(['nombre_cliente' => 'Cliente A']);

        $response = $this->putJson("/api/boletas/{$boleta->id}", [
            'pedido_id' => $boleta->pedido_id,
            'dni_cliente' => $boleta->dni_cliente,
            'nombre_cliente' => 'Cliente Actualizado',
            'fecha_emision' => $boleta->fecha_emision,
            'total' => 300.00,
            'estado' => true,
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['nombre_cliente' => 'Cliente Actualizado']);

        $this->assertDatabaseHas('boletas', ['id' => $boleta->id, 'nombre_cliente' => 'Cliente Actualizado']);
    }

    
    public function test_puede_eliminar_boleta()
    {
        $this->autenticar();
    
        $boleta = Boleta::factory()->create(['estado' => true]);
    
        $response = $this->deleteJson("/api/boletas/{$boleta->id}");
    
        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Boleta desactivada']);
    
        $this->assertDatabaseHas('boletas', [
            'id' => $boleta->id,
            'estado' => false,
        ]);
    }
    
}
