{{--
    «Caldero encantado»: la noche del laboratorio con el caldero sobre el fuego, la poción que
    burbujea y el humo; debajo, el nombre, los ingredientes y la receta de la fiesta (cuándo y dónde)
    anotada como en el recetario de pociones.
--}}
@php
    $potion = $colors['onPanelAccent'];
    $ink = $colors['onPanel'];
    $soft = $colors['onPanelSoft'];
    // El hierro del caldero: la noche apenas más clara, para que se vea sobre el panel
    $mix = function (string $from, string $to, float $amount): string {
        [$a, $b] = [sscanf($from, '#%02x%02x%02x'), sscanf($to, '#%02x%02x%02x')];

        return vsprintf('#%02x%02x%02x', array_map(fn ($x, $y) => (int) round($x + ($y - $x) * $amount), $a, $b));
    };
    $iron = $mix($colors['panel'], $ink, 0.22);
    $rim = $mix($colors['panel'], $ink, 0.38);
    $bubbles = '';
    foreach ([[118, 62, 7], [150, 44, 10], [182, 58, 6], [132, 24, 5], [170, 16, 8]] as [$x, $y, $r]) {
        $bubbles .= sprintf('<circle cx="%d" cy="%d" r="%d" fill="none" stroke="%s" stroke-width="2"/>', $x, $y, $r, $soft);
    }
    $cauldronSvg = 'data:image/svg+xml;base64,'.base64_encode(
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 262" width="300" height="262">'
        .$bubbles
        .'<rect x="86" y="244" width="128" height="11" rx="5.5" fill="'.$soft.'"/>'
        .'<path d="M112 246 C104 230 116 222 114 208 C128 220 132 234 128 246 Z" fill="'.$potion.'"/>'
        .'<path d="M140 246 C130 226 146 214 146 196 C162 212 170 230 162 246 Z" fill="'.$potion.'"/>'
        .'<path d="M174 246 C168 230 180 220 180 206 C192 218 196 234 190 246 Z" fill="'.$potion.'"/>'
        .'<path d="M80 196 L68 238 H82 L94 204 Z M220 196 L232 238 H218 L206 204 Z" fill="'.$iron.'"/>'
        .'<circle cx="30" cy="124" r="12" fill="none" stroke="'.$rim.'" stroke-width="6"/>'
        .'<circle cx="270" cy="124" r="12" fill="none" stroke="'.$rim.'" stroke-width="6"/>'
        .'<path d="M34 100 C22 176 84 208 150 208 C216 208 278 176 266 100 Z" fill="'.$iron.'"/>'
        .'<path d="M60 126 C58 156 76 180 100 190" fill="none" stroke="'.$rim.'" stroke-width="6" stroke-linecap="round"/>'
        .'<ellipse cx="150" cy="100" rx="122" ry="22" fill="'.$rim.'"/>'
        .'<ellipse cx="150" cy="102" rx="106" ry="15" fill="'.$potion.'"/>'
        .'<rect x="190" y="-8" width="9" height="116" rx="4.5" fill="'.$soft.'" transform="rotate(26 194 102)"/>'
        .'</svg>'
    );
@endphp
<style>
    .cl-night { background: {{ $colors['panel'] }}; padding: 12mm 10mm; text-align: center; }
    .cl-cauldron { width: 70mm; margin: 0 auto 5mm; }
    .cl-night .kicker { color: {{ $colors['onPanelAccent'] }}; }
    .cl-night .name { margin: 2mm 0 0; color: {{ $colors['onPanel'] }}; }
    .cl-night .message { margin-top: 5mm; color: {{ $colors['onPanelSoft'] }}; }
    .cl-ingredients { width: 120mm; margin: 5mm auto 0; padding: 2.5mm 4mm; border: 0.9pt dashed {{ $colors['onPanelAccent'] }}; color: {{ $colors['onPanelAccent'] }}; font-size: 10pt; }
    .cl-steps { width: 100%; margin-top: 7mm; border-collapse: collapse; }
    .cl-steps td { padding: 2mm 3mm; text-align: center; vertical-align: top; border-top: 0.6pt dotted {{ $colors['onPanelSoft'] }}; }
    .cl-steps .label { color: {{ $colors['onPanelAccent'] }}; }
    .cl-steps .value { color: {{ $colors['onPanel'] }}; }
</style>

<div class="sheet is-none">
    <table class="cover-fill">
        <tr>
            <td class="fill-cell cl-night">
                <img class="cl-cauldron" src="{{ $cauldronSvg }}" alt="">
                <p class="kicker">{{ $hero['subtitle'] }}</p>
                <h1 class="name">{{ $hero['name'] }}</h1>

                @if(!empty($copy['potion_ingredients']))
                    <p class="cl-ingredients">{{ $copy['potion_ingredients'] }}</p>
                @endif

                @if($hero['message'])
                    <p class="message">{{ $hero['message'] }}</p>
                @endif

                @if($hero['date'] || $hero['time'] || $location)
                    <table class="cl-steps">
                        <tr>
                            @if($hero['date'])
                                <td><p class="label">Fecha</p><p class="value">{{ $hero['date'] }}</p></td>
                            @endif
                            @if($hero['time'])
                                <td><p class="label">Hora</p><p class="value">{{ $hero['time'] }}</p></td>
                            @endif
                            @if($location)
                                <td><p class="label">Lugar</p><p class="value">{{ $location['name'] ?? $location['address'] }}</p></td>
                            @endif
                        </tr>
                    </table>
                @endif
            </td>
        </tr>
    </table>
</div>

@include('client.exports.pdf.partials.rsvp')
