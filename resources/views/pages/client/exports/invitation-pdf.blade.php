<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $invitation->title }}</title>
    <style>
        :root {
            --primary: {{ $themeColors['primary'] }};
            --secondary: {{ $themeColors['secondary'] }};
            --accent: {{ $themeColors['accent'] }};
            --text: {{ $themeColors['text'] }};
            --bg: {{ $themeColors['background'] }};
            --muted: #78716c;
            --line: #e7e5e4;
        }
        * { box-sizing: border-box; }
        body { margin: 0; padding: 24px; font-family: DejaVu Sans, sans-serif; color: var(--text); background: var(--bg); }
        .page { border: 1px solid var(--line); border-radius: 20px; background: #fff; overflow: hidden; }
        .hero { padding: 26px; background: linear-gradient(135deg, #fff 0%, #fcf7ef 58%, #f5ead9 100%); border-bottom: 1px solid var(--line); }
        .hero-top { display: flex; justify-content: space-between; gap: 18px; align-items: flex-start; margin-bottom: 14px; }
        .brand { font-family: serif; font-size: 24px; line-height: 1; font-weight: 700; color: var(--secondary); }
        .brand span { color: var(--primary); }
        .eyebrow { display: inline-block; margin-bottom: 6px; font-size: 10px; text-transform: uppercase; letter-spacing: 0.18em; color: var(--muted); }
        h1 { margin: 0; font-family: serif; font-size: 28px; line-height: 1.1; color: var(--secondary); }
        .subtitle { margin-top: 8px; font-size: 12px; line-height: 1.7; color: var(--muted); max-width: 430px; }
        .hero-message { margin-top: 16px; padding: 14px 16px; border-radius: 16px; background: rgba(255,255,255,0.8); border: 1px solid rgba(201,169,110,0.22); font-family: serif; font-size: 17px; line-height: 1.55; color: var(--secondary); }
        .section { padding: 24px 26px 0; }
        .section-title { margin: 0 0 10px; font-family: serif; font-size: 19px; color: var(--secondary); }
        .stats { width: 100%; border-collapse: separate; border-spacing: 8px; margin-top: 8px; }
        .metric { width: 20%; border: 1px solid var(--line); border-radius: 14px; padding: 12px 10px; background: linear-gradient(180deg, #fff 0%, #fcf8f3 100%); vertical-align: top; }
        .metric-value { font-family: serif; font-size: 22px; font-weight: 700; line-height: 1; color: var(--secondary); }
        .metric-label { margin-top: 6px; font-size: 10px; text-transform: uppercase; letter-spacing: 0.1em; color: var(--muted); }
        .grid { width: 100%; border-collapse: separate; border-spacing: 8px; margin-top: 8px; }
        .panel { width: 50%; vertical-align: top; border: 1px solid var(--line); border-radius: 16px; padding: 14px; background: #fff; }
        .info-row { display: flex; justify-content: space-between; gap: 12px; padding: 8px 0; border-bottom: 1px solid #f2efec; font-size: 12px; }
        .info-row:last-child { border-bottom: 0; }
        .info-label { color: var(--muted); }
        .info-value { font-weight: 700; text-align: right; }
        .badge { display: inline-block; margin: 0 6px 6px 0; padding: 5px 9px; border-radius: 999px; border: 1px solid var(--line); background: #faf7f2; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--secondary); }
        .block { border: 1px solid var(--line); border-radius: 16px; padding: 14px; background: #fff; margin-top: 12px; }
        .block-soft { background: linear-gradient(180deg, #fff 0%, #fffaf5 100%); }
        .link-box { margin-top: 10px; padding: 12px; border-radius: 14px; border: 1px solid var(--line); background: #fffaf5; font-size: 11px; line-height: 1.6; color: var(--muted); word-break: break-word; }
        .location { display: table; width: 100%; border-spacing: 8px; border-collapse: separate; }
        .location-main { display: table-cell; width: 60%; vertical-align: top; }
        .location-side { display: table-cell; width: 40%; vertical-align: top; }
        .photo { width: 100%; border-radius: 16px; overflow: hidden; border: 1px solid var(--line); margin-top: 10px; }
        .photo img { display: block; width: 100%; height: auto; }
        table.data { width: 100%; border-collapse: collapse; font-size: 11px; margin-top: 10px; }
        table.data th, table.data td { border: 1px solid var(--line); padding: 7px 6px; text-align: left; vertical-align: top; }
        table.data th { background: #f7f3ee; color: var(--muted); text-transform: uppercase; letter-spacing: 0.08em; font-size: 9px; }
        .footer { padding: 18px 26px 24px; color: var(--muted); font-size: 10px; text-align: right; }
    </style>
</head>
<body>
    <div class="page">
        <div class="hero">
            <div class="hero-top">
                <div>
                    <div class="eyebrow">Invitación</div>
                    <h1>{{ $modulos['bienvenida']['nombre_quinceanera'] ?? $invitation->title }}</h1>
                    <div class="subtitle">
                        {{ $modulos['bienvenida']['subtitulo'] ?? '' }}
                        <br>{{ $modulos['bienvenida']['fecha_texto'] ?? $invitation->event_date->format('d/m/Y') }}
                    </div>
                </div>
                <div class="brand"><span>Bida</span>Events</div>
            </div>

            @if(!empty($modulos['bienvenida']['mensaje']))
                <div class="hero-message">{{ $modulos['bienvenida']['mensaje'] }}</div>
            @endif
        </div>

        <div class="section">
            <div class="section-title">Resumen del evento</div>
            <table class="stats">
                <tr>
                    <td class="metric"><div class="metric-value">{{ $stats['confirmedGuests'] }}</div><div class="metric-label">Confirmados</div></td>
                    <td class="metric"><div class="metric-value">{{ $stats['pendingGuests'] }}</div><div class="metric-label">Pendientes</div></td>
                    <td class="metric"><div class="metric-value">{{ $stats['declinedGuests'] }}</div><div class="metric-label">Declinados</div></td>
                    <td class="metric"><div class="metric-value">{{ $stats['confirmedPasses'] }}</div><div class="metric-label">Pases confirmados</div></td>
                    <td class="metric"><div class="metric-value">{{ $stats['confirmationRate'] }}%</div><div class="metric-label">Cobertura</div></td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="grid">
                <table>
                    <tr>
                        <td class="panel">
                            <div class="section-title">Datos clave</div>
                            <div class="info-row"><span class="info-label">Fecha</span><span class="info-value">{{ $invitation->event_date->format('d/m/Y H:i') }}</span></div>
                            <div class="info-row"><span class="info-label">Total invitados</span><span class="info-value">{{ $stats['totalGuests'] }}</span></div>
                            <div class="info-row"><span class="info-label">Pases asignados</span><span class="info-value">{{ $stats['allocatedPasses'] }}</span></div>
                            <div class="info-row"><span class="info-label">Pases restantes</span><span class="info-value">{{ $stats['remainingPasses'] }}</span></div>
                        </td>
                        <td class="panel">
                            <div class="section-title">Módulos activos</div>
                            <div class="badge-list">
                                @foreach(array_keys($modulos) as $featureCode)
                                    <span class="badge">{{ ucfirst(str_replace('_', ' ', $featureCode)) }}</span>
                                @endforeach
                            </div>
                            <div class="info-row"><span class="info-label">Cobertura</span><span class="info-value">{{ $stats['confirmationRate'] }}%</span></div>
                            <div class="info-row"><span class="info-label">Confirmados</span><span class="info-value">{{ $stats['confirmedGuests'] }}</span></div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Ubicación</div>
            <table class="location">
                <tr>
                    <td class="location-main">
                        <div class="block block-soft">
                            <div class="info-row"><span class="info-label">Lugar</span><span class="info-value">{{ $location['nombre_lugar'] ?? 'Sin nombre' }}</span></div>
                            <div class="info-row"><span class="info-label">Dirección</span><span class="info-value">{{ $location['direccion'] ?? 'Sin dirección' }}</span></div>
                            <div class="info-row"><span class="info-label">Mapa</span><span class="info-value">{{ $location['maps_url'] ?? 'No configurado' }}</span></div>
                            @if(!empty($location['nota']))
                                <div class="link-box">{{ $location['nota'] }}</div>
                            @endif
                        </div>
                        @if(!empty($location['imagen_lugar']))
                            <div class="photo"><img src="{{ $location['imagen_lugar'] }}" alt="{{ $location['nombre_lugar'] ?? 'Lugar del evento' }}"></div>
                        @endif
                    </td>
                    <td class="location-side">
                        <div class="block">
                            <div class="section-title">Accesos rápidos</div>
                            <div class="info-row"><span class="info-label">Hashtag</span><span class="info-value">{{ $hashtag['hashtag'] ?? 'No configurado' }}</span></div>
                            <div class="info-row"><span class="info-label">Música</span><span class="info-value">{{ $music['titulo'] ?? 'Sin título' }}</span></div>
                            <div class="info-row"><span class="info-label">RSVP</span><span class="info-value">{{ $rsvp['titulo_confirmacion'] ?? 'Confirmación' }}</span></div>
                            <div class="info-row"><span class="info-label">Regalos</span><span class="info-value">{{ $giftData['titulo'] ?? 'Regalos' }}</span></div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        @if(!empty($itinerary) && is_array($itinerary))
            <div class="section">
                <div class="section-title">Itinerario</div>
                <table class="data">
                    <thead>
                        <tr>
                            <th>Hora</th>
                            <th>Título</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($itinerary as $item)
                            <tr>
                                <td>{{ $item['hora'] ?? '' }}</td>
                                <td>{{ $item['titulo'] ?? '' }}</td>
                                <td>{{ $item['descripcion'] ?? '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if(!empty($dressCode))
            <div class="section">
                <div class="section-title">Vestimenta</div>
                <div class="block">
                    <div class="info-row"><span class="info-label">Estilo</span><span class="info-value">{{ $dressCode['estilo'] ?? 'Sin estilo' }}</span></div>
                    <div class="info-row"><span class="info-label">Descripción</span><span class="info-value">{{ $dressCode['descripcion'] ?? 'Sin descripción' }}</span></div>
                </div>
            </div>
        @endif

        @if(!empty($giftData['opciones']) && is_array($giftData['opciones']))
            <div class="section">
                <div class="section-title">Opciones de regalo</div>
                <table class="data">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Descripción</th>
                            <th>Enlace</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($giftData['opciones'] as $gift)
                            <tr>
                                <td>{{ $gift['titulo'] ?? 'Opción' }}</td>
                                <td>{{ $gift['descripcion'] ?? '' }}</td>
                                <td>{{ $gift['enlace'] ?? '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if(!empty($destacados['padrinos'] ?? []) || !empty($destacados['chambelanes'] ?? []))
            <div class="section">
                <div class="section-title">Acompañantes</div>
                <div class="block">
                    @foreach(($destacados['chambelanes'] ?? []) as $persona)
                        <span class="badge">{{ is_array($persona) ? ($persona['nombre'] ?? 'Chambelán') : $persona }}</span>
                    @endforeach
                    @foreach(($destacados['damitas'] ?? []) as $persona)
                        <span class="badge">{{ is_array($persona) ? ($persona['nombre'] ?? 'Damita') : $persona }}</span>
                    @endforeach
                    @foreach(($destacados['padrinos'] ?? []) as $persona)
                        <span class="badge">{{ is_array($persona) ? ($persona['nombres'] ?? 'Padrino') : $persona }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="section">
            <div class="section-title">Música y RSVP</div>
            <table class="grid">
                <tr>
                    <td class="panel">
                        <div class="info-row"><span class="info-label">Título</span><span class="info-value">{{ $music['titulo'] ?? 'Sin título' }}</span></div>
                        <div class="info-row"><span class="info-label">Artista</span><span class="info-value">{{ $music['artista'] ?? 'Sin artista' }}</span></div>
                        <div class="info-row"><span class="info-label">Autoplay</span><span class="info-value">{{ !empty($music['autoplay']) ? 'Sí' : 'No' }}</span></div>
                    </td>
                    <td class="panel">
                        <div class="info-row"><span class="info-label">Texto confirmado</span><span class="info-value">{{ $rsvp['texto_confirmado'] ?? 'Sin texto' }}</span></div>
                        <div class="info-row"><span class="info-label">Texto declinado</span><span class="info-value">{{ $rsvp['texto_declinado'] ?? 'Sin texto' }}</span></div>
                        <div class="info-row"><span class="info-label">Botón</span><span class="info-value">{{ $rsvp['titulo_confirmacion'] ?? 'Confirmar asistencia' }}</span></div>
                    </td>
                </tr>
            </table>
        </div>

        @if(!empty($postEvent))
            <div class="section">
                <div class="section-title">Post evento</div>
                <div class="block block-soft">
                    <div class="info-row"><span class="info-label">Título</span><span class="info-value">{{ $postEvent['titulo'] ?? 'Galería' }}</span></div>
                    <div class="info-row"><span class="info-label">Mensaje</span><span class="info-value">{{ $postEvent['mensaje'] ?? 'Sin mensaje' }}</span></div>
                    <div class="info-row"><span class="info-label">Enlace externo</span><span class="info-value">{{ $postEvent['enlace_externo'] ?? 'Sin enlace' }}</span></div>
                </div>
            </div>
        @endif

        <div class="footer">Generado por Bida-Events</div>
    </div>
</body>
</html>
