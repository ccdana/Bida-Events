<?php

namespace App\Http\Controllers\Admin;

use App\EventProfiles\EventProfiles;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Modules\Module;
use App\Support\InvitationTemplates;
use App\Support\Offers;
use App\Support\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Ajustes del sitio: lo que antes había que editar en config/bida.php y ahora se maneja desde
 * el panel. Lo guardado pisa la configuración en cada petición (App\Support\SiteSettings).
 */
class SettingsController extends Controller
{
    public function __construct(private EventProfiles $profiles) {}

    public function edit(): View
    {
        $settings = SiteSettings::current();

        return view('admin.settings', [
            'settings' => $settings,
            'seasonalTemplates' => $this->seasonalTemplates($settings['templates']['disabled']),
            'promoActive' => Offers::launchPromoActive(),
            'seasonActive' => Offers::season() !== null,
        ]);
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        SiteSettings::put('promo', [
            'active' => $request->boolean('promo_active'),
            'ends_at' => $this->date($request->input('promo_ends_at')),
        ]);

        SiteSettings::put('packages', collect($request->input('packages', []))
            ->map(fn (array $package) => [
                'price' => (int) $package['price'],
                'promo_price' => ($package['promo_price'] ?? '') === '' ? null : (int) $package['promo_price'],
            ])
            ->all());

        SiteSettings::put('season', [
            'price' => (int) $request->input('season_price'),
            'promo_price' => $request->filled('season_promo_price') ? (int) $request->input('season_promo_price') : null,
            'ends_at' => $this->date($request->input('season_ends_at')),
        ]);

        // Llegan las encendidas; se guardan las apagadas, para que una plantilla nueva nazca visible
        $enabled = (array) $request->input('templates', []);
        SiteSettings::put('templates', [
            'disabled' => array_values(array_diff(array_keys($this->seasonalTemplates([])), $enabled)),
        ]);

        return back()->with('success', 'Ajustes guardados.');
    }

    /**
     * Las plantillas que se venden por temporada (las tarjetas), con su estado actual.
     *
     * @return array<string, array{label: string, tagline: string, event: string, enabled: bool}>
     */
    private function seasonalTemplates(array $disabled): array
    {
        return collect(InvitationTemplates::all())
            ->filter(fn (array $template) => $this->isCard($template['event']))
            ->map(fn (array $template, string $key) => [
                'label' => $template['label'],
                'tagline' => $template['tagline'],
                'event' => $this->profiles->get($template['event'])->label(),
                'enabled' => ! in_array($key, $disabled, true),
            ])
            ->all();
    }

    private function isCard(string $event): bool
    {
        return rescue(fn () => $this->profiles->get($event)->kind() === Module::KIND_CARD, false, report: false);
    }

    /** Las fechas del formulario llegan como «2026-09-21T23:59»; se guardan con segundos. */
    private function date(?string $value): ?string
    {
        return filled($value)
            ? rescue(fn () => Carbon::parse($value)->format('Y-m-d H:i:s'), null, report: false)
            : null;
    }
}
