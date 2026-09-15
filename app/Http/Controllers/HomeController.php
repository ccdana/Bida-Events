<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Support\InvitationTemplates;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request): View
    {
        $bida = config('bida');
        $whatsappNumber = preg_replace('/\D+/', '', (string) $bida['whatsapp']);
        $whatsapp = fn (string $message): string => "https://wa.me/{$whatsappNumber}?text=".rawurlencode($message);

        return view('home', [
            'bida' => $bida,
            'user' => $request->user(),
            'contactUrl' => $whatsapp("Hola {$bida['brand']}, quiero información sobre las invitaciones digitales."),
            'packages' => collect($bida['packages'])->map(fn (array $package): array => $package + [
                'whatsapp' => $whatsapp("Hola {$bida['brand']}, me interesa el paquete {$package['name']} ({$package['price']} Bs) para mi invitación."),
            ])->all(),
            'demoUrl' => $this->demoUrl($bida['demo_slug'] ?? null),
            'demos' => $this->demos($bida['demo_invitations'] ?? []),
        ]);
    }

    /** Invitaciones de muestra que el visitante recorre en la sección de plantillas, en el orden de la configuración. */
    private function demos(array $slugs): array
    {
        if (! $slugs) {
            return [];
        }

        $invitations = rescue(
            fn () => Invitation::whereIn('slug', $slugs)->where('status', 'active')->get(['slug', 'title', 'template'])->keyBy('slug'),
            collect(),
            report: false,
        );

        $events = [
            'xv' => ['XV años', 'crown-simple'],
            'boda' => ['Boda', 'heart'],
            'bautizo' => ['Bautizo', 'baby'],
            'cumple' => ['Cumpleaños', 'cake'],
        ];

        return collect($slugs)
            ->map(function (string $slug) use ($invitations, $events): ?array {
                $invitation = $invitations->get($slug);
                $template = $invitation ? (InvitationTemplates::all()[$invitation->template] ?? null) : null;

                if (! $template) {
                    return null;
                }

                [$event, $icon] = $events[$template['event']] ?? ['Evento', 'sparkle'];

                return [
                    // Muestra interactiva (nada se guarda) y la misma con la apertura que se abre sola
                    'demoUrl' => route('invitation.demo', $slug),
                    'coverUrl' => route('invitation.demo', ['slug' => $slug, 'portada' => 1]),
                    'title' => $invitation->title,
                    'label' => $template['label'],
                    'description' => $template['description'],
                    'event' => $event,
                    'eventKey' => $template['event'],
                    'icon' => $icon,
                ];
            })
            ->filter()
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
