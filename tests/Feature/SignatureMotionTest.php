<?php

namespace Tests\Feature;

use App\Services\InvitationModuleService;
use App\Support\InvitationTemplates;
use App\Support\TrendTemplates;
use Database\Seeders\ShowcaseInvitationsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * El movimiento de autor de Atelier, Esencia XV, Cuento desplegable, Joyero musical, El cambio de
 * zapatos y Mesa de honor
 * (css/invitation/tendencias/_motion.css): la escena del fondo espera a la apertura, las piezas
 * responden al tocarlas con el gesto de su tema, el programa sabe cuántos momentos tiene para que la
 * cinta o la cadena lleguen a cada uno a tiempo, y la página avisa cuándo suena la música.
 */
class SignatureMotionTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    public static function templates(): array
    {
        return [
            'Atelier' => [InvitationTemplates::XV_PREMIUM, 'xv-isabella', 'themes/atelier.css', [
                'at-ambient tr-scene', 'at-ambient__scrap--left at-ambient__scrap--satin', 'at-ambient__scrap--right at-ambient__scrap--weave',
                'data-poke="swing"', 'at-board__swatch--left" data-parallax="-0.06"', 'class="at-label" data-parallax',
                '<ol class="at-show" style="--looks: 8">',
            ]],
            'Esencia XV' => [TrendTemplates::key('esencia', 'xv'), 'xv-isabella', 'tendencias/esencia.css', [
                'ez-ambient tr-scene', 'ez-ambient__spray--left', 'ez-ambient__trail--right',
                'class="ez-campaign__bottle" data-poke="spritz" data-parallax="0.07"', 'data-poke-puff',
            ]],
            'Joyero musical' => [TrendTemplates::key('joyero', 'xv'), 'xv-isabella', 'tendencias/joyero.css', [
                'jo-ambient tr-scene', 'jo-ambient__pearl--left', 'jo-ambient__rings--right', 'jo-sparkle--downbeat',
                'class="jo-locket" data-poke="swing"', 'data-poke="spin"', 'style="--charms: 8"',
            ]],
            'Cuento desplegable' => [TrendTemplates::key('cuento', 'xv'), 'xv-isabella', 'tendencias/cuento.css', [
                'cu-ambient tr-scene', 'cu-ambient__beam', 'cu-ambient__mote', 'cu-ambient__layer--hills',
                'cu-ambient__side--left', 'cu-ambient__side--right', 'cu-plate__layer--back" data-parallax',
                'cu-character__medal" data-poke="pop"', 'cu-page__icon" data-poke="pop"',
            ]],
            'El cambio de zapatos' => [TrendTemplates::key('zapatos', 'xv'), 'xv-isabella', 'tendencias/zapatos.css', [
                'zp-ambient tr-scene', 'zp-ambient__trail--right', 'class="zp-shelf__box" data-step data-poke="pop"',
                'zp-open__shoe zp-open__shoe--left" data-parallax="0.05"', 'class="zp-steps" style="--steps: 8"',
            ]],
            'Mesa de honor' => [TrendTemplates::key('mesa', 'boda'), 'boda-camila-andres', 'tendencias/mesa.css', [
                'ms-ambient tr-scene', 'ms-ambient__candle--left', 'ms-ambient__flame', 'ms-ambient__petal--right',
                'class="ms-tent" data-poke="tip"', 'class="ms-seat" data-step data-poke="tip"',
            ]],
        ];
    }

    #[DataProvider('templates')]
    public function test_each_template_brings_its_background_scene_gestures_and_timing(string $template, string $demo, string $sheet, array $markers): void
    {
        $data = ShowcaseInvitationsSeeder::data($demo);
        $data['modules']['config']['template'] = $template;
        $invitation = $this->createInvitation(['template' => $template]);
        app(InvitationModuleService::class)->syncAllModules($invitation, $data['modules']);

        $html = $this->withoutVite()->get(route('invitation.show', $invitation->slug))->assertOk()->getContent();

        foreach ($markers as $marker) {
            $this->assertStringContainsString($marker, $html, "«{$template}» no trae «{$marker}»");
        }

        // La portada tiene su propia coreografía: ya no usa la entrada genérica
        $hero = str($html)->after('id="inicio"')->before('</header>');
        $this->assertStringNotContainsString('inv-fade-up', (string) $hero, "La portada de «{$template}» sigue con la entrada genérica");

        // La página avisa cuándo suena la música (html.inv-music-on)
        $this->assertStringContainsString("classList.add('inv-music-on')", $html);
        $this->assertStringContainsString("classList.remove('inv-music-on')", $html);

        // Su hoja comparte los gestos y las reglas del movimiento
        $css = file_get_contents(resource_path("css/invitation/{$sheet}"));
        $this->assertMatchesRegularExpression("~@import '(\.\./tendencias/|\./)_motion\.css';~", $css);
    }

    /**
     * Mientras una sección espera su aparición, su transformación no puede hacerla más ancha que la
     * pantalla: el celular achicaría toda la página. Nada de agrandarla ni de girarla en el plano.
     */
    public function test_section_entrances_never_make_a_section_wider_than_the_screen(): void
    {
        foreach (['themes/atelier.css', 'tendencias/esencia.css', 'tendencias/joyero.css', 'tendencias/cuento.css', 'tendencias/zapatos.css', 'tendencias/mesa.css'] as $sheet) {
            preg_match_all('/--themed-from:\s*([^;]+);/', file_get_contents(resource_path("css/invitation/{$sheet}")), $matches);

            $this->assertNotEmpty($matches[1], "{$sheet} no define su entrada de sección");

            foreach ($matches[1] as $from) {
                $this->assertDoesNotMatchRegularExpression('/(?<![XYZ])rotate\(/', $from, "{$sheet}: «{$from}» gira la sección en el plano");

                preg_match_all('/scale\(([\d.]+)\)/', $from, $scales);
                foreach ($scales[1] as $scale) {
                    $this->assertLessThanOrEqual(1, (float) $scale, "{$sheet}: «{$from}» agranda la sección");
                }
            }
        }
    }

    public function test_the_shared_motion_rules_wait_for_the_opening_and_respect_reduced_motion(): void
    {
        $css = file_get_contents(resource_path('css/invitation/tendencias/_motion.css'));

        $this->assertStringContainsString('html.inv-cover-waiting .tr-scene *', $css);
        $this->assertMatchesRegularExpression('/@media \(prefers-reduced-motion: reduce\)\s*\{[^@]*\.tr-scene \*[^}]*animation: none !important;/s', $css);
    }
}
