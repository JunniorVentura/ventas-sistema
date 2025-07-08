<?php

namespace Tests\Unit;

use App\Models\Log;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogUnitTest extends TestCase
{
    use RefreshDatabase;

    
    public function test_un_log_pertenece_a_un_usuario()
    {
        $usuario = Usuario::factory()->create();
        $log = Log::factory()->create([
            'usuario_id' => $usuario->id,
        ]);

        $this->assertInstanceOf(Usuario::class, $log->usuario);
        $this->assertEquals($usuario->id, $log->usuario->id);
    }

    
    public function test_accion_es_valida()
    {
        $log = Log::factory()->create([
            'accion' => 'crear',
        ]);

        $this->assertContains($log->accion, ['crear', 'editar', 'eliminar', 'login', 'logout']);
    }

    
    public function test_tabla_afectada_es_valida()
    {
        $log = Log::factory()->create([
            'tabla_afectada' => 'usuarios',
        ]);

        $this->assertIsString($log->tabla_afectada);
        $this->assertNotEmpty($log->tabla_afectada);
    }

    
    public function test_id_registro_es_entero()
    {
        $log = Log::factory()->create([
            'id_registro' => 42,
        ]);

        $this->assertIsInt($log->id_registro);
        $this->assertGreaterThan(0, $log->id_registro);
    }
}
