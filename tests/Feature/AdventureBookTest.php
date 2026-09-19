<?php

namespace Tests\Feature;

use App\EventProfiles\EventProfiles;
use App\Models\EventType;
use App\Models\Invitation;
use App\Models\User;
use App\Modules\ModuleRegistry;
use App\Services\InvitationModuleService;
use App\Support\InvitationTemplates;
use App\Support\NotebookPaginator;
use Database\Seeders\ShowcaseInvitationsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * Tarjeta «Libro de aventuras» (Día del Amor): un cuaderno que se hojea. Todo su contenido tiene que
 * estar en el HTML (sin JavaScript se leen las hojas apiladas) y el texto largo sigue en otras hojas
 * sin perderse.
 */
class AdventureBookTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    private const BOOK_MODULES = ['historia', 'recuerdos', 'collage', 'marcos', 'memoria', 'aventuras'];

    public function test_the_book_modules_save_and_read_back_the_same(): void
    {
        $registry = app(ModuleRegistry::class);
        $modules = self::sample();
        $book = $this->createBook();

        $this->assertDatabaseCount('card_entries', 12);
        $loaded = $registry->load($book->fresh());

        foreach (self::BOOK_MODULES as $code) {
            $this->assertEquals($modules[$code], $loaded[$code], "{$code} no vuelve igual");
        }

        // Reordenar los capítulos reutiliza las filas: los ids no cambian
        $ids = $book->cardEntries()->where('section', 'historia')->pluck('id')->all();
        $modules['historia']['capitulos'] = array_reverse($modules['historia']['capitulos']);
        $registry->save($book, $modules);

        $this->assertSame($ids, $book->cardEntries()->where('section', 'historia')->pluck('id')->all());
        $this->assertSame('El viaje que no olvidamos', $book->cardEntries()->where('section', 'historia')->first()->title);
    }

    public function test_the_book_shows_every_page_in_the_html(): void
    {
        $this->createBook(['slug' => 'libro-ana']);

        $html = $this->withoutVite()
            ->get(route('invitation.show', 'libro-ana'))
            ->assertOk()
            ->assertSee('inv-page inv-aventura', false)
            ->assertSee('data-notebook', false)
            ->assertSee('id="juntos-desde"', false)
            ->assertSee('id="dedicatoria"', false)
            ->assertSee('id="historia"', false)
            ->assertSee('id="nb-cap-1"', false)
            ->assertSee('id="recuerdos"', false)
            ->assertSee('id="collage"', false)
            ->assertSee('id="marcos"', false)
            ->assertSee('id="memoria"', false)
            ->assertSee('x-data="memoryGame(6)"', false)
            ->assertSee('Así de bien nos complementamos')
            ->assertSee('x-data="cardReply(', false)
            ->assertSee('Aventuras por vivir')
            ->assertDontSee('id="rsvp"', false)
            ->getContent();

        // Las hojas se pasan arrastrándolas: no hay botones de anterior/siguiente ni para abrir la tapa
        $this->assertStringNotContainsString('data-nb-prev', $html);
        $this->assertStringNotContainsString('data-nb-next', $html);
        $this->assertStringNotContainsString('data-nb-open', $html);
        $this->assertStringContainsString('Desliza la hoja con el dedo', $html);

        // La tercera hoja del collage (la que eran tiras de fotomatón) se acomoda sola a las fotos
        $this->assertStringContainsString('nb-collage--libre', $html);
        $this->assertStringContainsString('data-nb-free-collage', $html);
        $this->assertStringNotContainsString('nb-collage--fotomaton', $html);

        // Tapa + hojas + contratapa: siempre par, para que la contratapa cierre sola
        $this->assertSame(0, preg_match_all('/data-nb-page(?!s)/', $html) % 2);
        // Cada hoja numerada, sin marcadores sueltos
        $this->assertStringNotContainsString('<!--nb-folio-->', $html);
    }

    public function test_the_anniversary_page_marks_the_day_in_its_month(): void
    {
        $this->createBook(['slug' => 'libro-ana']);

        $html = $this->withoutVite()->get(route('invitation.show', 'libro-ana'))->assertOk()->getContent();

        // 14 de febrero de 2019 cayó jueves: la hoja de febrero empieza en viernes (4 casillas vacías)
        $this->assertStringContainsString('Febrero', $html);
        $this->assertMatchesRegularExpression('/<td class="is-marked" aria-current="date">.*?<span>14<\/span>/s', $html);
        $this->assertSame(1, substr_count($html, 'aria-current="date"'));
    }

    public function test_each_game_photo_appears_twice(): void
    {
        $this->createBook(['slug' => 'libro-ana']);

        $html = $this->withoutVite()->get(route('invitation.show', 'libro-ana'))->assertOk()->getContent();

        preg_match_all('/class="nb-card" data-pair="(\d+)"/', $html, $pairs);
        $this->assertCount(12, $pairs[1]);
        $this->assertSame(array_fill(0, 6, 2), array_values(array_count_values($pairs[1])));
    }

    public function test_a_long_chapter_takes_several_pages_without_losing_text(): void
    {
        $modules = self::sample();
        $paragraph = 'Caminamos por la plaza sin rumbo, contando historias de la infancia y planes imposibles. ';
        $text = trim(str_repeat($paragraph, 12))."\n\n".trim(str_repeat($paragraph, 10));
        $modules['historia']['capitulos'] = [['titulo' => 'Un capítulo largo', 'texto' => $text]];
        $this->createBook(['slug' => 'libro-largo'], $modules);

        $html = $this->withoutVite()->get(route('invitation.show', 'libro-largo'))->assertOk()->getContent();

        $this->assertGreaterThanOrEqual(2, substr_count($html, 'Capítulo 1 · continúa'));
        $this->assertStringContainsString('sigue en la próxima hoja', $html);

        $pages = NotebookPaginator::pages($text, 720, 900);
        $this->assertGreaterThan(2, count($pages));
        $this->assertSame(self::words($text), self::words(implode(' ', $pages)));

        foreach ($pages as $index => $page) {
            $this->assertLessThanOrEqual($index === 0 ? 720 : 900, mb_strlen($page));
        }
    }

    public function test_the_paginator_cuts_between_sentences_and_keeps_paragraphs(): void
    {
        $this->assertSame([], NotebookPaginator::pages('   ', 100, 100));
        $this->assertSame(["Hola.\n\nChau."], NotebookPaginator::pages("Hola.\n\n\n  Chau.", 100, 100));

        $pages = NotebookPaginator::pages(str_repeat('Una frase corta aquí. ', 20), 200, 200);

        foreach (array_slice($pages, 0, -1) as $page) {
            $this->assertStringEndsWith('.', $page);
        }
    }

    public function test_the_adventures_page_lists_what_is_left_and_disappears_when_empty(): void
    {
        $this->createBook(['slug' => 'libro-ana']);

        $html = $this->withoutVite()->get(route('invitation.show', 'libro-ana'))->assertOk()->getContent();

        $this->assertStringContainsString('id="aventuras"', $html);
        $this->assertStringContainsString('Aprender a bailar salsa', $html);
        $this->assertSame(6, substr_count($html, 'class="nb-todo__text"'));

        // Sin aventuras no hay hoja, y el libro sigue cerrando con un número par de hojas
        $modules = self::sample();
        $modules['aventuras']['lista'] = [];
        $this->createBook(['slug' => 'libro-sin-aventuras'], $modules);

        $html = $this->withoutVite()->get(route('invitation.show', 'libro-sin-aventuras'))->assertOk()->getContent();

        $this->assertStringNotContainsString('id="aventuras"', $html);
        $this->assertStringNotContainsString('nb-todo__text', $html);
        $this->assertSame(0, preg_match_all('/data-nb-page(?!s)/', $html) % 2);
    }

    public function test_the_adventures_list_has_a_limit(): void
    {
        $rules = app(ModuleRegistry::class)->rules('modulos');
        $items = fn (int $count, string $text = 'Viajar juntos') => ['modulos' => ['aventuras' => ['lista' => array_fill(0, $count, ['titulo' => $text])]]];

        $this->assertTrue(Validator::make($items(15), $rules)->passes());
        $this->assertTrue(Validator::make($items(16), $rules)->fails());
        $this->assertTrue(Validator::make($items(1, str_repeat('a', 121)), $rules)->fails());
    }

    public function test_the_memory_game_needs_between_three_and_eight_photos(): void
    {
        $rules = app(ModuleRegistry::class)->rules('modulos');
        $photos = fn (int $count) => ['modulos' => ['memoria' => ['fotos' => array_fill(0, $count, 'https://res.cloudinary.com/x/image/upload/a.jpg')]]];

        $this->assertTrue(Validator::make($photos(0), $rules)->passes());
        $this->assertTrue(Validator::make($photos(2), $rules)->fails());
        $this->assertTrue(Validator::make($photos(3), $rules)->passes());
        $this->assertTrue(Validator::make($photos(8), $rules)->passes());
        $this->assertTrue(Validator::make($photos(9), $rules)->fails());
    }

    public function test_the_sample_passes_the_rules_and_the_love_letter_keeps_its_modules(): void
    {
        $validator = Validator::make(['modulos' => self::sample()], app(ModuleRegistry::class)->rules('modulos'));
        $this->assertTrue($validator->passes(), json_encode($validator->errors()->all(), JSON_UNESCAPED_UNICODE));

        // La Carta de amor no ofrece las hojas del libro (el editor solo muestra las pestañas del perfil)
        $love = app(EventProfiles::class)->forTemplate(InvitationTemplates::TARJETA_AMOR);
        $this->assertEmpty(array_intersect(self::BOOK_MODULES, $love->modules()));

        $book = app(EventProfiles::class)->forTemplate(InvitationTemplates::TARJETA_AVENTURA);
        $this->assertSame('amor', $book->season());
        $this->assertEmpty(array_diff(self::BOOK_MODULES, $book->modules()));
    }

    public function test_the_book_can_be_opened_as_a_demo(): void
    {
        $this->seed(ShowcaseInvitationsSeeder::class);

        $this->withoutVite()
            ->get(route('invitation.demo', 'tarjeta-libro-aventuras'))
            ->assertOk()
            ->assertSee('inv-page inv-aventura', false)
            ->assertSee('Nuestro libro de aventuras');
    }

    public function test_the_editor_offers_the_book_pages(): void
    {
        $book = $this->createBook();
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->withoutVite()
            ->get(route('admin.invitations.edit', $book))
            ->assertOk()
            ->assertSee("id: 'libro'", false)
            ->assertSee("activeTab === 'historia'", false)
            ->assertSee("activeTab === 'recuerdos'", false)
            ->assertSee("activeTab === 'collage'", false)
            ->assertSee("activeTab === 'marcos'", false)
            ->assertSee("activeTab === 'memoria'", false)
            ->assertSee("activeTab === 'aventuras'", false)
            ->assertSee("addBookEntry('aventuras', 'lista', 15)", false)
            ->assertSee("uploadBookPhotos('memoria', \$event, 8)", false)
            ->assertSee("uploadBookEntryPhoto('historia', 'capitulos', i, \$event)", false);
    }

    private static function sample(): array
    {
        return ShowcaseInvitationsSeeder::data('tarjeta-libro-aventuras')['modules'];
    }

    /** @return list<string> */
    private static function words(string $text): array
    {
        return preg_split('/\s+/u', trim($text));
    }

    private function createBook(array $attributes = [], ?array $modules = null): Invitation
    {
        $eventType = EventType::firstOrCreate(
            ['slug' => 'libro-de-aventuras'],
            ['name' => 'Libro de aventuras (Día del Amor)', 'code' => 'aventura', 'kind' => 'card', 'season' => 'amor']
        );

        $book = $this->createInvitation(array_merge([
            'event_type_id' => $eventType->id,
            'slug' => 'libro-'.Str::lower(Str::random(6)),
            'template' => InvitationTemplates::TARJETA_AVENTURA,
            'title' => 'Libro de Luis para Ana',
        ], $attributes));

        app(InvitationModuleService::class)->syncAllModules($book, $modules ?? self::sample());

        return $book;
    }
}
