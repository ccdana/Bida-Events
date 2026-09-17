<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\ClientUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * Punto 30 y cliente de prueba: la revisión de salud, el registro de peticiones lentas y el
 * seeder del usuario cliente. El respaldo y su restauración están en BackupRestoreTest.
 */
class OperationsTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    private string $workspace;

    protected function setUp(): void
    {
        parent::setUp();

        // Carpeta de respaldos vacía y temporal: la revisión no debe leer la de la instalación
        $this->workspace = str_replace('\\', '/', sys_get_temp_dir()).'/bida-salud-'.uniqid();
        config(['operations.backups.path' => $this->workspace]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->workspace);

        parent::tearDown();
    }

    public function test_the_health_check_reports_failed_jobs_and_missing_backups(): void
    {
        DB::table('failed_jobs')->insert([
            'uuid' => (string) Str::uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => '{}',
            'exception' => 'No se pudo generar el Excel',
            'failed_at' => now(),
        ]);

        $this->artisan('bida:salud', ['--sin-correo' => true])
            ->expectsOutputToContain('Trabajos fallidos (24 h)')
            ->expectsOutputToContain('no hay ninguno')
            ->expectsOutputToContain('nunca se probó un respaldo')
            ->assertFailed();
    }

    public function test_the_health_check_passes_when_everything_is_in_order(): void
    {
        File::ensureDirectoryExists($this->workspace);
        File::put($this->workspace.'/base-2026-01-01_000000.sql.gz', gzencode('respaldo'));
        File::put($this->workspace.'/ultima-prueba.json', json_encode(['tested_at' => now()->toIso8601String(), 'passed' => true, 'error' => null]));
        config(['operations.alerts.min_free_disk_percent' => 0]);

        $this->artisan('bida:salud', ['--sin-correo' => true])->assertSuccessful();
    }

    public function test_slow_requests_are_logged_without_the_personal_token(): void
    {
        config(['operations.slow_request_ms' => 0]);
        $invitation = $this->createInvitation();
        $guest = $invitation->guests()->create(['name' => 'Familia Rojas', 'passes_allocated' => 1]);

        $logged = [];
        Log::shouldReceive('channel')->andReturnSelf();
        Log::shouldReceive('warning')->andReturnUsing(function (string $message, array $context = []) use (&$logged) {
            $logged[] = $context;
        });
        Log::shouldReceive('info', 'error', 'debug', 'notice', 'critical', 'log')->andReturnNull();

        $this->withoutVite()
            ->get(route('invitation.guest', ['slug' => $invitation->slug, 'token' => $guest->qr_code_token]))
            ->assertOk();

        $entry = collect($logged)->firstWhere('route', 'invitation.guest');
        $this->assertNotNull($entry, 'La petición lenta no quedó registrada');
        $this->assertSame(200, $entry['status']);
        $this->assertStringNotContainsString($guest->qr_code_token, json_encode($logged));
    }

    public function test_the_client_seeder_creates_a_test_client_without_taking_real_invitations(): void
    {
        $realClient = User::factory()->create();
        $free = $this->createInvitation(['slug' => ClientUserSeeder::INVITATION]);
        $owned = $this->createInvitation(['slug' => 'de-un-cliente-real', 'user_id' => $realClient->id]);

        putenv('SEED_CLIENT_PASSWORD=clave-de-prueba');
        $this->seed(ClientUserSeeder::class);
        putenv('SEED_CLIENT_PASSWORD');

        $client = User::where('username', ClientUserSeeder::USERNAME)->firstOrFail();
        $this->assertFalse($client->isAdmin());
        $this->assertTrue(Hash::check('clave-de-prueba', $client->password));
        $this->assertSame($client->id, $free->fresh()->user_id);

        // Una invitación con dueño no se toca, y correrlo de nuevo no duplica ni cambia la clave
        putenv('SEED_CLIENT_INVITATION=de-un-cliente-real');
        $this->seed(ClientUserSeeder::class);
        putenv('SEED_CLIENT_INVITATION');

        $this->assertSame($realClient->id, $owned->fresh()->user_id);
        $this->assertSame(1, User::where('username', ClientUserSeeder::USERNAME)->count());
        $this->assertTrue(Hash::check('clave-de-prueba', $client->fresh()->password));

        // Con su sesión entra a su invitación en el panel del cliente
        $this->actingAs($client)->get(route('client.invitation.show', $free))->assertOk();
    }
}
