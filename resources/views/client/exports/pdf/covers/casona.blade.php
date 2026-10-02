{{--
    «Casa de muñecas de medianoche»: la casa de noche, de frente y simétrica, con la luna detrás de la
    cumbrera, el gato en el tejado, la buhardilla y las ventanas encendidas y la puerta doble; debajo,
    el nombre, el mensaje y los datos de la fiesta (cuándo y dónde) como las placas de la casa.
--}}
@php
    $light = $colors['onPanelAccent'];
    $ink = $colors['onPanel'];
    $soft = $colors['onPanelSoft'];
    // La casa: la noche apenas más clara para la fachada, un poco más para las molduras
    $mix = function (string $from, string $to, float $amount): string {
        [$a, $b] = [sscanf($from, '#%02x%02x%02x'), sscanf($to, '#%02x%02x%02x')];

        return vsprintf('#%02x%02x%02x', array_map(fn ($x, $y) => (int) round($x + ($y - $x) * $amount), $a, $b));
    };
    $siding = $mix($colors['panel'], $ink, 0.16);
    $trim = $mix($colors['panel'], $ink, 0.5);
    $roof = $mix($colors['panel'], $light, 0.22);
    $moon = $mix($colors['panel'], $ink, 0.78);
    $window = fn (int $x, int $y, int $w, int $h) => sprintf(
        '<path d="M%1$d %2$d V%3$d A%4$d %4$d 0 0 1 %5$d %3$d V%2$d Z" fill="%6$s" stroke="%7$s" stroke-width="4"/><path d="M%8$d %2$d V%9$d M%1$d %10$d H%5$d" stroke="%7$s" stroke-width="3"/>',
        $x, $y + $h, $y + (int) ($w / 2), (int) ($w / 2), $x + $w, $light, $trim, $x + (int) ($w / 2), $y + (int) ($w / 2) - (int) ($w / 2), $y + (int) ($h * 0.55)
    );
    $houseSvg = 'data:image/svg+xml;base64,'.base64_encode(
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 300" width="300" height="300">'
        .'<circle cx="150" cy="44" r="40" fill="'.$moon.'"/>'
        .'<path d="M140 30 C137 26 138 18 141 14 L140 6 L144 10 C146 9 154 9 156 10 L160 6 L159 14 C162 18 163 26 160 30 Z" fill="'.$colors['panel'].'"/>'
        .'<path d="M40 128 L150 40 L260 128 Z" fill="'.$roof.'" stroke="'.$trim.'" stroke-width="5" stroke-linejoin="round"/>'
        .'<circle cx="150" cy="96" r="15" fill="'.$light.'" stroke="'.$trim.'" stroke-width="5"/>'
        .'<rect x="58" y="128" width="184" height="152" fill="'.$siding.'" stroke="'.$trim.'" stroke-width="4"/>'
        .$window(82, 146, 40, 52).$window(178, 146, 40, 52)
        .$window(76, 214, 34, 44).$window(190, 214, 34, 44)
        .'<path d="M126 280 V236 A24 24 0 0 1 174 236 V280 Z" fill="'.$mix($colors['panel'], $ink, 0.08).'" stroke="'.$trim.'" stroke-width="4"/>'
        .'<path d="M150 214 V280" stroke="'.$trim.'" stroke-width="3"/>'
        .'<circle cx="160" cy="258" r="4" fill="'.$light.'"/>'
        .'<rect x="48" y="280" width="204" height="10" fill="'.$trim.'"/>'
        .'</svg>'
    );
@endphp
<style>
    .cs-night { background: {{ $colors['panel'] }}; padding: 12mm 10mm; text-align: center; }
    .cs-house-img { width: 66mm; margin: 0 auto 5mm; }
    .cs-night .kicker { color: {{ $colors['onPanelAccent'] }}; }
    .cs-night .name { margin: 2mm 0 0; color: {{ $colors['onPanel'] }}; }
    .cs-night .message { margin-top: 5mm; color: {{ $colors['onPanelSoft'] }}; }
    .cs-plates { width: 100%; margin-top: 7mm; border-collapse: separate; border-spacing: 3mm 0; }
    .cs-plates td { padding: 2.5mm 3mm; text-align: center; vertical-align: top; border: 0.8pt solid {{ $trim }}; }
    .cs-plates .label { color: {{ $colors['onPanelAccent'] }}; }
    .cs-plates .value { color: {{ $colors['onPanel'] }}; }
</style>

<div class="sheet is-none">
    <table class="cover-fill">
        <tr>
            <td class="fill-cell cs-night">
                <img class="cs-house-img" src="{{ $houseSvg }}" alt="">
                <p class="kicker">{{ $hero['subtitle'] }}</p>
                <h1 class="name">{{ $hero['name'] }}</h1>

                @if($hero['message'])
                    <p class="message">{{ $hero['message'] }}</p>
                @endif

                @if($hero['date'] || $hero['time'] || $location)
                    <table class="cs-plates">
                        <tr>
                            @if($hero['date'])
                                <td><p class="label">{{ $copy['house_date'] ?? 'Fecha' }}</p><p class="value">{{ $hero['date'] }}</p></td>
                            @endif
                            @if($hero['time'])
                                <td><p class="label">{{ $copy['house_time'] ?? 'Hora' }}</p><p class="value">{{ $hero['time'] }}</p></td>
                            @endif
                            @if($location)
                                <td><p class="label">{{ $copy['house_place'] ?? 'La casa queda en' }}</p><p class="value">{{ $location['name'] ?? $location['address'] }}</p></td>
                            @endif
                        </tr>
                    </table>
                @endif
            </td>
        </tr>
    </table>
</div>

@include('client.exports.pdf.partials.rsvp')
