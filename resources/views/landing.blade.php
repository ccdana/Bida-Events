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
        '#disenos' => count($demos) > 1 ? __('Diseños') : null,
        '#incluye' => __('Qué incluye'),
        '#como' => __('Cómo funciona'),
        '#precios' => __('Precios'),
        '#preguntas' => __('Preguntas'),
    ]);
    // Los tres pasos, en el orden en que pasan (por eso van numerados)
    $steps = $isCard
        ? [
            [__('Elige el diseño'), __('Pruébalos acá mismo y quédate con el que más se parezca a lo que quieres decir.')],
            [__('Mándanos tu foto y tu mensaje'), __('Por WhatsApp. Nosotros la armamos y te la mostramos antes de enviarla.')],
            [__('Compártela'), __('Te pasamos el enlace listo para mandar por WhatsApp o por redes.')],
        ]
        : [
            [__('Elige el diseño'), __('Pruébalos como un invitado: confirma, vota o sugiere una canción. Nada se guarda.')],
            [__('Mándanos tus datos'), __('Nombres, fecha, lugar y fotos, por WhatsApp. Armamos la invitación y la revisas antes de compartirla.')],
            [__('Compártela con tus invitados'), __('Un enlace por WhatsApp; ellos confirman desde el celular y tú ves las respuestas en tu panel.')],
        ];
    // Para quién es: «tu boda», «tu graduación»…
    $forPhrase = $page['for'] ?? (collect($bida['showcase'])->firstWhere('event', $page['event'])['phrase'] ?? __('tu evento'));
    $ctaUrl = $isSeasonal && $season ? $season['whatsappUrl'] : $contactUrl;
    $ctaLabel = $isSeasonal && $season ? __('La quiero por :price', ['price' => \App\Support\Money::format($season['final_price'])]) : __('Escríbenos');
    $accountUrl = $user ? route('dashboard') : lroute('login');
    $accountLabel = $user ? __('Mi panel') : __('Ingresar');
    $faqs = array_merge($page['faqs'], [
        [$isCard ? __('¿Quien la recibe necesita instalar algo?') : __('¿Mis invitados necesitan instalar algo?'), __('No. Se abre en el navegador del celular desde el enlace que compartes por WhatsApp.')],
        [__('¿Cómo se realiza el pago?'), $isSeasonal ? ($isCard ? __('Coordinamos el pago por WhatsApp cuando nos pides la tarjeta, antes de armarla.') : __('Coordinamos el pago por WhatsApp cuando nos pides la invitación, antes de armarla.')) : __('La invitación que armas se publica gratis con lo básico, o con un pago único por el nivel que usaste, con tarjeta o con el QR de cualquier banco de Bolivia.')],
    ]);
    $socials = array_values(array_filter([
        ! empty($bida['instagram']) ? ['label' => 'Instagram', 'url' => 'https://www.instagram.com/'.$bida['instagram'].'/', 'icon' => 'instagram-logo'] : null,
        ! empty($bida['facebook']) ? ['label' => 'Facebook', 'url' => 'https://www.facebook.com/'.$bida['facebook'], 'icon' => 'facebook-logo'] : null,
        ! empty($bida['tiktok']) ? ['label' => 'TikTok', 'url' => 'https://www.tiktok.com/@'.$bida['tiktok'], 'icon' => 'tiktok-logo'] : null,
    ]));
    $noun = $isCard ? 'tarjeta' : __('invitación');
    // Hasta tres diseños van en abanico junto al texto; con más, en fila debajo de la portada
    $shotsInRow = count($demos) > 3;
    $demoNote = $page['demo_note'] ?? __('Toca un diseño para abrir su muestra y pruébala como un invitado: confirma, vota o sugiere una canción. Nada se guarda.');
@endphp

@push('head')
    {{-- Preguntas, ruta de navegación y precio de esta página para buscadores y motores de respuesta --}}
    @include('site.partials.structured-data', [
        'faqs' => $faqs,
        'serviceName' => $page['title'],
        'breadcrumbs' => [[$bida['brand'], lroute('home')], [$page['link'], lroute('landing', $slug)]],
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
                    <nav class="site-enter text-sm text-site-muted" aria-label="{{ __('Ruta') }}">
                        <a href="{{ lroute('home') }}" class="site-nav-link hover:text-site-ink">{{ $bida['brand'] }}</a>
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
                        @if($isSeasonal)
                            <a href="{{ $ctaUrl }}" target="_blank" rel="noopener" class="site-btn site-btn--lg justify-center" data-magnetic>
                                <x-phosphor-whatsapp-logo aria-hidden="true" />
                                {{ $ctaLabel }}
                            </a>
                        @else
                            <a href="{{ \App\Support\DiyInvitations::startUrl($user) }}" class="site-btn site-btn--lg justify-center" data-magnetic>
                                {{ __('Crear mi invitación gratis') }}
                                <x-phosphor-arrow-right class="site-btn__arrow" aria-hidden="true" />
                            </a>
                        @endif
                        <a href="#precios" class="site-btn site-btn--ghost site-btn--lg justify-center">
                            {{ __('Ver precios') }}
                            <x-phosphor-arrow-down class="site-btn__arrow" aria-hidden="true" />
                        </a>
                    </div>
                    <p class="site-enter mt-6 text-sm text-site-muted" style="--enter-index: 5">
                        @if($isSeasonal)
                            @if($season)
                                @if($season['old_price'])<del>{{ \App\Support\Money::format($season['old_price']) }}</del> @endif<strong class="text-site-ink">{{ \App\Support\Money::format($season['final_price']) }}</strong> {{ __('por temporada · :value · Se manda por WhatsApp', ['value' => $isCard ? __('Lista el mismo día') : __('Con confirmación de asistencia')]) }}
                            @else
                                {{ __(':value · Se manda por WhatsApp', ['value' => $isCard ? __('Lista el mismo día') : __('Con confirmación de asistencia')]) }}
                            @endif
                        @else
                            {{ __('Gratis para empezar · Desde :value si quieres más, pago único', ['value' => \App\Support\Money::format($fromPrice)]) }}
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
                        {{ __('Pensada para :forPhrase', ['forPhrase' => $forPhrase]) }}
                    </h2>
                    <p class="max-w-[42ch] text-lg leading-relaxed text-site-muted lg:col-span-4 lg:col-start-9" data-reveal>
                        {{ $page['features_note'] ?? __('Además de la cuenta regresiva, el itinerario y el mapa que lleva toda invitación.') }}
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
                    {{ __('De la idea al enlace, en tres pasos') }}
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
                            <p class="text-[0.95rem] font-medium text-site-accent">{{ __('Precio de temporada') }}</p>
                            <p class="mt-4 flex flex-wrap items-baseline gap-x-3 gap-y-1">
                                @if($season['old_price'])
                                    <del class="site-plan__old"><span class="sr-only">{{ __('Antes') }} </span>{{ \App\Support\Money::format($season['old_price']) }}</del>
                                @endif
                                <span class="site-plan__price">{{ $season['final_price'] }}</span>
                                <span class="text-xl text-site-muted">{{ __(':value por :noun', ['value' => \App\Support\Money::code(), 'noun' => $noun]) }}</span>
                            </p>
                            <p class="mt-3 text-site-muted">
                                {{ __(':promo_label hasta el :value. Todos los diseños de la temporada cuestan lo mismo.', ['promo_label' => $season['promo_label'], 'value' => $season['endsAt']->locale(app()->getLocale())->translatedFormat(\App\Support\Localization::dateFormat('l j \d\e F'))]) }}
                            </p>
                        </div>
                        <a href="{{ $season['whatsappUrl'] }}" target="_blank" rel="noopener" class="site-btn site-btn--lg" data-magnetic>
                            <x-phosphor-whatsapp-logo aria-hidden="true" />
                            {{ __('La quiero por :value', ['value' => \App\Support\Money::format($season['final_price'])]) }}
                        </a>
                    @else
                        <p class="max-w-[40ch] text-2xl font-semibold leading-snug tracking-tight md:text-3xl">{{ __('La temporada terminó. Escríbenos y te avisamos de la próxima.') }}</p>
                        <a href="{{ $contactUrl }}" target="_blank" rel="noopener" class="site-btn site-btn--lg" data-magnetic>
                            <x-phosphor-whatsapp-logo aria-hidden="true" />
                            {{ __('Escríbenos') }}
                        </a>
                    @endif
                </div>
            </section>
        @else
            {{-- Precios: armarla es gratis y se paga solo por lo que se usa; lo a medida se conversa, sin precio --}}
            <section id="precios" class="scroll-mt-20 border-t border-site-line bg-site-surface" aria-labelledby="precios-titulo">
                <div class="mx-auto max-w-7xl px-5 py-20 lg:px-8">
                    <h2 id="precios-titulo" class="site-display site-display--md" data-reveal>{{ __('Empieza gratis. Paga solo por lo que usas.') }}</h2>
                    <p class="site-muted-lead" data-reveal>
                        {{ __('Arma tu :noun gratis, con la portada, la cuenta regresiva y la ubicación. Si quieres más, un pago único desde :value, en dólares (:currency).', ['noun' => $noun, 'value' => \App\Support\Money::format($fromPrice), 'currency' => \App\Support\Money::code()]) }}
                    </p>
                    <p class="mt-8 flex flex-wrap gap-3" data-reveal>
                        <a href="{{ \App\Support\DiyInvitations::startUrl($user) }}" class="site-btn site-btn--lg">
                            {{ __('Crear mi invitación gratis') }}
                            <x-phosphor-arrow-right class="site-btn__arrow" aria-hidden="true" />
                        </a>
                        <a href="{{ lroute('pricing') }}" class="site-btn site-btn--ghost site-btn--lg">{{ __('Ver precios') }}</a>
                    </p>
                    @include('site.partials.custom-request')
                </div>
            </section>
        @endif

        @include('site.partials.faqs', ['faqs' => $faqs])
    </main>

    @include('site.partials.footer', ['navLinks' => $navLinks, 'socials' => $socials])
@endsection
