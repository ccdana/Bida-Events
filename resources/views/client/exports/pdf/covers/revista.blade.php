{{--
    «Edición especial»: la portada de la revista. La línea de la edición, el nombre de la revista a
    todo lo ancho, el recuadro de la tapa con el año y el nombre del graduado como estrella, y los
    titulares con los datos de la fiesta.
--}}
<style>
    .rv-issue { width: 100%; border-collapse: collapse; border-bottom: 0.6pt solid {{ $colors['ink'] }}; }
    .rv-issue td { padding-bottom: 1.5mm; font-size: 7.5pt; letter-spacing: 1.5pt; text-transform: uppercase; }
    .rv-mast { margin: 2mm 0 0; font-family: {!! $titleFont ?? 'serif' !!}; font-size: 48pt; font-weight: bold; line-height: 1; text-transform: uppercase; color: {{ $colors['primary'] }}; }
    .rv-cover { margin-top: 4mm; padding: 10mm 8mm; background: {{ $colors['primary'] }}; color: {{ $colors['paper'] }}; text-align: left; }
    .rv-cover .year { margin: 0; font-size: 60pt; font-weight: bold; line-height: 1; }
    .rv-cover .kicker { color: {{ $colors['paper'] }}; }
    .rv-cover .name { margin: 3mm 0 0; color: {{ $colors['paper'] }}; }
    .rv-lines { margin-top: 6mm; text-align: left; }
    .rv-lines p { margin: 0 0 2mm; font-size: 11pt; font-weight: bold; }
    .rv-lines span { color: {{ $colors['primary'] }}; }
</style>

<div class="sheet is-none" style="padding: 9mm 11mm;">
    <table class="rv-issue">
        <tr>
            <td>{{ $copy['mag_issue'] ?? 'N.º 1 · Edición especial' }}</td>
            <td style="text-align: right;">{{ $invitation->event_date?->locale('es')->translatedFormat('F Y') }}</td>
        </tr>
    </table>
    <p class="rv-mast">{{ $copy['mag_name'] ?? 'Promoción' }}</p>

    <div class="rv-cover">
        <p class="year">{{ $invitation->event_date?->format('Y') }}</p>
        <p class="kicker">{{ $copy['mag_exclusive'] ?? 'Exclusiva' }} · {{ $hero['subtitle'] }}</p>
        <h1 class="name">{{ $hero['name'] }}</h1>
    </div>

    <div class="rv-lines">
        @if($hero['date'] || $hero['time'])
            <p><span>▸</span> {{ $copy['mag_party'] ?? 'La fiesta del año' }}: {{ trim(implode(' · ', array_filter([$hero['date'], $hero['time']]))) }}</p>
        @endif
        @if($location)
            <p><span>▸</span> {{ $copy['mag_where'] ?? 'Dónde será' }}: {{ $location['name'] ?? $location['address'] }}</p>
        @endif
    </div>

    @if($hero['message'])
        <p class="message" style="width: auto; margin-top: 5mm; text-align: left;">{{ $hero['message'] }}</p>
    @endif
</div>

@include('client.exports.pdf.partials.rsvp')
