<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ColorContrast;
use App\Support\InvitationTemplates;
use Illuminate\View\View;

/**
 * Sistema visual: una sola página con los dos lenguajes que conviven en el producto
 * (el del sitio y los paneles, y el de la invitación) para no reinventar estilos
 * en cada pantalla nueva. Incluye el contraste medido de cada tema.
 */
class DesignSystemController extends Controller
{
    public function index(): View
    {
        $themes = collect(InvitationTemplates::all())
            ->map(fn (array $meta, string $key) => [
                'key' => $key,
                'label' => $meta['label'],
                'description' => $meta['description'],
                'palette' => $meta['palette'],
                'tokens' => ColorContrast::tokens($meta['palette']),
                'audit' => ColorContrast::audit($meta['palette']),
            ])
            ->values()
            ->all();

        return view('admin.design-system', [
            'themes' => $themes,
            'siteColors' => $this->siteColors(),
        ]);
    }

    /**
     * Los tokens del sitio viven en resources/css/site/site.css; aquí se repiten sus
     * valores del modo claro solo para poder medir el contraste y mostrar el hex.
     *
     * @return list<array{token: string, value: string, usage: string, ratio: float|null}>
     */
    private function siteColors(): array
    {
        $background = '#f4f4f2';

        $colors = [
            ['--site-bg', '#f4f4f2', 'Fondo de la página.', false],
            ['--site-surface', '#fbfbfa', 'Tarjetas, barras y menús.', false],
            ['--site-ink', '#1d1e20', 'Texto principal.', true],
            ['--site-muted', '#5f6166', 'Texto secundario y ayudas.', true],
            ['--site-line', '#dcddd9', 'Filetes y bordes de campos.', false],
            ['--site-accent', '#8a6a1c', 'Botones, enlaces y estados activos.', true],
            ['--site-on-accent', '#f4f4f2', 'Texto sobre el acento.', false],
            ['--site-tint', '#f2ead8', 'Fondo suave de avisos.', false],
            ['--site-danger', '#b3261e', 'Errores y acciones destructivas.', true],
        ];

        return array_map(fn (array $color) => [
            'token' => $color[0],
            'value' => $color[1],
            'usage' => $color[2],
            'ratio' => $color[3] ? ColorContrast::ratio($color[1], $background) : null,
        ], $colors);
    }
}
