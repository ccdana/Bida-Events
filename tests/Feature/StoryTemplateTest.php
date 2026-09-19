<?php

namespace Tests\Feature;

use App\Models\EventType;
use App\Models\Invitation;
use App\Modules\Card\StoryActsModule;
use App\Modules\ModuleRegistry;
use App\Services\InvitationModuleService;
use App\Support\InvitationDefaults;
use App\Support\InvitationTemplates;
use Database\Seeders\ShowcaseInvitationsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * «The Story We Write Together»: el módulo «historia» guarda y lee lo mismo, y cada acto conserva
 * su sentido aunque la pareja no haya escrito nada (textos *_fallback del catálogo).
 */
class StoryTemplateTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    private function sample(): array
    {
        return ShowcaseInvitationsSeeder::data('historia-ana-luis')['modules'];
    }

    private function createStory(array $modules): Invitation
    {
        $type = EventType::firstOrCreate(
            ['slug' => 'nuestra-historia'],
            ['name' => 'Nuestra historia', 'code' => 'historia', 'kind' => 'card', 'season' => 'amor'],
        );
        $invitation = $this->createInvitation([
            'template' => InvitationTemplates::WE_STORY_TOGETHER,
            'event_type_id' => $type->id,
        ]);
        app(InvitationModuleService::class)->syncAllModules($invitation, $modules);

        return $invitation;
    }

    public function test_the_sample_passes_the_module_rules_and_a_made_up_quote_does_not(): void
    {
        $registry = app(ModuleRegistry::class);
        $modules = $this->sample();

        $validator = Validator::make(['modulos' => $modules], $registry->rules('modulos'));
        $this->assertTrue($validator->passes(), json_encode($validator->errors()->all(), JSON_UNESCAPED_UNICODE));

        $modules['relato']['cita'] = 'bukowski';
        $this->assertTrue(Validator::make(['modulos' => $modules], $registry->rules('modulos'))->fails());

        $modules = $this->sample();
        $modules['relato']['anecdota_foto'] = 'blob:http://localhost/123';
        $this->assertTrue(Validator::make(['modulos' => $modules], $registry->rules('modulos'))->fails());
    }

    public function test_the_story_saves_to_its_tables_and_reads_back_the_same(): void
    {
        $registry = app(ModuleRegistry::class);
        $story = $this->sample()['relato'];
        $invitation = $this->createInvitation(['template' => InvitationTemplates::WE_STORY_TOGETHER]);

        $registry->save($invitation, ['relato' => $story]);

        $this->assertDatabaseHas('card_stories', ['invitation_id' => $invitation->id, 'quote_key' => 'cortazar', 'anecdote_title' => 'La noche del paraguas']);
        $this->assertDatabaseCount('card_story_moments', 3);

        $loaded = $registry->load($invitation->fresh())['relato'];

        foreach ($story as $key => $value) {
            $this->assertEquals($value, $loaded[$key] ?? null, "relato.{$key} no vuelve igual");
        }

        // Un momento vacío no se guarda; guardar vacío borra todo sin dejar filas sueltas
        $registry->save($invitation, ['relato' => ['momentos' => [['cuando' => 'Hoy'], ['titulo' => 'Uno solo']]]]);
        $this->assertDatabaseCount('card_story_moments', 1);
        $this->assertDatabaseMissing('card_stories', ['invitation_id' => $invitation->id]);

        $registry->save($invitation, ['relato' => []]);
        $this->assertDatabaseCount('card_story_moments', 0);
    }

    public function test_the_four_acts_render_with_the_couple_story(): void
    {
        $invitation = $this->createStory($this->sample());

        $this->withoutVite()->get(route('invitation.show', $invitation->slug))
            ->assertOk()
            ->assertSee('data-card="historia"', false)
            ->assertSee('Acto IV')
            ->assertSee('La noche del paraguas')
            ->assertSee('El primer café que duró cuatro horas')
            ->assertSee(StoryActsModule::QUOTES['cortazar']['text'])
            ->assertSee('Nos conocimos el')
            ->assertSee('Seguir escribiendo esta historia, una noche a la vez.')
            ->assertSee('id="dedicatoria"', false)
            ->assertSee('id="galeria"', false)
            ->assertSee('id="respuesta"', false)
            ->assertDontSee(InvitationTemplates::copy(InvitationTemplates::WE_STORY_TOGETHER)['act3_anecdote_fallback']);
    }

    public function test_every_act_keeps_its_meaning_when_the_couple_wrote_nothing(): void
    {
        $modules = InvitationDefaults::emptyModules();
        $modules['config']['template'] = InvitationTemplates::WE_STORY_TOGETHER;
        $modules['config']['modulos'] = array_fill_keys(array_keys($modules['config']['modulos']), true);
        $invitation = $this->createStory($modules);
        $copy = InvitationTemplates::copy(InvitationTemplates::WE_STORY_TOGETHER);

        $response = $this->withoutVite()->get(route('invitation.show', $invitation->slug))->assertOk();

        foreach (['act1_intro_fallback', 'act1_met_fallback', 'act1_first_fallback', 'act2_moments_fallback', 'act3_anecdote_fallback',
            'act3_dedication_fallback', 'act4_reflection_fallback', 'act4_promise_fallback'] as $key) {
            $response->assertSee($copy[$key]);
        }

        // Sin cita elegida va la de siempre
        $response->assertSee(StoryActsModule::QUOTES[StoryActsModule::DEFAULT_QUOTE]['text']);
    }

    public function test_the_public_page_opens_with_the_pond(): void
    {
        $invitation = $this->createStory($this->sample());

        $this->withoutVite()->get(route('invitation.show', $invitation->slug))->assertSee('pondCover', false);
    }
}
