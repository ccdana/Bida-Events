{{--
    «Noche de calabazas»: la hoja de noche, con la luna llena arriba, murciélagos y la calabaza.
    Como «Bajo la misma luna», se imprime sobre fondo oscuro: la fiesta pasa de noche.
--}}
<style>
    .calabaza-night { background: {{ $colors['panel'] }}; padding: 12mm 10mm; text-align: center; }
    .calabaza-night .kicker { color: {{ $colors['onPanelAccent'] }}; }
    .calabaza-night .name { color: {{ $colors['onPanel'] }}; }
    .calabaza-night .message { color: {{ $colors['onPanelSoft'] }}; }
    .calabaza-moon { width: 30mm; height: 30mm; margin: 0 auto 6mm; border-radius: 15mm; background: {{ $colors['onPanelAccent'] }}; }
    .calabaza-motif { width: 86mm; margin: 0 auto 6mm; }
    .calabaza-when { width: 100%; margin-top: 8mm; border-collapse: collapse; border-top: 0.6pt dashed {{ $colors['onPanelAccent'] }}; border-bottom: 0.6pt dashed {{ $colors['onPanelAccent'] }}; }
    .calabaza-when td { padding: 4mm 3mm; text-align: center; vertical-align: top; }
    .calabaza-when td.sep { border-left: 0.6pt dashed {{ $colors['onPanelAccent'] }}; }
    .calabaza-when .label { color: {{ $colors['onPanelSoft'] }}; }
    .calabaza-when .value { color: {{ $colors['onPanel'] }}; }
</style>

<div class="sheet is-none">
    <table class="cover-fill">
        <tr>
            <td class="fill-cell calabaza-night">
                <div class="calabaza-moon"></div>
                <img class="calabaza-motif" src="{{ $motifs['ink'] }}" alt="">
                <p class="kicker">{{ $hero['subtitle'] }}</p>
                <h1 class="name">{{ $hero['name'] }}</h1>

                @if($hero['message'])
                    <p class="message" style="margin-top: 5mm;">{{ $hero['message'] }}</p>
                @endif

                @if($hero['date'] || $hero['time'] || $location)
                    <table class="calabaza-when">
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
                                <td @class(['sep' => $hero['date'] || $hero['time']])>
                                    <p class="label">Lugar</p>
                                    <p class="value">{{ $location['name'] ?? $location['address'] }}</p>
                                </td>
                            @endif
                        </tr>
                    </table>
                @endif
            </td>
        </tr>
    </table>
</div>

@include('client.exports.pdf.partials.rsvp')
