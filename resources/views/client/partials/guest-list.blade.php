{{--
    Lista de invitados del cliente, en filas con filete: filtros por estado con su número (subrayados), buscador, el alta de
    uno o de varios pegando una lista y, por invitado, copiar su enlace personal, mandarlo o recordarle por WhatsApp y
    quitarlo. Recibe $invitation, $rows y $canAddGuests.
--}}
@php
    $guestIndex = $rows->map(fn (array $row) => [
        'name' => mb_strtolower($row['guest']->name),
        'status' => $row['guest']->status,
    ]);
    $counts = ['' => $rows->count(), 'confirmed' => $confirmed->count(), 'pending' => $pending->count(), 'declined' => $declined->count()];
    // Los filtros: «Todos» con palabra y los tres estados con su ícono y su número (la palabra, para lectores de pantalla)
    $filters = ['' => ['Todos', null], 'confirmed' => ['Confirmaron', 'check-circle'], 'pending' => ['Sin responder', 'clock'], 'declined' => ['No asisten', 'x-circle']];
@endphp

<section class="grid grid-cols-[minmax(0,1fr)] gap-4" aria-labelledby="mis-invitados"
    x-data="{
        q: '',
        estado: '',
        open: {{ $errors->any() ? 'true' : 'false' }},
        mode: '{{ $errors->has('list') ? 'list' : 'one' }}',
        guests: @js($guestIndex),
        matches(name, status) {
            const term = this.q.trim().toLowerCase();

            return (term === '' || name.includes(term)) && (this.estado === '' || status === this.estado);
        },
        get visible() {
            return this.guests.filter((guest) => this.matches(guest.name, guest.status)).length;
        },
    }">
    <h2 id="mis-invitados" class="sr-only">Mis invitados</h2>

    {{-- Al agregar un invitado, lo útil es su enlace personal: se copia o se manda de una vez --}}
    @if(session('guest'))
        <div class="ev-note" role="status">
            <x-phosphor-check-circle-light aria-hidden="true" />
            <span class="flex flex-wrap items-center gap-x-1 gap-y-1">
                <span>{{ session('guest')['name'] }} ya está en tu lista.</span>
                <code class="eg-door__url w-full">{{ session('guest')['link'] }}</code>
                <x-ui.copy-button :text="session('guest')['link']" label="Copiar su enlace" done="Enlace copiado" icon="link-simple-light" plain class="ev-act" title="Copiar su enlace" />
                <a href="https://wa.me/?text={{ rawurlencode(session('guest')['link']) }}" target="_blank" rel="noopener" class="ev-act" title="Mandar por WhatsApp" aria-label="Mandar su enlace por WhatsApp">
                    <x-phosphor-whatsapp-logo-light aria-hidden="true" />
                </a>
            </span>
        </div>
    @endif

    {{-- Filtros por estado (con su número) y el «+» para agregar --}}
    <div class="eg-bar">
        <div class="ev-tabs eg-filters" role="group" aria-label="Filtrar por estado">
            @foreach($filters as $value => [$label, $icon])
                <button type="button" class="ev-tab" :class="{ 'is-on': estado === '{{ $value }}' }" :aria-pressed="(estado === '{{ $value }}').toString()" @click="estado = '{{ $value }}'" title="{{ $label }}">
                    @if($icon)
                        <x-dynamic-component :component="'phosphor-'.$icon.'-light'" aria-hidden="true" />
                        <span class="sr-only">{{ $label }}</span>
                    @else
                        {{ $label }}
                    @endif
                    <span class="ev-tab__count">{{ $counts[$value] }}</span>
                </button>
            @endforeach
        </div>
        @if($canAddGuests)
            <button type="button" class="ev-add ev-add--sm" @click="open = !open" :aria-expanded="open.toString()" title="Agregar invitados" aria-label="Agregar invitados">
                <x-phosphor-plus-light aria-hidden="true" x-show="!open" />
                <x-phosphor-x-light aria-hidden="true" x-show="open" x-cloak />
            </button>
        @endif
    </div>

    @if($canAddGuests)
        {{-- Agregar: uno, o varios pegando una lista (un invitado por línea) --}}
        <div x-show="open" x-cloak class="eg-add">
            <div class="ev-tabs" role="group" aria-label="Cómo agregar">
                <button type="button" class="ev-tab" :class="{ 'is-on': mode === 'one' }" @click="mode = 'one'"><x-phosphor-user-plus-light aria-hidden="true" /> Uno</button>
                <button type="button" class="ev-tab" :class="{ 'is-on': mode === 'list' }" @click="mode = 'list'"><x-phosphor-list-plus-light aria-hidden="true" /> Varios</button>
            </div>

            <form method="POST" action="{{ route('client.guests.store', $invitation) }}" x-show="mode === 'one'" class="eg-form">
                @csrf
                <label class="eg-field">
                    <span class="site-label">Nombre o familia</span>
                    <input type="text" name="name" value="{{ old('name') }}" required class="site-input" placeholder="Familia Quispe">
                    @error('name')<span class="eg-error">{{ $message }}</span>@enderror
                </label>
                <div class="eg-pair">
                    <label class="eg-field">
                        <span class="site-label">Teléfono <span class="font-normal">(opcional)</span></span>
                        <input type="tel" name="phone" value="{{ old('phone') }}" inputmode="tel" class="site-input" placeholder="71234567">
                        @error('phone')<span class="eg-error">{{ $message }}</span>@enderror
                    </label>
                    <label class="eg-field">
                        <span class="site-label" title="Cuántas personas vienen con ese nombre">Pases</span>
                        <input type="number" name="passes_allocated" value="{{ old('passes_allocated', 1) }}" min="1" max="20" class="site-input">
                        @error('passes_allocated')<span class="eg-error">{{ $message }}</span>@enderror
                    </label>
                </div>
                <div>
                    <button type="submit" class="site-btn"><x-phosphor-plus-light aria-hidden="true" /> Agregar</button>
                </div>
            </form>

            <form method="POST" action="{{ route('client.guests.bulk', $invitation) }}" x-show="mode === 'list'" x-cloak class="eg-form">
                @csrf
                <label class="eg-field">
                    <span class="site-label">Uno por línea <span class="font-normal">· nombre, teléfono y pases (opcionales)</span></span>
                    <textarea name="list" rows="6" class="site-input eg-list" placeholder="Familia Quispe, 71234567, 3&#10;Ana Pérez&#10;Luis Rojas, 70011223">{{ old('list') }}</textarea>
                    @foreach((array) $errors->get('list') as $message)
                        <span class="eg-error">{{ $message }}</span>
                    @endforeach
                </label>
                <div>
                    <button type="submit" class="site-btn"><x-phosphor-list-plus-light aria-hidden="true" /> Agregar todos</button>
                </div>
            </form>
        </div>
    @endif

    @if($rows->isNotEmpty())
        <div class="ev-search">
            <label for="buscar-invitado" class="sr-only">Buscar invitado</label>
            <x-phosphor-magnifying-glass-light class="ev-search__icon" aria-hidden="true" />
            <input id="buscar-invitado" type="search" x-model="q" class="site-input" placeholder="Buscar por nombre" autocomplete="off">
        </div>
    @endif

    <ul class="ev-list">
        @foreach($rows as $row)
            @php($guest = $row['guest'])
            <li class="ev-row eg-row" x-show="matches(@js(mb_strtolower($guest->name)), @js($guest->status))">
                <span class="eg-state is-{{ $guest->status }}" title="{{ $row['statusLabel'] }}">
                    @if($guest->status === 'confirmed')
                        <x-phosphor-check-circle-light aria-hidden="true" />
                    @elseif($guest->status === 'declined')
                        <x-phosphor-x-circle-light aria-hidden="true" />
                    @else
                        <x-phosphor-clock-light aria-hidden="true" />
                    @endif
                    <span class="sr-only">{{ $row['statusLabel'] }}</span>
                </span>
                <div class="ev-row__main">
                    <div class="ev-row__head">
                        <h3 class="ev-row__name">{{ $guest->name }}</h3>
                        <span class="ev-row__date" title="Pases confirmados de los que tiene">{{ $row['passesLabel'] }} <span class="sr-only">pases</span></span>
                    </div>
                    <p class="ev-meta">
                        @if($guest->phone)<span>{{ $guest->phone }}</span>@endif
                        @if($guest->dietary_restrictions)<span>{{ $guest->dietary_restrictions }}</span>@endif
                        @if($guest->checked_in_at)<span>Ingresó {{ $guest->checked_in_passes }} · {{ $guest->checked_in_at->timezone(config('app.timezone'))->format('H:i') }}</span>@endif
                    </p>
                    <div class="ev-acts">
                        <x-ui.copy-button :text="$row['link']" label="Copiar enlace" done="Copiado" icon="link-simple-light" plain class="ev-act" title="Copiar su enlace personal" />
                        @if($row['shareUrl'])
                            <a href="{{ $row['shareUrl'] }}" target="_blank" rel="noopener" class="ev-act" title="Mandar su enlace por WhatsApp" aria-label="Mandar su enlace a {{ $guest->name }} por WhatsApp">
                                <x-phosphor-whatsapp-logo-light aria-hidden="true" />
                            </a>
                        @endif
                        {{-- A quien no respondió: un recordatorio con su enlace, para que confirme --}}
                        @if($row['remindUrl'])
                            <a href="{{ $row['remindUrl'] }}" target="_blank" rel="noopener" class="ev-act" title="Recordarle que confirme" aria-label="Recordarle a {{ $guest->name }} que confirme por WhatsApp">
                                <x-phosphor-bell-ringing-light aria-hidden="true" />
                            </a>
                        @endif
                        @if($canAddGuests && $row['canRemove'])
                            <form method="POST" action="{{ route('client.guests.destroy', [$invitation, $guest]) }}" class="ev-acts__end"
                                onsubmit="return confirm('¿Quitar a {{ e($guest->name) }} de tu lista?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="ev-act is-danger" aria-label="Quitar a {{ $guest->name }}" title="Quitar de la lista">
                                    <x-phosphor-trash-light aria-hidden="true" />
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </li>
        @endforeach
    </ul>

    @if($rows->isEmpty())
        <div class="ev-empty">
            <x-phosphor-users-three-light class="ev-empty__icon" aria-hidden="true" />
            <h3 class="ev-empty__title">Todavía no hay invitados</h3>
            <p class="ev-empty__text">{{ $canAddGuests ? 'Agrega el primero (o pega toda la lista) y comparte su enlace por WhatsApp.' : 'Cuando el equipo los agregue, verás aquí sus confirmaciones.' }}</p>
        </div>
    @else
        <div x-show="visible === 0" x-cloak class="ev-empty">
            <h3 class="ev-empty__title">Ningún invitado coincide</h3>
            <button type="button" class="site-help" @click="q = ''; estado = ''"><x-phosphor-x-light aria-hidden="true" /> Ver todos</button>
        </div>
    @endif
</section>
