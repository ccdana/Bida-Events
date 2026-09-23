@extends('layouts.client')

@section('title', 'Mis eventos')

@section('content')
    <header class="site-enter">
        <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">Mis eventos</h1>
        <p class="mt-2 max-w-[52ch] text-site-muted">
            Abre tu página, comparte el enlace y revisa quién respondió.
        </p>
    </header>

    @include('client.partials.export-status')

    {{-- Buscador: viaja en la URL, así el cliente puede volver al mismo resultado --}}
    @if($total > 0 || $search !== '')
        <form method="GET" class="site-enter mt-8 flex flex-wrap items-center gap-2" style="--enter-index: 1">
            <label for="buscar-evento" class="sr-only">Buscar entre mis eventos</label>
            <div class="relative min-w-[14rem] flex-1">
                <x-phosphor-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-site-muted" aria-hidden="true" />
                <input id="buscar-evento" type="search" name="q" value="{{ $search }}" class="admin-input has-icon"
                    placeholder="Buscar por nombre del evento" autocomplete="off">
            </div>
            <button type="submit" class="admin-link-button">Buscar</button>
            @if($search !== '')
                <a href="{{ route('client.dashboard') }}" class="admin-link-button">Ver todos</a>
            @endif
        </form>
    @endif

    @forelse($sections as $sectionIndex => $section)
        <section class="site-enter mt-10" style="--enter-index: {{ $sectionIndex + 2 }}">
            <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 border-b border-site-line pb-3">
                <h2 class="text-xl font-semibold tracking-tight">{{ $section['title'] }}</h2>
                <p class="text-sm text-site-muted">{{ $section['description'] }}</p>
            </div>

            <div class="mt-5 grid gap-5">
                @foreach($section['rows'] as $row)
                    @php($invitation = $row['invitation'])
                    <article class="admin-card p-6 lg:p-8">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <p class="text-sm text-site-muted">{{ $row['typeLabel'] }} · {{ $row['templateLabel'] }}</p>
                                <h3 class="mt-1 truncate text-2xl font-semibold tracking-tight">{{ $invitation->title }}</h3>
                                @if($invitation->event_date)
                                    <p class="mt-2 inline-flex items-center gap-2 text-site-muted">
                                        <x-phosphor-calendar-blank class="size-5 shrink-0" aria-hidden="true" />
                                        {{ $invitation->event_date->locale('es')->translatedFormat('j \d\e F \d\e Y') }}, {{ $invitation->event_date->format('H:i') }}
                                    </p>
                                @endif
                            </div>
                            <span class="admin-status-badge self-start {{ $row['isPast'] ? 'is-primary' : ($row['publicUrl'] ? 'is-success' : 'is-pending') }}">
                                <span class="admin-status-dot"></span>
                                {{ $row['isPast'] ? 'Ya celebrado' : ($row['publicUrl'] ? 'En línea' : 'Sin publicar') }}
                            </span>
                        </div>

                        <dl @class([
                            'mt-7 grid grid-cols-2 border-y border-site-line py-5',
                            'sm:grid-cols-3' => count($row['metrics']) === 3,
                        ])>
                            @foreach($row['metrics'] as $metric)
                                <div class="border-site-line px-4 first:pl-0 [&:not(:first-child)]:border-l">
                                    <dt class="text-sm text-site-muted">{{ $metric['label'] }}</dt>
                                    <dd class="mt-1 text-3xl font-semibold tracking-tight tabular-nums">{{ $metric['value'] }}</dd>
                                    @if($metric['note'] ?? null)
                                        <p class="mt-0.5 text-xs text-site-muted">{{ $metric['note'] }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </dl>

                        <div class="mt-6 flex flex-wrap items-center gap-2">
                            <a href="{{ route('client.invitation.show', $invitation) }}" class="admin-primary-button">
                                <x-dynamic-component :component="$row['isCard'] ? 'phosphor-chat-circle-text' : 'phosphor-list-checks'" aria-hidden="true" />
                                {{ $row['isCard'] ? 'Ver respuestas' : 'Ver invitados' }}
                            </a>

                            @if($row['publicUrl'])
                                <a href="{{ $row['publicUrl'] }}" target="_blank" rel="noopener" class="admin-link-button">
                                    <x-phosphor-arrow-square-out aria-hidden="true" />
                                    Abrir mi {{ mb_strtolower($row['kindLabel']) }}
                                </a>
                                <x-ui.copy-button :text="$row['publicUrl']" label="Copiar enlace" />
                            @else
                                <span class="inline-flex items-center gap-2 text-sm text-site-muted">
                                    <x-phosphor-info class="size-4" aria-hidden="true" />
                                    {{ $row['unavailableReason'] }}
                                </span>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @empty
        <div class="site-enter admin-card mt-10 flex flex-col items-center px-6 py-16 text-center" style="--enter-index: 2">
            <x-phosphor-calendar-blank-light class="size-12 text-site-accent" aria-hidden="true" />
            @if($search !== '')
                <h2 class="mt-4 text-lg font-medium">Ningún evento se llama así</h2>
                <p class="mt-1 max-w-[40ch] text-site-muted">Prueba con otra palabra del nombre.</p>
                <a href="{{ route('client.dashboard') }}" class="admin-link-button mt-6">Ver todos mis eventos</a>
            @else
                <h2 class="mt-4 text-lg font-medium">Todavía no tienes eventos</h2>
                <p class="mt-1 max-w-[40ch] text-site-muted">Cuando armemos tu invitación, aparecerá aquí con sus confirmaciones.</p>
                <a href="{{ route('home') }}#contacto" class="admin-link-button mt-6">Contactar al equipo</a>
            @endif
        </div>
    @endforelse
@endsection
