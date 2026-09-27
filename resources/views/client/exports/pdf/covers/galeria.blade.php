{{--
    «Galería Quince»: la pared de la exposición. El rótulo de la muestra, el nombre dentro de un
    marco con paspartú (en papel no va la foto) y, debajo, la cédula del museo con la inauguración.
--}}
<style>
    .gq-kicker { margin: 0; font-size: 8pt; letter-spacing: 4pt; text-transform: uppercase; color: {{ $colors['accent'] }}; }
    .gq-frame { width: 118mm; margin: 7mm auto 0; padding: 3mm; border: 2.4mm solid {{ $colors['line'] }}; }
    .gq-mat { padding: 12mm 8mm; border: 0.6pt solid {{ $colors['line'] }}; background: {{ $colors['tint'] }}; }
    .gq-mat .name { margin: 0; }
    .gq-mat .subtitle { margin: 3mm 0 0; font-style: italic; font-size: 12pt; }
    .gq-label { width: 92mm; margin: 8mm auto 0; padding: 4mm 5mm; border-left: 1.4mm solid {{ $colors['primary'] }}; background: {{ $colors['tint'] }}; text-align: left; }
    .gq-label .title { margin: 0; font-weight: bold; font-size: 10.5pt; }
    .gq-label .work { margin: 1mm 0 3mm; font-style: italic; font-size: 10pt; }
    .gq-label .label { margin-top: 2mm; }
    .gq-label .value { font-size: 11pt; }
</style>

<div class="sheet is-thin">
    <table class="cover-fill">
        <tr>
            <td class="fill-cell center">
                <p class="gq-kicker">{{ $copy['exhibit_label'] ?? 'Exposición' }}</p>
                <div class="gq-frame">
                    <div class="gq-mat">
                        <h1 class="name">{{ $hero['name'] }}</h1>
                        <p class="subtitle">{{ $hero['subtitle'] }}</p>
                    </div>
                </div>

                <div class="gq-label">
                    <p class="title">{{ $hero['name'] }}</p>
                    <p class="work">{{ $copy['exhibit_title'] ?? 'Quince' }} · {{ $copy['exhibit_piece'] ?? 'Retrato' }}</p>
                    @if($hero['date'] || $hero['time'])
                        <p class="label">{{ $copy['exhibit_opening'] ?? 'Inauguración' }}</p>
                        <p class="value">{{ trim(implode(' · ', array_filter([$hero['date'], $hero['time']]))) }}</p>
                    @endif
                    @if($location)
                        <p class="label">{{ $copy['exhibit_room'] ?? 'Sala principal' }}</p>
                        <p class="value">{{ $location['name'] ?? $location['address'] }}</p>
                    @endif
                </div>

                @if($hero['message'])
                    <p class="message" style="margin-top: 7mm;">{{ $hero['message'] }}</p>
                @endif
            </td>
        </tr>
    </table>
</div>

@include('client.exports.pdf.partials.rsvp')
