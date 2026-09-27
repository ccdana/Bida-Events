{{--
    «Gira mundial»: el afiche de la gira. «En concierto», el nombre en mayúsculas, la gira y la lista
    de fechas: las ciudades tachadas y canceladas, y la fiesta resaltada como fecha única.
--}}
@php
    $tourCities = array_values(array_filter(array_map('trim', explode('·', (string) ($copy['tour_cities'] ?? 'Tokio · París · Nueva York · Londres')))));
@endphp
<style>
    .gr-presents { display: inline-block; margin: 0; padding: 1.5mm 3mm; border: 1.2pt solid {{ $colors['ink'] }}; font-size: 8.5pt; letter-spacing: 3pt; text-transform: uppercase; }
    .gr-name { margin: 5mm 0 0; font-family: {!! $titleFont ?? 'serif' !!}; font-size: {{ $hero['nameSize'] }}; font-weight: bold; text-transform: uppercase; color: {{ $colors['ink'] }}; }
    .gr-tour { display: inline-block; margin: 3mm 0 0; padding: 1mm 3mm; background: {{ $colors['ink'] }}; color: {{ $colors['paper'] }}; font-size: 10pt; letter-spacing: 3pt; text-transform: uppercase; }
    .gr-dates { width: 140mm; margin: 9mm auto 0; border-collapse: collapse; border-top: 1.4pt solid {{ $colors['ink'] }}; }
    .gr-dates td { padding: 2.2mm 1mm; border-bottom: 0.5pt solid {{ $colors['line'] }}; font-size: 10.5pt; text-transform: uppercase; text-align: left; }
    .gr-dates .off { color: {{ $colors['muted'] }}; text-decoration: line-through; }
    .gr-dates .state { text-align: right; font-size: 8pt; letter-spacing: 1.5pt; color: {{ $colors['primary'] }}; }
    .gr-dates tr.on td { background: {{ $colors['tint'] }}; font-weight: bold; font-size: 12pt; }
</style>

<div class="sheet is-block" style="padding: 10mm;">
    <table class="cover-fill" style="height: {{ max(120, $cover['fill'] - 20) }}mm;">
        <tr>
            <td class="fill-cell center">
                <p class="gr-presents">{{ $copy['tour_presents'] ?? 'En concierto' }}</p>
                <h1 class="gr-name">{{ $hero['name'] }}</h1>
                <p class="gr-tour">{{ $copy['tour_name'] ?? 'Gira' }} {{ $invitation->event_date?->format('Y') }}</p>

                <table class="gr-dates">
                    @foreach($tourCities as $city)
                        <tr>
                            <td class="off">{{ $city }}</td>
                            <td class="state">{{ $copy['tour_cancelled'] ?? 'Cancelado' }}</td>
                        </tr>
                    @endforeach
                    <tr class="on">
                        <td>{{ $location['name'] ?? $hero['date'] }}</td>
                        <td class="state">{{ $copy['tour_only'] ?? 'Fecha única' }}</td>
                    </tr>
                </table>

                @if($hero['message'])
                    <p class="message" style="margin-top: 7mm;">{{ $hero['message'] }}</p>
                @endif

                @include('client.exports.pdf.partials.when')
            </td>
        </tr>
    </table>
</div>

@include('client.exports.pdf.partials.rsvp')
