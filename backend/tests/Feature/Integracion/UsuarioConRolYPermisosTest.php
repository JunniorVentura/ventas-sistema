<?php

namespace Tests\Feature\Integracion;

use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Permiso;
use App\Models\RolPermiso;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UsuarioConRolYPermisosTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_con_rol_y_permisos_funciona_correctamente()
    {
        // Crear permisos
        $permisoVer = Permiso::create(['nombre' => 'ver productos']);
        $permisoEditar = Permiso::create(['nombre' => 'editar productos']);

        // Crear rol
        $rol = Rol::create(['nombre' => 'Administrador']);

        // Asignar permisos al rol (manualmente en tabla rol_permiso)
        RolPermiso::create([
            'rol_id' => $rol->id,
            'permiso_id' => $permisoVer->id,
            'estado' => true,
        ]);
        RolPermiso::create([
            'rol_id' => $rol->id,
            'permiso_id' => $permisoEditar->id,
            'estado' => true,
        ]);

        // Crear usuario y asignarle el rol
        $usuario = Usuario::factory()->create([
            'rol_id' => $rol->id,
        ]);

        // Autenticarse con Sanctum
        Sanctum::actingAs($usuario);

        // Verificar que el usuario tiene asignado el rol correcto
        $this->assertEquals('Administrador', $usuario->rol->nombre);

        // Verificar que el rol tiene los permisos asignados
        $permisos = RolPermiso::where('rol_id', $rol->id)
                        ->where('estado', true)
                        ->with('permiso')
                        ->get()
                        ->pluck('permiso.nombre')
                        ->toArray();

        $this->assertContains('ver productos', $permisos);
        $this->assertContains('editar productos', $permisos);

        // Simular una ruta protegida (opcional)
        // Puedes hacer un route temporal en `routes/api.php`:
        // Route::get('/api/protegido', fn() => response('OK'))->middleware('auth:sanctum');
        $response = $this->getJson('/api/protegido');
        $response->assertStatus(200)
                 ->assertSee('OK');
    }

    public function test_usuario_con_token_expirado_no_puede_acceder_ruta_protegida()
    {
        $rol = Rol::create(['nombre' => 'Administrador']);
        $usuario = Usuario::factory()->create([
            'rol_id' => $rol->id,
            'token_expiration' => now()->subMinutes(10), // token expirado
        ]);
    
        Sanctum::actingAs($usuario);
    
        $response = $this->getJson('/api/protegido');
    
        $response->assertStatus(401)
                 ->assertJson(['message' => 'Token expirado. Por favor inicie sesión nuevamente.']);
    }
    
}
