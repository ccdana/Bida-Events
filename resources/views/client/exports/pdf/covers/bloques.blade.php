{{--
    «Bloques de juguete»: su nombre armado con bloques de madera (cada uno con la cara de arriba y
    la del costado en perspectiva, alternando los colores), y debajo el nombre, el mensaje y tres
    bloques bajos con la fecha, la hora y el lugar.
--}}
@php
    $mix = function (string $from, string $to, float $amount): string {
        [$a, $b] = [sscanf($from, '#%02x%02x%02x'), sscanf($to, '#%02x%02x%02x')];

        return vsprintf('#%02x%02x%02x', array_map(fn ($x, $y) => (int) round($x + ($y - $x) * $amount), $a, $b));
    };
    $word = \Illuminate\Support\Str::of((string) $hero['name'])->explode(' ')->first() ?: '';
    $word = mb_strlen($word) >= 2 && mb_strlen($word) <= 10 ? $word : 'Bebé';
    $letters = mb_str_split(mb_strtoupper($word));
    $tones = [$colors['primary'], $colors['accent'], $mix($colors['primary'], $colors['ink'], 0.5)];
    $size = 44;
    $step = $size + 16;
    $width = count($letters) * $step + 10;
    $blocks = '';
    foreach ($letters as $index => $letter) {
        $x = 4 + $index * $step;
        $y = 14;
        $tone = $tones[$index % 3];
        $blocks .= sprintf('<path d="M%1$d %2$d l10 -10 h%3$d l-10 10 Z" fill="%4$s"/>', $x, $y, $size, $mix($tone, '#ffffff', 0.35));
        $blocks .= sprintf('<path d="M%1$d %2$d l10 -10 v%3$d l-10 10 Z" fill="%4$s"/>', $x + $size, $y, $size, $mix($tone, '#000000', 0.3));
        $blocks .= sprintf('<rect x="%d" y="%d" width="%d" height="%d" rx="4" fill="%s"/>', $x, $y, $size, $size, $tone);
        $blocks .= sprintf('<rect x="%d" y="%d" width="%d" height="%d" rx="3" fill="none" stroke="%s" stroke-width="2"/>', $x + 4, $y + 4, $size - 8, $size - 8, $mix($tone, '#ffffff', 0.3));
        $blocks .= sprintf('<text x="%d" y="%d" text-anchor="middle" font-family="sans-serif" font-weight="bold" font-size="24" fill="%s">%s</text>', $x + $size / 2, $y + $size / 2 + 9, $colors['paper'], htmlspecialchars($letter, ENT_QUOTES));
    }
    $blocksSvg = 'data:image/svg+xml;base64,'.base64_encode(
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$width.' 62" width="'.$width.'" height="62">'.$blocks.'</svg>'
    );
@endphp
<style>
    .bl-row { width: {{ min(160, count($letters) * 16) }}mm; margin: 0 auto 8mm; }
    .bl-cover .kicker { color: {{ $colors['accent'] }}; }
    .bl-cover .name { margin: 2mm 0 0; }
    .bl-cells { width: 100%; margin-top: 8mm; border-collapse: separate; border-spacing: 3mm 0; }
    .bl-cells td { padding: 3mm 2mm; text-align: center; vertical-align: top; background: {{ $colors['tint'] }}; border-bottom: 1.4mm solid {{ $colors['line'] }}; }
</style>

<div class="sheet is-none">
    <table class="cover-fill">
        <tr>
            <td class="fill-cell center bl-cover">
                <img class="bl-row" src="{{ $blocksSvg }}" alt="">
                <p class="kicker">{{ $hero['subtitle'] }}</p>
                <h1 class="name">{{ $hero['name'] }}</h1>

                @if($hero['message'])
                    <p class="message" style="margin-top: 5mm;">{{ $hero['message'] }}</p>
                @endif

                @if($hero['date'] || $hero['time'] || $location)
                    <table class="bl-cells">
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
