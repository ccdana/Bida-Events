@php
    $c = $colors;
    $fontPath = fn (?string $path) => $path ? str_replace('\\', '/', $path) : null;
    $titleFont = ($fonts['titles'] ? "'InvTitles', " : '')."'DejaVu Serif', serif";
    $scriptFont = ($fonts['script'] ? "'InvScript', " : ($fonts['titles'] ? "'InvTitles', " : ''))."'DejaVu Serif', serif";
    $hasDetails = $location || $itinerary || $dressCode || $honor || $gifts;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $hero['name'] }}</title>
    <style>
        @if($fonts['titles'])
            @font-face { font-family: 'InvTitles'; font-style: normal; font-weight: normal; src: url('{{ $fontPath($fonts['titles']) }}') format('truetype'); }
        @endif
        @if($fonts['script'])
            @font-face { font-family: 'InvScript'; font-style: normal; font-weight: normal; src: url('{{ $fontPath($fonts['script']) }}') format('truetype'); }
        @endif
        @page { margin: 14mm 15mm 18mm; }
        body { margin: 0; font-family: 'DejaVu Sans', sans-serif; font-size: 9.5pt; line-height: 1.55; color: {{ $c['body'] }}; }
        p { margin: 0 0 1.5mm; }
        footer { position: fixed; bottom: -12mm; left: 0; right: 0; text-align: center; font-size: 7.5pt; color: {{ $c['muted'] }}; }
        footer img { height: 4.5mm; margin-right: 1.5mm; vertical-align: middle; }
        .titles { font-family: {!! $titleFont !!}; font-weight: normal; }

        .cover { background: {{ $c['paper'] }}; border: 0.6pt solid {{ $c['line'] }}; }
        .cover-photo { display: block; width: 100%; }
        .cover-body { padding: 9mm 12mm 8mm; text-align: center; }
        .kicker { margin: 0; font-size: 8.5pt; letter-spacing: 2.5pt; text-transform: uppercase; color: {{ $c['accent'] }}; }
        .name { margin: 2mm 0 0; font-family: {!! $scriptFont !!}; font-weight: normal; @unless($fonts['script']) font-style: italic; @endunless font-size: 38pt; line-height: 1.2; color: {{ $c['ink'] }}; }
        .ornament { width: 56mm; margin: 4mm auto 5mm; border-collapse: collapse; }
        .ornament td { padding: 0; }
        .ornament .rule { height: 1.2mm; border-bottom: 0.8pt solid {{ $c['primary'] }}; }
        .ornament .center { width: 7mm; text-align: center; }
        .ornament .dot { display: inline-block; width: 2mm; height: 2mm; border-radius: 1mm; background: {{ $c['primary'] }}; }
        .message { width: 132mm; margin: 0 auto; font-family: {!! $titleFont !!}; font-size: 12pt; line-height: 1.6; color: {{ $c['body'] }}; }
        .when { width: 100%; margin-top: 7mm; border-collapse: collapse; border-top: 0.6pt solid {{ $c['line'] }}; border-bottom: 0.6pt solid {{ $c['line'] }}; }
        .when td { padding: 4mm 3mm; text-align: center; vertical-align: top; }
        .when td.sep { border-left: 0.6pt solid {{ $c['line'] }}; }
        .label { margin: 0; font-size: 7.5pt; letter-spacing: 1.5pt; text-transform: uppercase; color: {{ $c['muted'] }}; }
        .value { margin: 1mm 0 0; font-family: {!! $titleFont !!}; font-size: 12pt; line-height: 1.35; color: {{ $c['ink'] }}; }

        .strip { width: 100%; margin-top: 5mm; border-collapse: collapse; background: {{ $c['soft'] }}; }
        .strip td { padding: 4mm 5mm; vertical-align: middle; }
        .strip .qr-cell { width: 34mm; }
        .qr { width: 28mm; height: 28mm; }
        .strip-title { margin: 0 0 1mm; font-family: {!! $titleFont !!}; font-size: 13pt; color: {{ $c['ink'] }}; }
        .url { margin: 1.5mm 0 0; font-size: 8pt; color: {{ $c['accent'] }}; }

        .page-break { page-break-before: always; }
        .details-title { margin: 1mm 0 6mm; font-family: {!! $titleFont !!}; font-size: 20pt; font-weight: normal; color: {{ $c['ink'] }}; }
        /* Los bloques pueden seguir en la hoja siguiente para no dejar espacios vacíos; títulos y filas no se cortan */
        .block { margin-bottom: 7mm; }
        .block-title { margin: 0 0 3mm; padding-bottom: 2mm; border-bottom: 0.8pt solid {{ $c['primary'] }}; font-family: {!! $titleFont !!}; font-size: 13pt; font-weight: normal; color: {{ $c['ink'] }}; page-break-after: avoid; }
        tr, .hashtag { page-break-inside: avoid; }
        .strong { font-weight: bold; color: {{ $c['ink'] }}; }
        .muted { color: {{ $c['muted'] }}; }
        .lead { font-family: {!! $titleFont !!}; font-size: 12pt; color: {{ $c['ink'] }}; }
        .note { margin-top: 2mm; padding: 2mm 3mm; background: {{ $c['soft'] }}; }
        .split { width: 100%; border-collapse: collapse; }
        .split td { vertical-align: top; }
        .qr-side { width: 36mm; padding-left: 6mm; text-align: center; }
        .qr-small { width: 28mm; height: 28mm; }
        .caption { margin-top: 1.5mm; font-size: 7.5pt; color: {{ $c['muted'] }}; }
        .timeline { width: 100%; border-collapse: collapse; }
        .timeline td { padding: 1.6mm 0; border-bottom: 0.5pt solid {{ $c['line'] }}; vertical-align: top; }
        .timeline p { margin: 0; }
        .timeline .time { width: 20mm; font-weight: bold; color: {{ $c['accent'] }}; }
        .swatches { margin-top: 2mm; border-collapse: collapse; }
        .swatches td { padding: 1mm 5mm 1mm 0; font-size: 8.5pt; }
        .swatch { display: inline-block; width: 5mm; height: 5mm; margin-right: 1.5mm; border: 0.5pt solid {{ $c['line'] }}; border-radius: 2.5mm; vertical-align: middle; }
        .cols { width: 100%; margin-top: 3mm; border-collapse: collapse; }
        .cols td { width: 33.33%; padding: 0 5mm 3mm 0; vertical-align: top; }
        .pairs { width: 100%; border-collapse: collapse; }
        .pairs td { padding: 1.6mm 0; border-bottom: 0.5pt solid {{ $c['line'] }}; vertical-align: top; }
        .pairs .pair-label { width: 42mm; color: {{ $c['muted'] }}; }
        .hashtag { padding: 5mm; background: {{ $c['soft'] }}; text-align: center; }
        .hashtag-text { margin: 1mm 0 0; font-family: {!! $titleFont !!}; font-size: 20pt; color: {{ $c['accent'] }}; }
    </style>
</head>
<body>
    <footer><img src="{{ $logo }}" alt="">Invitación creada con {{ config('bida.brand') }}</footer>

    {{-- Página 1: la invitación --}}
    <div class="cover">
        @if($hero['photo'])
            <img class="cover-photo" src="{{ $hero['photo'] }}" alt="">
        @endif
        <div class="cover-body">
            @if($hero['subtitle'])
                <p class="kicker">{{ $hero['subtitle'] }}</p>
            @endif
            <h1 class="name">{{ $hero['name'] }}</h1>
            <table class="ornament">
                <tr>
                    <td class="rule"></td>
                    <td class="center"><span class="dot"></span></td>
                    <td class="rule"></td>
                </tr>
            </table>
            @if($hero['message'])
                <p class="message">{{ $hero['message'] }}</p>
            @endif

            <table class="when">
                <tr>
                    @if($hero['date'])
                        <td>
                            <p class="label">Fecha</p>
                            <p class="value">{{ $hero['date'] }}</p>
                        </td>
                    @endif
                    @if($hero['time'])
                        <td @class(['sep' => $hero['date']])>
                            <p class="label">Hora</p>
                            <p class="value">{{ $hero['time'] }}</p>
                        </td>
                    @endif
                    @if($location)
                        <td class="sep">
                            <p class="label">Lugar</p>
                            <p class="value">{{ $location['name'] ?? $location['address'] }}</p>
                        </td>
                    @endif
                </tr>
            </table>
        </div>
    </div>

    <table class="strip">
        <tr>
            @if($rsvp['qr'])
                <td class="qr-cell"><img class="qr" src="{{ $rsvp['qr'] }}" alt=""></td>
            @endif
            <td>
                <p class="strip-title">{{ $rsvp['title'] }}</p>
                @if($rsvp['message'])
                    <p>{{ $rsvp['message'] }}</p>
                @endif
                <p class="muted">Escanea el código para ver la invitación digital{{ $rsvp['enabled'] ? ' y confirmar tu asistencia' : '' }}.</p>
                <p class="url">{{ $rsvp['url'] }}</p>
                @if($hashtag)
                    <p class="muted" style="margin-top: 2mm;">Comparte tus fotos con <span class="strong">{{ $hashtag }}</span></p>
                @endif
            </td>
        </tr>
    </table>

    {{-- Página 2: detalles para los invitados --}}
    @if($hasDetails)
        <div class="page-break"></div>
        @if($hero['subtitle'])
            <p class="kicker">{{ $hero['subtitle'] }}</p>
        @endif
        <h2 class="details-title">Detalles del evento</h2>

        @if($location)
            <div class="block">
                <h3 class="block-title">Ubicación</h3>
                <table class="split">
                    <tr>
                        <td>
                            @if($location['name'])
                                <p class="lead">{{ $location['name'] }}</p>
                            @endif
                            @if($location['address'])
                                <p>{{ $location['address'] }}</p>
                            @endif
                            @if($location['note'])
                                <p class="note">{{ $location['note'] }}</p>
                            @endif
                        </td>
                        @if($location['qr'])
                            <td class="qr-side">
                                <img class="qr-small" src="{{ $location['qr'] }}" alt="">
                                <p class="caption">Escanea para llegar</p>
                            </td>
                        @endif
                    </tr>
                </table>
            </div>
        @endif

        @if($itinerary)
            <div class="block">
                <h3 class="block-title">Itinerario</h3>
                <table class="timeline">
                    @foreach($itinerary as $item)
                        <tr>
                            <td class="time">{{ $item['time'] }}</td>
                            <td>
                                <p class="strong">{{ $item['title'] }}</p>
                                @if($item['description'])
                                    <p class="muted">{{ $item['description'] }}</p>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif

        {{-- Los bloques se ordenan para llenar las hojas: padrinos junto al itinerario, vestimenta y regalos en la siguiente --}}
        @if($honor)
            <div class="block">
                <h3 class="block-title">Padrinos y cortejo</h3>
                @if($honor['godparents'])
                    <table class="pairs">
                        @foreach($honor['godparents'] as $godparent)
                            <tr>
                                <td class="pair-label">{{ $godparent['role'] ?? 'Padrinos' }}</td>
                                <td class="strong">{{ $godparent['names'] }}</td>
                            </tr>
                        @endforeach
                    </table>
                @endif
                @if($honor['chambelanes'])
                    <p style="margin-top: 3mm;"><span class="strong">Chambelanes:</span> {{ implode(', ', $honor['chambelanes']) }}</p>
                @endif
                @if($honor['damitas'])
                    <p><span class="strong">Damitas:</span> {{ implode(', ', $honor['damitas']) }}</p>
                @endif
            </div>
        @endif

        @if($dressCode)
            <div class="block">
                <h3 class="block-title">Código de vestimenta</h3>
                @if($dressCode['style'])
                    <p class="lead">{{ $dressCode['style'] }}</p>
                @endif
                @if($dressCode['description'])
                    <p>{{ $dressCode['description'] }}</p>
                @endif
                @foreach(array_chunk($dressCode['colors'], 5) as $colorRow)
                    <table class="swatches">
                        <tr>
                            @foreach($colorRow as $color)
                                <td><span class="swatch" style="background: {{ $color['hex'] }};"></span>{{ $color['name'] }}</td>
                            @endforeach
                        </tr>
                    </table>
                @endforeach
                @foreach(array_chunk($dressCode['suggestions'], 3) as $suggestionRow)
                    <table class="cols">
                        <tr>
                            @foreach($suggestionRow as $suggestion)
                                <td>
                                    @if($suggestion['for'])
                                        <p class="label">{{ $suggestion['for'] }}</p>
                                    @endif
                                    <p class="strong">{{ $suggestion['title'] }}</p>
                                    @if($suggestion['description'])
                                        <p class="muted">{{ $suggestion['description'] }}</p>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    </table>
                @endforeach
                @if($dressCode['avoid'])
                    <p><span class="strong">Evitar:</span> {{ implode(', ', $dressCode['avoid']) }}.</p>
                @endif
            </div>
        @endif

        @if($gifts)
            <div class="block">
                <h3 class="block-title">{{ $gifts['title'] }}</h3>
                <table class="split">
                    <tr>
                        <td>
                            @if($gifts['envelopes'])
                                <p class="strong">{{ $gifts['envelopes']['title'] }}</p>
                                <p>{{ $gifts['envelopes']['address'] }}</p>
                            @endif
                            @if($gifts['bank'])
                                <p class="strong" style="margin-top: 3mm;">Transferencia bancaria</p>
                                <table class="pairs">
                                    @foreach($gifts['bank'] as $label => $value)
                                        <tr>
                                            <td class="pair-label">{{ $label }}</td>
                                            <td>{{ $value }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            @endif
                            @if($gifts['store'])
                                <p style="margin-top: 3mm;"><span class="strong">{{ $gifts['store']['label'] }}:</span> {{ $gifts['store']['url'] }}</p>
                            @endif
                            @foreach($gifts['options'] as $option)
                                <p><span class="strong">{{ $option['title'] }}</span>@if($option['description']): {{ $option['description'] }}@endif</p>
                            @endforeach
                        </td>
                        @if($gifts['bankQr'])
                            <td class="qr-side">
                                <img class="qr-small" src="{{ $gifts['bankQr'] }}" alt="">
                                <p class="caption">QR para transferencias</p>
                            </td>
                        @endif
                    </tr>
                </table>
            </div>
        @endif
    @endif
</body>
</html>
