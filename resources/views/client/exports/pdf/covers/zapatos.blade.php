{{--
    «El cambio de zapatos»: la tapa de la caja de la zapatería. Sobre la laca (el secundario), su
    nombre como la marca con la línea de la colección y el filete; debajo, la etiqueta de papel con el
    modelo y la talla, el mensaje, y la fecha, la hora y el lugar.
--}}
<style>
    .zp-lid { width: 140mm; margin: 4mm auto 0; padding: 3mm; background: {{ $colors['panel'] }}; }
    .zp-lid-inner { padding: 12mm 8mm 10mm; border: 0.6pt solid {{ $colors['onPanelAccent'] }}; }
    .zp-lid .kicker { margin: 0; color: {{ $colors['onPanelSoft'] }}; }
    .zp-lid .name { margin: 4mm 0 0; color: {{ $colors['onPanel'] }}; }
    .zp-lid .line { margin: 3mm 0 0; font-size: 8pt; letter-spacing: 3pt; text-transform: uppercase; color: {{ $colors['onPanelAccent'] }}; }
    .zp-tag { width: 96mm; margin: 6mm auto 0; border-collapse: collapse; background: #ffffff; border: 0.6pt solid {{ $colors['line'] }}; }
    .zp-tag td { padding: 2.4mm 4mm; text-align: left; vertical-align: bottom; }
    .zp-tag .label { margin: 0; font-size: 6.5pt; letter-spacing: 1.6pt; text-transform: uppercase; color: {{ $colors['muted'] }}; }
    .zp-tag .value { margin: 0.6mm 0 0; font-size: 11pt; }
    .zp-tag .size { width: 18mm; border-left: 0.6pt solid {{ $colors['line'] }}; text-align: center; font-size: 15pt; font-weight: bold; }
</style>

<div class="sheet is-double">
    <table class="cover-fill">
        <tr>
            <td class="fill-cell center">
                <div class="zp-lid">
                    <div class="zp-lid-inner">
                        <p class="kicker">{{ $copy['shoe_made'] ?? 'Hecho a mano para' }}</p>
                        <h1 class="name">{{ $hero['name'] }}</h1>
                        <p class="line">{{ $copy['shoe_line'] ?? 'Colección XV' }}</p>
                    </div>
                </div>

                <table class="zp-tag">
                    <tr>
                        <td>
                            <p class="label">{{ $copy['shoe_model'] ?? 'Modelo' }}</p>
                            <p class="value">{{ $copy['shoe_model_name'] ?? 'Mis primeros tacones' }}</p>
                        </td>
                        <td class="size">
                            <p class="label">{{ $copy['shoe_size'] ?? 'Talla' }}</p>
                            XV
                        </td>
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
