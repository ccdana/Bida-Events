{{--
    «Bajo la misma luna»: la noche con su luna y sus estrellas. Es la única portada que se
    imprime sobre fondo oscuro, porque la historia entera pasa de noche.
--}}
<style>
    .luna-night { background: {{ $colors['panel'] }}; padding: 14mm 10mm; text-align: center; }
    .luna-night .kicker { color: {{ $colors['onPanelAccent'] }}; }
    .luna-night .name { color: {{ $colors['onPanel'] }}; }
    .luna-night .message { color: {{ $colors['onPanelSoft'] }}; }
    .luna-motif { width: 84mm; margin: 0 auto 7mm; }
    .luna-rule { width: 46mm; margin: 7mm auto; border-collapse: collapse; }
    .luna-rule td { height: 0.4mm; padding: 0; background: {{ $colors['onPanelAccent'] }}; }
    .luna-when { width: 100%; margin-top: 8mm; border-collapse: collapse; border-top: 0.6pt solid {{ $colors['onPanelAccent'] }}; border-bottom: 0.6pt solid {{ $colors['onPanelAccent'] }}; }
    .luna-when td { padding: 4mm 3mm; text-align: center; vertical-align: top; }
    .luna-when td.sep { border-left: 0.6pt solid {{ $colors['onPanelAccent'] }}; }
    .luna-when .label { color: {{ $colors['onPanelSoft'] }}; }
    .luna-when .value { color: {{ $colors['onPanel'] }}; }
</style>

<div class="sheet is-none">
    <table class="cover-fill">
        <tr>
            <td class="fill-cell luna-night">
                <img class="luna-motif" src="{{ $motifs['ink'] }}" alt="">
                <p class="kicker">{{ $hero['subtitle'] }}</p>
                <h1 class="name">{{ $hero['name'] }}</h1>
                <table class="luna-rule"><tr><td></td></tr></table>

                @if($hero['message'])
                    <p class="message">{{ $hero['message'] }}</p>
                @endif

                @if($hero['date'] || $hero['time'] || $location)
                    <table class="luna-when">
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
