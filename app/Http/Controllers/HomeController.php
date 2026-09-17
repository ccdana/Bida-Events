<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Support\LeadSource;
use App\Support\ShareMeta;
use App\Support\ShowcaseDemos;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request): View
    {
        $bida = config('bida');
        // Cada mensaje lleva el código de origen (LeadSource) para saber de qué campaña escribió
        $whatsapp = fn (string $message): string => LeadSource::whatsappUrl($request, $message, LeadSource::HOME);

        return view('home', [
            'bida' => $bida,
            'user' => $request->user(),
            'contactUrl' => $whatsapp('Hola {brand}, quiero información sobre las invitaciones digitales.'),
            'packages' => $this->packages($bida['packages'], $whatsapp),
            'demoUrl' => $this->demoUrl($bida['demo_slug'] ?? null),
            'demos' => ShowcaseDemos::find($bida['demo_invitations'] ?? []),
            'landings' => self::landingLinks(),
            'share' => ShareMeta::make(
                "{$bida['brand']} | Invitaciones digitales para tus eventos",
                'Invitaciones digitales para bodas, bautizos, cumpleaños y XV años, con confirmación de asistencia, música, fotos y mapa. Paquetes desde 200 Bs.',
                ShareMeta::siteImage('inicio'),
                route('home'),
            ),
        ]);
    }

    /** Paquetes con su botón de WhatsApp; también los usa cada página por evento. */
    public static function packages(array $packages, \Closure $whatsapp): array
    {
        return collect($packages)->map(fn (array $package): array => $package + [
            'whatsapp' => $whatsapp("Hola {brand}, me interesa el paquete {$package['name']} ({$package['price']} Bs) para mi invitación."),
        ])->all();
    }

    /** Enlaces a las páginas por tipo de evento, para el pie y el enlazado interno. */
    public static function landingLinks(): array
    {
        return collect(config('bida.landings', []))
            ->map(fn (array $landing, string $slug) => ['url' => route('landing', $slug), 'label' => $landing['link'], 'slug' => $slug, 'event' => $landing['event']])
            ->values()
            ->all();
    }

    /** Enlace a la invitación de ejemplo que se muestra dentro del teléfono de la portada. */
    private function demoUrl(?string $slug): ?string
    {
        if (! $slug) {
            return null;
        }

        // Si la base de datos no responde, la portada muestra una imagen en lugar de la vista previa
        $exists = rescue(
            fn () => Invitation::where('slug', $slug)->where('status', 'active')->exists(),
            false,
            report: false,
        );

        return $exists ? route('invitation.show', $slug) : null;
    }
}
