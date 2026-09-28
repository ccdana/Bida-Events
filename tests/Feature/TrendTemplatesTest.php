<?php

namespace Tests\Feature;

use App\EventProfiles\EventProfiles;
use App\Models\EventType;
use App\Models\User;
use App\Services\InvitationModuleService;
use App\Support\InvitationTemplates;
use App\Support\ResellerSubscription;
use App\Support\TrendTemplates;
use Database\Seeders\EventTypeSeeder;
use Database\Seeders\ShowcaseInvitationsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * Colección «tendencias»: una plantilla temática por evento (App\Support\TrendTemplates) y el
 * evento nuevo, baby shower. Cada temática tiene que verse como su historia —su apertura, su
 * portada y sus textos— con los datos del evento, y ofrecer todos los módulos.
 */
class TrendTemplatesTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    /** Lo que distingue a cada temática en el HTML: su apertura, su portada y sus textos. */
    public static function themes(): array
    {
        return [
            'Galería Quince' => ['galeria', 'xv', 'xv-isabella', ['gq-barrier', 'Toca el cordón para entrar', 'gq-label', 'Mecenas de la exposición', 'Programa de la inauguración']],
            'Partitura a dos voces' => ['partitura', 'boda', 'boda-camila-andres', ['pt-baton', 'Toca la batuta para empezar', 'Concierto para dos voces', 'pt-system', 'Andante', 'Quienes tocan con nosotros']],
            'Móvil de cuna' => ['movil', 'bautizo', 'bautizo-emilia', ['mv-mobile', 'Toca el móvil para que gire', 'mv-balance', 'mv-step']],
            'Gira mundial' => ['gira', 'cumple', 'cumple-daniela-30', ['gr-band', 'Fecha única', 'Cancelado', 'Line-up', 'Arma el setlist']],
            'Edición especial' => ['revista', 'graduacion', 'graduacion-mariana', ['rv-band', 'En esta edición', 'rv-barcode', 'Agenda', 'Créditos de esta edición']],
            'Función de medianoche' => ['funcion', 'halloween', 'halloween-noche-diego', ['fn-leader', 'Clasificación A', 'fn-billing', 'Cartelera', 'Taquilla']],
            'Mapa de estrellas' => ['estrellas', 'bautizo', 'bautizo-emilia', ['es-constellation__line', 'Toca la estrella más brillante para unir la constelación', 'es-chart__moon', 'es-point__star', 'Las estrellas que me guían']],
            'Bordado a mano' => ['bordado', 'bautizo', 'bautizo-emilia', ['bd-needle', 'Toca la aguja para bordar su nombre', 'bd-wreath__backstitch', 'bd-row__stitch', 'N &amp; J', 'E &amp; C']],
            'Caldero encantado' => ['caldero', 'halloween', 'halloween-noche-diego', ['cl-cauldron', 'Toca el caldero para revolver la poción', 'cl-jar__liquid', 'Receta secreta', 'cl-brewer__bottle', 'cl-step__vial']],
            'Bloques de juguete' => ['bloques', 'babyshower', 'babyshower-valentina', ['bl-chest', 'Toca el baúl para sacar los bloques', 'bl-cube__face--front', 'bl-floor__time', 'bl-host__block']],
            'Encomienda especial' => ['encomienda', 'babyshower', 'babyshower-valentina', ['en-tape', 'Toca la cinta para abrir la caja', 'en-waybill', 'Seguimiento del envío', 'Entregado', 'en-postage']],
            'Tendedero' => ['tendedero', 'babyshower', 'babyshower-valentina', ['td-basket', 'Toca el canasto para colgar la ropita', 'td-garment--onesie', 'td-string', 'Anfitrionas']],
        ];
    }

    #[DataProvider('themes')]
    public function test_each_theme_renders_its_own_story_with_the_event_content(string $theme, string $event, string $demo, array $markers): void
    {
        $template = TrendTemplates::key($theme, $event);
        $data = ShowcaseInvitationsSeeder::data($demo);
        // La muestra guarda su propia plantilla en la configuración: al sincronizar, manda esta
        $data['modules']['config']['template'] = $template;
        $invitation = $this->createInvitation(['slug' => "prueba-{$theme}", 'template' => $template, 'title' => $data['invitation']['title']]);
        app(InvitationModuleService::class)->syncAllModules($invitation, $data['modules']);
        $guest = $invitation->guests()->create(['name' => 'Familia Quispe', 'passes_allocated' => 3]);

        $html = $this->withoutVite()
            ->get(route('invitation.guest', ['slug' => $invitation->slug, 'token' => $guest->qr_code_token]))
            ->assertOk()
            ->assertSee("inv-page inv-{$theme} inv-themed inv-trend", false)
            ->assertSee('Familia Quispe')
            // La tinta sobre cada color de la paleta viene calculada en la cabecera
            ->assertSee('--tr-ink-primary', false)
            ->getContent();

        foreach ($markers as $marker) {
            $this->assertStringContainsString($marker, $html, "«{$theme}» no muestra «{$marker}»");
        }

        $this->assertSame($event, app(EventProfiles::class)->forTemplate($template)->code());
    }

    public function test_every_theme_offers_every_module_of_an_invitation(): void
    {
        $modules = ['cuenta_regresiva', 'ubicacion', 'itinerario', 'rsvp', 'dress_code', 'galeria', 'destacados', 'regalos', 'video', 'playlist', 'encuestas', 'hashtag', 'fotomural', 'post_evento'];

        foreach (TrendTemplates::themes() as $theme => $meta) {
            $order = InvitationTemplates::get(TrendTemplates::key($theme, $meta['event']))['order'];

            $this->assertEqualsCanonicalizing($modules, $order, "«{$theme}» no ofrece todos los módulos");
        }
    }

    public function test_every_event_gets_a_themed_template_and_baby_shower_is_a_full_event(): void
    {
        $this->seed(EventTypeSeeder::class);

        $themed = collect(TrendTemplates::themes())->pluck('event')->unique()->all();
        $this->assertEqualsCanonicalizing(['xv', 'boda', 'bautizo', 'cumple', 'graduacion', 'halloween', 'babyshower'], $themed);

        $profile = app(EventProfiles::class)->get('babyshower');
        $this->assertSame('Baby shower', $profile->label());
        $this->assertContains('regalos', $profile->modules());
        $this->assertSame(['padrinos', 'chambelanes', 'damitas'], array_keys($profile->featuredGroups()));
        $this->assertSame('babyshower', EventType::where('slug', 'baby-shower')->value('code'));

        // La primera plantilla de baby shower es su clásica: la tienen todos los planes
        $this->assertSame('clasica', InvitationTemplates::get(TrendTemplates::key('tendedero', 'babyshower'))['collection']);
    }

    public function test_the_themed_collection_comes_with_the_upper_reseller_plans(): void
    {
        $galeria = TrendTemplates::key('galeria', 'xv');

        $inicial = array_keys(ResellerSubscription::allowedTemplates(User::factory()->reseller('inicial')->make()));
        $emprendedor = array_keys(ResellerSubscription::allowedTemplates(User::factory()->reseller('emprendedor')->make()));

        $this->assertNotContains($galeria, $inicial);
        $this->assertContains(TrendTemplates::key('tendedero', 'babyshower'), $inicial);
        $this->assertContains($galeria, $emprendedor);
    }

    public function test_the_baby_shower_page_shows_its_demo(): void
    {
        $this->seed(ShowcaseInvitationsSeeder::class);

        $this->withoutVite()->get(route('landing', 'invitaciones-de-baby-shower'))
            ->assertOk()
            ->assertSee('Invitaciones de baby shower para celebrar su llegada')
            ->assertSee(route('invitation.demo', 'babyshower-valentina'), false)
            ->assertSee('Tendedero');

        // Cada página de evento suma la muestra de su temática
        $this->withoutVite()->get(route('landing', 'invitaciones-de-boda'))
            ->assertOk()
            ->assertSee(route('invitation.demo', 'boda-camila-andres-partitura'), false)
            ->assertSee('Partitura a dos voces');
    }

    public function test_without_a_photo_the_magazine_cover_is_typographic(): void
    {
        $data = ShowcaseInvitationsSeeder::data('graduacion-mariana');
        $data['modules']['bienvenida']['imagen_hero'] = null;
        $data['modules']['config']['template'] = TrendTemplates::key('revista', 'graduacion');
        $invitation = $this->createInvitation(['template' => TrendTemplates::key('revista', 'graduacion')]);
        app(InvitationModuleService::class)->syncAllModules($invitation, $data['modules']);

        $this->withoutVite()->get(route('invitation.show', $invitation->slug))
            ->assertOk()
            ->assertSee('inv-page inv-revista', false)
            ->assertSee('rv-cover__photo is-typographic', false)
            ->assertSee('rv-cover__type', false);
    }
}
