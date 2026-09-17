<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\GuestContribution;
use App\Models\PollVote;
use App\Models\User;
use App\Services\InvitationModuleService;
use Database\Seeders\XvSofiaModuleData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * Punto 29: lo que pasa cuando dos personas tocan a la vez, cuando suben un archivo que no
 * corresponde y cuando alguien insiste más de la cuenta.
 */
class ConcurrencyAndLimitsTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    private const THROTTLE_MESSAGE = 'Demasiados intentos. Espera un momento e inténtalo de nuevo.';

    // ── Concurrencia ─────────────────────────────────────────────────────────

    public function test_two_simultaneous_votes_from_the_same_person_count_once(): void
    {
        $invitation = $this->createInvitation();
        app(InvitationModuleService::class)->syncAllModules($invitation, XvSofiaModuleData::all());
        $pollId = $invitation->polls()->where('poll_key', 'color-vestido')->value('id');

        // Se simula la carrera: el otro toque inserta su voto justo después de la comprobación
        // «¿ya votó?» y justo antes de este insert. Solo la clave única puede frenarlo.
        PollVote::creating(function (PollVote $vote) {
            PollVote::withoutEvents(fn () => DB::table('poll_votes')->insert([
                'invitation_id' => $vote->invitation_id,
                'invitation_poll_id' => $vote->invitation_poll_id,
                'option_index' => 1,
                'voter_key' => $vote->voter_key,
                'created_at' => now(),
            ]));
        });

        $this->postJson(
            route('invitation.poll.vote', ['slug' => $invitation->slug, 'pollId' => 'color-vestido']),
            ['option_index' => 0, 'guest_token' => 'mismo-celular']
        )
            ->assertStatus(422)
            ->assertJson(['success' => false, 'message' => 'Ya votaste en esta encuesta.']);

        $this->assertSame(1, PollVote::where('invitation_poll_id', $pollId)->where('voter_key', 'mismo-celular')->count());
    }

    public function test_two_simultaneous_confirmations_keep_a_valid_state(): void
    {
        $invitation = $this->createInvitation();
        $guest = $invitation->guests()->create(['name' => 'Familia Choque', 'passes_allocated' => 3]);

        // Dos celulares de la misma familia confirman casi a la vez con cantidades distintas.
        // La confirmación es una sola fila por invitado: no hay carrera que duplique, gana la última.
        $confirm = fn (int $passes) => $this->postJson(
            route('invitation.rsvp', ['slug' => $invitation->slug, 'token' => $guest->qr_code_token]),
            ['status' => 'confirmed', 'passes_confirmed' => $passes]
        );

        $confirm(2)->assertOk();
        $confirm(9)->assertOk()->assertJson(['passes_confirmed' => 3]);

        // Gana la última respuesta y nunca pasa de los lugares reservados, ni duplica al invitado
        $guest->refresh();
        $this->assertSame('confirmed', $guest->status);
        $this->assertSame(3, (int) $guest->passes_confirmed);
        $this->assertSame(1, Guest::where('invitation_id', $invitation->id)->count());
    }

    // ── Subidas ──────────────────────────────────────────────────────────────

    public function test_the_photo_wall_rejects_large_disallowed_and_disguised_files(): void
    {
        config(['cloudinary.cloud_url' => null]);
        Storage::fake('public');
        $invitation = $this->createInvitation();
        $upload = fn (UploadedFile $file) => $this->postJson(route('invitation.fotomural', $invitation->slug), ['photo' => $file]);

        $files = [
            'foto gigante' => UploadedFile::fake()->image('gigante.jpg')->size(12000),
            'PDF' => UploadedFile::fake()->create('menu.pdf', 200, 'application/pdf'),
            'SVG con script' => UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
            'PHP con extensión de foto' => UploadedFile::fake()->createWithContent('foto.jpg', '<?php echo "hola"; ?>'),
        ];

        foreach ($files as $label => $file) {
            $response = $upload($file);
            $this->assertSame(422, $response->status(), "El fotomural aceptó: {$label}");
        }

        $this->assertSame(0, GuestContribution::count());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_the_editor_upload_rejects_svg_and_files_of_the_wrong_kind(): void
    {
        config(['cloudinary.cloud_url' => null]);
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $cases = [
            'SVG como imagen' => [UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"/>'), 'image'],
            'imagen de más de 10 MB' => [UploadedFile::fake()->image('portada.jpg')->size(11000), 'image'],
            'ejecutable como video' => [UploadedFile::fake()->create('video.exe', 100, 'application/x-msdownload'), 'video'],
            'imagen como audio' => [UploadedFile::fake()->image('cancion.jpg'), 'audio'],
        ];

        foreach ($cases as $label => [$file, $type]) {
            $response = $this->postJson(route('admin.media.upload'), ['file' => $file, 'type' => $type]);
            $this->assertSame(422, $response->status(), "El editor aceptó: {$label}");
        }

        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    // ── Límites ──────────────────────────────────────────────────────────────

    public function test_every_public_endpoint_answers_429_with_a_readable_message(): void
    {
        config(['cloudinary.cloud_url' => null]);
        Storage::fake('public');
        config(['optimizations.rate_limits' => ['login' => 5, 'rsvp' => 1, 'songs' => 1, 'photos' => 1, 'votes' => 1]]);

        $invitation = $this->createInvitation();
        app(InvitationModuleService::class)->syncAllModules($invitation, XvSofiaModuleData::all());
        $guest = $invitation->guests()->create(['name' => 'Familia Condori', 'passes_allocated' => 2]);
        $slug = $invitation->slug;

        $requests = [
            'confirmación' => fn () => $this->postJson(route('invitation.rsvp', ['slug' => $slug, 'token' => $guest->qr_code_token]), ['status' => 'confirmed', 'passes_confirmed' => 1]),
            'canciones' => fn () => $this->postJson(route('invitation.playlist', $slug), ['content_text' => 'Cumbia del recuerdo']),
            'fotos' => fn () => $this->postJson(route('invitation.fotomural', $slug), ['photo' => UploadedFile::fake()->image('foto.jpg')]),
            'votos' => fn () => $this->postJson(route('invitation.poll.vote', ['slug' => $slug, 'pollId' => 'color-vestido']), ['option_index' => 0, 'guest_token' => 'votante-'.uniqid()]),
        ];

        foreach ($requests as $label => $send) {
            $send()->assertSuccessful();

            $send()
                ->assertStatus(429)
                ->assertJson(['success' => false, 'message' => self::THROTTLE_MESSAGE]);
        }
    }

    public function test_the_login_limit_shows_the_same_message_in_the_form(): void
    {
        config(['optimizations.rate_limits.login' => 1]);
        $credentials = ['username' => 'nadie', 'password' => 'incorrecta'];

        $this->post('/login', $credentials);

        $this->post('/login', $credentials)
            ->assertSessionHasErrors(['username' => self::THROTTLE_MESSAGE]);
    }
}
