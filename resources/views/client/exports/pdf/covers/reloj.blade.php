{{--
    «A la misma hora»: la esfera del reloj dibujada en SVG, con sus marcas, los números romanos y las
    agujas en la hora de la boda; debajo, los nombres, el mensaje y el día, la hora y el lugar.
--}}
@php
    $time = preg_match('/(\d{1,2}):(\d{2})/', (string) ($hero['time'] ?? ''), $match) ? [(int) $match[1], (int) $match[2]] : [12, 0];
    $hourAngle = deg2rad(($time[0] % 12) * 30 + $time[1] * 0.5 - 90);
    $minuteAngle = deg2rad($time[1] * 6 - 90);
    $ticks = '';
    for ($tick = 0; $tick < 60; $tick++) {
        $angle = deg2rad($tick * 6);
        $inner = $tick % 5 === 0 ? 82 : 88;
        $ticks .= sprintf('<line x1="%.1f" y1="%.1f" x2="%.1f" y2="%.1f" stroke="%s" stroke-width="%s"/>',
            100 + $inner * sin($angle), 100 - $inner * cos($angle), 100 + 92 * sin($angle), 100 - 92 * cos($angle),
            $colors['body'], $tick % 5 === 0 ? 2.4 : 0.8);
    }
    $numerals = '';
    foreach (['XII' => 0, 'III' => 90, 'VI' => 180, 'IX' => 270] as $numeral => $degrees) {
        $angle = deg2rad($degrees);
        $numerals .= sprintf('<text x="%.1f" y="%.1f" text-anchor="middle" font-family="serif" font-size="13" font-weight="bold" fill="%s">%s</text>',
            100 + 62 * sin($angle), 104.5 - 62 * cos($angle), $colors['body'], $numeral);
    }
    $dial = 'data:image/svg+xml;base64,'.base64_encode(
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="200" height="200">'
        .'<circle cx="100" cy="100" r="98" fill="'.$colors['line'].'"/>'
        .'<circle cx="100" cy="100" r="93" fill="'.$colors['paper'].'"/>'
        .$ticks.$numerals
        .sprintf('<line x1="100" y1="100" x2="%.1f" y2="%.1f" stroke="%s" stroke-width="6" stroke-linecap="round"/>', 100 + 44 * cos($hourAngle), 100 + 44 * sin($hourAngle), $colors['primary'])
        .sprintf('<line x1="100" y1="100" x2="%.1f" y2="%.1f" stroke="%s" stroke-width="4" stroke-linecap="round"/>', 100 + 66 * cos($minuteAngle), 100 + 66 * sin($minuteAngle), $colors['primary'])
        .'<circle cx="100" cy="100" r="6" fill="'.$colors['line'].'"/>'
        .'</svg>'
    );
@endphp
<style>
    .rl-dial { width: 70mm; height: 70mm; margin: 5mm auto 0; }
</style>

<div class="sheet is-thin">
    <table class="cover-fill">
        <tr>
            <td class="fill-cell center">
                <p class="kicker">{{ $hero['subtitle'] }}</p>
                <img class="rl-dial" src="{{ $dial }}" alt="">

                <h1 class="name" style="margin-top: 7mm;">{{ $hero['name'] }}</h1>

                @if($hero['message'])
                    <p class="message" style="margin-top: 5mm;">{{ $hero['message'] }}</p>
                @endif

                @include('client.exports.pdf.partials.when')
            </td>
        </tr>
    </table>
</div>

@include('client.exports.pdf.partials.rsvp')
