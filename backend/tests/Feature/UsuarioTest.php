<?php

namespace Tests\Feature;

use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Models\Permiso;

class UsuarioTest extends TestCase
{
    use RefreshDatabase;

    private function autenticar()
    {
        $usuario = Usuario::factory()->create();
    
        $permisos = ['ver_usuarios', 'crear_usuarios', 'editar_usuarios', 'eliminar_usuarios'];
        foreach ($permisos as $permiso) {
            $permisoCreado = Permiso::firstOrCreate(['nombre' => $permiso]);
    
            // Asigna permiso al rol del usuario
            $usuario->rol->permisos()->attach($permisoCreado->id, ['estado' => true]);
        }
    
        Sanctum::actingAs($usuario);
    } 

    
    public function test_puede_listar_usuarios()
    {
        $this->autenticar();

        Usuario::factory()->count(5)->create();

        $response = $this->getJson('/api/usuarios');

        $response->assertStatus(200)
                 ->assertJsonStructure([['id', 'nombre', 'email', 'rol_id']]);
    }

    
    public function test_puede_crear_un_usuario()
    {
        $this->autenticar();

        $rol = Rol::factory()->create();

        $response = $this->postJson('/api/usuarios', [
            'nombre' => 'Nuevo Usuario',
            'email' => 'nuevo@correo.com',
            'password' => 'secret123',
            'rol_id' => $rol->id,
            'estado' => true,
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['email' => 'nuevo@correo.com']);

        $this->assertDatabaseHas('usuarios', ['email' => 'nuevo@correo.com']);
    }

    
    public function test_puede_ver_detalle_de_un_usuario()
    {
        $this->autenticar();

        $usuario = Usuario::factory()->create();

        $response = $this->getJson("/api/usuarios/{$usuario->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['email' => $usuario->email]);
    }

    
    public function test_puede_actualizar_un_usuario()
    {
        $this->autenticar();

        $usuario = Usuario::factory()->create([
            'nombre' => 'Antiguo',
        ]);

        $response = $this->putJson("/api/usuarios/{$usuario->id}", [
            'nombre' => 'Actualizado',
            'email' => $usuario->email,
            'rol_id' => $usuario->rol_id,
            'estado' => true,
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['nombre' => 'Actualizado']);

        $this->assertDatabaseHas('usuarios', ['nombre' => 'Actualizado']);
    }

   
    public function test_puede_eliminar_un_usuario()
    {
        $this->autenticar();
    
        $usuario = Usuario::factory()->create(['estado' => true]);
    
        $response = $this->deleteJson("/api/usuarios/{$usuario->id}");
    
        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Usuario desactivado']);
    
        $this->assertDatabaseHas('usuarios', [
            'id' => $usuario->id,
            'estado' => false,
        ]);
    }

    public function test_usuario_con_token_expirado_no_puede_listar_usuarios()
    {
        $usuario = Usuario::factory()->create([
            'token_expiration' => now()->subMinutes(10), // Expirado hace 10 minutos
        ]);

        Sanctum::actingAs($usuario);

        $response = $this->getJson('/api/usuarios');

        $response->assertStatus(401)
                ->assertJson(['message' => 'Token expirado. Por favor inicie sesión nuevamente.']);
    }
    
}
