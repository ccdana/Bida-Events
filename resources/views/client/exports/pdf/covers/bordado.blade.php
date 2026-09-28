{{--
    «Bordado a mano»: el bastidor con su corona bordada (tallo en punto atrás, hojitas y florcitas en
    punto margarita) y, adentro, las iniciales en hilo; debajo, el nombre y una etiqueta cosida con
    la fecha, la hora y el lugar.
--}}
@php
    $thread = $colors['accent'];
    $floss = $colors['line'];
    $leaf = $colors['muted'];
    $wood = $colors['soft'];
    $woodEdge = $colors['line'];
    $initials = htmlspecialchars(mb_strtoupper(mb_substr(trim((string) $hero['name']), 0, 1)), ENT_QUOTES);
    $wreath = '';
    for ($angle = 0; $angle < 360; $angle += 14) {
        $radians = deg2rad($angle);
        $side = ($angle / 14) % 2 === 0 ? 1 : -1;
        $wreath .= sprintf('<path d="M0 0 C3.5 -4.5 10 -5 14 0 C10 5 3.5 4.5 0 0 Z" fill="%s" transform="translate(%.1f %.1f) rotate(%d)"/>',
            $leaf, 160 + cos($radians) * 112, 160 + sin($radians) * 112, $angle + 90 + $side * 38);
    }
    foreach ([-60, 0, 60, 120, 180, 240] as $index => $angle) {
        $x = 160 + cos(deg2rad($angle)) * 112;
        $y = 160 + sin(deg2rad($angle)) * 112;
        $petal = $index % 2 ? $thread : $floss;
        for ($p = 0; $p < 5; $p++) {
            $wreath .= sprintf('<ellipse cx="0" cy="-7" rx="3.4" ry="6" fill="%s" stroke="%s" stroke-width="1.4" transform="translate(%.1f %.1f) rotate(%d)"/>', $colors['paper'], $petal, $x, $y, $p * 72 + $angle);
        }
        $wreath .= sprintf('<circle cx="%.1f" cy="%.1f" r="3" fill="%s"/>', $x, $y, $index % 2 ? $floss : $thread);
    }
    $hoopSvg = 'data:image/svg+xml;base64,'.base64_encode(
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 340" width="320" height="340">'
        .'<g transform="translate(0 20)">'
        .'<rect x="146" y="-18" width="28" height="22" rx="4" fill="'.$wood.'" stroke="'.$woodEdge.'" stroke-width="1.5"/>'
        .'<rect x="140" y="-11" width="48" height="6" rx="3" fill="'.$colors['muted'].'"/>'
        .'<circle cx="160" cy="160" r="156" fill="'.$wood.'" stroke="'.$woodEdge.'" stroke-width="2"/>'
        .'<circle cx="160" cy="160" r="148" fill="none" stroke="'.$woodEdge.'" stroke-width="1.2"/>'
        .'<circle cx="160" cy="160" r="140" fill="'.$colors['paper'].'" stroke="'.$woodEdge.'" stroke-width="1.5"/>'
        .'<circle cx="160" cy="160" r="112" fill="none" stroke="'.$leaf.'" stroke-width="1.4" stroke-dasharray="6 1.5"/>'
        .$wreath
        .'<circle cx="160" cy="160" r="86" fill="none" stroke="'.$thread.'" stroke-width="1.6" stroke-dasharray="5 2.6"/>'
        .'<text x="160" y="190" text-anchor="middle" font-family="serif" font-style="italic" font-size="88" fill="'.$thread.'">'.$initials.'</text>'
        .'</g></svg>'
    );
@endphp
<style>
    .bd-hoop-img { width: 74mm; margin: 0 auto 4mm; }
    .bd-cover .kicker { color: {{ $colors['accent'] }}; }
    .bd-cover .name { margin: 2mm 0 0; }
    .bd-stitch { width: 60mm; margin: 4mm auto 0; border-top: 1.2pt dashed {{ $colors['line'] }}; }
    .bd-label { width: 130mm; margin: 5mm auto 0; padding: 2mm; background: {{ $colors['tint'] }}; }
    .bd-label-inner { border: 0.9pt dashed {{ $colors['line'] }}; padding: 3mm 4mm; }
    .bd-label table { width: 100%; border-collapse: collapse; }
    .bd-label td { padding: 1mm 2mm; text-align: center; vertical-align: top; }
</style>

<div class="sheet is-none">
    <table class="cover-fill">
        <tr>
            <td class="fill-cell center bd-cover">
                <img class="bd-hoop-img" src="{{ $hoopSvg }}" alt="">
                <p class="kicker">{{ $hero['subtitle'] }}</p>
                <h1 class="name">{{ $hero['name'] }}</h1>
                <div class="bd-stitch"></div>

                @if($hero['message'])
                    <p class="message" style="margin-top: 5mm;">{{ $hero['message'] }}</p>
                @endif

                @if($hero['date'] || $hero['time'] || $location)
                    <div class="bd-label">
                        <div class="bd-label-inner">
                            <table>
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
                        </div>
                    </div>
                @endif
            </td>
        </tr>
    </table>
</div>

@include('client.exports.pdf.partials.rsvp')
