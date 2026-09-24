{{--
    Lista de invitados del cliente: buscador, filtro por estado, alta de invitados y el enlace
    personal de cada uno. Recibe $invitation, $rows y $canAddGuests.
--}}
@php
    $guestIndex = $rows->map(fn (array $row) => [
        'name' => mb_strtolower($row['guest']->name),
        'status' => $row['guest']->status,
    ]);
@endphp

<section class="site-enter mt-10" style="--enter-index: 2"
    x-data="{
        q: '',
        estado: '',
        open: {{ $errors->any() ? 'true' : 'false' }},
        guests: @js($guestIndex),
        matches(name, status) {
            const term = this.q.trim().toLowerCase();

            return (term === '' || name.includes(term)) && (this.estado === '' || status === this.estado);
        },
        get visible() {
            return this.guests.filter((guest) => this.matches(guest.name, guest.status)).length;
        },
    }">
    <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-2 border-b border-site-line pb-3">
        <h2 class="text-xl font-semibold tracking-tight">Mis invitados</h2>
        <p class="text-sm text-site-muted">
            {{ $rows->count() }} {{ $rows->count() === 1 ? 'invitado' : 'invitados' }}. Cada uno recibe su propio enlace para confirmar.
        </p>
    </div>

    @if($canAddGuests)
        <div class="mt-4">
            <button type="button" class="admin-link-button" @click="open = !open" :aria-expanded="open ? 'true' : 'false'">
                <x-phosphor-user-plus aria-hidden="true" />
                <span x-text="open ? 'Cerrar' : 'Agregar invitado'">Agregar invitado</span>
            </button>

            <form method="POST" action="{{ route('client.guests.store', $invitation) }}" x-show="open" x-cloak
                class="mt-4 grid gap-4 rounded-[12px] border border-site-line bg-site-surface p-5 sm:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_7rem_auto] sm:items-end">
                @csrf
                <div>
                    <label for="nuevo-invitado" class="admin-label">Nombre o familia</label>
                    <input id="nuevo-invitado" type="text" name="name" value="{{ old('name') }}" required class="admin-input" placeholder="Familia Quispe">
                    @error('name')
                        <p class="mt-1.5 text-sm text-site-danger">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="nuevo-telefono" class="admin-label">Teléfono <span class="font-normal text-site-muted">(opcional)</span></label>
                    <input id="nuevo-telefono" type="tel" name="phone" value="{{ old('phone') }}" inputmode="tel" class="admin-input" placeholder="71234567">
                    @error('phone')
                        <p class="mt-1.5 text-sm text-site-danger">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="nuevos-pases" class="admin-label">Pases</label>
                    <input id="nuevos-pases" type="number" name="passes_allocated" value="{{ old('passes_allocated', 1) }}" min="1" max="20" class="admin-input">
                    @error('passes_allocated')
                        <p class="mt-1.5 text-sm text-site-danger">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="admin-primary-button min-h-11">
                    <x-phosphor-plus aria-hidden="true" />
                    Agregar
                </button>
                <p class="text-sm text-site-muted sm:col-span-4">
                    Los pases son cuántas personas vienen con ese nombre. Para cambiar mesas o pases ya confirmados, escríbenos.
                </p>
            </form>
        </div>
    @endif

    <div class="mt-5 overflow-hidden rounded-[16px] border border-site-line bg-site-surface">
        {{-- Buscar y filtrar sin recargar: la lista completa ya está en la página --}}
        <div class="flex flex-wrap items-end gap-3 border-b border-site-line p-4">
            <div class="relative min-w-[12rem] flex-1">
                <label for="buscar-invitado" class="sr-only">Buscar invitado</label>
                <x-phosphor-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-site-muted" aria-hidden="true" />
                <input id="buscar-invitado" type="search" x-model="q" class="admin-input has-icon" placeholder="Buscar por nombre" autocomplete="off">
            </div>
            <div>
                <label for="filtrar-invitados" class="sr-only">Filtrar por estado</label>
                <select id="filtrar-invitados" x-model="estado" class="admin-input">
                    <option value="">Todos</option>
                    <option value="confirmed">Confirmados</option>
                    <option value="pending">Sin responder</option>
                    <option value="declined">No asisten</option>
                </select>
            </div>
            <p class="text-sm text-site-muted" x-show="q !== '' || estado !== ''" x-cloak>
                <span x-text="visible"></span> de {{ $rows->count() }}
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th scope="col">Invitado</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Pases</th>
                        <th scope="col">Alimentación</th>
                        <th scope="col"><span class="sr-only">Acciones</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        <tr x-show="matches(@js(mb_strtolower($row['guest']->name)), @js($row['guest']->status))">
                            <td class="font-medium">
                                {{ $row['guest']->name }}
                                @if($row['guest']->phone)
                                    <span class="mt-0.5 block text-sm font-normal text-site-muted">{{ $row['guest']->phone }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="admin-status-badge {{ $row['statusClass'] }}">
                                    <span class="admin-status-dot"></span>
                                    {{ $row['statusLabel'] }}
                                </span>
                            </td>
                            <td class="tabular-nums">
                                {{ $row['passesLabel'] }}
                                @if($row['guest']->checked_in_at)
                                    <span class="mt-0.5 block text-xs text-site-muted">Ingresó {{ $row['guest']->checked_in_passes }} · {{ $row['guest']->checked_in_at->timezone(config('app.timezone'))->format('H:i') }}</span>
                                @endif
                            </td>
                            <td class="text-site-muted">{{ $row['dietaryRestrictions'] }}</td>
                            <td>
                                <div class="flex justify-end gap-1">
                                    <x-ui.copy-button :text="$row['link']" label="Enlace" done="Copiado" icon="link-simple" class="admin-link-button" />
                                    @if($row['whatsapp'])
                                        <a href="{{ $row['whatsapp'] }}?text={{ rawurlencode('Te comparto tu invitación: '.$row['link']) }}"
                                            target="_blank" rel="noopener" class="admin-icon-button"
                                            aria-label="Escribir a {{ $row['guest']->name }} por WhatsApp" title="Enviar por WhatsApp">
                                            <x-phosphor-whatsapp-logo aria-hidden="true" />
                                        </a>
                                    @endif
                                    @if($canAddGuests && $row['canRemove'])
                                        <form method="POST" action="{{ route('client.guests.destroy', [$invitation, $row['guest']]) }}"
                                            onsubmit="return confirm('¿Quitar a {{ e($row['guest']->name) }} de tu lista?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-icon-button is-danger" aria-label="Quitar a {{ $row['guest']->name }}" title="Quitar de la lista">
                                                <x-phosphor-trash aria-hidden="true" />
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach

                    @if($rows->isEmpty())
                        <tr>
                            <td colspan="5">
                                <div class="flex flex-col items-center py-12 text-center">
                                    <x-phosphor-users-three-light class="size-12 text-site-accent" aria-hidden="true" />
                                    <p class="mt-4 font-medium">Todavía no hay invitados</p>
                                    <p class="mt-1 text-site-muted">
                                        {{ $canAddGuests ? 'Agrega el primero y comparte su enlace por WhatsApp.' : 'Cuando el equipo los agregue, verás aquí sus confirmaciones.' }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @else
                        <tr x-show="visible === 0" x-cloak>
                            <td colspan="5">
                                <div class="flex flex-col items-center py-12 text-center">
                                    <p class="font-medium">Ningún invitado coincide con la búsqueda</p>
                                    <button type="button" class="admin-link-button mt-3" @click="q = ''; estado = ''">Ver todos</button>
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</section>
