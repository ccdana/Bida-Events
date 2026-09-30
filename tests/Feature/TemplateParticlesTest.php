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
        'inv-drift--leaf', 'inv-drift--feather', 'inv-drift--streamer', 'inv-drift--star',
        'inv-drift--bokeh', 'inv-drift--twinkle', 'inv-boda-petals', 'inv-boda-butterfly', 'inv-hw-ambient__bat',
        'inv-hw-ambient__ember', 'inv-cumple-ambient__balloon', 'inv-cumple-ambient__confetti',
        // Escenas de fondo propias de las plantillas temáticas (shell/themed-ambient)
        'nb-ambient__cloud', 'br-ambient__cap', 'td-ambient__bubble', 'pt-ambient__note',
        'at-ambient__tape', 'rv-ambient__cut', 'cu-ambient__garland', 'es-ambient__sky', 'bd-ambient__seam', 'bd-ambient__fall',
        'cl-ambient__mist', 'cl-ambient__bubble', 'ka-ambient__mandala', 'jo-ambient__glint', 'ez-ambient__mist', 'ms-ambient__glow', 'rl-ambient__gear', 'fd-ambient__leaf', 'cb-ambient__flash', 'bl-ambient__fall', 'en-ambient__fall',
    ];

    public function test_every_invitation_template_has_at_least_two_kinds_of_particles(): void
    {
        foreach (['xv-premium', 'boda-jardin', 'bautizo-cielo', 'cumple-fiesta', 'graduacion-birrete', 'halloween-calabazas', 'lienzo', 'tarjeta-aventura', 'bautizo-estrellas', 'bautizo-bordado', 'halloween-caldero', 'babyshower-bloques', 'babyshower-encomienda', 'xv-cuento', 'xv-caleidoscopio', 'xv-joyero', 'xv-esencia', 'boda-mesa', 'boda-reloj', 'cumple-feriado', 'cumple-cabina'] as $template) {
            $invitation = $this->createInvitation(['template' => "invitations.templates.{$template}"]);

            $html = $this->withoutVite()->get(route('invitation.show', $invitation->slug))->assertOk()->getContent();
            $found = array_filter(self::KINDS, fn (string $kind) => str_contains($html, $kind.'"') || str_contains($html, $kind.' '));

            $this->assertGreaterThanOrEqual(2, count($found), "La plantilla {$template} tiene menos de dos tipos de partículas: ".implode(', ', $found));
        }
    }
}
