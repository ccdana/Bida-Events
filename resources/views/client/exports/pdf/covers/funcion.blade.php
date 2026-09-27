{{--
    «Función de medianoche»: el afiche de la película sobre la sala a oscuras. «Función de estreno»,
    el título con las luces de la marquesina alrededor, la clasificación y el bloque de créditos con
    el estreno, la función y la sala.
--}}
<style>
    .fn-night { background: {{ $colors['panel'] }}; padding: 10mm; }
    .fn-marquee { padding: 8mm 6mm; border: 1.6mm dotted {{ $colors['onPanelAccent'] }}; text-align: center; }
    .fn-marquee .kicker { color: {{ $colors['onPanelAccent'] }}; letter-spacing: 4pt; }
    .fn-marquee .name { margin: 4mm 0 0; color: {{ $colors['onPanelAccent'] }}; text-transform: uppercase; }
    .fn-tag { margin: 4mm 0 0; font-size: 12pt; text-transform: uppercase; color: {{ $colors['onPanel'] }}; }
    .fn-rating { display: inline-block; margin-top: 5mm; padding: 1.5mm 3mm; border: 0.8pt solid {{ $colors['onPanel'] }}; font-size: 8.5pt; color: {{ $colors['onPanel'] }}; }
    .fn-billing { width: 100%; margin-top: 7mm; border-collapse: collapse; border-top: 0.6pt solid {{ $colors['onPanelSoft'] }}; }
    .fn-billing td { padding: 3mm 2mm 0; text-align: center; color: {{ $colors['onPanel'] }}; }
    .fn-billing .label { color: {{ $colors['onPanelSoft'] }}; }
    .fn-billing .value { color: {{ $colors['onPanel'] }}; text-transform: uppercase; }
    .fn-night .message { margin-top: 6mm; color: {{ $colors['onPanel'] }}; }
</style>

<div class="sheet is-none">
    <table class="cover-fill">
        <tr>
            <td class="fill-cell fn-night">
                <div class="fn-marquee">
                    <p class="kicker">{{ $copy['intro_eyebrow'] ?? 'Función de estreno' }}</p>
                    <h1 class="name">{{ $hero['name'] }}</h1>
                    <p class="fn-tag">{{ $hero['subtitle'] }}</p>
                    <p class="fn-rating">{{ $copy['film_rating'] ?? 'Clasificación A' }} · {{ $copy['film_rating_text'] ?? 'Apta para valientes de todas las edades' }}</p>

                    @if($hero['message'])
                        <p class="message">{{ $hero['message'] }}</p>
                    @endif

                    <table class="fn-billing">
                        <tr>
                            @if($hero['date'])
                                <td><p class="label">{{ $copy['film_premiere'] ?? 'Estreno' }}</p><p class="value">{{ $hero['date'] }}</p></td>
                            @endif
                            @if($hero['time'])
                                <td><p class="label">{{ $copy['film_show'] ?? 'Función' }}</p><p class="value">{{ $hero['time'] }}</p></td>
                            @endif
                            @if($location)
                                <td><p class="label">{{ $copy['film_theater'] ?? 'Sala' }}</p><p class="value">{{ $location['name'] ?? $location['address'] }}</p></td>
                            @endif
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>
</div>

@include('client.exports.pdf.partials.rsvp')
