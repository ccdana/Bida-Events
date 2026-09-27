{{--
    «Tendedero»: el cordel con la ropita del bebé. El nombre va en el paño del medio, colgado con dos
    pinzas, y debajo cuelgan las etiquetas con la fecha, la hora y el lugar.
--}}
@php
    $lineTags = array_filter([
        $copy['line_date'] ?? 'Fecha' => $hero['date'],
        $copy['line_time'] ?? 'Hora' => $hero['time'],
        $copy['line_place'] ?? 'Lugar' => $location['name'] ?? $location['address'] ?? null,
    ]);
@endphp
<style>
    .td-cord { width: 150mm; margin: 0 auto; border-top: 0.8pt solid {{ $colors['muted'] }}; }
    .td-pins { width: 90mm; margin: -1.5mm auto 0; border-collapse: collapse; }
    .td-pins td { padding: 0; text-align: center; }
    .td-pin { display: inline-block; width: 2.4mm; height: 7mm; border-radius: 0.6mm; background: {{ $colors['line'] }}; }
    .td-cloth { width: 110mm; margin: -2mm auto 0; padding: 10mm 6mm 9mm; border-radius: 3mm; background: {{ $colors['primary'] }}; }
    .td-cloth .kicker { color: {{ $colors['paper'] }}; }
    .td-cloth .name { margin: 2mm 0 0; color: {{ $colors['paper'] }}; }
    .td-tags { width: 150mm; margin: 12mm auto 0; border-collapse: separate; border-spacing: 3mm 0; border-top: 0.8pt solid {{ $colors['muted'] }}; }
    .td-tags td { padding: 5mm 3mm 4mm; border: 0.8pt dashed {{ $colors['line'] }}; border-top: 0; background: {{ $colors['tint'] }}; text-align: center; vertical-align: top; }
</style>

<div class="sheet is-none">
    <table class="cover-fill">
        <tr>
            <td class="fill-cell center">
                <div class="td-cord"></div>
                <table class="td-pins"><tr><td><span class="td-pin"></span></td><td><span class="td-pin"></span></td></tr></table>
                <div class="td-cloth">
                    <p class="kicker">{{ $hero['subtitle'] }}</p>
                    <h1 class="name">{{ $hero['name'] }}</h1>
                </div>

                @if(count($lineTags))
                    <table class="td-tags">
                        <tr>
                            @foreach($lineTags as $label => $value)
                                <td><p class="label">{{ $label }}</p><p class="value">{{ $value }}</p></td>
                            @endforeach
                        </tr>
                    </table>
                @endif

                @if($hero['message'])
                    <p class="message" style="margin-top: 8mm;">{{ $hero['message'] }}</p>
                @endif
            </td>
        </tr>
    </table>
</div>

@include('client.exports.pdf.partials.rsvp')
