<?php

namespace App\Modules\Invitation;

use App\Models\Feature;
use App\Models\Invitation;
use App\Models\InvitationTheme;
use App\Modules\Concerns\ReadsValues;
use App\Modules\Module;
use App\Modules\ModuleRegistry;
use Illuminate\Support\Facades\DB;

/**
 * Configuración: plantilla (invitations.template), colores y tipografías (invitation_themes) y
 * qué módulos se muestran (invitation_features, una fila por módulo).
 */
class ConfigModule extends Module
{
    use ReadsValues;

    public const COLORS = [
        'primary' => 'color_primary',
        'secondary' => 'color_secondary',
        'accent' => 'color_accent',
        'text' => 'color_text',
        'background' => 'color_background',
    ];

    public const FONTS = [
        'titulos' => 'font_titles',
        'cuerpo' => 'font_body',
        'script' => 'font_script',
    ];

    public function code(): string
    {
        return 'config';
    }

    public function label(): string
    {
        return 'Configuración';
    }

    public function kinds(): array
    {
        return [self::KIND_INVITATION, self::KIND_CARD];
    }

    public function defaults(): array
    {
        return [
            'colores' => [
                'primary' => '#C9A96E',
                'secondary' => '#2C1810',
                'accent' => '#F5E6D3',
                'text' => '#1A1A1A',
                'background' => '#FFFAF5',
            ],
            'tipografias' => [
                'titulos' => 'Playfair Display',
                'cuerpo' => 'Montserrat',
                'script' => 'Great Vibes',
            ],
            'modulos' => app(ModuleRegistry::class)->visibilityDefaults(),
            'template' => 'invitations.templates.xv-premium',
        ];
    }

    public function relations(): array
    {
        return ['theme', 'features'];
    }

    public function load(Invitation $invitation): array
    {
        $config = ['template' => $invitation->template];
        $theme = $invitation->theme;

        if ($theme) {
            $config['colores'] = $this->compact(array_map(fn (string $column) => $theme->{$column}, self::COLORS));
            $config['tipografias'] = $this->compact(array_map(fn (string $column) => $theme->{$column}, self::FONTS));
        }

        if ($invitation->features->isNotEmpty()) {
            $config['modulos'] = $invitation->features
                ->mapWithKeys(fn (Feature $feature) => [$feature->code => (bool) $feature->pivot->is_enabled])
                ->all();
        }

        return $config;
    }

    public function save(Invitation $invitation, array $data): void
    {
        if ($template = $this->text($data['template'] ?? null, 255)) {
            if ($invitation->template !== $template) {
                $invitation->forceFill(['template' => $template])->saveQuietly();
            }
        }

        $colors = $this->items($data['colores'] ?? null);
        $fonts = $this->items($data['tipografias'] ?? null);

        $attributes = [];

        foreach (self::COLORS as $key => $column) {
            $attributes[$column] = $this->text($colors[$key] ?? null, 20);
        }

        foreach (self::FONTS as $key => $column) {
            $attributes[$column] = $this->text($fonts[$key] ?? null, 100);
        }

        InvitationTheme::updateOrCreate(['invitation_id' => $invitation->id], $attributes);

        $this->saveVisibility($invitation, $this->items($data['modulos'] ?? null));
    }

    /** Una fila por módulo con su interruptor; los módulos que no llegan quedan como estaban. */
    protected function saveVisibility(Invitation $invitation, array $flags): void
    {
        $registry = app(ModuleRegistry::class);

        foreach ($flags as $code => $enabled) {
            if (! is_string($code) || ! $registry->has($code) || $code === $this->code()) {
                continue;
            }

            DB::table('invitation_features')->updateOrInsert(
                ['invitation_id' => $invitation->id, 'feature_id' => Feature::idFor($code, $registry->get($code)->label())],
                ['is_enabled' => (bool) $enabled],
            );
        }
    }
}
