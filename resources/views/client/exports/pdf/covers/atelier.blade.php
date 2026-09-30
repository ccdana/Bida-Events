{{--
    «Atelier»: la etiqueta tejida con su nombre (la casa de moda es ella) y, debajo, la ficha del
    desfile con la fecha, la hora y la pasarela, sobre el papel de molde con su línea de corte.
--}}
<style>
    .at-kicker { margin: 0; font-size: 8pt; letter-spacing: 4pt; text-transform: uppercase; color: {{ $colors['accent'] }}; }
    .at-label { width: 128mm; margin: 8mm auto 0; padding: 9mm 8mm; border: 0.6pt dashed {{ $colors['line'] }}; outline: 0; background: {{ $colors['tint'] }}; }
    .at-label .house { margin: 0; font-size: 8pt; letter-spacing: 4pt; text-transform: uppercase; color: {{ $colors['accent'] }}; }
    .at-label .name { margin: 2mm 0 0; }
    .at-label .line { margin: 2mm 0 0; font-size: 8pt; letter-spacing: 3pt; text-transform: uppercase; color: {{ $colors['muted'] }}; }
    .at-cut { width: 128mm; margin: 7mm auto 0; border-top: 1pt dashed {{ $colors['line'] }}; }
    .at-sheet { width: 128mm; margin: 6mm auto 0; text-align: left; }
    .at-sheet .title { margin: 0 0 2mm; font-size: 8pt; letter-spacing: 3pt; text-transform: uppercase; color: {{ $colors['muted'] }}; }
</style>

<div class="sheet is-thin">
    <table class="cover-fill">
        <tr>
            <td class="fill-cell center">
                <p class="at-kicker">{{ $hero['subtitle'] }}</p>

                <div class="at-label">
                    <p class="house">{{ $copy['atelier_house'] ?? 'Maison' }}</p>
                    <h1 class="name">{{ $hero['name'] }}</h1>
                    <p class="line">{{ $copy['atelier_collection'] ?? 'Colección XV' }}</p>
                </div>

                <div class="at-cut"></div>

                <div class="at-sheet">
                    <p class="title">{{ $copy['atelier_sheet'] ?? 'Ficha del desfile' }}</p>
                    @include('client.exports.pdf.partials.when')
                </div>

                @if($hero['message'])
                    <p class="message" style="margin-top: 7mm;">{{ $hero['message'] }}</p>
                @endif
            </td>
        </tr>
    </table>
</div>

@include('client.exports.pdf.partials.rsvp')
