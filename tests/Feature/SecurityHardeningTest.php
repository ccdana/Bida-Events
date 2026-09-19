<?php

namespace Tests\Feature;

use App\Services\MediaUploadService;
use Database\Seeders\ShowcaseInvitationsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    public function test_invitations_are_not_indexable(): void
    {
        $invitation = $this->createInvitation();
        $guest = $invitation->guests()->create(['name' => 'Familia Pérez', 'passes_allocated' => 2]);

        $this->withoutVite()
            ->get(route('invitation.show', $invitation->slug))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);

        $this->withoutVite()
            ->get(route('invitation.guest', [$invitation->slug, $guest->qr_code_token]))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);

        $robots = file_get_contents(public_path('robots.txt'));
        $this->assertStringContainsString('Disallow: /p/', $robots);
        $this->assertStringContainsString('Disallow: /muestra/', $robots);
    }

    public function test_the_personal_invitation_is_never_cached_as_public(): void
    {
        config(['optimizations.http.public_invitation_cache' => true]);

        $invitation = $this->createInvitation();
        $guest = $invitation->guests()->create(['name' => 'Familia Pérez', 'passes_allocated' => 2]);

        // El enlace general sí puede guardarse en una caché compartida
        $this->withoutVite()
            ->get(route('invitation.show', $invitation->slug))
            ->assertHeader('Cache-Control', 'max-age=300, must-revalidate, public');

        // El de un invitado lleva su nombre y su pase: solo su propio navegador puede guardarlo
        $response = $this->withoutVite()
            ->get(route('invitation.guest', [$invitation->slug, $guest->qr_code_token]));

        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
        $this->assertStringNotContainsString('public', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('Cookie', $response->headers->get('Vary'));
    }

    public function test_every_page_carries_the_security_headers(): void
    {
        $this->seed(ShowcaseInvitationsSeeder::class);

        $response = $this->withoutVite()->get(route('home'))->assertOk();

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $this->assertStringContainsString('camera=(self)', $response->headers->get('Permissions-Policy'));
        // El micrófono se permite solo al propio sitio (el diente de león de la tarjeta se sopla)
        $this->assertStringContainsString('microphone=(self)', $response->headers->get('Permissions-Policy'));
        $this->assertStringContainsString('geolocation=()', $response->headers->get('Permissions-Policy'));

        // Empieza en modo solo reporte para medir antes de bloquear
        $this->assertNotNull($response->headers->get('Content-Security-Policy-Report-Only'));
        $this->assertNull($response->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString('res.cloudinary.com', $response->headers->get('Content-Security-Policy-Report-Only'));
    }

    public function test_the_content_policy_can_be_enforced_from_the_environment(): void
    {
        config(['security.csp.enforce' => true]);

        $this->withoutVite()
            ->get(route('home'))
            ->assertOk()
            ->assertHeader('Content-Security-Policy');
    }

    public function test_uploads_reject_svg_files(): void
    {
        $service = app(MediaUploadService::class);
        $svg = UploadedFile::fake()->createWithContent(
            'logo.svg',
            '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'
        );

        $this->expectException(ValidationException::class);

        $service->validateFile($svg, 'image');
    }
}
