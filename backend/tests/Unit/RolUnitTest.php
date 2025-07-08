<?php

namespace Tests\Unit;

use App\Models\Rol;
use App\Models\Usuario;
use App\Models\Permiso;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolUnitTest extends TestCase
{
    use RefreshDatabase;

    
    public function test_un_rol_puede_tener_muchos_usuarios()
    {
        $rol = Rol::factory()->create();
        $usuarios = Usuario::factory()->count(3)->create([
            'rol_id' => $rol->id,
        ]);

        $this->assertCount(3, $rol->usuarios);
        $this->assertInstanceOf(Usuario::class, $rol->usuarios->first());
    }

    
    public function test_un_rol_puede_tener_muchos_permisos()
    {
        $rol = Rol::factory()->create();
        $permisos = Permiso::factory()->count(2)->create();

        $rol->permisos()->attach($permisos->pluck('id'));

        $this->assertCount(2, $rol->permisos);
        $this->assertInstanceOf(Permiso::class, $rol->permisos->first());
    }

    
    public function test_el_nombre_del_rol_es_requerido_y_valido()
    {
        $rol = Rol::factory()->create([
            'nombre' => 'Administrador'
        ]);

        $this->assertNotEmpty($rol->nombre);
        $this->assertIsString($rol->nombre);
    }

    
    public function test_el_estado_del_rol_es_true_por_defecto()
    {
        $rol = Rol::factory()->create();

        $this->assertTrue($rol->estado);
    }
}
