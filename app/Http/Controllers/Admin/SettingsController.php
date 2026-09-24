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
            // Por qué cada temporada se ve o no en el sitio (se vende, apagada, terminada, sin diseños)
            'seasonStatuses' => collect(config('bida.seasons', []))->keys()->mapWithKeys(fn (string $key) => [$key => Offers::seasonStatus($key)])->all(),
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

        // Cada temporada por separado: se enciende, se apaga y cambia de precio sin tocar a las demás
        SiteSettings::put('season', collect($request->input('seasons', []))
            ->only(array_keys(config('bida.seasons', [])))
            ->map(fn (array $season) => [
                'active' => (bool) ($season['active'] ?? false),
                'price' => (int) $season['price'],
                'promo_price' => ($season['promo_price'] ?? '') === '' ? null : (int) $season['promo_price'],
                'ends_at' => $this->date($season['ends_at'] ?? null),
            ])
            ->all());

        SiteSettings::put('reseller_plans', collect($request->input('reseller_plans', []))
            ->only(array_keys(config('bida.reseller_plans', [])))
            ->map(fn (array $plan) => [
                'price' => (int) $plan['price'],
                'quota_per_month' => ($plan['quota_per_month'] ?? '') === '' ? null : (int) $plan['quota_per_month'],
            ])
            ->all());

        // Llegan las encendidas; se guardan las apagadas, para que una plantilla nueva nazca visible
        $enabled = (array) $request->input('templates', []);
        SiteSettings::put('templates', [
            'disabled' => array_values(array_diff(array_keys($this->seasonalTemplates([])), $enabled)),
        ]);

        return back()->with('success', 'Ajustes guardados.');
    }

    /**
     * Las plantillas que se venden por temporada (las tarjetas y las invitaciones de temporada,
     * como Halloween), con su estado actual.
     *
     * @return array<string, array{label: string, tagline: string, event: string, season: ?string, enabled: bool}>
     */
    private function seasonalTemplates(array $disabled): array
    {
        return collect(InvitationTemplates::all())
            ->filter(fn (array $template) => $this->isSeasonal($template['event']))
            ->map(fn (array $template, string $key) => [
                'label' => $template['label'],
                'tagline' => $template['tagline'],
                'event' => $this->profiles->get($template['event'])->label(),
                // A qué temporada pertenece, para listarla dentro de su tarjeta en Ajustes
                'season' => $this->profiles->get($template['event'])->season(),
                'enabled' => ! in_array($key, $disabled, true),
            ])
            ->all();
    }

    private function isSeasonal(string $event): bool
    {
        return rescue(function () use ($event): bool {
            $profile = $this->profiles->get($event);

            return $profile->kind() === Module::KIND_CARD || $profile->season() !== null;
        }, false, report: false);
    }

    /** Las fechas del formulario llegan como «2026-09-21T23:59»; se guardan con segundos. */
    private function date(?string $value): ?string
    {
        return filled($value)
            ? rescue(fn () => Carbon::parse($value)->format('Y-m-d H:i:s'), null, report: false)
            : null;
    }
}
