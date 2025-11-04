<?php

namespace Tests\Feature\Integracion;

use App\Models\Usuario;
use App\Models\Cliente;
use App\Models\Pedido;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\Permiso;

class EliminarUsuarioConPedidosTest extends TestCase
{
    use RefreshDatabase;

    private function autenticar()
    {
        $usuario = Usuario::factory()->create();
    
        $permisos = ['eliminar_usuarios'];
        foreach ($permisos as $permiso) {
            $permisoCreado = Permiso::firstOrCreate(['nombre' => $permiso]);
    
            // Asigna permiso al rol del usuario
            $usuario->rol->permisos()->attach($permisoCreado->id, ['estado' => true]);
        }
    
        Sanctum::actingAs($usuario);
    } 

    public function test_no_se_puede_eliminar_usuario_con_pedidos()
    {
        // Autenticación
        $this->autenticar();

        // Crear un cliente/usuario con pedidos
        $clienteConPedidos = Usuario::factory()->create();
        Pedido::factory()->count(2)->create(['usuario_id' => $clienteConPedidos->id]);

        // Intentar eliminarlo
        $response = $this->deleteJson("/api/usuarios/{$clienteConPedidos->id}");

        // Esperamos un error (por ejemplo 400 o 409 - conflicto)
        $response->assertStatus(409)
                 ->assertJsonFragment([
                     'error' => 'No se puede eliminar el usuario porque tiene pedidos asociados.'
                 ]);

        // El usuario aún debe existir en la base de datos
        $this->assertDatabaseHas('usuarios', ['id' => $clienteConPedidos->id, 'estado' => true]);
    }
}
