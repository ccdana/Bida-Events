{{--
    «Próxima salida»: el panel de salidas sobre fondo oscuro. El nombre en casillas, la franja del
    destino y las filas de fecha, hora y puerta, como el tablero de la terminal.
--}}
@php
    $panelWords = preg_split('/\s+/u', mb_strtoupper(trim($hero['name']))) ?: [$hero['name']];
@endphp
<style>
    .panel-night { background: {{ $colors['panel'] }}; padding: 12mm 10mm; }
    .panel-title { margin: 0; font-size: 10pt; color: {{ $colors['onPanelAccent'] }}; }
    .panel-word { margin: 3mm 0 0; border-collapse: separate; border-spacing: 1.2mm 0; }
    .panel-word td { width: 9mm; height: 12mm; border-radius: 1mm; background: {{ $colors['onPanelSoft'] }}; color: {{ $colors['panel'] }}; text-align: center; font-size: 17pt; font-weight: bold; }
    .panel-stripe { margin: 6mm 0 0; padding: 2.5mm 4mm; background: {{ $colors['onPanelAccent'] }}; color: {{ $colors['panel'] }}; font-size: 11pt; font-weight: bold; }
    .panel-rows { width: 100%; margin-top: 6mm; border-collapse: collapse; }
    .panel-rows td { padding: 2.5mm 0; border-bottom: 0.5pt solid {{ $colors['onPanelSoft'] }}; vertical-align: top; }
    .panel-rows .label { width: 30mm; color: {{ $colors['onPanelSoft'] }}; }
    .panel-rows .value { color: {{ $colors['onPanel'] }}; font-size: 12pt; font-weight: bold; }
    .panel-night .message { width: auto; margin-top: 6mm; color: {{ $colors['onPanelSoft'] }}; }
</style>

<div class="sheet is-none">
    <table class="cover-fill">
        <tr>
            <td class="fill-cell panel-night">
                <p class="panel-title">{{ $copy['board_title'] ?? 'Próxima salida' }}</p>
                @foreach(array_slice($panelWords, 0, 3) as $word)
                    <table class="panel-word">
                        <tr>
                            @foreach(array_slice(mb_str_split($word), 0, 14) as $letter)
                                <td>{{ $letter }}</td>
                            @endforeach
                        </tr>
                    </table>
                @endforeach

                <p class="panel-stripe">{{ $hero['subtitle'] }}</p>

                <table class="panel-rows">
                    @foreach(array_filter([
                        $copy['board_date'] ?? 'Fecha' => $hero['date'],
                        $copy['board_time'] ?? 'Hora' => $hero['time'],
                        $copy['board_gate'] ?? 'Puerta' => $location['name'] ?? $location['address'] ?? null,
                    ]) as $label => $value)
                        <tr>
                            <td class="label">{{ $label }}</td>
                            <td class="value">{{ $value }}</td>
                        </tr>
                    @endforeach
                </table>

                @if($hero['message'])
                    <p class="message">{{ $hero['message'] }}</p>
                @endif
            </td>
        </tr>
    </table>
</div>

@include('client.exports.pdf.partials.rsvp')
