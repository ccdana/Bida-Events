<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * Cada plantilla de invitación tiene al menos dos tipos de partículas de fondo en movimiento
 * (las propias de su ambiente y las de partials/drift): globos, hojas, plumas, destellos…
 */
class TemplateParticlesTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    /** Marcas de cada tipo de partícula en el HTML. */
    private const KINDS = [
        'inv-particles', 'inv-drift--leaf', 'inv-drift--feather', 'inv-drift--streamer', 'inv-drift--star',
        'inv-drift--bokeh', 'inv-drift--twinkle', 'inv-boda-petals', 'inv-boda-butterfly', 'inv-bautizo-ambient__bubble',
        'inv-bautizo-ambient__dove', 'inv-grad-ambient__cap', 'inv-grad-ambient__spark', 'inv-hw-ambient__bat',
        'inv-hw-ambient__ember', 'inv-cumple-ambient__balloon', 'inv-cumple-ambient__confetti',
    ];

    public function test_every_invitation_template_has_at_least_two_kinds_of_particles(): void
    {
        foreach (['xv-premium', 'boda-jardin', 'bautizo-cielo', 'cumple-fiesta', 'graduacion-birrete', 'halloween-calabazas', 'lienzo', 'tarjeta-aventura'] as $template) {
            $invitation = $this->createInvitation(['template' => "invitations.templates.{$template}"]);

            $html = $this->withoutVite()->get(route('invitation.show', $invitation->slug))->assertOk()->getContent();
            $found = array_filter(self::KINDS, fn (string $kind) => str_contains($html, $kind.'"') || str_contains($html, $kind.' '));

            $this->assertGreaterThanOrEqual(2, count($found), "La plantilla {$template} tiene menos de dos tipos de partículas: ".implode(', ', $found));
        }
    }
}
