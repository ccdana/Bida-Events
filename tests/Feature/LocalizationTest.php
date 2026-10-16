<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Localization;
use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * El sitio público en español (en la raíz) y en inglés (bajo /en): cada idioma con sus propias
 * direcciones, enlazados entre sí, y con todos sus enlaces internos en el mismo idioma.
 */
class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    /** Páginas públicas en español y su equivalente en inglés. */
    public static function pages(): array
    {
        return [
            'portada' => ['/', '/en'],
            'precios' => ['/precios', '/en/pricing'],
            'únete' => ['/unete', '/en/join'],
            'guía' => ['/guia-invitaciones-digitales', '/en/digital-invitations-guide'],
            'boda' => ['/invitaciones-de-boda', '/en/wedding-invitations'],
            'privacidad' => ['/legal/privacidad', '/en/legal/privacy'],
            'términos' => ['/legal/terminos', '/en/legal/terms'],
            'cookies' => ['/legal/cookies', '/en/legal/cookies'],
            'ingreso' => ['/login', '/en/login'],
            'registro' => ['/registro', '/en/sign-up'],
            'recuperar' => ['/contrasena/olvide', '/en/password/forgot'],
        ];
    }

    /** @dataProvider pages */
    public function test_every_public_page_exists_in_both_languages(string $spanish, string $english): void
    {
        $es = $this->withoutVite()->get($spanish)->assertOk();
        $en = $this->withoutVite()->get($english)->assertOk();

        $this->assertStringContainsString('<html lang="es"', $es->getContent());
        $this->assertStringContainsString('<html lang="en"', $en->getContent());
        $en->assertHeader('Content-Language', 'en');
        $es->assertHeader('Content-Language', 'es');
    }

    /** @dataProvider pages */
    public function test_each_version_points_to_the_other_with_hreflang(string $spanish, string $english): void
    {
        // El ingreso y el registro tampoco indexan, pero se enlazan igual para el selector
        $es = $this->withoutVite()->get($spanish)->getContent();
        $en = $this->withoutVite()->get($english)->getContent();

        foreach ([$es, $en] as $html) {
            $this->assertStringContainsString('hreflang="es" href="'.url($spanish).'"', $html);
            $this->assertStringContainsString('hreflang="en" href="'.url($english).'"', $html);
            $this->assertStringContainsString('hreflang="x-default" href="'.url($spanish).'"', $html);
        }

        // El selector lleva a la misma página del otro idioma
        $this->assertStringContainsString('href="'.url($english).'" hreflang="en"', $es);
        $this->assertStringContainsString('href="'.url($spanish).'" hreflang="es"', $en);
    }

    public function test_a_landing_page_only_answers_to_the_address_of_its_language(): void
    {
        $this->withoutVite()->get('/en/wedding-invitations')->assertOk();
        $this->withoutVite()->get('/en/invitaciones-de-boda')->assertNotFound();
        $this->withoutVite()->get('/wedding-invitations')->assertNotFound();
    }

    public function test_an_english_page_links_to_english_pages(): void
    {
        $html = $this->withoutVite()->get('/en')->getContent();

        // Ninguna de estas páginas se enlaza en español desde la versión en inglés
        foreach (['/precios', '/unete', '/guia-invitaciones-digitales', '/invitaciones-de-boda', '/legal/privacidad', '/registro'] as $spanish) {
            $this->assertStringNotContainsString('href="'.url($spanish).'"', $html, "La portada en inglés enlaza a {$spanish}");
        }

        $this->assertStringContainsString('href="'.url('/en/pricing').'"', $html);
        $this->assertStringContainsString('href="'.url('/en/wedding-invitations').'"', $html);
    }

    public function test_the_language_switch_keeps_the_query_string(): void
    {
        $html = $this->withoutVite()->get('/precios?utm_source=facebook')->getContent();

        $this->assertStringContainsString('href="'.url('/en/pricing').'?utm_source=facebook"', $html);
    }

    public function test_pages_without_a_translation_have_no_language_switch_or_hreflang(): void
    {
        $this->assertSame([], Localization::alternates());

        $html = $this->withoutVite()->get('/dashboard')->getContent();
        $this->assertStringNotContainsString('hreflang', $html);
    }

    public function test_the_sitemap_lists_both_languages_with_alternates(): void
    {
        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();

        $this->assertStringContainsString('<loc>'.url('/precios').'</loc>', $xml);
        $this->assertStringContainsString('<loc>'.url('/en/pricing').'</loc>', $xml);
        $this->assertStringContainsString('<xhtml:link rel="alternate" hreflang="en" href="'.url('/en/pricing').'"/>', $xml);
        $this->assertStringContainsString('<xhtml:link rel="alternate" hreflang="x-default" href="'.url('/precios').'"/>', $xml);
    }

    public function test_lroute_builds_the_url_of_the_active_language(): void
    {
        $this->assertSame(url('/precios'), lroute('pricing'));
        $this->assertSame(url('/en/pricing'), lroute('pricing', locale: 'en'));
        $this->assertSame(url('/en/wedding-invitations'), lroute('landing', ['landing' => 'invitaciones-de-boda'], locale: 'en'));
        $this->assertSame(url('/en/legal/privacy'), lroute('legal', 'privacidad', locale: 'en'));
        // Una ruta que no existe por idioma queda como está
        $this->assertSame(url('/p/mi-boda'), lroute('invitation.show', 'mi-boda', locale: 'en'));
    }

    public function test_accounts_remember_the_language_they_were_opened_in(): void
    {
        $this->post('/en/sign-up', [
            'name' => 'Maria Perez',
            'email' => 'maria@example.com',
            'password' => 'una-clave-larga-1',
        ])->assertRedirect();

        $this->assertSame('en', User::where('email', 'maria@example.com')->value('locale'));
    }

    public function test_dates_and_money_follow_the_language(): void
    {
        $this->assertSame('1,200', Money::number(1200) === '1.200' ? '1,200' : '?');

        Localization::apply('en');
        $this->assertSame('1,200', Money::number(1200));
        $this->assertSame('29.50', Money::number(29.5));
        $this->assertSame('F j, Y', Localization::dateFormat('j \d\e F \d\e Y'));
        $this->assertSame('October 15, 2026', Carbon::parse('2026-10-15')->translatedFormat(Localization::dateFormat('j \d\e F \d\e Y')));

        Localization::apply('es');
        $this->assertSame('1.200', Money::number(1200));
        $this->assertSame('15 de octubre de 2026', Carbon::parse('2026-10-15')->translatedFormat(Localization::dateFormat('j \d\e F \d\e Y')));
    }

    public function test_every_text_in_use_has_an_english_translation(): void
    {
        $this->artisan('bida:traducciones')->assertSuccessful();
    }

    public function test_translations_keep_the_placeholders_of_their_source(): void
    {
        $tokens = fn (string $text): array => collect(preg_match_all('/:[A-Za-z_][A-Za-z0-9_]*|\{[a-z_]+\}/', $text, $found) ? $found[0] : [])->sort()->values()->all();
        $broken = [];

        foreach (json_decode(file_get_contents(lang_path('en.json')), true) as $source => $english) {
            if ($tokens($source) !== $tokens($english)) {
                $broken[] = $source;
            }
        }

        $this->assertSame([], $broken);
    }

    public function test_the_english_site_shows_no_spanish_copy(): void
    {
        $spanish = '/\b(invitaci[oó]n|invitaciones|precios|iniciar sesi[oó]n|contraseña|cumpleaños|tu evento|crea tu)\b/iu';

        foreach (['/en', '/en/pricing', '/en/join', '/en/login', '/en/sign-up', '/en/wedding-invitations'] as $path) {
            $html = $this->get($path)->assertOk()->getContent();
            $text = strip_tags(preg_replace('#<(script|style)\b.*?</\1>#is', '', $html));

            preg_match($spanish, $text, $found, PREG_OFFSET_CAPTURE);

            $this->assertSame([], $found, "Spanish copy on {$path}: ".trim(substr($text, max(0, ($found[0][1] ?? 0) - 60), 140)));
        }
    }

    public function test_an_account_opened_in_english_gets_its_email_in_english(): void
    {
        $user = User::factory()->unverified()->create(['locale' => 'en']);

        $user->sendEmailVerificationNotification();

        $message = collect(app('mail.manager')->mailer()->getSymfonyTransport()->messages())->last();

        $this->assertNotNull($message);
        $this->assertStringNotContainsStringIgnoringCase('Confirma', $message->getOriginalMessage()->getSubject());
    }
}
