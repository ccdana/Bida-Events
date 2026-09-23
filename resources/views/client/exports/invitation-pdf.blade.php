@php
    /**
     * Invitación impresa, en dos hojas: la portada de la plantilla (primera) y los detalles
     * del evento (segunda). Cada plantilla trae su propia portada en pdf/covers; lo común
     * (cuándo y dónde, el QR, los detalles) se comparte desde pdf/partials.
     */
    $c = $colors;
    $fontPath = fn (?string $path) => $path ? str_replace('\\', '/', $path) : null;
    $titleFont = ($fonts['titles'] ? "'InvTitles', " : '')."'DejaVu Serif', serif";
    $scriptFont = ($fonts['script'] ? "'InvScript', " : ($fonts['titles'] ? "'InvTitles', " : ''))."'DejaVu Serif', serif";
    $hasDetails = $location || $itinerary || $dressCode || $honor || $gifts || $hashtag;
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
        @page { margin: 12mm 12mm 16mm; }
        body { margin: 0; font-family: 'DejaVu Sans', sans-serif; font-size: 9.5pt; line-height: 1.55; color: {{ $c['body'] }}; }
        p { margin: 0 0 1.5mm; }
        img { display: block; }
        footer { position: fixed; bottom: -11mm; left: 0; right: 0; text-align: center; font-size: 7.5pt; color: {{ $c['muted'] }}; }
        footer img { display: inline; height: 4.5mm; margin-right: 1.5mm; vertical-align: middle; }

        .titles { font-family: {!! $titleFont !!}; font-weight: normal; }
        .script { font-family: {!! $scriptFont !!}; font-weight: normal; @unless($fonts['script']) font-style: italic; @endunless }

        /* Marco de la hoja: cada plantilla elige el suyo en PdfTemplateStyle */
        .sheet { background: {{ $c['paper'] }}; }
        .sheet.is-double { border: 3pt double {{ $c['primary'] }}; padding: 7mm; }
        .sheet.is-thin { border: 0.8pt solid {{ $c['line'] }}; padding: 8mm; }
        .sheet.is-dashed { border: 1.2pt dashed {{ $c['line'] }}; padding: 7mm; }
        .sheet.is-block, .sheet.is-none { border: 0; padding: 0; }

        /* La invitación ocupa la hoja entera, como una tarjeta impresa */
        .cover-fill { width: 100%; height: {{ $cover['fill'] }}mm; border-collapse: collapse; }
        .cover-fill .fill-cell { vertical-align: middle; }
        .is-tight .message { font-size: 10.5pt; line-height: 1.5; }
        .is-tight .when td { padding: 3mm 2mm; }
        .is-tight .strip td { padding: 3mm 4mm; }
        .is-tight .qr { width: 24mm; height: 24mm; }

        .center { text-align: center; }
        .kicker { margin: 0; font-size: 8.5pt; letter-spacing: 2.5pt; text-transform: uppercase; color: {{ $c['accent'] }}; }
        .name { margin: 2mm 0 0; font-family: {!! $scriptFont !!}; @unless($fonts['script']) font-style: italic; @endunless font-size: {{ $hero['nameSize'] }}; line-height: 1.18; color: {{ $c['ink'] }}; }
        .motif { width: 62mm; margin: 4mm auto 4mm; }
        .message { width: 128mm; margin: 0 auto; font-family: {!! $titleFont !!}; font-size: 11.5pt; line-height: 1.6; }

        /* Cuándo y dónde: lo que el invitado busca primero */
        .when { width: 100%; margin-top: 6mm; border-collapse: collapse; border-top: 0.6pt solid {{ $c['line'] }}; border-bottom: 0.6pt solid {{ $c['line'] }}; }
        .when td { padding: 4mm 3mm; text-align: center; vertical-align: top; }
        .when td.sep { border-left: 0.6pt solid {{ $c['line'] }}; }
        .label { margin: 0; font-size: 7.5pt; letter-spacing: 1.5pt; text-transform: uppercase; color: {{ $c['muted'] }}; }
        .value { margin: 1mm 0 0; font-family: {!! $titleFont !!}; font-size: 12pt; line-height: 1.35; color: {{ $c['ink'] }}; }

        .strip { width: 100%; margin-top: 5mm; border-collapse: collapse; background: {{ $c['soft'] }}; }
        .strip td { padding: 4mm 5mm; vertical-align: middle; }
        .strip .qr-cell { width: 32mm; }
        .qr { width: 27mm; height: 27mm; }
        .strip-title { margin: 0 0 1mm; font-family: {!! $titleFont !!}; font-size: 13pt; color: {{ $c['ink'] }}; }
        .url { margin: 1.5mm 0 0; font-size: 8pt; color: {{ $c['accent'] }}; }
        .muted { color: {{ $c['muted'] }}; }
        .strong { font-weight: bold; color: {{ $c['ink'] }}; }

        /* Segunda hoja */
        .page-break { page-break-before: always; }
        .details-head { width: 100%; border-collapse: collapse; border-bottom: 0.8pt solid {{ $c['primary'] }}; }
        .details-head td { padding-bottom: 2.5mm; vertical-align: bottom; }
        .details-title { margin: 0; font-family: {!! $titleFont !!}; font-size: 17pt; font-weight: normal; color: {{ $c['ink'] }}; }
        .details-motif { width: 30mm; }
        .details-cols { width: 100%; margin-top: 2mm; border-collapse: collapse; table-layout: fixed; }
        .details-cols td { width: 50%; padding-right: 6mm; vertical-align: top; word-wrap: break-word; font-size: 8.5pt; line-height: 1.42; }
        .details-cols td.col-right { padding-right: 0; padding-left: 6mm; border-left: 0.5pt solid {{ $c['line'] }}; }
        /* Hoja 2 apretada: cuando hay mucho que contar, antes que soltar un bloque se achica la letra */
        .details-cols.is-dense td { font-size: 7.8pt; line-height: 1.34; }
        .is-dense .block { margin-top: 4mm; }
        .is-dense .block-title { font-size: 11pt; }
        .is-dense .lead { font-size: 10pt; }
        .is-dense .qr-small { width: 18mm; height: 18mm; }
        .details-foot { margin-top: 5mm; padding-top: 2.5mm; border-top: 0.6pt solid {{ $c['line'] }}; text-align: center; font-size: 8.5pt; color: {{ $c['muted'] }}; }
        .block { margin-top: 5mm; }
        .block-title { margin: 0 0 2mm; font-family: {!! $titleFont !!}; font-size: 12pt; font-weight: normal; color: {{ $c['accent'] }}; page-break-after: avoid; }
        .timeline tr, .pairs tr, .swatches tr, .hashtag { page-break-inside: avoid; }
        .lead { font-family: {!! $titleFont !!}; font-size: 11pt; color: {{ $c['ink'] }}; }
        .note { margin-top: 2mm; padding: 2mm 3mm; background: {{ $c['tint'] }}; }
        .qr-small { width: 21mm; height: 21mm; }
        .caption { margin-top: 1.5mm; font-size: 7.5pt; color: {{ $c['muted'] }}; }
        .timeline { width: 100%; border-collapse: collapse; }
        .timeline td { padding: 1.2mm 0; border-bottom: 0.5pt solid {{ $c['line'] }}; vertical-align: top; }
        .timeline p { margin: 0; }
        .timeline .time { width: 19mm; font-weight: bold; color: {{ $c['accent'] }}; }
        .swatches { margin-top: 2mm; border-collapse: collapse; }
        .swatches td { padding: 1mm 5mm 1mm 0; font-size: 8.5pt; }
        .swatch { display: inline-block; width: 5mm; height: 5mm; margin-right: 1.5mm; border: 0.5pt solid {{ $c['line'] }}; vertical-align: middle; }
        .pairs { width: 100%; border-collapse: collapse; }
        .pairs td { padding: 1.2mm 0; border-bottom: 0.5pt solid {{ $c['line'] }}; vertical-align: top; }
        .pairs .pair-label { width: 40mm; color: {{ $c['muted'] }}; }
        .hashtag { margin-top: 5mm; padding: 3.5mm; background: {{ $c['tint'] }}; text-align: center; }
        .hashtag-text { margin: 1mm 0 0; font-family: {!! $titleFont !!}; font-size: 16pt; color: {{ $c['accent'] }}; }
    </style>
</head>
<body @class(['is-tight' => $cover['tight']])>
    <footer><img src="{{ $logo }}" alt="">{{ $style['label'] }} · Invitación creada con {{ config('bida.brand') }}</footer>

    {{-- Hoja 1: la invitación, con la portada de su plantilla --}}
    @include($style['view'])

    {{-- Hoja 2: los complementos --}}
    @if($hasDetails)
        <div class="page-break"></div>
        @include('client.exports.pdf.details')
    @endif
</body>
</html>
