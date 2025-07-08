<?php

namespace Tests\Feature\Integracion;

use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Permiso;
use App\Models\RolPermiso;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AsignarRolPermisoTest extends TestCase
{
    use RefreshDatabase;

    private function autenticar()
    {
        $usuario = Usuario::factory()->create();
    
        $permisos = ['asignar_permisos'];
        foreach ($permisos as $permiso) {
            $permisoCreado = Permiso::firstOrCreate(['nombre' => $permiso]);
    
            // Asigna permiso al rol del usuario
            $usuario->rol->permisos()->attach($permisoCreado->id, ['estado' => true]);
        }
    
        Sanctum::actingAs($usuario);
    } 

    public function test_se_puede_asignar_rol_y_permisos_a_un_usuario()
    {
        
        // Autenticación
        $this->autenticar();

        // Crear rol y permisos
        $rol = Rol::factory()->create(['nombre' => 'Administrador']);
        $permiso1 = Permiso::factory()->create(['nombre' => 'crear_pedidos']);
        $permiso2 = Permiso::factory()->create(['nombre' => 'eliminar_usuarios']);

        // Asignar permisos al rol
        $responsePermisos = $this->postJson("/api/rol-permisos/asignar/{$rol->id}", [
            'permiso_ids' => [$permiso1->id, $permiso2->id],
        ]);

        $responsePermisos->assertStatus(200)
                         ->assertJsonFragment(['mensaje' => 'Permisos asignados correctamente']);

        // Crear usuario y asignarle el rol directamente
        $usuario = Usuario::factory()->create([
            'rol_id' => $rol->id,
        ]);

        // Verificar relaciones en BD
        $this->assertEquals($rol->id, $usuario->rol_id);

        $this->assertDatabaseHas('rol_permiso', [
            'rol_id' => $rol->id,
            'permiso_id' => $permiso1->id,
            'estado' => true,
        ]);

        $this->assertDatabaseHas('rol_permiso', [
            'rol_id' => $rol->id,
            'permiso_id' => $permiso2->id,
            'estado' => true,
        ]);
    }
}
