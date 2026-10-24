@extends('layouts.site')

{{--
    Página por tipo de evento o de tarjeta. El contenido vive en config/bida.php (landings); ver
    EventLandingController. Arriba, cada diseño se ve como una captura de cómo empieza su apertura
    (bida:capturas-muestras) y al tocarlo se abre su muestra completa; luego lo propio del evento junto a
    los tres pasos, una franja con el precio y las preguntas. Poco texto, íconos donde sirven. Las muestras
    salen de «demos» o, en las tarjetas, de la temporada, así sumar un diseño no cambia esta vista.
--}}
@section('title', $page['title'].' | '.$bida['brand'])
@section('description', $page['description'])

@php
    $isCard = ($page['kind'] ?? null) === 'card';
    // Tarjetas e invitaciones de temporada: precio único de temporada en lugar de paquetes
    $isSeasonal = \App\Http\Controllers\EventLandingController::isSeasonal($page);
    // Los tres pasos, en el orden en que pasan
    $steps = $isCard
        ? [
            ['cursor-click', __('Elige el diseño'), __('Pruébalos acá mismo y quédate con el que más te guste.')],
            ['whatsapp-logo', __('Mándanos tu foto y tu mensaje'), __('Por WhatsApp. La armamos y te la mostramos antes de enviarla.')],
            ['paper-plane-tilt', __('Compártela'), __('Te pasamos el enlace para mandar por WhatsApp o por redes.')],
        ]
        : ($isSeasonal
            // Las invitaciones de temporada (Halloween) se piden por WhatsApp y las armamos nosotros
            ? [
                ['cursor-click', __('Elige el diseño'), __('Pruébalo como invitado: nada se guarda.')],
                ['whatsapp-logo', __('Mándanos tus datos'), __('Nombres, fecha, lugar y fotos por WhatsApp. La revisas antes de compartirla.')],
                ['paper-plane-tilt', __('Compártela con tus invitados'), __('Un enlace por WhatsApp; confirman desde el celular y tú ves las respuestas.')],
            ]
            // Las demás las arma cada quien en su cuenta (Hazlo tú), igual que dice el botón principal
            : [
                ['cursor-click', __('Elige el diseño'), __('Pruébalo como invitado: nada se guarda.')],
                ['user-plus', __('Crea tu cuenta y ármala'), __('Gratis: subes tus fotos, escribes los datos y la ves en vivo.')],
                ['paper-plane-tilt', __('Publícala y compártela'), __('Gratis o con pago único. Se envía por WhatsApp y ves las respuestas en tu panel.')],
            ]);
    // Para quién es: «tu boda», «tu graduación»…
    $forPhrase = $page['for'] ?? (collect($bida['showcase'])->firstWhere('event', $page['event'])['phrase'] ?? __('tu evento'));
    // Fuera de temporada (la fecha de Ajustes ya pasó) la página no vende: queda abierta para probar las muestras
    // y el botón anota a quien quiere que le avisen cuando vuelva
    $ended = $isSeasonal && ! $season;
    $ctaUrl = $ended ? $waitlistUrl : ($isSeasonal && $season ? $season['whatsappUrl'] : $contactUrl);
    $ctaLabel = $ended ? __('Avísame cuando vuelva') : ($isSeasonal && $season ? __('La quiero por :price', ['price' => \App\Support\Money::format($season['final_price'])]) : __('Escríbenos'));
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
    $demoNote = $page['demo_note'] ?? __('Toca un diseño para abrir su muestra y pruébala: nada se guarda.');
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

    @include('site.partials.header')

    {{-- El diseño elegido se comparte entre la portada (el teléfono) y la sección de diseños --}}
    <main id="contenido" tabindex="-1">
        {{-- ═══ Portada: el texto del evento y sus diseños, cada uno como la captura de su apertura ═══ --}}
        <section class="site-landing">
            <div class="mx-auto grid max-w-7xl items-center gap-6 px-5 pb-6 pt-3 md:pt-8 lg:min-h-[min(calc(100dvh-72px),46rem)] lg:grid-cols-12 lg:gap-8 lg:px-8 lg:py-10">
                <div class="lg:col-span-5">
                    <nav class="site-enter text-sm text-site-muted" aria-label="{{ __('Ruta') }}">
                        <a href="{{ lroute('home') }}" class="site-nav-link site-touch-y hover:text-site-ink">{{ $bida['brand'] }}</a>
                        <span class="mx-2" aria-hidden="true">/</span>
                        <span class="text-site-ink">{{ $page['link'] }}</span>
                    </nav>
                    @if($ended)
                        <p class="site-enter mt-3 flex items-start gap-2 text-sm" style="--enter-index: 1" role="status">
                            <x-phosphor-hourglass-medium class="mt-0.5 size-4 shrink-0 text-site-accent" aria-hidden="true" />
                            <span><strong class="font-semibold text-site-ink">{{ __('La temporada terminó.') }}</strong> {{ __('Las muestras siguen abiertas para que las pruebes.') }}</span>
                        </p>
                    @endif
                    <h1 class="site-enter site-display site-display--lg mt-3" style="--enter-index: 1">
                        {{ $page['heading'] }}
                    </h1>
                    <p class="site-enter site-hero__lead" style="--enter-index: 2">
                        {{ $page['intro'] }}
                    </p>

                    <div class="site-enter site-hero__actions" style="--enter-index: 4">
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
                        @unless($ended)
                            <a href="#precios" class="site-btn site-btn--ghost site-btn--lg site-btn--icon" aria-label="{{ __('Ver precios') }}" title="{{ __('Ver precios') }}">
                                <x-phosphor-arrow-down aria-hidden="true" />
                            </a>
                        @endunless
                    </div>
                    @if($isSeasonal)
                        <p class="site-enter mt-4 text-sm text-site-muted" style="--enter-index: 5">
                            @if($ended)
                                {{ __('Te avisamos por WhatsApp cuando vuelva la temporada.') }}
                            @elseif($season)
                                @if($season['old_price'])<del>{{ \App\Support\Money::format($season['old_price']) }}</del> @endif<strong class="text-site-ink">{{ \App\Support\Money::format($season['final_price']) }}</strong> {{ __('por temporada · :value · Se manda por WhatsApp', ['value' => $isCard ? __('Lista el mismo día') : __('Con confirmación de asistencia')]) }}
                            @endif
                        </p>
                    @endif
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
                    {{-- Con los diseños en fila debajo, la foto solo acompaña en pantallas anchas: en el celular son dos pantallas de más --}}
                    <div @class(['site-photo aspect-[16/10] w-full lg:aspect-[6/5] lg:col-span-6 lg:col-start-7', 'hidden lg:block' => $shotsInRow])>
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

        {{-- ═══ Lo propio de este evento y, al lado, los tres pasos (fuera de temporada no se puede pedir) ═══ --}}
        <section id="incluye" @class(['site-how site-how--event scroll-mt-20', 'site-how--solo' => $ended]) aria-labelledby="incluye-titulo">
            <div class="site-how__includes">
                <h2 id="incluye-titulo" class="site-display site-display--md" data-reveal>{{ __('Pensada para :forPhrase', ['forPhrase' => $forPhrase]) }}</h2>

                <ul class="site-include">
                    @foreach($page['highlights'] as $index => $highlight)
                        <li class="site-include__item" data-reveal>
                            <x-dynamic-component :component="'phosphor-'.$highlight['icon'].'-light'" class="site-include__icon" aria-hidden="true" />
                            <div>
                                <h3 class="site-include__title">{{ $highlight['title'] }}</h3>
                                <p class="site-include__text">{{ $highlight['text'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            @unless($ended)
                <div id="como" class="site-how__steps scroll-mt-20">
                    <h2 class="site-display site-display--md" data-reveal>{{ __('En tres pasos') }}</h2>

                    <ol class="site-steps">
                        @foreach($steps as [$stepIcon, $stepTitle, $stepText])
                            <li class="site-step" data-reveal>
                                <span class="site-step__node">
                                    <x-dynamic-component :component="'phosphor-'.$stepIcon.'-light'" class="size-5" aria-hidden="true" />
                                </span>
                                <div>
                                    <h3 class="site-step__title">{{ $stepTitle }}</h3>
                                    <p class="site-step__text">{{ $stepText }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </div>
            @endunless
        </section>

        @if($isSeasonal)
            {{-- De temporada: precio de temporada tachado y rebajado; pasada la fecha, se consulta --}}
            <section id="precios" class="site-close site-section--surface scroll-mt-20">
                @if($season)
                    <div data-reveal>
                        <p class="flex flex-wrap items-baseline gap-x-3 gap-y-1">
                            @if($season['old_price'])
                                <del class="site-plan__old"><span class="sr-only">{{ __('Antes') }} </span>{{ \App\Support\Money::format($season['old_price']) }}</del>
                            @endif
                            <span class="site-plan__price">{{ $season['final_price'] }}</span>
                            <span class="text-xl text-site-muted">{{ __(':value por :noun', ['value' => \App\Support\Money::code(), 'noun' => $noun]) }}</span>
                        </p>
                        <p class="mt-2 text-sm text-site-muted">
                            {{ __(':promo_label hasta el :value. Todos los diseños de la temporada cuestan lo mismo.', ['promo_label' => $season['promo_label'], 'value' => $season['endsAt']->locale(app()->getLocale())->translatedFormat(\App\Support\Localization::dateFormat('l j \d\e F'))]) }}
                        </p>
                    </div>
                    <div class="site-close__actions" data-reveal>
                        <a href="{{ $season['whatsappUrl'] }}" target="_blank" rel="noopener" class="site-btn site-btn--lg" data-magnetic>
                            <x-phosphor-whatsapp-logo aria-hidden="true" />
                            {{ __('La quiero por :value', ['value' => \App\Support\Money::format($season['final_price'])]) }}
                        </a>
                    </div>
                @else
                    <p class="max-w-[40ch] font-serif text-2xl leading-snug md:text-3xl" data-reveal>{{ __('La temporada terminó. Escríbenos y te avisamos de la próxima.') }}</p>
                    <div class="site-close__actions" data-reveal>
                        <a href="{{ $waitlistUrl }}" target="_blank" rel="noopener" class="site-btn site-btn--lg" data-magnetic>
                            <x-phosphor-whatsapp-logo aria-hidden="true" />
                            {{ __('Avísame cuando vuelva') }}
                        </a>
                    </div>
                @endif
            </section>
        @else
            {{-- Precio en una franja: armarla es gratis y se paga solo por lo que se usa; lo a medida se conversa, sin precio --}}
            <section id="precios" class="site-close site-section--surface scroll-mt-20" aria-labelledby="precios-titulo">
                <div>
                    <h2 id="precios-titulo" class="site-display site-display--md" data-reveal>{{ __('Empieza gratis. Paga solo por lo que usas.') }}</h2>
                    <p class="site-muted-lead" data-reveal>
                        {{ __('Gratis con lo básico; si quieres más, un pago único desde :value (:currency).', ['value' => \App\Support\Money::format($fromPrice), 'currency' => \App\Support\Money::code()]) }}
                        <a href="{{ lroute('pricing') }}" class="site-link-arrow">{{ __('Ver todos los precios') }} <x-phosphor-arrow-right aria-hidden="true" /></a>
                    </p>
                </div>
                <div class="site-close__actions" data-reveal>
                    <a href="{{ \App\Support\DiyInvitations::startUrl($user) }}" class="site-btn site-btn--lg">
                        {{ __('Crear mi invitación gratis') }}
                        <x-phosphor-arrow-right class="site-btn__arrow" aria-hidden="true" />
                    </a>
                    @include('site.partials.custom-request', ['customLabel' => __('¿Prefieres que la armemos por ti?')])
                </div>
            </section>
        @endif

        @include('site.partials.faqs', ['faqs' => $faqs])
    </main>

    @include('site.partials.footer', ['socials' => $socials])
@endsection
