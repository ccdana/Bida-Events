{{--
    «Cabina de fotos»: la tira de la cabina, sin fotos (el PDF no las lleva): tres cuadros oscuros con el
    nombre, la edad y el día, cada uno con el sello de la fecha en la esquina, y al pie el mensaje; debajo,
    el día, la hora y el lugar.
--}}
@php
    $boothDate = $invitation->event_date?->copy()->locale('es');
    $boothStamp = $boothDate ? $boothDate->format('d m')." '".$boothDate->format('y') : '';
    // Sin la fuente de títulos (el PDF solo usa fuentes estáticas), una sans gruesa, como el letrero de la cabina
    $boothFont = $fonts['titles'] ? $titleFont : "'DejaVu Sans', sans-serif";
    $boothWeight = $fonts['titles'] ? 'normal' : 'bold';
    $boothAge = preg_match('/\b(\d{1,3})\b/u', (string) $hero['subtitle'], $match) ? $match[1] : null;
@endphp
<style>
    .cb-strip { width: 92mm; margin: 5mm auto 0; border-collapse: separate; border-spacing: 0 3mm; padding: 0 3.5mm; border: 0.8pt solid {{ $colors['line'] }}; background: {{ $colors['paper'] }}; }
    .cb-frame { height: 36mm; padding: 0 4mm; background: {{ $colors['panel'] }}; color: {{ $colors['onPanel'] }}; text-align: center; vertical-align: middle; }
    .cb-frame p { margin: 0; }
    .cb-big { margin: 0; font-family: {!! $boothFont !!}; font-weight: {{ $boothWeight }}; font-size: {{ $hero['nameSize'] }}; line-height: 1.05; text-transform: uppercase; }
    .cb-small { font-size: 8pt; letter-spacing: 2.5pt; text-transform: uppercase; color: {{ $colors['onPanelSoft'] }}; }
    .cb-stamp { margin-top: 2mm !important; font-size: 7.5pt; letter-spacing: 1pt; text-align: right; color: {{ $colors['onPanelAccent'] }}; }
    .cb-foot { padding: 1mm 4mm 3mm; text-align: center; }
</style>

<div class="sheet is-none">
    <table class="cover-fill">
        <tr>
            <td class="fill-cell center">
                <p class="kicker">{{ $copy['booth_sign'] ?? 'Fotos' }} · {{ $hero['subtitle'] }}</p>

                <table class="cb-strip">
                    <tr>
                        <td class="cb-frame">
                            <p class="cb-small">{{ $copy['hero_eyebrow'] ?? '¡Celebremos juntos!' }}</p>
                            <h1 class="cb-big">{{ $hero['name'] }}</h1>
                            <p class="cb-stamp">{{ $boothStamp }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="cb-frame">
                            @if($boothAge)
                                <p class="cb-big" style="font-size: 44pt;">{{ $boothAge }}</p>
                                <p class="cb-small">{{ $copy['booth_age'] ?? 'años' }}</p>
                            @else
                                <p class="cb-big" style="font-size: 20pt;">{{ $hero['subtitle'] }}</p>
                            @endif
                            <p class="cb-stamp">{{ $boothStamp }}</p>
                        </td>
                    </tr>
                    @if($boothDate)
                        <tr>
                            <td class="cb-frame">
                                <p class="cb-big" style="font-size: 44pt;">{{ $boothDate->format('j') }}</p>
                                <p class="cb-small">{{ $boothDate->translatedFormat('F') }} · {{ $boothDate->format('H:i') }}</p>
                                <p class="cb-stamp">{{ $boothStamp }}</p>
                            </td>
                        </tr>
                    @endif
                    @if($hero['message'])
                        <tr>
                            <td class="cb-foot">
                                <p class="message" style="width: auto; font-size: 10pt;">{{ $hero['message'] }}</p>
                            </td>
                        </tr>
                    @endif
                </table>

                @include('client.exports.pdf.partials.when')
            </td>
        </tr>
    </table>
</div>

@include('client.exports.pdf.partials.rsvp')
