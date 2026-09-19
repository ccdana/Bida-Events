<?php

namespace Tests\Feature;

use App\EventProfiles\EventProfile;
use App\EventProfiles\EventProfiles;
use App\Models\User;
use App\Modules\ModuleRegistry;
use App\Support\ColorContrast;
use App\Support\ColorPalettes;
use App\Support\InvitationTemplates;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Los editables de cada plantilla: que todo módulo que ofrece un perfil se pueda editar de verdad
 * (pestaña en el editor y panel que responde a esa pestaña) y que las paletas listas de «Estética»
 * estén completas y se lean.
 */
class EditorPanelsTest extends TestCase
{
    use RefreshDatabase;

    /** Pestañas declaradas en el editor: código de módulo => id de la pestaña. */
    private function editorTabs(): array
    {
        $script = file_get_contents(resource_path('views/admin/invitations/editor/script.blade.php'));
        preg_match_all("/\{ id: '([a-z_]+)'[^}]*?moduleCode: '([a-z_]+)'/", $script, $matches, PREG_SET_ORDER);

        return collect($matches)->mapWithKeys(fn (array $m) => [$m[2] => $m[1]])->all();
    }

    /** Paneles que el editor tiene a mano: los de la barra lateral y los propios de cada módulo. */
    private function panelSources(): array
    {
        $sidebar = file_get_contents(resource_path('views/admin/invitations/editor/sidebar.blade.php'));
        preg_match_all("/@include\('(admin\.invitations\.panels\.[a-z0-9.\-_]+)'/", $sidebar, $matches);

        $views = $matches[1];

        foreach (app(ModuleRegistry::class)->all() as $module) {
            if ($module->panel()) {
                $views[] = $module->panel();
            }
        }

        return collect($views)
            ->unique()
            ->mapWithKeys(fn (string $view) => [$view => file_get_contents(resource_path('views/'.str_replace('.', '/', $view).'.blade.php'))])
            ->all();
    }

    public function test_every_module_of_every_profile_can_be_edited(): void
    {
        $registry = app(ModuleRegistry::class);
        $tabs = $this->editorTabs();
        $panels = $this->panelSources();

        foreach (app(EventProfiles::class)->all() as $profile) {
            /** @var EventProfile $profile */
            foreach ($profile->modules() as $code) {
                $this->assertTrue($registry->has($code), "{$profile->code()}: el módulo «{$code}» no está en config/modules.php");
                $this->assertArrayHasKey($code, $tabs, "{$profile->code()}: el módulo «{$code}» no tiene pestaña en el editor");

                // Alguna vista del editor tiene que dibujar esa pestaña, o el módulo no se puede editar
                $tab = $tabs[$code];
                $shown = collect($panels)->filter(fn (string $source) => str_contains($source, "activeTab === '{$tab}'"))->keys();

                $this->assertCount(1, $shown, "El módulo «{$code}» debería tener un solo panel para la pestaña «{$tab}»; hay ".$shown->count());

                // El módulo con panel propio declara el que de verdad lo dibuja
                if ($registry->get($code)->panel()) {
                    $this->assertSame($registry->get($code)->panel(), $shown->first(), "El módulo «{$code}» apunta a otro panel");
                }
            }
        }
    }

    public function test_the_editor_opens_with_every_template_and_its_palettes(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->withoutVite()->get(route('admin.invitations.create'))->assertOk();
        $config = $response->viewData('editorConfig');

        $this->assertSame(
            array_keys(InvitationTemplates::all()),
            collect($config['templateOptions'])->pluck('value')->all(),
            'El editor no ofrece todas las plantillas'
        );

        // Cada plantilla lleva su paleta original a «Estética»
        $originals = collect($config['palettes'])->filter(fn (array $palette) => $palette['template'])->pluck('template')->all();
        $this->assertEqualsCanonicalizing(array_keys(InvitationTemplates::all()), $originals);

        // Y cada perfil recibe sus módulos, que son las pestañas que verá el editor
        foreach ($config['profiles'] as $code => $profile) {
            $this->assertNotEmpty($profile['modules'], "El perfil «{$code}» no ofrece módulos");
        }
    }

    public function test_every_ready_made_palette_is_complete_and_readable(): void
    {
        $palettes = ColorPalettes::all();
        $this->assertGreaterThan(20, count($palettes));

        foreach ($palettes as $palette) {
            $this->assertNotEmpty($palette['name']);
            $this->assertContains($palette['mode'], ['light', 'night'], "{$palette['name']}: modo desconocido");

            foreach (ColorPalettes::ROLES as $role) {
                $this->assertMatchesRegularExpression('/^#[0-9A-Fa-f]{6}$/', $palette['colors'][$role] ?? '', "{$palette['name']}: falta el color «{$role}»");
            }

            foreach (ColorContrast::audit($palette['colors']) as $check) {
                $this->assertGreaterThanOrEqual(
                    $check['min'],
                    $check['ratio'],
                    "{$palette['name']}: «{$check['label']}» queda en {$check['ratio']}:1"
                );
            }
        }

        // Ni dos paletas con el mismo nombre ni dos con los mismos colores y distinto nombre:
        // el editor las identifica por el nombre y marcaría varias como la elegida
        $names = array_column($palettes, 'name');
        $this->assertSame(array_unique($names), $names, 'Hay paletas con el nombre repetido');

        $colors = array_map(fn (array $palette) => implode(',', $palette['colors']), $palettes);
        $this->assertSame(array_unique($colors), $colors, 'Hay paletas con los mismos colores');
        // Cada tipo de evento tiene de dónde elegir, además de la original de su plantilla
        foreach (array_unique(array_column(InvitationTemplates::all(), 'event')) as $event) {
            $forEvent = array_filter($palettes, fn (array $palette) => in_array($event, $palette['events'], true) && ! $palette['template']);
            $this->assertNotEmpty($forEvent, "El evento «{$event}» no tiene paletas propias en config/palettes.php");
        }
    }
}
