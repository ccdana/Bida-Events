@extends('layouts.client')

@section('title', $invitation->title)

{{--
    La ficha de un evento: arriba su nombre, cuándo es, cómo van las confirmaciones y los íconos de acción (abrir, copiar,
    WhatsApp, descargar, editar); debajo, pestañas con subrayado, solo las que tienen algo: Invitados (o Respuestas, en una
    tarjeta), Fotos, Música, Encuestas, Puerta y, para el revendedor, Cliente. Una pantalla a la vez, sin tarjetas.
    La pestaña sale de la dirección (#puerta) y se recuerda al enviar un formulario. Estilos: admin.css («La ficha del evento»).
--}}
@section('content')
    @php
        $user = auth()->user();
        $canDoor = ! $isCard && $user->can('manageOwnGuests', $invitation) && \App\Support\Packages::allowsFor($invitation, 'door');
        // El revendedor que armó el evento maneja el acceso de su cliente
        $isResellerOwner = $user->can('update', $invitation) && ! $user->isAdmin();
        $shareUrl = $publicUrl ? 'https://wa.me/?text='.rawurlencode("Te invito a «{$invitation->title}»: {$publicUrl}") : null;

        // Solo las pestañas que tienen algo que mostrar; la primera es lo que más se busca
        $tabs = collect([
            $isCard ? ['id' => 'respuestas', 'label' => 'Respuestas', 'icon' => 'chat-circle-text', 'count' => $replies->count()] : ['id' => 'invitados', 'label' => 'Invitados', 'icon' => 'list-checks', 'count' => $rows->count()],
            $photos->isNotEmpty() ? ['id' => 'fotos', 'label' => 'Fotos', 'icon' => 'image', 'count' => $photos->count()] : null,
            $songs->isNotEmpty() ? ['id' => 'musica', 'label' => 'Música', 'icon' => 'music-notes', 'count' => $songs->count()] : null,
            $polls->isNotEmpty() ? ['id' => 'encuestas', 'label' => 'Encuestas', 'icon' => 'chart-bar', 'count' => $polls->count()] : null,
            $canDoor ? ['id' => 'puerta', 'label' => 'Puerta', 'icon' => 'door-open', 'count' => null] : null,
            $isResellerOwner ? ['id' => 'cliente', 'label' => 'Cliente', 'icon' => 'user-circle', 'count' => null] : null,
        ])->filter()->values();
        // Recién creadas las credenciales de un cliente, lo que se busca es verlas
        $initialTab = session('client_credentials') && $isResellerOwner ? 'cliente' : $tabs->first()['id'];
    @endphp

    <div class="mx-auto grid w-full max-w-[45rem] grid-cols-[minmax(0,1fr)] gap-5"
        x-data="{
            tab: @js($initialTab),
            ids: @js($tabs->pluck('id')),
            init() {
                // La pestaña viene de la dirección (#puerta) o de la que se estaba usando al enviar un formulario
                let saved = null;
                try { saved = sessionStorage.getItem('ficha-tab'); sessionStorage.removeItem('ficha-tab'); } catch (error) {}
                const wanted = location.hash.slice(1) || saved;
                if (this.ids.includes(wanted)) this.tab = wanted;
                this.$watch('tab', (value) => history.replaceState(null, '', '#' + value));
            },
        }"
        @submit="try { sessionStorage.setItem('ficha-tab', tab) } catch (error) {}">
        <header class="site-enter">
            <a href="{{ route('client.dashboard') }}" class="ev-back">
                <x-phosphor-arrow-left-light aria-hidden="true" />
                Mis eventos
            </a>

            <h1 class="site-display site-display--md mt-3">{{ $invitation->title }}</h1>

            <p class="ev-meta">
                <span><span class="ev-dot is-{{ $isPast ? 'past' : ($publicUrl ? 'live' : 'draft') }}" aria-hidden="true"></span> {{ $isPast ? 'Ya celebrado' : ($publicUrl ? 'En línea' : 'Sin publicar') }}</span>
                @if($whenLabel && ! $isPast)<span>{{ $whenLabel }}</span>@endif
                @if($longDateLabel)<span><x-phosphor-calendar-blank-light aria-hidden="true" /> {{ $longDateLabel }}</span>@endif
                <span>{{ $typeLabel }} · {{ $templateLabel }}</span>
            </p>

            {{-- Cómo van las confirmaciones: una línea fina y los números sueltos --}}
            @unless($isCard)
                @if($guests->count() > 0)
                    <div class="ev-bar mt-4" role="img" aria-label="{{ $confirmed->count() }} de {{ $guests->count() }} invitados confirmaron"><i style="width: {{ $progress }}%"></i></div>
                    <p class="ev-next__count">
                        <span><b>{{ $confirmed->count() }}</b> confirmaron</span>
                        <span><b>{{ $pending->count() }}</b> sin responder</span>
                        @if($declined->count() > 0)<span><b>{{ $declined->count() }}</b> no asisten</span>@endif
                        <span><b>{{ $totalPasses }}</b> personas</span>
                    </p>
                @endif
            @endunless

            {{-- Lo primero que se busca aquí, a un toque: abrir la página y mandarla --}}
            <div class="ev-acts ev-acts--head">
                @if($publicUrl)
                    <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="ev-act" title="Abrir mi {{ mb_strtolower($kindLabel) }}" aria-label="Abrir mi {{ mb_strtolower($kindLabel) }}">
                        <x-phosphor-arrow-square-out-light aria-hidden="true" />
                    </a>
                    <x-ui.copy-button :text="$publicUrl" label="Copiar enlace" done="Enlace copiado" icon="copy-light" plain class="ev-act" title="Copiar enlace" />
                    @unless($isCard)
                        {{-- La misma invitación, pero se abre directo como historias de Instagram --}}
                        <x-ui.copy-button :text="$publicUrl.'?historias'" label="Copiar enlace en historias" done="Enlace copiado" icon="instagram-logo-light" plain class="ev-act" title="Copiar enlace en historias" />
                    @endunless
                    <a href="{{ $shareUrl }}" target="_blank" rel="noopener" class="ev-act" title="Mandar por WhatsApp" aria-label="Mandar por WhatsApp">
                        <x-phosphor-whatsapp-logo-light aria-hidden="true" />
                    </a>
                @endif

                {{-- La lista de invitados para el salón y el catering, y la invitación lista para imprimir --}}
                @can('export', $invitation)
                    <div class="ev-menu" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                        <button type="button" class="ev-act" @click="open = !open" :aria-expanded="open.toString()" title="Descargar" aria-label="Descargar">
                            <x-phosphor-download-simple-light aria-hidden="true" />
                        </button>
                        <div class="ev-menu__list" x-show="open" x-cloak x-transition.opacity>
                            @include('client.partials.export-buttons', ['invitation' => $invitation, 'isCard' => $isCard])
                        </div>
                    </div>
                @endcan

                @can('update', $invitation)
                    @unless($user->isAdmin())
                        <a href="{{ route('client.invitations.edit', $invitation) }}" class="ev-act" title="Editar" aria-label="Editar">
                            <x-phosphor-pencil-simple-line-light aria-hidden="true" />
                        </a>
                    @endunless
                @endcan
                @can('editOwn', $invitation)
                    <a href="{{ route('client.diy.edit', $invitation) }}" class="ev-act" title="Editar" aria-label="Editar">
                        <x-phosphor-pencil-simple-line-light aria-hidden="true" />
                    </a>
                @endcan
            </div>

            @unless($publicUrl)
                <p class="ev-hint"><x-phosphor-info-light aria-hidden="true" /> {{ $unavailableReason }}</p>
            @endunless
        </header>

        @include('client.partials.export-status')

        @if(session('success'))
            <p class="site-enter flex items-start gap-2 border-l-2 border-site-accent py-1 pl-3 text-sm" role="status">
                <x-phosphor-check-circle-light class="mt-0.5 size-4 shrink-0 text-site-accent" aria-hidden="true" />
                {{ session('success') }}
            </p>
        @endif

        {{-- Las pestañas: solo las que tienen algo; si no hay más que una, no hace falta la barra --}}
        @if($tabs->count() > 1)
            <nav class="site-enter ev-tabs" role="tablist" aria-label="Secciones del evento" style="--enter-index: 1">
                @foreach($tabs as $item)
                    <button type="button" role="tab" class="ev-tab" :class="{ 'is-on': tab === '{{ $item['id'] }}' }" :aria-selected="(tab === '{{ $item['id'] }}').toString()"
                        @click="tab = '{{ $item['id'] }}'">
                        <x-dynamic-component :component="'phosphor-'.$item['icon'].'-light'" aria-hidden="true" />
                        {{ $item['label'] }}
                        @if($item['count'] !== null)<span class="ev-tab__count">{{ $item['count'] }}</span>@endif
                    </button>
                @endforeach
            </nav>
        @endif

        @if($isCard)
            <div x-show="tab === 'respuestas'" role="tabpanel">
                @include('client.partials.replies')
            </div>
        @else
            <div x-show="tab === 'invitados'" role="tabpanel">
                @include('client.partials.guest-list')
            </div>
        @endif

        @if($photos->isNotEmpty())
            <div x-show="tab === 'fotos'" x-cloak role="tabpanel">
                @include('client.partials.contribution-photos')
            </div>
        @endif

        @if($songs->isNotEmpty())
            <div x-show="tab === 'musica'" x-cloak role="tabpanel">
                @include('client.partials.contribution-songs')
            </div>
        @endif

        @if($polls->isNotEmpty())
            <div x-show="tab === 'encuestas'" x-cloak role="tabpanel">
                @include('client.partials.poll-results')
            </div>
        @endif

        @if($canDoor)
            <div x-show="tab === 'puerta'" x-cloak role="tabpanel">
                @include('client.partials.door', ['doorStats' => \App\Http\Controllers\Public\DoorController::stats($invitation)])
            </div>
        @endif

        @if($isResellerOwner)
            <div x-show="tab === 'cliente'" x-cloak role="tabpanel">
                @include('client.partials.event-client')
            </div>
        @endif
    </div>
@endsection
