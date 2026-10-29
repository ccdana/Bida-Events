{{--
    Una invitación en el panel: una fila con filete (el tipo como ícono, el nombre, la fecha, el estado, el cliente y los
    números) con los íconos de acción y, al abrirla, su ficha en filas (cliente, evento, enlace, invitados y eliminar) para
    no tener que entrar al editor solo a mirar. Recibe $row (ver App\ViewModels\Admin\DashboardViewData).
--}}
@php($invitation = $row['invitation'])

<li @class(['ev-row adm-row', 'is-past' => $row['isPast']]) x-data="{ open: false }">
    <span class="ev-type" title="{{ $row['typeName'] }}">
        <x-dynamic-component :component="'phosphor-'.$row['typeIcon'].'-light'" aria-hidden="true" />
        <span class="sr-only">{{ $row['typeName'] }}</span>
    </span>

    <div class="ev-row__main">
        <div class="ev-row__head">
            <h3 class="ev-row__name">{{ $invitation->title }}</h3>
            <span class="ev-row__date">{{ $row['dateLabel'] }}</span>
        </div>

        <p class="ev-meta">
            <span><span class="ev-dot is-{{ $row['state'][0] }}" aria-hidden="true"></span> {{ $row['state'][1] }}</span>
            @if($row['whenLabel'])<span>{{ $row['whenLabel'] }}</span>@endif
            <span><x-phosphor-user-light aria-hidden="true" /> {{ $row['client']['name'] ?? 'Sin cliente' }}</span>
            @if($row['packageLabel'])<span>{{ $row['packageLabel'] }}</span>@endif
        </p>

        {{-- Abajo, en una sola línea: cuántos confirmaron (o, en una tarjeta, cuántos aportes dejaron) y los íconos de acción --}}
        <div class="adm-foot">
        <p class="ev-nums">
            @if($row['isCard'])
                <span><x-phosphor-chat-circle-text-light aria-hidden="true" /> <b>{{ $row['contributionCount'] }}</b> <span class="sr-only">respuestas y aportes</span></span>
            @else
                <span><x-phosphor-check-circle-light aria-hidden="true" /> <b>{{ $row['confirmedCount'] }}</b> de {{ $row['guestCount'] }} <span class="sr-only">confirmaron</span></span>
            @endif
        </p>

        <div class="ev-acts">
            @unless($row['isCard'])
                <a href="{{ route('admin.guests.index', $invitation) }}" class="ev-act" title="Invitados" aria-label="Invitados">
                    <x-phosphor-list-checks-light aria-hidden="true" />
                </a>
            @endunless
            <a href="{{ route('admin.invitations.edit', $invitation) }}" class="ev-act" title="Editar" aria-label="Editar">
                <x-phosphor-pencil-simple-line-light aria-hidden="true" />
            </a>
            <a href="{{ $row['publicUrl'] }}" target="_blank" rel="noopener" class="ev-act" title="Abrir" aria-label="Abrir">
                <x-phosphor-arrow-square-out-light aria-hidden="true" />
            </a>
            <x-ui.copy-button :text="$row['publicUrl']" label="Copiar enlace" done="Enlace copiado" icon="copy-light" plain class="ev-act" title="Copiar enlace" />
            <button type="button" class="ev-act" @click="open = !open" :aria-expanded="open ? 'true' : 'false'" title="Ver ficha" aria-label="Ver ficha">
                <x-phosphor-caret-down-light class="transition-transform" ::class="open ? 'rotate-180' : ''" aria-hidden="true" />
            </button>
        </div>
        </div>
    </div>

    {{-- La ficha: lo mismo que había que abrir el editor para ver, en filas con filete --}}
    <div x-show="open" x-cloak class="adm-ficha">
        <dl class="adm-kv">
            <div>
                <dt><x-phosphor-user-light aria-hidden="true" /> Cliente</dt>
                <dd>
                    @if($row['client'])
                        {{ $row['client']['name'] }} <span class="font-mono text-xs text-site-muted">{{ $row['client']['username'] }}</span>
                    @else
                        <span class="text-site-muted">Sin cliente</span>
                        <a href="{{ route('admin.invitations.edit', $invitation) }}" class="font-medium underline underline-offset-4">Asignar uno</a>
                    @endif
                </dd>
            </div>
            <div>
                <dt><x-phosphor-confetti-light aria-hidden="true" /> Evento</dt>
                <dd>{{ $row['typeName'] }} · {{ $row['templateLabel'] }}</dd>
            </div>
            <div>
                <dt><x-phosphor-calendar-blank-light aria-hidden="true" /> Cuándo</dt>
                <dd>{{ $row['eventDateLabel'] }}{{ $row['isPast'] ? ' · ya pasó' : '' }}</dd>
            </div>
            <div>
                <dt><x-phosphor-package-light aria-hidden="true" /> Paquete</dt>
                <dd>{{ $row['packageLabel'] ?? 'Todo incluido' }}</dd>
            </div>
            <div>
                <dt><x-phosphor-hourglass-medium-light aria-hidden="true" /> Vence</dt>
                <dd @class(['text-site-danger' => $row['isExpired']])>{{ $row['expiresLabel'] }}{{ $row['isExpired'] ? ' · enlace vencido' : '' }} <span class="text-site-muted">· creada {{ $row['createdLabel'] }}</span></dd>
            </div>
            <div>
                <dt><x-phosphor-link-simple-light aria-hidden="true" /> Enlace</dt>
                <dd class="break-all font-mono text-xs text-site-muted">{{ $row['publicUrl'] }}</dd>
            </div>
        </dl>

        @unless($row['isCard'])
            <dl class="adm-stats">
                @foreach([
                    'En la lista' => $row['guestCount'],
                    'Confirmaron' => $row['confirmedCount'],
                    'Sin responder' => $row['pendingCount'],
                    'Personas' => $row['confirmedPasses'],
                ] as $label => $value)
                    <div>
                        <dt>{{ $label }}</dt>
                        <dd>{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
            @if($row['responseRate'] !== null)
                <p class="adm-ficha__note">{{ $row['responseRate'] }}% ya respondió</p>
            @endif
        @endunless

        @if($row['contributionCount'] > 0)
            <p class="adm-ficha__note">
                <x-phosphor-image-light aria-hidden="true" />
                {{ $row['contributionCount'] }} {{ $row['isCard'] ? 'respuestas y aportes' : 'fotos, canciones y respuestas' }} de los invitados
            </p>
        @endif

        {{-- Eliminar, al final y con confirmación: se borra la invitación con sus invitados, confirmaciones y aportes --}}
        <form method="POST" action="{{ route('admin.invitations.destroy', $invitation) }}" class="adm-ficha__del"
            onsubmit="return confirm('¿Eliminar «{{ e($invitation->title) }}» y todo lo suyo? Esto no se puede deshacer.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="adm-del">
                <x-phosphor-trash-light aria-hidden="true" />
                Eliminar {{ $row['isCard'] ? 'tarjeta' : 'invitación' }}
            </button>
        </form>
    </div>
</li>
