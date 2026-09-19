<?php

namespace Tests\Feature;

use App\EventProfiles\EventProfiles;
use App\Models\Invitation;
use App\Modules\Module;
use App\Modules\ModuleRegistry;
use App\Support\InvitationTemplates;
use Database\Seeders\ShowcaseInvitationsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * El contrato de los módulos: todo lo que se registra en config/modules.php tiene que poder
 * guardarse, leerse, validarse y mostrarse. Un módulo nuevo de temporada entra solo a estas pruebas.
 */
class ModuleRegistryTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    public function test_every_module_declares_a_valid_contract(): void
    {
        $registry = app(ModuleRegistry::class);
        $invitation = new Invitation;

        foreach ($registry->all() as $code => $module) {
            $this->assertSame($code, $module->code());
            $this->assertNotSame('', $module->label(), "{$code} no tiene nombre");
            $this->assertNotEmpty($module->kinds(), "{$code} no dice si es de invitación o de tarjeta");
            $this->assertEmpty(array_diff($module->kinds(), [Module::KIND_INVITATION, Module::KIND_CARD]), "{$code} declara un tipo desconocido");

            foreach ([$module->partial(), $module->panel()] as $view) {
                if ($view !== null) {
                    $this->assertTrue(View::exists($view), "{$code}: no existe la vista {$view}");
                }
            }

            foreach ($module->relations() as $relation) {
                $this->assertTrue(method_exists($invitation, explode('.', $relation)[0]), "{$code}: Invitation no tiene la relación {$relation}");
            }

            // Las reglas quedan bajo el código del módulo, sin pisar las de otro
            foreach (array_keys($module->rules('modulos')) as $field) {
                $this->assertStringStartsWith("modulos.{$code}", $field, "{$code} valida un campo ajeno: {$field}");
            }
        }

    }

    public function test_every_profile_uses_registered_modules_of_its_kind(): void
    {
        $registry = app(ModuleRegistry::class);

        foreach (app(EventProfiles::class)->all() as $profile) {
            foreach ($profile->modules() as $code) {
                $this->assertTrue($registry->has($code), "{$profile->code()}: el módulo {$code} no está registrado");
                $this->assertContains($profile->kind(), $registry->get($code)->kinds(), "{$profile->code()}: {$code} no sirve para {$profile->kind()}");
            }

            $this->assertEmpty(array_diff($profile->enabledByDefault(), $profile->modules()), "{$profile->code()} enciende módulos que no ofrece");
            $this->assertEmpty(array_diff(array_keys($profile->required()), $profile->modules()), "{$profile->code()} exige módulos que no ofrece");
        }

        // Cada plantilla del catálogo apunta a un perfil que existe
        foreach (InvitationTemplates::all() as $template => $entry) {
            $this->assertSame($entry['event'], app(EventProfiles::class)->forTemplate($template)->code(), "{$template} no tiene perfil");
        }
    }

    public function test_the_card_sample_passes_the_module_rules(): void
    {
        $modules = ShowcaseInvitationsSeeder::data('tarjeta-ana-luis')['modules'];
        $registry = app(ModuleRegistry::class);

        $validator = Validator::make(['modulos' => $modules], $registry->rules('modulos'));
        $this->assertTrue($validator->passes(), json_encode($validator->errors()->all(), JSON_UNESCAPED_UNICODE));

        // Una fecha futura para «juntos desde» no tiene sentido
        $modules['juntos_desde']['fecha'] = now()->addYear()->toDateString();
        $this->assertTrue(Validator::make(['modulos' => $modules], $registry->rules('modulos'))->fails());
    }

    public function test_card_modules_save_to_their_tables_and_read_back_the_same(): void
    {
        $registry = app(ModuleRegistry::class);
        // Las dos muestras juntas cubren todos los módulos propios de las tarjetas
        $modules = array_merge(
            ShowcaseInvitationsSeeder::data('tarjeta-libro-aventuras')['modules'],
            ShowcaseInvitationsSeeder::data('tarjeta-ana-luis')['modules'],
        );
        $invitation = $this->createInvitation(['template' => InvitationTemplates::TARJETA_AVENTURA]);

        $registry->save($invitation, $modules);

        $this->assertDatabaseHas('card_dedications', [
            'invitation_id' => $invitation->id,
            'from_name' => 'Luis',
            'to_name' => 'Ana',
        ]);
        $this->assertDatabaseHas('card_milestones', ['invitation_id' => $invitation->id]);

        $loaded = $registry->load($invitation->fresh());

        // Solo los módulos propios de las tarjetas; los compartidos ya los recorre StructuredModulesRoundTripTest
        $cardOnly = array_keys(array_filter($registry->all(), fn (Module $module) => $module->kinds() === [Module::KIND_CARD]));
        $this->assertEqualsCanonicalizing(
            ['dedicatoria', 'juntos_desde', 'respuesta', 'historia', 'recuerdos', 'collage', 'marcos', 'memoria'],
            $cardOnly,
        );

        foreach ($cardOnly as $code) {
            foreach ($modules[$code] as $key => $value) {
                $this->assertEquals($value, $loaded[$code][$key] ?? null, "{$code}.{$key} no vuelve igual");
            }
        }

        // Guardar vacío borra lo de la tarjeta sin dejar filas sueltas
        $registry->save($invitation, array_merge($modules, array_fill_keys($cardOnly, [])));
        $this->assertDatabaseMissing('card_dedications', ['invitation_id' => $invitation->id]);
        $this->assertDatabaseMissing('card_milestones', ['invitation_id' => $invitation->id]);
        $this->assertDatabaseMissing('card_entries', ['invitation_id' => $invitation->id]);
    }
}
