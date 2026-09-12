<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte invitados</title>
    <style>
        body { margin: 0; padding: 24px; font-family: DejaVu Sans, sans-serif; color: #2c1810; background: #fffaf5; }
        .sheet { border: 1px solid #e7e5e4; border-radius: 18px; background: #fff; padding: 22px; }
        .header { display: flex; justify-content: space-between; gap: 18px; align-items: flex-start; margin-bottom: 18px; padding-bottom: 16px; border-bottom: 1px solid #e7e5e4; }
        .eyebrow { display: inline-block; font-size: 10px; letter-spacing: 0.2em; text-transform: uppercase; color: #78716c; margin-bottom: 6px; }
        h1 { margin: 0; font-family: serif; font-size: 24px; line-height: 1.15; }
        .meta { margin-top: 8px; font-size: 11px; color: #78716c; line-height: 1.6; }
        .brand { font-family: serif; font-size: 24px; font-weight: 700; line-height: 1; }
        .brand span { color: #c9a96e; }
        .summary { width: 100%; border-collapse: separate; border-spacing: 8px; margin-bottom: 12px; }
        .metric { width: 20%; border: 1px solid #e7e5e4; border-radius: 14px; padding: 12px 10px; background: linear-gradient(180deg, #fff 0%, #fcf8f3 100%); vertical-align: top; }
        .metric-value { font-family: serif; font-size: 22px; font-weight: 700; line-height: 1; }
        .metric-label { margin-top: 6px; font-size: 10px; text-transform: uppercase; letter-spacing: 0.1em; color: #78716c; }
        .insights { width: 100%; border-collapse: separate; border-spacing: 8px; margin-bottom: 14px; }
        .panel { width: 50%; vertical-align: top; border: 1px solid #e7e5e4; border-radius: 14px; padding: 14px; background: #fff; }
        .section-title { margin: 0 0 10px; font-family: serif; font-size: 18px; }
        .info-row { display: flex; justify-content: space-between; gap: 12px; padding: 8px 0; border-bottom: 1px solid #f2efec; font-size: 12px; }
        .info-row:last-child { border-bottom: 0; }
        .info-label { color: #78716c; }
        .info-value { font-weight: 700; text-align: right; }
        .badge { display: inline-block; margin: 0 6px 6px 0; padding: 5px 9px; border-radius: 999px; background: #faf7f2; border: 1px solid #e7e5e4; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; }
        table.data { width: 100%; border-collapse: collapse; font-size: 11px; margin-top: 10px; }
        table.data th, table.data td { border: 1px solid #e7e5e4; padding: 7px 6px; text-align: left; vertical-align: top; }
        table.data th { background: #f7f3ee; color: #78716c; text-transform: uppercase; letter-spacing: 0.08em; font-size: 9px; }
        .footer { margin-top: 14px; padding-top: 10px; border-top: 1px solid #e7e5e4; color: #78716c; font-size: 10px; text-align: right; }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="header">
            <div>
                <div class="eyebrow">Reporte ejecutivo</div>
                <h1>{{ $invitation->title }}</h1>
                <div class="meta">
                    <div>Fecha: {{ $invitation->event_date->format('d/m/Y H:i') }}</div>
                    <div>Invitados registrados: {{ $stats['totalGuests'] }}</div>
                </div>
            </div>
            <div class="brand"><span>Bida</span>Events</div>
        </div>

        <table class="summary">
            <tr>
                <td class="metric"><div class="metric-value">{{ $stats['confirmedGuests'] }}</div><div class="metric-label">Confirmados</div></td>
                <td class="metric"><div class="metric-value">{{ $stats['pendingGuests'] }}</div><div class="metric-label">Pendientes</div></td>
                <td class="metric"><div class="metric-value">{{ $stats['declinedGuests'] }}</div><div class="metric-label">Declinados</div></td>
                <td class="metric"><div class="metric-value">{{ $stats['confirmedPasses'] }}</div><div class="metric-label">Pases confirmados</div></td>
                <td class="metric"><div class="metric-value">{{ $stats['confirmationRate'] }}%</div><div class="metric-label">Cobertura</div></td>
            </tr>
        </table>

        <table class="insights">
            <tr>
                <td class="panel">
                    <div class="section-title">Lectura rápida</div>
                    <div class="info-row"><span class="info-label">Pases asignados</span><span class="info-value">{{ $stats['allocatedPasses'] }}</span></div>
                    <div class="info-row"><span class="info-label">Pases restantes</span><span class="info-value">{{ $stats['remainingPasses'] }}</span></div>
                    <div class="info-row"><span class="info-label">Ratio de cobertura</span><span class="info-value">{{ $stats['confirmationRate'] }}%</span></div>
                    <div class="info-row"><span class="info-label">Diferencia de pases</span><span class="info-value">{{ max(0, $stats['allocatedPasses'] - $stats['confirmedPasses']) }}</span></div>
                </td>
                <td class="panel">
                    <div class="section-title">Sugerencias</div>
                    <div class="badge">Prioriza pendientes: {{ $stats['pendingGuests'] }}</div>
                    <div class="badge">Revisa cambios de mesa</div>
                    <div class="badge">Ajusta catering con {{ $stats['confirmedGuests'] }} confirmados</div>
                    <div class="badge">Controla {{ $stats['remainingPasses'] }} pases libres</div>
                    <div class="meta" style="margin-top:10px">
                        El archivo incluye los cálculos necesarios para seguimiento operativo y una tabla detallada por invitado.
                    </div>
                </td>
            </tr>
        </table>

        @if(!empty($modulos))
            <div class="section-title">Módulos activos</div>
            <div style="margin-bottom:12px">
                @foreach(array_keys($modulos) as $featureCode)
                    <span class="badge">{{ ucfirst(str_replace('_', ' ', $featureCode)) }}</span>
                @endforeach
            </div>
        @endif

        <div class="section-title">Detalle de invitados</div>
        <table class="data">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Teléfono</th>
                    <th>Asignados</th>
                    <th>Confirmados</th>
                    <th>Restantes</th>
                    <th>Cobertura</th>
                    <th>Estado</th>
                    <th>Mesa</th>
                    <th>Restricciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($guestRows as $row)
                    <tr>
                        <td>{{ $row['guest']->name }}</td>
                        <td>{{ $row['guest']->phone ?? '' }}</td>
                        <td>{{ $row['allocated'] }}</td>
                        <td>{{ $row['confirmed'] }}</td>
                        <td>{{ $row['remaining'] }}</td>
                        <td>{{ $row['coverage'] }}%</td>
                        <td>{{ $row['statusLabel'] }}</td>
                        <td>{{ $row['guest']->table_number ?? '' }}</td>
                        <td>{{ $row['guest']->dietary_restrictions ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">Generado por Bida-Events</div>
    </div>
</body>
</html>
