@php
    $s = $stats;
    $segments = collect([
        ['label' => 'Confirmados', 'value' => $s['confirmedGuests'], 'color' => '#b8902e'],
        ['label' => 'Sin responder', 'value' => $s['pendingGuests'], 'color' => '#cfd0cc'],
        ['label' => 'No asistirán', 'value' => $s['declinedGuests'], 'color' => '#9b4a3f'],
    ])->filter(fn (array $segment) => $segment['value'] > 0);
    $total = max(1, $s['totalGuests']);
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de invitados | {{ $invitation->title }}</title>
    <style>
        @page { margin: 16mm 15mm 20mm; }
        body { margin: 0; font-family: 'DejaVu Sans', sans-serif; font-size: 9pt; line-height: 1.45; color: #1d1e20; }
        p { margin: 0; }
        footer { position: fixed; bottom: -13mm; left: 0; right: 0; padding-top: 2.5mm; border-top: 0.5pt solid #e3e4e0; font-size: 7.5pt; color: #6c6e73; }
        footer table { width: 100%; border-collapse: collapse; }
        .page-number:after { content: counter(page); }
        .top { width: 100%; border-collapse: collapse; }
        .top td { vertical-align: middle; }
        .brand img { height: 10mm; vertical-align: middle; }
        .brand span { margin-left: 2mm; font-size: 11pt; font-weight: bold; vertical-align: middle; }
        .doc-type { text-align: right; font-size: 8pt; color: #6c6e73; }
        .doc-type strong { display: block; font-size: 10pt; color: #1d1e20; }
        h1 { margin: 7mm 0 1mm; font-size: 19pt; line-height: 1.2; }
        .meta { color: #6c6e73; }
        .kpis { width: 100%; margin-top: 6mm; border-collapse: collapse; border-top: 0.8pt solid #1d1e20; border-bottom: 0.5pt solid #e3e4e0; }
        .kpis td { width: 25%; padding: 4mm 3mm 4mm 0; vertical-align: top; }
        .kpis td.sep { padding-left: 4mm; border-left: 0.5pt solid #e3e4e0; }
        .kpi-value { font-size: 20pt; font-weight: bold; line-height: 1.1; }
        .kpi-label { margin-top: 1mm; font-size: 8.5pt; }
        .kpi-note { margin-top: 0.5mm; font-size: 7.5pt; color: #8a6a1c; }
        .bar { width: 100%; margin-top: 5mm; border-collapse: collapse; }
        .bar td { height: 2.6mm; padding: 0; }
        .legend { margin-top: 2mm; font-size: 7.5pt; color: #6c6e73; }
        .legend .key { display: inline-block; width: 2.4mm; height: 2.4mm; margin: 0 1.2mm 0 3mm; border-radius: 1.2mm; }
        .legend .key.first { margin-left: 0; }
        .plan { margin-top: 6mm; padding: 4mm 5mm; background: #faf6ec; border-left: 1.2mm solid #b8902e; }
        .plan-title { font-size: 10.5pt; font-weight: bold; }
        .plan ul { margin: 2mm 0 0; padding-left: 4.5mm; }
        .plan li { margin-bottom: 1.2mm; }
        h2 { margin: 8mm 0 0.5mm; font-size: 12pt; }
        .section-note { margin-bottom: 2mm; font-size: 8pt; color: #6c6e73; }
        table.list { width: 100%; border-collapse: collapse; }
        table.list th { padding: 1.8mm 2mm; border-bottom: 0.8pt solid #1d1e20; text-align: left; font-size: 7.5pt; font-weight: normal; color: #6c6e73; }
        table.list td { padding: 2mm; border-bottom: 0.5pt solid #e3e4e0; vertical-align: top; }
        table.list tr { page-break-inside: avoid; }
        table.list .num { text-align: right; white-space: nowrap; }
        .section { page-break-inside: avoid; }
        .strong { font-weight: bold; }
        .small { font-size: 7.5pt; color: #6c6e73; }
        .empty { color: #6c6e73; }
        .tag { padding: 0.4mm 1.6mm; background: #f3ead4; color: #735817; font-size: 7.5pt; }
    </style>
</head>
<body>
    <footer>
        <table>
            <tr>
                <td>{{ config('bida.brand') }} | {{ $invitation->title }}</td>
                <td style="text-align: right;">Página <span class="page-number"></span></td>
            </tr>
        </table>
    </footer>

    <table class="top">
        <tr>
            <td class="brand"><img src="{{ $logo }}" alt=""><span>{{ config('bida.brand') }}</span></td>
            <td class="doc-type"><strong>Reporte de invitados</strong>Generado el {{ $generatedAt }}</td>
        </tr>
    </table>

    <h1>{{ $invitation->title }}</h1>
    <p class="meta">
        {{ $eventDateLabel }}
        @if($daysLeft !== null && $daysLeft > 0)
            | Faltan {{ $daysLeft }} {{ $daysLeft === 1 ? 'día' : 'días' }}
        @elseif($daysLeft === 0)
            | Es hoy
        @endif
    </p>

    <table class="kpis">
        <tr>
            <td>
                <p class="kpi-value">{{ $s['confirmedPeople'] }}</p>
                <p class="kpi-label">Personas confirmadas</p>
                <p class="kpi-note">de {{ $s['allocatedPasses'] }} pases asignados</p>
            </td>
            <td class="sep">
                <p class="kpi-value">{{ $s['responseRate'] }}%</p>
                <p class="kpi-label">Respondieron</p>
                <p class="kpi-note">{{ $s['respondedGuests'] }} de {{ $s['totalGuests'] }} invitados</p>
            </td>
            <td class="sep">
                <p class="kpi-value">{{ $s['pendingGuests'] }}</p>
                <p class="kpi-label">Sin responder</p>
                <p class="kpi-note">hasta {{ $s['pendingPeople'] }} personas más</p>
            </td>
            <td class="sep">
                <p class="kpi-value">{{ $s['declinedGuests'] }}</p>
                <p class="kpi-label">No asistirán</p>
                <p class="kpi-note">{{ $s['releasedPasses'] }} pases libres</p>
            </td>
        </tr>
    </table>

    @if($segments->isNotEmpty())
        <table class="bar">
            <tr>
                @foreach($segments as $segment)
                    <td style="width: {{ round($segment['value'] / $total * 100, 2) }}%; background: {{ $segment['color'] }};"></td>
                @endforeach
            </tr>
        </table>
        <p class="legend">
            @foreach($segments->values() as $index => $segment)
                <span @class(['key', 'first' => $index === 0]) style="background: {{ $segment['color'] }};"></span>{{ $segment['label'] }} ({{ $segment['value'] }})
            @endforeach
        </p>
    @endif

    <div class="plan">
        <p class="plan-title">Qué hacer ahora</p>
        <ul>
            @foreach($actions as $action)
                <li>{{ $action }}</li>
            @endforeach
        </ul>
    </div>

    {{-- Las listas cortas no se cortan entre páginas: el título queda junto a su tabla --}}
    <div @class(['section' => $groups['pending']->count() <= 15])>
    <h2>Por contactar</h2>
    <p class="section-note">Invitados que aún no respondieron, primero los que tienen más pases.</p>
    <table class="list">
        <thead>
            <tr>
                <th>Invitado</th>
                <th>Teléfono</th>
                <th class="num">Pases</th>
            </tr>
        </thead>
        <tbody>
            @forelse($groups['pending'] as $row)
                <tr>
                    <td class="strong">{{ $row['name'] }}</td>
                    <td>{{ $row['phone'] ?? 'Sin teléfono' }}</td>
                    <td class="num">{{ $row['allocated'] }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="empty">Todos los invitados ya respondieron.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>

    <div @class(['section' => $groups['confirmed']->count() <= 15])>
    <h2>Confirmados</h2>
    <p class="section-note">{{ $s['confirmedGuests'] }} invitados, {{ $s['confirmedPeople'] }} personas.</p>
    <table class="list">
        <thead>
            <tr>
                <th>Invitado</th>
                <th class="num">Personas</th>
                <th>Mesa</th>
                <th>Alimentación</th>
                <th class="num">Confirmó</th>
            </tr>
        </thead>
        <tbody>
            @forelse($groups['confirmed'] as $row)
                <tr>
                    <td class="strong">{{ $row['name'] }}</td>
                    <td class="num">{{ $row['confirmed'] }} de {{ $row['allocated'] }}</td>
                    <td>{{ $row['table'] ?? '-' }}</td>
                    <td>
                        @if($row['dietary'])
                            <span class="tag">{{ $row['dietary'] }}</span>
                        @else
                            <span class="small">Sin indicar</span>
                        @endif
                    </td>
                    <td class="num small">{{ $row['confirmedAt']?->format('d/m/Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty">Todavía nadie confirmó.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>

    <div @class(['section' => $groups['declined']->count() <= 15])>
    <h2>No asistirán</h2>
    <p class="section-note">Sus pases quedan libres para reasignar.</p>
    <table class="list">
        <thead>
            <tr>
                <th>Invitado</th>
                <th>Teléfono</th>
                <th class="num">Pases libres</th>
            </tr>
        </thead>
        <tbody>
            @forelse($groups['declined'] as $row)
                <tr>
                    <td class="strong">{{ $row['name'] }}</td>
                    <td>{{ $row['phone'] ?? 'Sin teléfono' }}</td>
                    <td class="num">{{ $row['allocated'] }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="empty">Nadie avisó que no asistirá.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>

    @if($tables->isNotEmpty())
        <div class="section">
        <h2>Mesas</h2>
        <p class="section-note">Invitados confirmados con mesa asignada.</p>
        <table class="list">
            <thead>
                <tr>
                    <th>Mesa</th>
                    <th class="num">Invitados</th>
                    <th class="num">Personas</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tables as $table)
                    <tr>
                        <td class="strong">{{ $table['table'] }}</td>
                        <td class="num">{{ $table['guests'] }}</td>
                        <td class="num">{{ $table['people'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    @endif
</body>
</html>
