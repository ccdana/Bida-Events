{{--
    «Expediente abierto»: la hoja del caso dentro de su carpeta, sobre fondo oscuro. El sello de caso
    abierto, el nombre y los datos en renglones con línea punteada.
--}}
@php
    $caseWhen = trim(implode(', ', array_filter([$hero['date'], $hero['time']])));
    $caseFields = array_filter([
        $copy['case_seen'] ?? 'Cita' => $caseWhen !== '' ? $caseWhen : null,
        $copy['case_place'] ?? 'Lugar' => $location['name'] ?? $location['address'] ?? null,
    ]);
@endphp
<style>
    .exp-night { background: {{ $colors['panel'] }}; padding: 12mm 9mm; }
    .exp-folder { padding: 5mm; border-radius: 2mm; background: {{ $colors['onPanelSoft'] }}; }
    .exp-doc { padding: 8mm 8mm 9mm; background: {{ $colors['onPanel'] }}; color: {{ $colors['panel'] }}; }
    .exp-head { width: 100%; border-collapse: collapse; border-bottom: 0.6pt dashed {{ $colors['panel'] }}; }
    .exp-head td { padding-bottom: 3mm; font-size: 9.5pt; }
    .exp-stamp { padding: 1mm 2.5mm; border: 1.4pt solid {{ $colors['onPanelAccent'] }}; color: {{ $colors['onPanelAccent'] }}; font-size: 9pt; }
    .exp-doc .kicker { margin-top: 5mm; color: {{ $colors['panel'] }}; }
    .exp-doc .name { color: {{ $colors['panel'] }}; }
    .exp-field { margin: 4mm 0 0; padding-bottom: 1.5mm; border-bottom: 0.6pt dashed {{ $colors['panel'] }}; }
    .exp-field .label,
    .exp-field .value { color: {{ $colors['panel'] }}; }
    .exp-doc .message { width: auto; margin-top: 5mm; color: {{ $colors['panel'] }}; font-size: 10.5pt; }
</style>

<div class="sheet is-none">
    <table class="cover-fill">
        <tr>
            <td class="fill-cell exp-night">
                <div class="exp-folder">
                    <div class="exp-doc">
                        <table class="exp-head">
                            <tr>
                                <td>{{ $copy['case_label'] ?? 'Expediente' }} N.º {{ $invitation->event_date?->format('d-m') }}</td>
                                <td style="text-align: right;"><span class="exp-stamp">{{ $copy['case_open'] ?? 'Caso abierto' }}</span></td>
                            </tr>
                        </table>

                        <p class="kicker">{{ $copy['case_lead'] ?? 'Investigador a cargo' }}</p>
                        <h1 class="name">{{ $hero['name'] }}</h1>

                        @foreach($caseFields as $label => $value)
                            <div class="exp-field">
                                <p class="label">{{ $label }}</p>
                                <p class="value">{{ $value }}</p>
                            </div>
                        @endforeach

                        @if($hero['message'])
                            <p class="message">{{ $hero['message'] }}</p>
                        @endif
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>

@include('client.exports.pdf.partials.rsvp')
