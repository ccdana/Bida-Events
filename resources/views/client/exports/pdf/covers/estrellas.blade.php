{{--
    «Mapa de estrellas»: la carta del cielo del día del bautizo sobre la noche. El círculo con sus
    marcas de grados, la grilla, las estrellas y la constelación en forma de corazón; debajo, el
    nombre y la fecha, la hora y el lugar como las anotaciones de un mapa celeste.
--}}
@php
    $starColor = $colors['onPanelAccent'];
    $skyInk = $colors['onPanel'];
    $gridInk = $colors['onPanelSoft'];
    $seed = crc32(($hero['name'] ?? '').($hero['date'] ?? ''));
    $next = function () use (&$seed): float {
        $seed = ($seed * 1103515245 + 12345) & 0x7fffffff;

        return $seed / 0x7fffffff;
    };
    $chart = '';
    for ($tick = 0; $tick < 72; $tick++) {
        $long = $tick % 6 === 0;
        $angle = deg2rad($tick * 5);
        $chart .= sprintf('<line x1="%.1f" y1="%.1f" x2="%.1f" y2="%.1f" stroke="%s" stroke-width="%s"/>',
            160 + sin($angle) * 156, 160 - cos($angle) * 156, 160 + sin($angle) * ($long ? 146 : 150), 160 - cos($angle) * ($long ? 146 : 150), $starColor, $long ? '1.3' : '0.7');
    }
    foreach ([48, 96, 132] as $radius) {
        $chart .= sprintf('<circle cx="160" cy="160" r="%d" fill="none" stroke="%s" stroke-width="0.6" stroke-dasharray="2 4"/>', $radius, $gridInk);
    }
    for ($star = 0; $star < 70; $star++) {
        $angle = $next() * M_PI * 2;
        $radius = 20 + sqrt($next()) * 124;
        $size = 0.6 + $next() * $next() * 2.4;
        $chart .= sprintf('<circle cx="%.1f" cy="%.1f" r="%.2f" fill="%s"/>', 160 + cos($angle) * $radius, 160 + sin($angle) * $radius, $size, $size > 2 ? $starColor : $skyInk);
    }
    // La constelación en forma de corazón, en el centro del mapa
    $heart = [[160, 132], [140, 111], [114, 115], [99, 142], [115, 176], [160, 218], [205, 176], [221, 142], [206, 115], [180, 111]];
    foreach ($heart as $index => [$x, $y]) {
        [$toX, $toY] = $heart[($index + 1) % count($heart)];
        $chart .= sprintf('<line x1="%d" y1="%d" x2="%d" y2="%d" stroke="%s" stroke-width="1.1"/>', $x, $y, $toX, $toY, $starColor);
    }
    foreach ($heart as [$x, $y]) {
        $chart .= sprintf('<path d="M%1$d %2$d l1.8 4.2 l4.2 1.8 l-4.2 1.8 l-1.8 4.2 l-1.8 -4.2 l-4.2 -1.8 l4.2 -1.8 Z" transform="translate(0 -6)" fill="%3$s"/>', $x, $y, $starColor);
    }
    foreach (['N' => [160, 28], 'E' => [290, 163], 'S' => [160, 300], 'O' => [30, 163]] as $letter => [$x, $y]) {
        $chart .= sprintf('<text x="%d" y="%d" text-anchor="middle" font-family="sans-serif" font-size="9" font-weight="bold" fill="%s">%s</text>', $x, $y, $starColor, $letter);
    }
    $chartSvg = 'data:image/svg+xml;base64,'.base64_encode(
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 320" width="320" height="320">'
        .'<circle cx="160" cy="160" r="156" fill="none" stroke="'.$starColor.'" stroke-width="1.3"/>'
        .'<circle cx="160" cy="160" r="150" fill="none" stroke="'.$starColor.'" stroke-width="0.6"/>'
        .$chart.'</svg>'
    );
@endphp
<style>
    .es-night { background: {{ $colors['panel'] }}; padding: 9mm 10mm; text-align: center; }
    .es-night .kicker { color: {{ $colors['onPanelAccent'] }}; letter-spacing: 3pt; }
    .es-night .name { margin: 3mm 0 0; color: {{ $colors['onPanel'] }}; }
    .es-night .message { margin-top: 5mm; color: {{ $colors['onPanelSoft'] }}; }
    .es-map { width: 74mm; margin: 0 auto 5mm; }
    .es-notes { width: 100%; margin-top: 5mm; border-collapse: collapse; }
    .es-notes td { padding: 2mm 3mm; text-align: center; vertical-align: top; border-top: 0.6pt dashed {{ $colors['onPanelSoft'] }}; }
    .es-notes .label { color: {{ $colors['onPanelAccent'] }}; }
    .es-notes .value { color: {{ $colors['onPanel'] }}; }
</style>

<div class="sheet is-none">
    <table class="cover-fill">
        <tr>
            <td class="fill-cell es-night">
                <img class="es-map" src="{{ $chartSvg }}" alt="">
                <p class="kicker">{{ $copy['sky_label'] ?? $hero['subtitle'] }}</p>
                <h1 class="name">{{ $hero['name'] }}</h1>

                @if($hero['message'])
                    <p class="message">{{ $hero['message'] }}</p>
                @endif

                @if($hero['date'] || $hero['time'] || $location)
                    <table class="es-notes">
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
