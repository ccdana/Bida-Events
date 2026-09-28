@extends('layouts.site')

{{--
    Página por tipo de evento o de tarjeta. El contenido vive en config/bida.php (landings); ver
    EventLandingController. Arriba, cada diseño se ve como una captura de cómo empieza su apertura
    (bida:capturas-muestras) y al tocarlo se abre su muestra completa; luego lo propio del evento, el
    precio y las preguntas. Las muestras salen de «demos» o, en las tarjetas, de la temporada, así
    sumar un diseño no cambia esta vista.
--}}
@section('title', $page['title'].' | '.$bida['brand'])
@section('description', $page['description'])

@php
    $isCard = ($page['kind'] ?? null) === 'card';
    // Tarjetas e invitaciones de temporada: precio único de temporada en lugar de paquetes
    $isSeasonal = \App\Http\Controllers\EventLandingController::isSeasonal($page);
    $navLinks = array_filter([
        '#disenos' => count($demos) > 1 ? 'Diseños' : null,
        '#incluye' => 'Qué incluye',
        '#como' => 'Cómo funciona',
        '#precios' => 'Precios',
        '#preguntas' => 'Preguntas',
    ]);
    // Los tres pasos, en el orden en que pasan (por eso van numerados)
    $steps = $isCard
        ? [
            ['Elige el diseño', 'Pruébalos acá mismo y quédate con el que más se parezca a lo que quieres decir.'],
            ['Mándanos tu foto y tu mensaje', 'Por WhatsApp. Nosotros la armamos y te la mostramos antes de enviarla.'],
            ['Compártela', 'Te pasamos el enlace listo para mandar por WhatsApp o por redes.'],
        ]
        : [
            ['Elige el diseño', 'Pruébalos como un invitado: confirma, vota o sugiere una canción. Nada se guarda.'],
            ['Mándanos tus datos', 'Nombres, fecha, lugar y fotos, por WhatsApp. Armamos la invitación y la revisas antes de compartirla.'],
            ['Compártela con tus invitados', 'Un enlace por WhatsApp; ellos confirman desde el celular y tú ves las respuestas en tu panel.'],
        ];
    // Para quién es: «tu boda», «tu graduación»…
    $forPhrase = $page['for'] ?? (collect($bida['showcase'])->firstWhere('event', $page['event'])['phrase'] ?? 'tu evento');
    $ctaUrl = $isSeasonal && $season ? $season['whatsappUrl'] : $contactUrl;
    $ctaLabel = $isSeasonal && $season ? 'La quiero por '.\App\Support\Money::format($season['final_price']) : 'Escríbenos';
    $accountUrl = $user ? route('dashboard') : route('login');
    $accountLabel = $user ? 'Mi panel' : 'Ingresar';
    $faqs = array_merge($page['faqs'], [
        [$isCard ? '¿Quien la recibe necesita instalar algo?' : '¿Mis invitados necesitan instalar algo?', 'No. Se abre en el navegador del celular desde el enlace que compartes por WhatsApp.'],
        ['¿Cómo se realiza el pago?', $isSeasonal ? 'Coordinamos el pago por WhatsApp cuando nos pides la '.($isCard ? 'tarjeta' : 'invitación').', antes de armarla.' : 'Coordinamos el pago por WhatsApp cuando eliges tu paquete, antes de empezar el diseño.'],
    ]);
    $socials = array_values(array_filter([
        ! empty($bida['instagram']) ? ['label' => 'Instagram', 'url' => 'https://www.instagram.com/'.$bida['instagram'].'/', 'icon' => 'instagram-logo'] : null,
        ! empty($bida['facebook']) ? ['label' => 'Facebook', 'url' => 'https://www.facebook.com/'.$bida['facebook'], 'icon' => 'facebook-logo'] : null,
        ! empty($bida['tiktok']) ? ['label' => 'TikTok', 'url' => 'https://www.tiktok.com/@'.$bida['tiktok'], 'icon' => 'tiktok-logo'] : null,
    ]));
    $noun = $isCard ? 'tarjeta' : 'invitación';
    // Hasta tres diseños van en abanico junto al texto; con más, en fila debajo de la portada
    $shotsInRow = count($demos) > 3;
    $demoNote = $page['demo_note'] ?? 'Toca un diseño para abrir su muestra y pruébala como un invitado: confirma, vota o sugiere una canción. Nada se guarda.';
@endphp

@push('head')
    {{-- Preguntas, ruta de navegación y precio de esta página para buscadores y motores de respuesta --}}
    @include('site.partials.structured-data', [
        'faqs' => $faqs,
        'serviceName' => $page['title'],
        'breadcrumbs' => [[$bida['brand'], route('home')], [$page['link'], route('landing', $slug)]],
        'offers' => $isSeasonal
            ? ($season ? [['name' => $page['link'], 'price' => $season['final_price'], 'description' => $page['description']]] : [])
            : null,
    ])
@endpush

@section('content')
    <div data-header-sentinel class="pointer-events-none absolute inset-x-0 top-0 h-4" aria-hidden="true"></div>

    @include('site.partials.header', ['navLinks' => $navLinks])

    {{-- El diseño elegido se comparte entre la portada (el teléfono) y la sección de diseños --}}
    <main>
        {{-- ═══ Portada: el texto del evento y sus diseños, cada uno como la captura de su apertura ═══ --}}
        <section class="site-landing">
            <div class="mx-auto grid max-w-7xl items-center gap-12 px-5 pb-16 pt-8 md:pt-14 lg:min-h-[calc(100dvh-72px)] lg:grid-cols-12 lg:gap-8 lg:px-8 lg:py-12">
                <div class="lg:col-span-5">
                    <nav class="site-enter text-sm text-site-muted" aria-label="Ruta">
                        <a href="{{ route('home') }}" class="site-nav-link hover:text-site-ink">{{ $bida['brand'] }}</a>
                        <span class="mx-2" aria-hidden="true">/</span>
                        <span class="text-site-ink">{{ $page['link'] }}</span>
                    </nav>
                    <h1 class="site-enter site-display mt-5 max-w-[16ch]" style="--enter-index: 1">
                        {{ $page['heading'] }}
                    </h1>
                    <p class="site-enter mt-6 max-w-[44ch] text-lg leading-relaxed text-site-muted" style="--enter-index: 2">
                        {{ $page['intro'] }}
                    </p>

                    <div class="site-enter mt-9 flex flex-col gap-3 sm:flex-row" style="--enter-index: 4">
                        <a href="{{ $ctaUrl }}" target="_blank" rel="noopener" class="site-btn site-btn--lg justify-center" data-magnetic>
                            <x-phosphor-whatsapp-logo aria-hidden="true" />
                            {{ $ctaLabel }}
                        </a>
                        <a href="#precios" class="site-btn site-btn--ghost site-btn--lg justify-center">
                            Ver precios
                            <x-phosphor-arrow-down class="site-btn__arrow" aria-hidden="true" />
                        </a>
                    </div>
                    <p class="site-enter mt-6 text-sm text-site-muted" style="--enter-index: 5">
                        @if($isSeasonal)
                            @if($season)
                                @if($season['old_price'])<del>{{ \App\Support\Money::format($season['old_price']) }}</del> @endif<strong class="text-site-ink">{{ \App\Support\Money::format($season['final_price']) }}</strong> por temporada · {{ $isCard ? 'Lista el mismo día' : 'Con confirmación de asistencia' }} · Se manda por WhatsApp
                            @else
                                {{ $isCard ? 'Lista el mismo día' : 'Con confirmación de asistencia' }} · Se manda por WhatsApp
                            @endif
                        @else
                            Paquetes desde {{ \App\Support\Money::format($fromPrice) }} · Pago único por invitación
                        @endif
                    </p>
                </div>

                @if(count($demos) && ! $shotsInRow)
                    {{-- Los diseños: la captura de cómo empieza cada apertura, en abanico sobre la foto del evento. Al tocarla se abre la muestra completa --}}
                    <div id="disenos" class="site-landing__stage scroll-mt-24 lg:col-span-7">
                        <div class="site-landing__photo" aria-hidden="true">
                            <x-site.image :key="$page['image']" :priority="true" />
                        </div>

                        @include('site.partials.shots', ['demos' => $demos, 'noun' => $noun, 'layout' => 'fan', 'eager' => true])

                        <p class="site-landing__hint">{{ $demoNote }}</p>
                    </div>
                @else
                    <div class="site-photo aspect-[4/5] w-full lg:col-span-6 lg:col-start-7">
                        <x-site.image :key="$page['image']" :priority="true" />
                    </div>
                @endif
            </div>

            @if($shotsInRow)
                {{-- Con más de tres diseños van en fila, debajo de la portada --}}
                <div id="disenos" class="site-landing__row scroll-mt-24">
                    @include('site.partials.shots', ['demos' => $demos, 'noun' => $noun, 'layout' => 'row'])
                    <p class="site-landing__hint">{{ $demoNote }}</p>
                </div>
            @endif
        </section>

        {{-- ═══ Qué incluye: lo propio de este evento, en una grilla de filetes ═══ --}}
        <section id="incluye" class="scroll-mt-20 border-t border-site-line">
            <div class="mx-auto max-w-7xl px-5 py-20 lg:px-8 lg:py-28">
                <div class="grid gap-6 lg:grid-cols-12 lg:items-end lg:gap-8">
                    <h2 class="max-w-[18ch] text-3xl font-semibold leading-[1.1] tracking-tight md:text-5xl lg:col-span-7" data-reveal>
                        Pensada para {{ $forPhrase }}
                    </h2>
                    <p class="max-w-[42ch] text-lg leading-relaxed text-site-muted lg:col-span-4 lg:col-start-9" data-reveal>
                        {{ $page['features_note'] ?? 'Además de la cuenta regresiva, el itinerario y el mapa que lleva toda invitación.' }}
                    </p>
                </div>

                <ul class="site-grid-features mt-14">
                    @foreach($page['highlights'] as $index => $highlight)
                        <li data-reveal style="--reveal-index: {{ $index % 2 }}">
                            <x-dynamic-component :component="'phosphor-'.$highlight['icon'].'-light'" class="site-grid-features__icon" aria-hidden="true" />
                            <h3>{{ $highlight['title'] }}</h3>
                            <p>{{ $highlight['text'] }}</p>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>

        {{-- ═══ Cómo funciona: tres pasos, en orden ═══ --}}
        <section id="como" class="scroll-mt-20 border-t border-site-line">
            <div class="mx-auto max-w-7xl px-5 py-20 lg:px-8 lg:py-24">
                <h2 class="max-w-[20ch] text-3xl font-semibold leading-[1.1] tracking-tight md:text-5xl" data-reveal>
                    De la idea al enlace, en tres pasos
                </h2>
                <ol class="site-howto mt-12">
                    @foreach($steps as $index => [$stepTitle, $stepText])
                        <li class="site-howto__item" data-reveal style="--reveal-index: {{ $index }}">
                            <span class="site-howto__num" aria-hidden="true">{{ $index + 1 }}</span>
                            <h3 class="site-howto__title">{{ $stepTitle }}</h3>
                            <p class="site-howto__text">{{ $stepText }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        @if($isSeasonal)
            {{-- De temporada: precio de temporada tachado y rebajado; pasada la fecha, se consulta --}}
            <section id="precios" class="scroll-mt-20 border-t border-site-line bg-site-surface">
                <div class="mx-auto flex max-w-7xl flex-col items-start gap-8 px-5 py-20 lg:flex-row lg:items-end lg:justify-between lg:px-8" data-reveal>
                    @if($season)
                        <div>
                            <p class="text-[0.95rem] font-medium text-site-accent">Precio de temporada</p>
                            <p class="mt-4 flex flex-wrap items-baseline gap-x-3 gap-y-1">
                                @if($season['old_price'])
                                    <del class="site-plan__old"><span class="sr-only">Antes </span>{{ \App\Support\Money::format($season['old_price']) }}</del>
                                @endif
                                <span class="site-plan__price">{{ $season['final_price'] }}</span>
                                <span class="text-xl text-site-muted">{{ \App\Support\Money::code() }} por {{ $noun }}</span>
                            </p>
                            <p class="mt-3 text-site-muted">
                                {{ $season['promo_label'] }} hasta el {{ $season['endsAt']->locale('es')->translatedFormat('l j \d\e F') }}. Todos los diseños de la temporada cuestan lo mismo.
                            </p>
                        </div>
                        <a href="{{ $season['whatsappUrl'] }}" target="_blank" rel="noopener" class="site-btn site-btn--lg" data-magnetic>
                            <x-phosphor-whatsapp-logo aria-hidden="true" />
                            La quiero por {{ \App\Support\Money::format($season['final_price']) }}
                        </a>
                    @else
                        <p class="max-w-[40ch] text-2xl font-semibold leading-snug tracking-tight md:text-3xl">La temporada terminó. Escríbenos y te avisamos de la próxima.</p>
                        <a href="{{ $contactUrl }}" target="_blank" rel="noopener" class="site-btn site-btn--lg" data-magnetic>
                            <x-phosphor-whatsapp-logo aria-hidden="true" />
                            Escríbenos
                        </a>
                    @endif
                </div>
            </section>
        @else
            @include('site.partials.plans')
        @endif

        @include('site.partials.faqs', ['faqs' => $faqs])
    </main>

    @include('site.partials.footer', ['navLinks' => $navLinks, 'socials' => $socials])
@endsection
