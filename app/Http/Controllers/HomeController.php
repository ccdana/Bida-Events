<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
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
        ]);
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
