<?php

namespace Tests\Unit;

use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Pedido;
use App\Models\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsuarioUnitTest extends TestCase
{
    use RefreshDatabase;

    
    public function test_un_usuario_tiene_una_relacion_con_rol()
    {
        $rol = Rol::factory()->create();
        $usuario = Usuario::factory()->create(['rol_id' => $rol->id]);

        $this->assertInstanceOf(Rol::class, $usuario->rol);
        $this->assertEquals($rol->id, $usuario->rol->id);
    }

    
    public function test_un_usuario_puede_tener_muchos_pedidos()
    {
        $usuario = Usuario::factory()->create();
        $usuario->pedidos()->createMany(
            Pedido::factory()->count(2)->make(['usuario_id' => null])->toArray()
        );

        $this->assertCount(2, $usuario->pedidos);
        $this->assertInstanceOf(Pedido::class, $usuario->pedidos->first());
    }

    
    public function test_un_usuario_puede_tener_muchos_logs()
    {
        $usuario = Usuario::factory()->create();
        $usuario->logs()->createMany(
            Log::factory()->count(3)->make(['usuario_id' => null])->toArray()
        );

        $this->assertCount(3, $usuario->logs);
        $this->assertInstanceOf(Log::class, $usuario->logs->first());
    }

    
    public function test_oculta_el_password_en_serializacion()
    {
        $usuario = Usuario::factory()->create(['password' => 'secret']);

        $this->assertArrayNotHasKey('password', $usuario->toArray());
    }
    
    public function test_usuario_tiene_token_expiration_valido()
    {
        $usuario = Usuario::factory()->create();

        $this->assertNotNull($usuario->token_expiration);
        $this->assertTrue(now()->lessThanOrEqualTo($usuario->token_expiration));
    }

}
