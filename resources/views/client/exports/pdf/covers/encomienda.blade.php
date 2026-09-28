{{--
    «Encomienda especial»: la guía del envío. El título y el número de guía, el código de barras,
    el contenido (su nombre), los sellos de «frágil» y «con mucho amor», y las casillas con cuándo
    llega, a qué hora y dónde se entrega.
--}}
@php
    $code = 'BB-'.preg_replace('/\D/', '', (string) ($hero['date'] ?? '')).'-'.strtoupper(substr(md5((string) $hero['name']), 0, 4));
    $seed = crc32((string) $hero['name']);
    $bars = '';
    $x = 0;
    for ($bar = 0; $bar < 60; $bar++) {
        $seed = ($seed * 1103515245 + 12345) & 0x7fffffff;
        $width = 1 + $seed % 3;
        $bars .= sprintf('<rect x="%d" y="0" width="%d" height="40" fill="%s"/>', $x, $width, $colors['body']);
        $x += $width + 1 + intdiv($seed, 7) % 2;
    }
    $barcode = 'data:image/svg+xml;base64,'.base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$x.' 40" width="'.$x.'" height="40" preserveAspectRatio="none">'.$bars.'</svg>');
    $stamp = function (string $text, int $angle, bool $round) use ($colors): string {
        $text = htmlspecialchars(mb_strtoupper($text), ENT_QUOTES);
        $width = 22 + mb_strlen($text) * 13;
        $radius = $round ? 26 : 4;

        return 'data:image/svg+xml;base64,'.base64_encode(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.($width + 40).' 90" width="'.($width + 40).'" height="90">'
            .'<g transform="rotate('.$angle.' '.(($width + 40) / 2).' 45)">'
            .'<rect x="20" y="22" width="'.$width.'" height="46" rx="'.$radius.'" fill="none" stroke="'.$colors['accent'].'" stroke-width="3"/>'
            .'<rect x="25" y="27" width="'.($width - 10).'" height="36" rx="'.max(2, $radius - 5).'" fill="none" stroke="'.$colors['accent'].'" stroke-width="1.5"/>'
            .'<text x="'.(($width + 40) / 2).'" y="53" text-anchor="middle" font-family="monospace" font-weight="bold" font-size="20" fill="'.$colors['accent'].'">'.$text.'</text>'
            .'</g></svg>'
        );
    };
@endphp
<style>
    .en-bill { border: 1pt solid {{ $colors['line'] }}; padding: 7mm 8mm; background: {{ $colors['paper'] }}; }
    .en-head { width: 100%; border-collapse: collapse; border-bottom: 1.4pt solid {{ $colors['body'] }}; }
    .en-head td { padding: 0 0 2mm; font-family: 'Courier', monospace; }
    .en-title { font-size: 14pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1pt; }
    .en-code { text-align: right; font-size: 8pt; color: {{ $colors['muted'] }}; }
    .en-bars { width: 100%; height: 12mm; margin-top: 3mm; }
    .en-stamps { width: 100%; margin-top: 4mm; border-collapse: collapse; }
    .en-stamps td { text-align: center; }
    .en-stamps img { height: 16mm; }
    .en-content { margin-top: 3mm; font-family: 'Courier', monospace; font-size: 8.5pt; letter-spacing: 2pt; text-transform: uppercase; color: {{ $colors['muted'] }}; text-align: center; }
    .en-bill .name { margin: 1mm 0 0; text-align: center; }
    .en-grid { width: 100%; margin-top: 6mm; border-collapse: collapse; border: 1.4pt solid {{ $colors['body'] }}; }
    .en-grid td { padding: 2.5mm 3mm; border: 0.8pt solid {{ $colors['body'] }}; vertical-align: top; }
    .en-grid .label { font-family: 'Courier', monospace; }
</style>

<div class="sheet is-none">
    <table class="cover-fill">
        <tr>
            <td class="fill-cell">
                <div class="en-bill">
                    <table class="en-head">
                        <tr>
                            <td class="en-title">{{ $copy['parcel_title'] ?? 'Encomienda especial' }}</td>
                            <td class="en-code">N.º {{ $code }}</td>
                        </tr>
                    </table>
                    <img class="en-bars" src="{{ $barcode }}" alt="">

                    <table class="en-stamps">
                        <tr>
                            <td><img src="{{ $stamp($copy['parcel_fragile'] ?? 'Frágil', -8, false) }}" alt=""></td>
                            <td><img src="{{ $stamp($copy['parcel_care'] ?? 'Con mucho amor', 6, true) }}" alt=""></td>
                        </tr>
                    </table>

                    <p class="en-content">{{ $copy['parcel_content'] ?? 'Contenido' }}</p>
                    <h1 class="name">{{ $hero['name'] }}</h1>

                    @if($hero['message'])
                        <p class="message" style="margin-top: 4mm; text-align: center;">{{ $hero['message'] }}</p>
                    @endif

                    @if($hero['date'] || $hero['time'] || $location)
                        <table class="en-grid">
                            <tr>
                                @if($hero['date'])
                                    <td><p class="label">{{ $copy['parcel_arrival'] ?? 'Llega' }}</p><p class="value">{{ $hero['date'] }}</p></td>
                                @endif
                                @if($hero['time'])
                                    <td><p class="label">Hora</p><p class="value">{{ $hero['time'] }}</p></td>
                                @endif
                            </tr>
                            @if($location)
                                <tr>
                                    <td colspan="2"><p class="label">{{ $copy['parcel_address'] ?? 'Entrega en' }}</p><p class="value">{{ $location['name'] ?? $location['address'] }}</p></td>
                                </tr>
                            @endif
                        </table>
                    @endif
                </div>
            </td>
        </tr>
    </table>
</div>

@include('client.exports.pdf.partials.rsvp')
