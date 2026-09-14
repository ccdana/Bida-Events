@extends('layouts.site')

@section('title', $bida['brand'].' | Invitaciones digitales para tus eventos')
@section('description', 'Invitaciones digitales para bodas, bautizos, cumpleaños y XV años, con confirmación de asistencia, música, fotos y mapa. Paquetes desde 200 Bs.')

@php
    $sections = array_filter([
        'nosotros' => 'Nosotros',
        'servicios' => 'Servicios',
        'plantillas' => count($demos) ? 'Plantillas' : null,
        'precios' => 'Precios',
        'preguntas' => 'Preguntas',
        'contacto' => 'Contacto',
    ]);
    $accountUrl = $user ? route('dashboard') : route('login');
    $accountLabel = $user ? 'Mi panel' : 'Ingresar';
    $showcase = $bida['showcase'];
    $statement = "Somos un equipo de {$bida['city']} que diseña invitaciones digitales para bodas, bautizos, cumpleaños y cada fecha que tu familia quiere celebrar.";
    $steps = [
        ['icon' => 'chat-circle-text', 'title' => 'Nos escribes', 'text' => 'Cuéntanos qué celebras, la fecha y el lugar. Te ayudamos a elegir el paquete por WhatsApp.'],
        ['icon' => 'pencil-simple-line', 'title' => 'Diseñamos contigo', 'text' => 'Armamos tu invitación con tus fotos, colores y textos. Revisas la vista previa y pides cambios.'],
        ['icon' => 'paper-plane-tilt', 'title' => 'Compartes tu enlace', 'text' => 'Recibes el enlace general y, según tu paquete, uno personal para cada invitado.'],
        ['icon' => 'list-checks', 'title' => 'Sigues las confirmaciones', 'text' => 'Ves en tu panel quién confirmó y cuántas personas asistirán.'],
    ];
    $faqs = [
        ['¿Para qué tipo de eventos sirve?', 'Bodas, bautizos, cumpleaños, XV años, graduaciones y cualquier celebración. Adaptamos los textos y las secciones a tu evento.'],
        ['¿Mis invitados necesitan instalar algo?', 'No. La invitación se abre en el navegador del celular desde el enlace que compartes por WhatsApp.'],
        ['¿Puedo hacer cambios después de compartirla?', 'Sí. Horarios, ubicación y textos se actualizan en el mismo enlace, así tus invitados siempre ven la versión correcta.'],
        ['¿Cómo confirman asistencia mis invitados?', 'Desde el paquete Estándar, cada invitado recibe su propio enlace, indica cuántas personas van y obtiene un pase con código QR.'],
        ['¿Cuánto tardan en entregarla?', 'Depende del paquete y de cuándo nos envíes las fotos y los datos. Te damos una fecha de entrega al confirmar tu pedido.'],
        ['¿Cómo se realiza el pago?', 'Coordinamos el pago por WhatsApp cuando eliges tu paquete, antes de empezar el diseño.'],
    ];
@endphp

@section('content')
    <div data-header-sentinel class="pointer-events-none absolute inset-x-0 top-0 h-4" aria-hidden="true"></div>

    {{-- ═══ Navegación ═══ --}}
    <header data-site-header x-data="{ open: false }" @keydown.escape.window="open = false"
        class="site-header sticky top-0 z-40" :class="{ 'is-open': open }">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-6 px-5 lg:h-[72px] lg:px-8">
            <a href="{{ route('home') }}" class="text-lg">
                <x-brand.logo animated />
            </a>

            <nav class="hidden items-center gap-8 text-[0.95rem] text-site-muted lg:flex" aria-label="Secciones">
                @foreach($sections as $id => $label)
                    <a href="#{{ $id }}" class="site-nav-link hover:text-site-ink">{{ $label }}</a>
                @endforeach
            </nav>

            <div class="hidden items-center gap-5 lg:flex">
                @include('layouts.partials.theme-toggle')
                <a href="{{ $accountUrl }}" class="site-nav-link text-[0.95rem] font-medium text-site-muted hover:text-site-ink">{{ $accountLabel }}</a>
                <a href="{{ $contactUrl }}" target="_blank" rel="noopener" class="site-btn" data-magnetic>
                    <x-phosphor-whatsapp-logo aria-hidden="true" />
                    Escríbenos
                </a>
            </div>

            <div class="flex items-center gap-1 lg:hidden">
                @include('layouts.partials.theme-toggle')
                <button type="button" class="relative grid size-11 place-items-center rounded-full border border-site-line"
                    @click="open = !open" :aria-expanded="open.toString()" aria-controls="menu-movil">
                    <span class="sr-only" x-text="open ? 'Cerrar menú' : 'Abrir menú'">Abrir menú</span>
                    <x-phosphor-list class="site-swap is-on" x-bind:class="{ 'is-on': !open }" aria-hidden="true" />
                    <x-phosphor-x class="site-swap" x-bind:class="{ 'is-on': open }" aria-hidden="true" />
                </button>
            </div>
        </div>

        <div id="menu-movil" x-show="open" x-cloak
            x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="-translate-y-2 opacity-0"
            x-transition:leave="transition duration-200 ease-in" x-transition:leave-end="-translate-y-2 opacity-0"
            class="px-5 pb-6 lg:hidden">
            <nav class="flex flex-col text-lg" aria-label="Secciones">
                @foreach($sections as $id => $label)
                    <a href="#{{ $id }}" @click="open = false" class="border-b border-site-line py-3.5">{{ $label }}</a>
                @endforeach
            </nav>
            <div class="mt-6 grid gap-3">
                <a href="{{ $contactUrl }}" target="_blank" rel="noopener" class="site-btn site-btn--lg justify-center">
                    <x-phosphor-whatsapp-logo aria-hidden="true" />
                    Escríbenos
                </a>
                <a href="{{ $accountUrl }}" class="site-btn site-btn--ghost site-btn--lg justify-center">{{ $accountLabel }}</a>
            </div>
        </div>
    </header>

    <main>
        {{-- ═══ Portada: la palabra del evento y su foto rotan juntas ═══ --}}
        <section data-rotator data-rotator-interval="3200"
            class="mx-auto grid max-w-7xl items-center gap-12 px-5 pb-16 pt-8 md:pt-14 lg:min-h-[calc(100dvh-72px)] lg:grid-cols-[1.1fr_0.9fr] lg:gap-12 lg:px-8 lg:py-12">
            <div>
                <h1 class="site-enter text-[2.5rem] font-semibold leading-[1.06] tracking-tight sm:text-5xl lg:text-[3rem] xl:text-[3.5rem]">
                    <span class="sr-only">Invitaciones digitales para bodas, bautizos, cumpleaños y XV años</span>
                    <span aria-hidden="true">
                        Invitaciones digitales
                        <span class="sm:whitespace-nowrap">para
                            <span class="site-rotator text-site-accent" data-rotator-group>
                                @foreach($showcase as $index => $event)
                                    <span @class(['is-active' => $index === 0])>{{ $event['phrase'] }}</span>
                                @endforeach
                            </span>
                        </span>
                    </span>
                </h1>
                <p class="site-enter mt-6 max-w-[40ch] text-lg leading-relaxed text-site-muted" style="--enter-index: 1">
                    Diseñamos la invitación web de tu evento, con confirmación de asistencia, música, fotos y mapa. Lista para enviar por WhatsApp.
                </p>
                <div class="site-enter mt-9 flex flex-col gap-3 sm:flex-row" style="--enter-index: 2">
                    <a href="{{ $contactUrl }}" target="_blank" rel="noopener" class="site-btn site-btn--lg justify-center" data-magnetic>
                        <x-phosphor-whatsapp-logo aria-hidden="true" />
                        Escríbenos
                    </a>
                    <a href="#precios" class="site-btn site-btn--ghost site-btn--lg justify-center">
                        Ver precios
                        <x-phosphor-arrow-down class="site-btn__arrow" aria-hidden="true" />
                    </a>
                </div>
            </div>

            <div class="relative mx-auto w-full max-w-[22rem] pb-10 sm:max-w-md lg:max-w-none lg:pb-12">
                <div class="site-stage ml-auto aspect-[4/5] w-[80%] lg:w-[76%]" data-rotator-group>
                    @foreach($showcase as $index => $event)
                        <x-site.image :key="$event['image']" :priority="$index === 0" :class="$index === 0 ? 'is-active' : ''" />
                    @endforeach
                </div>

                <div class="site-phone absolute bottom-0 left-0">
                    <div class="site-phone__screen">
                        @if($demoUrl)
                            <iframe src="{{ $demoUrl }}" title="Vista previa de una invitación de ejemplo" loading="lazy" tabindex="-1" aria-hidden="true"></iframe>
                        @else
                            {{-- TODO: captura real de una invitación en celular, 390x844 --}}
                            <img src="https://picsum.photos/seed/bida-invitacion-celular/390/844?grayscale" alt="" width="390" height="844" loading="lazy">
                        @endif
                    </div>
                </div>
            </div>
        </section>

        {{-- ═══ Tipos de evento ═══ --}}
        <section class="border-y border-site-line py-7 lg:py-9" aria-label="Eventos para los que diseñamos">
            <div class="site-marquee">
                <div class="site-marquee__track">
                    @foreach([false, true] as $isCopy)
                        <ul class="site-marquee__list" @if($isCopy) aria-hidden="true" @endif>
                            @foreach($bida['event_types'] as $name => $icon)
                                <li class="flex items-center gap-3 whitespace-nowrap text-2xl font-medium tracking-tight lg:text-3xl">
                                    <x-dynamic-component :component="'phosphor-'.$icon.'-light'" class="size-7 text-site-accent lg:size-8" aria-hidden="true" />
                                    {{ $name }}
                                </li>
                            @endforeach
                        </ul>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ═══ Nosotros: el texto se ilumina con el scroll ═══ --}}
        <section id="nosotros" class="scroll-mt-20">
            <div class="mx-auto grid max-w-7xl gap-12 px-5 py-20 md:grid-cols-12 md:gap-8 lg:px-8 lg:py-32">
                <div class="md:col-span-7">
                    <h2 class="text-[0.95rem] font-medium text-site-accent" data-reveal>Nosotros</h2>
                    <p class="site-statement mt-6 text-[1.9rem] font-medium leading-[1.2] tracking-tight md:text-[2.4rem] lg:text-[2.8rem]">
                        @foreach(explode(' ', $statement) as $word)
                            <span class="site-word">{{ $word }}</span>
                        @endforeach
                    </p>
                    <p class="mt-8 max-w-[46ch] text-lg leading-relaxed text-site-muted" data-reveal>
                        Te acompañamos por WhatsApp desde el primer mensaje hasta el día del evento.
                    </p>

                    <dl class="mt-12 grid max-w-md grid-cols-2 gap-6 border-t border-site-line pt-8" data-reveal>
                        <div>
                            <dt class="text-sm text-site-muted">Hecho en</dt>
                            <dd class="mt-1 text-xl font-medium">Bolivia</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-site-muted">Atención</dt>
                            <dd class="mt-1 text-xl font-medium">Por WhatsApp</dd>
                        </div>
                    </dl>
                </div>

                <figure class="site-zoom overflow-hidden rounded-[20px] md:col-span-5 md:mt-28" data-reveal style="--reveal-index: 1">
                    <x-site.image key="nosotros" class="aspect-[4/5] w-full object-cover" />
                </figure>
            </div>
        </section>

        {{-- ═══ Servicios (bento de 5 celdas) ═══ --}}
        <section id="servicios" class="scroll-mt-20 border-t border-site-line">
            <div class="mx-auto max-w-7xl px-5 py-20 lg:px-8 lg:py-28">
                <h2 class="max-w-[20ch] text-3xl font-semibold leading-[1.1] tracking-tight md:text-5xl" data-reveal>
                    Todo lo que tu invitación puede incluir
                </h2>
                <p class="mt-5 max-w-[50ch] text-lg leading-relaxed text-site-muted" data-reveal>
                    Eliges lo que tu evento necesita y todo se ve bien en el celular.
                </p>

                <div class="mt-14 grid gap-4 md:grid-cols-2 lg:auto-rows-[minmax(14rem,auto)] lg:grid-cols-6">
                    <article class="site-zoom flex flex-col overflow-hidden rounded-[20px] bg-site-surface md:col-span-2 lg:col-span-3 lg:row-span-2" data-reveal>
                        <div class="flex-1 overflow-hidden">
                            <x-site.image key="servicio-enlace" class="aspect-[10/7] size-full object-cover" />
                        </div>
                        <div class="p-7 lg:p-8">
                            <h3 class="text-2xl font-medium tracking-tight">Tu invitación, en un enlace</h3>
                            <p class="mt-2 max-w-[44ch] leading-relaxed text-site-muted">Portada, cuenta regresiva, itinerario y mapa. Se abre desde cualquier celular sin instalar nada.</p>
                        </div>
                    </article>

                    <article class="site-lift flex flex-col justify-between gap-10 rounded-[20px] bg-site-tint p-7 lg:col-span-3 lg:p-8" data-reveal style="--reveal-index: 1">
                        <x-phosphor-qr-code-light class="site-lift__icon size-10 text-site-accent" aria-hidden="true" />
                        <div>
                            <h3 class="text-2xl font-medium tracking-tight">Confirmación con pase QR</h3>
                            <p class="mt-2 max-w-[40ch] leading-relaxed text-site-muted">Cada invitado confirma desde su enlace y recibe un pase para presentar en la entrada.</p>
                        </div>
                    </article>

                    <article class="site-lift flex flex-col justify-between gap-10 rounded-[20px] border border-site-line p-7 lg:col-span-3 lg:p-8" data-reveal style="--reveal-index: 2">
                        <x-phosphor-music-notes-light class="site-lift__icon size-10 text-site-accent" aria-hidden="true" />
                        <div>
                            <h3 class="text-2xl font-medium tracking-tight">Música, fotos y video</h3>
                            <p class="mt-2 max-w-[40ch] leading-relaxed text-site-muted">Tu canción de fondo, una galería que se desliza con el dedo y el video de tu save the date.</p>
                        </div>
                    </article>

                    <article class="site-invert site-lift flex flex-col justify-between gap-10 rounded-[20px] p-7 lg:col-span-2 lg:p-8" data-reveal style="--reveal-index: 1">
                        <x-phosphor-users-three-light class="site-lift__icon size-10 text-site-accent" aria-hidden="true" />
                        <div>
                            <h3 class="text-2xl font-medium tracking-tight">Lista de invitados al día</h3>
                            <p class="mt-2 leading-relaxed text-site-muted">Ves quién confirmó y cuántas personas van. Descargas el reporte en PDF o Excel.</p>
                        </div>
                    </article>

                    <article class="site-zoom grid overflow-hidden rounded-[20px] bg-site-surface sm:grid-cols-2 lg:col-span-4" data-reveal style="--reveal-index: 2">
                        <div class="flex flex-col justify-end p-7 lg:p-8">
                            <x-phosphor-images-light class="mb-10 size-10 text-site-accent" aria-hidden="true" />
                            <h3 class="text-2xl font-medium tracking-tight">Recuerdos en vivo</h3>
                            <p class="mt-2 leading-relaxed text-site-muted">Tus invitados suben fotos durante el evento y todos las ven en el fotomural.</p>
                        </div>
                        <div class="order-first overflow-hidden sm:order-none">
                            <x-site.image key="servicio-fotomural" class="aspect-square size-full object-cover sm:aspect-auto" />
                        </div>
                    </article>
                </div>
            </div>
        </section>

        {{-- ═══ Plantillas: el visitante elige un evento y recorre la invitación de muestra ═══ --}}
        @if(count($demos))
            <section id="plantillas" class="scroll-mt-20 border-t border-site-line bg-site-surface"
                x-data="{ active: 0, loading: true, demos: @js($demos) }">
                <div class="mx-auto grid max-w-7xl gap-12 px-5 py-20 lg:grid-cols-12 lg:items-center lg:gap-8 lg:px-8 lg:py-28">
                    <div class="lg:col-span-6">
                        <p class="text-[0.95rem] font-medium text-site-accent" data-reveal>Plantillas</p>
                        <h2 class="mt-4 max-w-[18ch] text-3xl font-semibold leading-[1.1] tracking-tight md:text-5xl" data-reveal>
                            Recorre una invitación real
                        </h2>
                        <p class="mt-5 max-w-[46ch] text-lg leading-relaxed text-site-muted" data-reveal>
                            Elige un evento y mírala como la verán tus invitados: fotos, música, mapa, confirmación de asistencia y cada sección.
                        </p>

                        <div class="mt-10 grid gap-3 sm:grid-cols-2" role="tablist" aria-label="Plantillas de invitación" data-reveal>
                            @foreach($demos as $index => $demo)
                                <button type="button" role="tab" id="plantilla-tab-{{ $index }}" aria-controls="plantilla-vista"
                                    aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                                    :aria-selected="(active === {{ $index }}).toString()"
                                    @click="if (active !== {{ $index }}) { active = {{ $index }}; loading = true }"
                                    @class(['site-template', 'is-active' => $index === 0])
                                    :class="{ 'is-active': active === {{ $index }} }">
                                    <x-dynamic-component :component="'phosphor-'.$demo['icon'].'-light'" class="site-template__icon" aria-hidden="true" />
                                    <span class="min-w-0">
                                        <span class="block text-sm text-site-muted">{{ $demo['event'] }}</span>
                                        <span class="block text-lg font-medium leading-snug">{{ $demo['label'] }}</span>
                                    </span>
                                </button>
                            @endforeach
                        </div>

                        @foreach($demos as $index => $demo)
                            <div class="mt-8" x-show="active === {{ $index }}" @if($index > 0) x-cloak @endif>
                                <p class="text-sm text-site-muted">Ejemplo: {{ $demo['title'] }}</p>
                                <p class="mt-2 max-w-[46ch] leading-relaxed">{{ $demo['description'] }}</p>
                                <a href="{{ $demo['url'] }}" target="_blank" rel="noopener" class="site-btn site-btn--ghost site-btn--lg mt-6">
                                    Abrir en pantalla completa
                                    <x-phosphor-arrow-up-right class="site-btn__arrow" aria-hidden="true" />
                                </a>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex flex-col items-center gap-4 lg:col-span-5 lg:col-start-8" data-reveal style="--reveal-index: 1">
                        <div class="site-phone site-phone--showcase">
                            <div id="plantilla-vista" role="tabpanel" aria-labelledby="plantilla-tab-0"
                                :aria-labelledby="'plantilla-tab-' + active"
                                class="site-phone__screen" :class="{ 'is-loading': loading }">
                                <iframe src="{{ $demos[0]['url'] }}" :src="demos[active].url"
                                    title="Invitación de muestra: {{ $demos[0]['title'] }}" :title="'Invitación de muestra: ' + demos[active].title"
                                    loading="lazy" @load="loading = false"></iframe>
                            </div>
                        </div>
                        <p class="text-sm text-site-muted">Desliza dentro del teléfono para recorrerla</p>
                    </div>
                </div>
            </section>
        @endif

        {{-- ═══ Cómo trabajamos: la línea se dibuja con el scroll ═══ --}}
        <section class="border-t border-site-line">
            <div class="mx-auto grid max-w-7xl gap-12 px-5 py-20 lg:grid-cols-12 lg:gap-8 lg:px-8 lg:py-28">
                <div class="lg:col-span-5">
                    <div class="lg:sticky lg:top-32">
                        <h2 class="text-3xl font-semibold leading-[1.1] tracking-tight md:text-5xl" data-reveal>Así trabajamos</h2>
                        <p class="mt-5 max-w-[38ch] text-lg leading-relaxed text-site-muted" data-reveal>
                            De tu primer mensaje a las confirmaciones de tus invitados, en cuatro pasos.
                        </p>
                    </div>
                </div>

                <ol class="site-steps grid gap-12 lg:col-span-6 lg:col-start-7 lg:gap-16">
                    @foreach($steps as $index => $step)
                        <li class="grid grid-cols-[3rem_1fr] gap-5 sm:gap-7" data-reveal>
                            <span class="site-step__node">
                                <x-dynamic-component :component="'phosphor-'.$step['icon'].'-light'" class="size-6" aria-hidden="true" />
                            </span>
                            <div class="pt-2.5">
                                <h3 class="text-xl font-medium md:text-2xl">{{ $step['title'] }}</h3>
                                <p class="mt-2 max-w-[42ch] leading-relaxed text-site-muted">{{ $step['text'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        {{-- ═══ Precios ═══ --}}
        <section id="precios" class="scroll-mt-20 border-t border-site-line bg-site-surface">
            <div class="mx-auto max-w-7xl px-5 py-20 lg:px-8 lg:py-28">
                <p class="text-[0.95rem] font-medium text-site-accent" data-reveal>Precios</p>
                <h2 class="mt-4 text-3xl font-semibold leading-[1.1] tracking-tight md:text-5xl" data-reveal>Elige tu paquete</h2>
                <p class="mt-5 max-w-[52ch] text-lg leading-relaxed text-site-muted" data-reveal>
                    Pago único por invitación, en bolivianos. Cada paquete incluye todo lo del anterior.
                </p>

                <div class="mt-14 grid gap-5 lg:grid-cols-3 lg:items-center">
                    @foreach($packages as $index => $package)
                        @php($featured = $package['featured'] ?? false)
                        @php($premium = $package['premium'] ?? false)
                        <article @class([
                                'site-lift flex flex-col rounded-[20px]',
                                'p-7 lg:p-8' => ! $premium,
                                'site-invert lg:py-11' => $featured,
                                'site-premium p-8 lg:p-10' => $premium,
                                'border border-site-line bg-site-bg' => ! $featured && ! $premium,
                            ])
                            data-reveal style="--reveal-index: {{ $index }}">
                            @if($premium)
                                {{-- Marco interior con esquinas doradas y un brillo que recorre la tarjeta --}}
                                <span class="site-premium__frame" aria-hidden="true"><i></i><i></i><i></i><i></i></span>
                                <span class="site-premium__sheen" aria-hidden="true"></span>
                            @endif

                            <div class="flex items-center justify-between gap-3">
                                <h3 class="flex items-center gap-2.5 text-xl font-medium">
                                    @if($premium)
                                        <x-phosphor-crown-simple-fill class="size-5 text-site-accent" aria-hidden="true" />
                                    @endif
                                    {{ $package['name'] }}
                                </h3>
                                @if($featured)
                                    <span class="rounded-full bg-site-accent px-3 py-1 text-sm font-medium text-site-on-accent">Recomendado</span>
                                @elseif($premium)
                                    <span class="site-premium__badge">Experiencia completa</span>
                                @endif
                            </div>

                            <p class="mt-6 flex items-baseline gap-2">
                                <span @class(['text-5xl font-semibold tracking-tight tabular-nums', 'site-premium__price' => $premium])>{{ $package['price'] }}</span>
                                <span class="text-xl text-site-muted">Bs</span>
                            </p>

                            <p class="mt-4 leading-relaxed text-site-muted">{{ $package['summary'] }}</p>

                            @if($premium)
                                <div class="site-premium__rule mt-7" aria-hidden="true">
                                    <x-phosphor-diamond-fill />
                                </div>
                            @endif

                            <ul @class([
                                'flex-1 space-y-3',
                                'mt-7 border-t border-site-line pt-7' => ! $premium,
                                'mt-6' => $premium,
                            ])>
                                @foreach($package['features'] as $feature)
                                    <li class="flex gap-3">
                                        @if($premium)
                                            <span class="site-premium__check"><x-phosphor-check-bold aria-hidden="true" /></span>
                                        @else
                                            <x-phosphor-check-bold class="mt-1 size-4 shrink-0 text-site-accent" aria-hidden="true" />
                                        @endif
                                        <span>{{ $feature }}</span>
                                    </li>
                                @endforeach
                            </ul>

                            <a href="{{ $package['whatsapp'] }}" target="_blank" rel="noopener"
                                @class([
                                    'site-btn site-btn--lg mt-9 justify-center',
                                    'site-btn--gold' => $premium,
                                    'site-btn--ghost' => ! $featured && ! $premium,
                                ])>
                                Elegir {{ $package['name'] }}
                                <x-phosphor-arrow-right class="site-btn__arrow" aria-hidden="true" />
                            </a>
                        </article>
                    @endforeach
                </div>

                <p class="mt-10 text-site-muted" data-reveal>
                    ¿Buscas algo distinto?
                    <a href="{{ $contactUrl }}" target="_blank" rel="noopener" class="font-medium text-site-ink underline underline-offset-4">Escríbenos</a>
                    y armamos un paquete para tu evento.
                </p>
            </div>
        </section>

        {{-- ═══ Preguntas frecuentes ═══ --}}
        <section id="preguntas" class="scroll-mt-20 border-t border-site-line">
            <div class="mx-auto max-w-3xl px-5 py-20 lg:py-28">
                <h2 class="text-3xl font-semibold leading-[1.1] tracking-tight md:text-5xl" data-reveal>Preguntas frecuentes</h2>

                <div class="mt-12 divide-y divide-site-line border-y border-site-line" data-reveal>
                    @foreach($faqs as [$question, $answer])
                        <details class="site-faq group" name="preguntas">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-6 py-5 text-lg font-medium transition-colors hover:text-site-accent [&::-webkit-details-marker]:hidden">
                                {{ $question }}
                                <span class="grid size-8 shrink-0 place-items-center rounded-full border border-site-line transition-[rotate,background-color] duration-500 group-open:rotate-45 group-open:bg-site-tint">
                                    <x-phosphor-plus class="size-4" aria-hidden="true" />
                                </span>
                            </summary>
                            <p class="max-w-[60ch] pb-6 leading-relaxed text-site-muted">{{ $answer }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ═══ Contacto ═══ --}}
        <section id="contacto" class="scroll-mt-20 px-5 pb-20 lg:px-8 lg:pb-28">
            <div class="mx-auto grid max-w-7xl gap-12 rounded-[20px] bg-site-tint px-6 py-14 md:px-14 md:py-20 lg:grid-cols-[1.2fr_0.8fr] lg:items-end" data-reveal>
                <div>
                    <h2 class="max-w-[18ch] text-3xl font-semibold leading-[1.08] tracking-tight md:text-5xl">¿Ya tienes fecha para tu evento?</h2>
                    <p class="mt-5 max-w-[46ch] text-lg leading-relaxed text-site-muted">
                        Escríbenos por WhatsApp y te ayudamos a elegir el paquete ideal.
                    </p>
                    <a href="{{ $contactUrl }}" target="_blank" rel="noopener" class="site-btn site-btn--lg mt-9" data-magnetic>
                        <x-phosphor-whatsapp-logo aria-hidden="true" />
                        Escríbenos
                    </a>
                </div>

                <ul class="grid gap-4 text-lg">
                    <li>
                        <a href="mailto:{{ $bida['email'] }}" class="site-nav-link inline-flex items-center gap-3 hover:text-site-accent">
                            <x-phosphor-envelope-simple-light class="size-6 shrink-0" aria-hidden="true" />
                            {{ $bida['email'] }}
                        </a>
                    </li>
                    @if(!empty($bida['instagram']))
                        <li>
                            <a href="https://www.instagram.com/{{ $bida['instagram'] }}/" target="_blank" rel="noopener" class="site-nav-link inline-flex items-center gap-3 hover:text-site-accent">
                                <x-phosphor-instagram-logo-light class="size-6 shrink-0" aria-hidden="true" />
                                {{ '@'.$bida['instagram'] }}
                            </a>
                        </li>
                    @endif
                    @if(!empty($bida['tiktok']))
                        <li>
                            <a href="https://www.tiktok.com/@{{ $bida['tiktok'] }}" target="_blank" rel="noopener" class="site-nav-link inline-flex items-center gap-3 hover:text-site-accent">
                                <x-phosphor-tiktok-logo-light class="size-6 shrink-0" aria-hidden="true" />
                                {{ '@'.$bida['tiktok'] }}
                            </a>
                        </li>
                    @endif
                    <li class="inline-flex items-center gap-3 text-site-muted">
                        <x-phosphor-map-pin-light class="size-6 shrink-0" aria-hidden="true" />
                        {{ $bida['city'] }}
                    </li>
                </ul>
            </div>
        </section>
    </main>

    <footer class="border-t border-site-line">
        <div class="mx-auto flex max-w-7xl flex-col gap-6 px-5 py-10 text-[0.95rem] text-site-muted md:flex-row md:items-center md:justify-between lg:px-8">
            <div class="flex items-center gap-4">
                <x-brand.mark class="h-8 w-auto" />
                <p>© {{ now()->year }} {{ $bida['brand'] }}. Invitaciones digitales hechas en Bolivia.</p>
            </div>
            <nav class="flex flex-wrap gap-x-6 gap-y-2" aria-label="Pie de página">
                @foreach($sections as $id => $label)
                    <a href="#{{ $id }}" class="site-nav-link hover:text-site-ink">{{ $label }}</a>
                @endforeach
                <a href="{{ $accountUrl }}" class="site-nav-link font-medium text-site-ink">{{ $accountLabel }}</a>
            </nav>
        </div>
    </footer>
@endsection
