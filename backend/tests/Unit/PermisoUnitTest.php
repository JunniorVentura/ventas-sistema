<?php

namespace Tests\Unit;

use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermisoUnitTest extends TestCase
{
    use RefreshDatabase;

    
    public function test_un_permiso_puede_estar_asignado_a_muchos_roles()
    {
        $permiso = Permiso::factory()->create();
        $roles = Rol::factory()->count(2)->create();

        $permiso->roles()->attach($roles->pluck('id'));

        $this->assertCount(2, $permiso->roles);
        $this->assertInstanceOf(Rol::class, $permiso->roles->first());
    }

    
    public function test_el_nombre_del_permiso_es_requerido_y_valido()
    {
        $permiso = Permiso::factory()->create([
            'nombre' => 'crear_usuarios',
        ]);

        $this->assertNotEmpty($permiso->nombre);
        $this->assertIsString($permiso->nombre);
    }

    
    public function test_el_estado_del_permiso_es_true_por_defecto()
    {
        $permiso = Permiso::factory()->create();

        $this->assertTrue($permiso->estado);
    }
}
