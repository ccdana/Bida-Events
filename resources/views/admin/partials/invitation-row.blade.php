{{--
    Una invitación en el panel: la línea de siempre y, al abrirla, su ficha completa (evento,
    cliente, invitados y enlaces) para no tener que entrar al editor solo a mirar.
    Recibe $row (ver App\ViewModels\Admin\DashboardViewData).
--}}
@php($invitation = $row['invitation'])

<li class="overflow-hidden rounded-[16px] border border-site-line bg-site-surface" x-data="{ open: false }">
    <div class="adm-row flex flex-col gap-4 p-5 lg:flex-row lg:items-center lg:justify-between lg:px-6">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-3">
                <h3 class="truncate text-lg font-medium">{{ $invitation->title }}</h3>
                <span class="admin-status-badge {{ $row['statusClass'] }}">
                    <span class="admin-status-dot"></span>
                    {{ $row['statusLabel'] }}
                </span>
                @if($row['isExpired'])
                    <span class="text-xs font-medium text-site-danger">Enlace vencido</span>
                @endif
            </div>
            <p class="mt-2 flex flex-wrap items-center gap-x-5 gap-y-1.5 text-sm text-site-muted">
                <span class="inline-flex items-center gap-1.5">
                    <x-dynamic-component :component="$row['isCard'] ? 'phosphor-heart' : 'phosphor-confetti'" class="size-4" aria-hidden="true" />
                    {{ $row['templateLabel'] }}
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <x-phosphor-calendar-blank class="size-4" aria-hidden="true" />
                    {{ $row['eventDateLabel'] }}
                </span>
                @unless($row['isCard'])
                    <span class="inline-flex items-center gap-1.5">
                        <x-phosphor-users class="size-4" aria-hidden="true" />
                        {{ $row['confirmedCount'] }} de {{ $row['guestCount'] }} confirmaron
                    </span>
                @endunless
                <span class="inline-flex items-center gap-1.5">
                    <x-phosphor-user class="size-4" aria-hidden="true" />
                    {{ $row['client']['name'] ?? 'Sin cliente' }}
                </span>
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <button type="button" class="admin-link-button" @click="open = !open" :aria-expanded="open ? 'true' : 'false'">
                <x-phosphor-caret-down class="transition-transform" ::class="open ? 'rotate-180' : ''" aria-hidden="true" />
                <span x-text="open ? 'Cerrar ficha' : 'Ver ficha'">Ver ficha</span>
            </button>
            @unless($row['isCard'])
                <a href="{{ route('admin.guests.index', $invitation) }}" class="admin-link-button">
                    <x-phosphor-users aria-hidden="true" />
                    Invitados
                </a>
            @endunless
            <a href="{{ route('admin.invitations.edit', $invitation) }}" class="admin-primary-button">
                <x-phosphor-pencil-simple aria-hidden="true" />
                Editar
            </a>
        </div>
    </div>

    {{-- La ficha: lo mismo que había que abrir el editor para ver --}}
    <div x-show="open" x-cloak class="grid gap-6 border-t border-site-line bg-site-bg p-5 lg:grid-cols-3 lg:px-6">
        <div>
            <h4 class="admin-metric-label">El evento</h4>
            <dl class="mt-2 grid gap-1.5 text-sm">
                @foreach([
                    'Tipo' => $row['typeName'],
                    'Plantilla' => $row['templateLabel'],
                    'Cuándo' => $row['eventDateLabel'].($row['isPast'] ? ' · ya pasó' : ''),
                    'Creada' => $row['createdLabel'],
                    'Vence' => $row['expiresLabel'],
                ] as $label => $value)
                    <div class="flex gap-3">
                        <dt class="w-24 shrink-0 text-site-muted">{{ $label }}</dt>
                        <dd class="min-w-0 flex-1">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        <div>
            <h4 class="admin-metric-label">El cliente</h4>
            @if($row['client'])
                <dl class="mt-2 grid gap-1.5 text-sm">
                    <div class="flex gap-3">
                        <dt class="w-24 shrink-0 text-site-muted">Nombre</dt>
                        <dd class="min-w-0 flex-1">{{ $row['client']['name'] }}</dd>
                    </div>
                    <div class="flex gap-3">
                        <dt class="w-24 shrink-0 text-site-muted">Usuario</dt>
                        <dd class="min-w-0 flex-1 font-mono text-xs">{{ $row['client']['username'] }}</dd>
                    </div>
                </dl>
                <p class="mt-2 text-xs text-site-muted">Entra a su panel con ese usuario; la contraseña se genera desde el editor.</p>
            @else
                <p class="mt-2 text-sm text-site-muted">Sin cliente asignado: nadie ve esta invitación desde el panel del cliente.</p>
                <a href="{{ route('admin.invitations.edit', $invitation) }}" class="admin-link-button mt-3">Asignar uno</a>
            @endif

            @unless($row['isCard'])
                <h4 class="admin-metric-label mt-6">Invitados</h4>
                <dl class="mt-2 grid gap-1.5 text-sm">
                    @foreach([
                        'En la lista' => $row['guestCount'],
                        'Confirmaron' => $row['confirmedCount'].($row['responseRate'] !== null ? " · {$row['responseRate']}% respondió" : ''),
                        'Sin responder' => $row['pendingCount'],
                        'Personas' => $row['confirmedPasses'],
                    ] as $label => $value)
                        <div class="flex gap-3">
                            <dt class="w-24 shrink-0 text-site-muted">{{ $label }}</dt>
                            <dd class="min-w-0 flex-1 tabular-nums">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            @endunless

            @if($row['contributionCount'] > 0)
                <p class="mt-3 text-sm text-site-muted">
                    {{ $row['contributionCount'] }} {{ $row['isCard'] ? 'respuestas y aportes' : 'fotos, canciones y respuestas' }} de los invitados.
                </p>
            @endif
        </div>

        <div>
            <h4 class="admin-metric-label">Enlace</h4>
            <p class="mt-2 break-all font-mono text-xs text-site-muted">{{ $row['publicUrl'] }}</p>
            <div class="mt-3 flex flex-wrap gap-2">
                <a href="{{ $row['publicUrl'] }}" target="_blank" rel="noopener" class="admin-link-button">
                    <x-phosphor-arrow-square-out aria-hidden="true" />
                    Abrir
                </a>
                <x-ui.copy-button :text="$row['publicUrl']" label="Copiar" done="Copiado" />
            </div>

            <h4 class="admin-metric-label mt-6">Eliminar</h4>
            <p class="mt-2 text-sm text-site-muted">
                Se borra {{ $row['isCard'] ? 'la tarjeta' : 'la invitación' }} con sus invitados, confirmaciones y
                aportes. No se puede deshacer.
            </p>
            <form method="POST" action="{{ route('admin.invitations.destroy', $invitation) }}" class="mt-3"
                onsubmit="return confirm('¿Eliminar «{{ e($invitation->title) }}» y todo lo suyo? Esto no se puede deshacer.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="admin-link-button is-danger">
                    <x-phosphor-trash aria-hidden="true" />
                    Eliminar {{ $row['isCard'] ? 'tarjeta' : 'invitación' }}
                </button>
            </form>
        </div>
    </div>
</li>
