<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\Permiso;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RolPermisoTest extends TestCase
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

    
    public function test_puede_asignar_permisos_a_un_rol()
    {
        $this->autenticar();

        $rol = Rol::factory()->create();
        $permisos = Permiso::factory()->count(3)->create();

        $data = [
            'permiso_ids' => $permisos->pluck('id')->toArray()
        ];

        $response = $this->postJson("/api/rol-permisos/asignar/{$rol->id}", $data);

        $response->assertStatus(200)
                 ->assertJsonFragment(['mensaje' => 'Permisos asignados correctamente']);

        foreach ($permisos as $permiso) {
            $this->assertDatabaseHas('rol_permiso', [
                'rol_id' => $rol->id,
                'permiso_id' => $permiso->id
            ]);
        }
    }

    
    public function test_puede_listar_permisos_de_un_rol()
    {
        $this->autenticar();

        $rol = Rol::factory()->create();
        $permisos = Permiso::factory()->count(2)->create();
        $rol->permisos()->attach($permisos->pluck('id')->toArray());

        $response = $this->getJson("/api/rol-permisos/{$rol->id}/listar");

        $response->assertStatus(200)
                 ->assertJsonCount(2)
                 ->assertJsonFragment(['id' => $permisos[0]->id])
                 ->assertJsonFragment(['id' => $permisos[1]->id]);
    }
}
