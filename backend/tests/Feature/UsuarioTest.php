<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UsuarioTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'usuario_test',
            'database.connections.usuario_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);
        DB::purge('usuario_test');
        // Esquema final para probar el modelo. SQLite requiere DBAL para change();
        // estas pruebas no validan la aplicación de la migración en MySQL.
        Schema::create('usuario', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('authOId')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        DB::purge('usuario_test');
        parent::tearDown();
    }

    private function migracion(): \Illuminate\Database\Migrations\Migration
    {
        return require database_path('migrations/2026_09_20_000002_prepare_usuario_for_local_login.php');
    }

    public function test_login_inicia_sesion_y_permite_consultar_usuario(): void
    {
        $usuario = Usuario::create(['username' => 'sole', 'password' => 'ClaveDePrueba123!']);
        $this->withSession(['marca' => 'conservar']);
        $sessionId = session()->getId();

        $this->postJson('/api/login', ['username' => 'sole', 'password' => 'ClaveDePrueba123!'])
            ->assertOk()
            ->assertExactJson(['user' => ['id' => $usuario->id, 'username' => 'sole']]);

        $this->assertAuthenticatedAs($usuario, 'web');
        $this->assertNotSame($sessionId, session()->getId());
        // Obliga al guard a recuperar al usuario desde la sesion en la siguiente solicitud.
        app('auth')->forgetGuards();
        $this->withHeader('Origin', 'http://localhost:5173')->getJson('/api/user')
            ->assertOk()->assertJsonPath('username', 'sole')
            ->assertJsonMissingPath('password')->assertJsonMissingPath('remember_token');
    }

    public function test_login_rechaza_clave_incorrecta_usuario_inexistente_y_cuenta_sin_clave(): void
    {
        Usuario::create(['username' => 'sole', 'password' => 'ClaveDePrueba123!']);
        Usuario::create(['username' => 'anterior']);

        foreach (['sole', 'inexistente', 'anterior'] as $username) {
            $this->postJson('/api/login', ['username' => $username, 'password' => 'incorrecta'])
                ->assertUnauthorized()->assertExactJson(['message' => 'Credenciales incorrectas.']);
            $this->assertGuest('web');
        }
    }

    public function test_logout_invalida_sesion_y_retira_acceso_sin_borrar_usuario(): void
    {
        $usuario = Usuario::create(['username' => 'sole', 'password' => 'ClaveDePrueba123!']);
        $this->postJson('/api/login', ['username' => 'sole', 'password' => 'ClaveDePrueba123!'])
            ->assertOk();
        app('auth')->forgetGuards();
        $this->withHeader('Origin', 'http://localhost:5173')->getJson('/api/user')->assertOk();

        $this->withSession(['marca' => 'eliminar']);
        $sessionId = session()->getId();
        $csrfToken = session()->token();
        app('auth')->forgetGuards();

        $this->postJson('/api/logout')->assertOk()
            ->assertExactJson(['message' => 'Sesion cerrada.'])
            ->assertSessionMissing('marca');

        $this->assertGuest('web');
        $this->assertNotSame($sessionId, session()->getId());
        $this->assertNotEmpty(session()->token());
        $this->assertNotSame($csrfToken, session()->token());
        app('auth')->forgetGuards();
        $this->getJson('/api/user')->assertUnauthorized();
        $this->assertTrue(Hash::check('ClaveDePrueba123!', $usuario->fresh()->password));

        $this->postJson('/api/logout')->assertOk();
        app('auth')->forgetGuards();
        $this->getJson('/api/user')->assertUnauthorized();
    }

    public function test_logout_requiere_csrf_y_conserva_sesion_si_falta(): void
    {
        $usuario = Usuario::create(['username' => 'sole', 'password' => 'ClaveDePrueba123!']);
        $this->postJson('/api/login', ['username' => 'sole', 'password' => 'ClaveDePrueba123!'])
            ->assertOk();
        $this->app->instance('env', 'local');
        $this->postJson('/api/logout')->assertStatus(419);
        $this->assertAuthenticatedAs($usuario, 'web');
    }

    public function test_login_valida_campos_y_limita_intentos(): void
    {
        $this->postJson('/api/login', [])->assertUnprocessable()
            ->assertJsonValidationErrors(['username', 'password']);

        for ($intento = 0; $intento < 4; $intento++) {
            $this->postJson('/api/login', ['username' => 'inexistente', 'password' => 'incorrecta'])
                ->assertUnauthorized();
        }

        $this->postJson('/api/login', ['username' => 'inexistente', 'password' => 'incorrecta'])
            ->assertStatus(429);
    }

    public function test_usuario_privado_requiere_autenticacion(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
    }

    public function test_login_requiere_csrf_fuera_del_entorno_de_pruebas(): void
    {
        $this->app->instance('env', 'local');
        $this->postJson('/api/login', ['username' => 'sole', 'password' => 'incorrecta'])
            ->assertStatus(419);
    }

    public function test_guarda_hash_y_oculta_credenciales(): void
    {
        $usuario = Usuario::create(['username' => 'prueba', 'password' => 'ClaveDePrueba123!']);
        $usuario->remember_token = 'token-de-prueba';
        $usuario->save();
        $usuario = $usuario->fresh();

        $this->assertNull($usuario->authOId);
        $this->assertNotSame('ClaveDePrueba123!', $usuario->password);
        $this->assertTrue(Hash::check('ClaveDePrueba123!', $usuario->password));
        $this->assertArrayNotHasKey('password', $usuario->toArray());
        $this->assertArrayNotHasKey('remember_token', $usuario->toArray());
        $this->assertTrue(Auth::validate(['username' => 'prueba', 'password' => 'ClaveDePrueba123!']));
        $this->assertFalse(Auth::validate(['username' => 'prueba', 'password' => 'incorrecta']));
    }

    public function test_una_cuenta_anterior_no_recibe_una_clave_por_defecto(): void
    {
        $original = Usuario::create(['username' => 'anterior', 'authOId' => 'externo-123']);
        $usuario = $original->fresh();
        $this->assertSame('externo-123', $usuario->authOId);
        $this->assertNull($usuario->password);
        $this->assertNull($usuario->getAuthPassword());
    }

    public function test_rechaza_nombres_de_usuario_repetidos(): void
    {
        Usuario::create(['username' => 'prueba']);
        $this->expectException(QueryException::class);
        Usuario::create(['username' => 'prueba']);
    }

    public function test_detecta_duplicados_anteriores_sin_modificar_cuentas(): void
    {
        Schema::drop('usuario');
        (require database_path('migrations/2025_10_10_235012_create_usuario_table.php'))->up();
        DB::table('usuario')->insert([
            ['username' => 'repetido', 'authOId' => 'uno'],
            ['username' => 'repetido', 'authOId' => 'dos'],
        ]);
        try {
            $this->migracion()->up();
            $this->fail('Debía detectar nombres duplicados.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('nombres de usuario duplicados', $exception->getMessage());
            $this->assertSame(2, Usuario::count());
        }
    }
}
