@extends('layouts.site')

{{--
    Portada. Mobile first: cada bloque se lee primero en el celular y se abre en columnas en pantallas
    anchas. Sin numeraciones ni grillas de tarjetas: jerarquía con tipografía (serif editorial para los
    titulares), filetes finos y fotos. Datos: HomeController (temporadas, servicios, muestras).
    Estilos: resources/css/site/site.css y resources/css/site/home.css.
--}}
@section('title', $bida['brand'].' | '.__('Crea tu invitación digital para bodas, XV años, bautizos y graduaciones'))
@section('description', __('Arma tu invitación digital gratis con plantillas profesionales y paga solo si quieres más: confirmación de asistencia, galería y pase QR.'))

@php
    // Lo principal es armarla (las plantillas abren el menú); los precios de las tres formas, en su bloque y en /precios
    $sections = array_filter([
        'plantillas' => count($designs) ? __('Plantillas') : null,
        'incluye' => __('Qué incluye'),
        'precios' => __('Precios'),
        'preguntas' => __('Preguntas'),
    ]);
    $navLinks = collect($sections)->mapWithKeys(fn (string $label, string $id) => ['#'.$id => $label])->all();
    $accountUrl = $user ? route('dashboard') : lroute('login');
    $accountLabel = $user ? __('Mi panel') : __('Ingresar');
    $showcase = $bida['showcase'];
    // Con muestras, las fotos y la palabra de la portada siguen el orden del teléfono para cambiar junto con él
    if (count($demos)) {
        $ordered = [];
        foreach (array_column($demos, 'eventKey') as $eventKey) {
            foreach ($showcase as $position => $event) {
                if (($event['event'] ?? null) === $eventKey) {
                    $ordered[] = $event;
                    unset($showcase[$position]);
                    break;
                }
            }
        }
        $showcase = array_merge($ordered, array_values($showcase));
    }
    // Qué puede incluir una invitación, agrupado por el momento del evento en que se usa
    $moments = [
        __('Antes del evento') => [
            ['icon' => 'link-simple', 'title' => __('Un enlace que se abre en cualquier celular'), 'text' => __('Portada, cuenta regresiva, itinerario y mapa con «Cómo llegar». Sin instalar nada.')],
            ['icon' => 'check-circle', 'title' => __('Confirmación en un toque'), 'text' => __('Cada invitado tiene su enlace personal: dice si va, con cuántas personas y si tiene alguna restricción alimentaria.')],
            ['icon' => 'music-notes', 'title' => __('Música, fotos y video'), 'text' => __('Tu canción de fondo, una galería que se desliza con el dedo y el video de tu save the date.')],
            ['icon' => 'chart-bar', 'title' => __('Encuestas y playlist'), 'text' => __('Tus invitados votan y sugieren las canciones de la pista antes de la fiesta.')],
        ],
        __('En la puerta') => [
            ['icon' => 'qr-code', 'title' => __('Pase QR y control de entrada'), 'text' => __('En Premium, quien recibe a los invitados escanea el pase con su teléfono: ve si confirmó, cuántas personas entran y si ese pase ya se usó.')],
        ],
        __('Durante y después') => [
            ['icon' => 'camera', 'title' => __('Fotomural en vivo'), 'text' => __('Tus invitados suben fotos desde su celular y todos las ven al instante.')],
            ['icon' => 'images', 'title' => __('Las fotos, en el mismo enlace'), 'text' => __('Pasado el evento, la invitación reúne las fotos oficiales y las de tus invitados.')],
            ['icon' => 'file-text', 'title' => __('Tu lista, lista para imprimir'), 'text' => __('En Premium: invitados, confirmaciones y alimentación en PDF o Excel para el salón y el catering, y la invitación para imprimir.')],
        ],
    ];
    // El recorrido principal: la persona arma su propia invitación gratis y paga solo si quiere más
    $steps = [
        ['icon' => 'user-plus', 'title' => __('Creas tu cuenta'), 'text' => __('Gratis, con tu correo o con Google. Eliges qué celebras y el diseño que más te guste.')],
        ['icon' => 'pencil-simple-line', 'title' => __('Armas tu invitación'), 'text' => __('Subes tus fotos y cambias colores, letras y textos mientras la ves en vivo. Prueba todas las partes que quieras.')],
        ['icon' => 'paper-plane-tilt', 'title' => __('La publicas y la compartes'), 'text' => __('Gratis con lo básico, o con un pago único si usaste más. Envías tu enlace por WhatsApp; tus invitados la abren sin instalar nada.')],
        ['icon' => 'list-checks', 'title' => __('Sigues las confirmaciones'), 'text' => __('Ves en tu panel quién confirmó, cuántas personas asistirán y, el día del evento, quién ya llegó.')],
    ];
    $faqs = [
        [__('¿Puedo armar mi propia invitación?'), __('Sí, es la forma principal de usar :brand. Creas tu cuenta gratis, eliges una plantilla y la armas con todas las partes que quieras. Al publicarla decides: gratis con la portada, la cuenta regresiva y la ubicación, o con un pago único por todo lo que usaste. Si armas invitaciones cada mes (las tuyas o las de tus clientes), hay planes para eso.', ['brand' => $bida['brand']])],
        [__('¿Qué es una invitación digital?'), __('Es una página web con los datos de tu evento (fecha, lugar, itinerario, fotos y música) que tus invitados abren desde un enlace, por lo general enviado por WhatsApp. A diferencia de una imagen, permite confirmar asistencia, ver el mapa y recibir un pase de entrada con código QR.')],
        [__('¿Para qué tipo de eventos sirve?'), __('Bodas, XV años, bautizos, cumpleaños, graduaciones, fiestas de temporada como Halloween y cualquier celebración. Adaptamos los textos y las secciones a tu evento.')],
        [__('¿Mis invitados necesitan instalar algo?'), __('No. La invitación se abre en el navegador del celular desde el enlace que compartes.')],
        [__('¿Cómo confirman asistencia y cómo se controla la entrada?'), __('En el nivel Estándar, cada invitado recibe su enlace personal y confirma por WhatsApp: se abre el mensaje con su nombre y cuántas personas van. En Premium la respuesta queda guardada en tu panel y el invitado recibe un pase con código QR; el día del evento, quien recibe a los invitados lo escanea con su teléfono y ve si puede pasar.')],
        [__('¿Puedo hacer cambios después de compartirla?'), __('Sí. Horarios, ubicación y textos se actualizan en el mismo enlace, así tus invitados siempre ven la versión correcta.')],
        [__('¿Cuánto tardo en tenerla lista?'), __('La publicas cuando quieras, apenas esté lista.')],
        [__('¿Hacen invitaciones a medida?'), __('Si quieres algo a medida, escríbenos por WhatsApp o al correo :email y lo conversamos.', ['email' => $bida['email']])],
        [__('¿Cómo se realiza el pago?'), __('La invitación que armas tú se paga una sola vez al publicarla (si no es la gratuita), con tarjeta o con el QR de cualquier banco de Bolivia. Los planes se pagan por mes o por varios meses de una vez. Los precios están en dólares.')],
    ];
    $socials = array_values(array_filter([
        ! empty($bida['instagram']) ? ['label' => 'Instagram', 'url' => 'https://www.instagram.com/'.$bida['instagram'].'/', 'icon' => 'instagram-logo'] : null,
        ! empty($bida['facebook']) ? ['label' => 'Facebook', 'url' => 'https://www.facebook.com/'.$bida['facebook'], 'icon' => 'facebook-logo'] : null,
        ! empty($bida['tiktok']) ? ['label' => 'TikTok', 'url' => 'https://www.tiktok.com/@'.$bida['tiktok'], 'icon' => 'tiktok-logo'] : null,
    ]));
    // El teléfono de la portada recorre la apertura de cada plantilla (se abren solas). Solo las de los
    // eventos que tienen foto y palabra en la portada: el baby shower se prueba abajo, en «Pruébala como invitado»
    $coverReel = array_values(array_filter(array_map(function (array $demo) use ($showcase): ?array {
        $position = array_search($demo['eventKey'], array_column($showcase, 'event'), true);

        return $position === false ? null : ['url' => $demo['coverUrl'], 'label' => $demo['label'], 'rotator' => $position];
    }, $demos)));
    [$mainService, $otherServices] = [$services[0] ?? null, array_slice($services, 1)];
    $serviceOverlines = ['hazlo' => __('La armas tú'), 'planes' => __('Si armas muchas'), 'temporada' => __('Por temporada')];
@endphp

@push('head')
    @include('site.partials.structured-data', ['faqs' => $faqs])
@endpush

@section('content')
    <div data-header-sentinel class="pointer-events-none absolute inset-x-0 top-0 h-4" aria-hidden="true"></div>

    @include('site.partials.header', ['navLinks' => $navLinks])

    <main>
        {{-- ═══ Portada: la palabra del evento y la foto cambian con la apertura que muestra el teléfono ═══ --}}
        <section data-rotator data-rotator-interval="3200" @if(count($demos)) data-rotator-driven @endif class="site-hero">
            <div class="site-hero__copy">
                <h1 class="site-enter site-display">
                    <span class="sr-only">{{ __('Arma tu invitación digital para bodas, XV años, bautizos, cumpleaños y graduaciones') }}</span>
                    <span aria-hidden="true">
                        {{ __('Arma tu invitación digital') }}
                        <span class="site-hero__for">{{ __('para') }}
                            <span class="site-rotator site-hero__word" data-rotator-group>
                                @foreach($showcase as $index => $event)
                                    <span @class(['is-active' => $index === 0])>{{ $event['phrase'] }}</span>
                                @endforeach
                            </span>
                        </span>
                    </span>
                </h1>
                <p class="site-enter site-hero__lead" style="--enter-index: 1">
                    {{ __('Elige una plantilla, pon tus fotos y compártela por WhatsApp. Tus invitados confirman en un toque y entran a la fiesta con su pase QR.') }}
                </p>
                {{-- La acción de la portada: crear la invitación gratis (el modelo principal); ver las muestras queda a un toque --}}
                <div class="site-enter site-hero__actions" style="--enter-index: 2">
                    <a href="{{ $startUrl }}" class="site-btn site-btn--lg">
                        {{ __('Crear mi invitación gratis') }}
                        <x-phosphor-arrow-right class="site-btn__arrow" aria-hidden="true" />
                    </a>
                    @if(count($designs))
                        <a href="#plantillas" class="site-btn site-btn--ghost site-btn--lg">{{ __('Ver muestras') }}</a>
                    @endif
                </div>
                <ul class="site-enter site-hero__facts" style="--enter-index: 3">
                    <li>{{ __('La armas tú, gratis') }}</li>
                    <li>{{ __('Sin instalar nada') }}</li>
                    <li>{{ __('Pase QR en la puerta') }}</li>
                </ul>
            </div>

            <div class="site-hero__stage">
                <div class="site-stage site-hero__photo" data-rotator-group>
                    @foreach($showcase as $index => $event)
                        <x-site.image :key="$event['image']" :priority="$index === 0" :class="$index === 0 ? 'is-active' : ''" />
                    @endforeach
                </div>

                <div class="site-phone site-hero__phone"
                    @if(count($demos)) data-cover-reel="{{ json_encode($coverReel, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}" @endif>
                    <div class="site-phone__screen">
                        @if(count($demos))
                            <iframe data-defer-src="{{ $demos[0]['coverUrl'] }}" data-defer-until-idle title="{{ __('Apertura de las invitaciones de muestra') }}" tabindex="-1" aria-hidden="true" data-cover-reel-frame></iframe>
                        @elseif($demoUrl)
                            <iframe src="{{ $demoUrl }}" title="{{ __('Vista previa de una invitación de ejemplo') }}" loading="lazy" tabindex="-1" aria-hidden="true"></iframe>
                        @endif
                    </div>

                    @if(count($demos))
                        <p class="site-phone__tag" aria-hidden="true">
                            <span class="site-phone__tag-dot"></span>
                            <span data-cover-reel-label>{{ $demos[0]['label'] }}</span>
                        </p>
                    @endif
                </div>
            </div>
        </section>

        {{-- ═══ Tipos de evento ═══ --}}
        <section class="border-y border-site-line py-6 lg:py-8" aria-label="{{ __('Eventos para los que diseñamos') }}">
            <div class="site-marquee">
                <div class="site-marquee__track">
                    @foreach([false, true] as $isCopy)
                        <ul class="site-marquee__list" @if($isCopy) aria-hidden="true" @endif>
                            @foreach($bida['event_types'] as $name => $icon)
                                <li class="site-marquee__item">
                                    <x-dynamic-component :component="'phosphor-'.$icon.'-light'" aria-hidden="true" />
                                    {{ $name }}
                                </li>
                            @endforeach
                        </ul>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ═══ Lo que ofrecemos: armarla tú, los planes y lo de temporada ═══ --}}
        @if($mainService)
            <section id="servicios" class="site-offer scroll-mt-20" aria-labelledby="servicios-titulo">
                <div class="site-offer__intro">
                    <h2 id="servicios-titulo" class="site-display site-display--md" data-reveal>{{ __('Elige cómo quieres tu invitación') }}</h2>
                    <p class="site-muted-lead" data-reveal>{{ __('La armas tú con tu propio panel, con un plan o la eliges de la temporada.') }}</p>
                </div>

                <div class="site-offer__grid">
                    <a href="{{ $mainService['url'] ?? $contactUrl }}" class="site-offer__main" data-reveal>
                        {{-- Foto real: una pareja mirando su invitación en el teléfono, y encima la respuesta que
                             llega por WhatsApp cuando un invitado confirma --}}
                        <figure class="site-offer__visual">
                            <img src="{{ \App\Support\SiteImage::url('servicio-pareja') }}" alt="{{ \App\Support\SiteImage::alt('servicio-pareja') }}"
                                width="{{ \App\Support\SiteImage::size('servicio-pareja')[0] }}" height="{{ \App\Support\SiteImage::size('servicio-pareja')[1] }}"
                                loading="lazy" decoding="async">
                            <div class="site-offer__bubble" aria-hidden="true">
                                <span>{{ __('Hola, soy Familia Rojas. Confirmo mi asistencia con 3 personas.') }}</span>
                                <small>19:42 <x-phosphor-checks aria-hidden="true" /></small>
                            </div>
                        </figure>
                        <div class="site-offer__body">
                            <p class="site-overline">{{ $serviceOverlines[$mainService['key']] ?? '' }}</p>
                            <h3 class="site-offer__name">{{ $mainService['name'] }}</h3>
                            <p class="site-offer__text">{{ $mainService['text'] }}</p>
                            <p class="site-offer__meta">
                                @if($mainService['priceLabel'] ?? null)
                                    <span><b>{{ $mainService['priceLabel'] }}</b></span>
                                @elseif($mainService['fromPrice'])
                                    <span>{{ __('Desde') }} <b>{{ \App\Support\Money::format($mainService['fromPrice']) }}</b>@if($mainService['priceSuffix'] ?? null) {{ $mainService['priceSuffix'] }}@endif</span>
                                @endif
                                <span class="site-link-arrow">{{ $mainService['cta'] }} <x-phosphor-arrow-right aria-hidden="true" /></span>
                            </p>
                        </div>
                    </a>

                    <div class="site-offer__side" style="--offer-cols: {{ min(3, max(1, count($otherServices))) }}">
                        @foreach($otherServices as $service)
                            <a href="{{ $service['url'] ?? $contactUrl }}" class="site-offer__row" data-reveal style="--reveal-index: {{ $loop->index + 1 }}"
                                @unless($service['url']) target="_blank" rel="noopener" @endunless>
                                <p class="site-overline">
                                    @if($service['live'])
                                        <span class="site-live-dot" aria-hidden="true"></span> {{ __('Ahora: :live', ['live' => $service['live']]) }}
                                    @else
                                        {{ $serviceOverlines[$service['key']] ?? __('Por temporada') }}
                                    @endif
                                </p>
                                <h3 class="site-offer__name site-offer__name--sm">{{ $service['name'] }}</h3>
                                <p class="site-offer__text">{{ $service['text'] }}</p>
                                <p class="site-offer__meta">
                                    @if($service['priceLabel'] ?? null)
                                        <span><b>{{ $service['priceLabel'] }}</b></span>
                                    @elseif($service['fromPrice'])
                                        <span>{{ __('Desde') }} <b>{{ \App\Support\Money::format($service['fromPrice']) }}</b>@if($service['priceSuffix'] ?? null) {{ $service['priceSuffix'] }}@endif</span>
                                    @endif
                                    <span class="site-link-arrow">{{ $service['cta'] }} <x-phosphor-arrow-right aria-hidden="true" /></span>
                                </p>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- ═══ Probar como invitado: los diseños de cada evento como capturas de cómo empieza su apertura ═══ --}}
        @if(count($designs))
            <section id="plantillas" class="site-tester scroll-mt-20" aria-labelledby="plantillas-titulo" x-data="{ active: 0 }">
                <div class="site-tester__layout">
                    <div class="site-tester__head">
                        <h2 id="plantillas-titulo" class="site-display site-display--md" data-reveal>{{ __('Pruébala como invitado') }}</h2>
                        <p class="site-muted-lead" data-reveal>
                            {{ __('Así empieza cada diseño. Toca uno para abrir su muestra completa y pruébala como un invitado: confirma, vota o sugiere una canción. Nada se guarda.') }}
                        </p>
                    </div>

                    {{-- Índice de eventos: el nombre en la letra de los títulos y cuántos diseños tiene; el
                         elegido se subraya con un filete dorado --}}
                    <div class="site-tester__picker site-events" role="tablist" aria-label="{{ __('Diseños por evento') }}">
                        @foreach($designs as $index => $group)
                            <button type="button" role="tab" id="plantilla-tab-{{ $index }}" aria-controls="plantilla-panel-{{ $index }}"
                                aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                                :aria-selected="(active === {{ $index }}).toString()"
                                @click="active = {{ $index }}; $el.scrollIntoView({ inline: 'center', block: 'nearest', behavior: 'smooth' })"
                                @class(['site-events__tab', 'is-active' => $index === 0])
                                :class="{ 'is-active': active === {{ $index }} }">
                                <span class="site-events__name">{{ $group['label'] }}</span>
                                <span class="site-events__count">{{ count($group['demos']) }}<span class="sr-only"> {{ count($group['demos']) === 1 ? __('diseño') : __('diseños') }}</span></span>
                            </button>
                        @endforeach
                    </div>

                    @foreach($designs as $index => $group)
                        <div id="plantilla-panel-{{ $index }}" role="tabpanel" aria-labelledby="plantilla-tab-{{ $index }}" class="site-tester__panel"
                            x-show="active === {{ $index }}" @if($index > 0) x-cloak @endif>
                            @include('site.partials.shots', ['demos' => $group['demos'], 'noun' => __('invitación'), 'layout' => 'row', 'label' => __('Diseños de :label', ['label' => $group['label']])])
                            <a href="{{ $group['landingUrl'] }}" class="site-link-arrow site-tester__more">
                                {{ $group['landingLabel'] }} <x-phosphor-arrow-right aria-hidden="true" />
                            </a>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ═══ Qué incluye: agrupado por el momento del evento en que se usa ═══ --}}
        <section id="incluye" class="site-include scroll-mt-20" aria-labelledby="incluye-titulo">
            <div class="site-include__aside">
                <div class="lg:sticky lg:top-28">
                    <h2 id="incluye-titulo" class="site-display site-display--md" data-reveal>{{ __('Todo lo que tu invitación puede hacer') }}</h2>
                    <p class="site-muted-lead" data-reveal>{{ __('Antes, en la puerta y después del evento. Eliges lo que necesitas; todo se ve bien en el celular.') }}</p>
                    <figure class="site-photo site-photo--caption mt-8 aspect-[4/3] lg:aspect-[4/5]" data-reveal>
                        <x-site.image key="servicio-fotomural" />
                        <figcaption>
                            <span>{{ __('Durante la fiesta') }}</span>
                            {{ __('Las fotos de tus invitados, al instante en el fotomural.') }}
                        </figcaption>
                    </figure>
                </div>
            </div>

            <div class="site-include__list">
                @foreach($moments as $moment => $items)
                    <div class="site-include__moment">
                        <h3 class="site-overline" data-reveal>{{ $moment }}</h3>
                        <ul>
                            @foreach($items as $item)
                                <li class="site-include__item" data-reveal>
                                    <x-dynamic-component :component="'phosphor-'.$item['icon'].'-light'" class="site-include__icon" aria-hidden="true" />
                                    <div>
                                        <p class="site-include__title">{{ $item['title'] }}</p>
                                        <p class="site-include__text">{{ $item['text'] }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ═══ Cómo funciona: el recorrido de quien arma su invitación; la línea se dibuja con el scroll ═══ --}}
        <section class="border-t border-site-line">
            <div class="mx-auto grid max-w-7xl gap-12 px-5 py-20 lg:grid-cols-12 lg:gap-8 lg:px-8 lg:py-28">
                <div class="lg:col-span-5">
                    <div class="lg:sticky lg:top-32">
                        <h2 class="site-display site-display--md" data-reveal>{{ __('Así funciona') }}</h2>
                        <p class="site-muted-lead" data-reveal>{{ __('De crear tu cuenta a las confirmaciones de tus invitados. Pagas solo si quieres más.') }}</p>
                        <a href="{{ $startUrl }}" class="site-btn mt-8" data-reveal>
                            {{ __('Crear mi invitación gratis') }}
                            <x-phosphor-arrow-right class="site-btn__arrow" aria-hidden="true" />
                        </a>
                    </div>
                </div>

                <ol class="site-steps grid gap-12 lg:col-span-6 lg:col-start-7 lg:gap-16">
                    @foreach($steps as $step)
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

        {{-- ═══ Precios: las formas en resumen (HomeController::pricingSummary); el detalle, en /precios ═══ --}}
        <section id="precios" class="scroll-mt-20 border-t border-site-line bg-site-surface" aria-labelledby="precios-titulo">
            <div class="mx-auto max-w-7xl px-5 py-20 lg:px-8 lg:py-28">
                <div class="max-w-3xl">
                    <h2 id="precios-titulo" class="site-display site-display--md" data-reveal>{{ __('Empieza gratis. Paga solo por lo que usas.') }}</h2>
                    <p class="site-muted-lead" data-reveal>
                        {{ __('Ármala tú o, si armas muchas, elige un plan. Precios en dólares (:value).', ['value' => \App\Support\Money::code()]) }}
                    </p>
                </div>

                <div class="site-plans mt-14">
                    @foreach($pricingSummary as $index => $way)
                        <x-site.price-card
                            :name="$way['name']"
                            :price="$way['price']"
                            :old="$way['old'] ?? null"
                            :suffix="$way['suffix']"
                            :summary="$way['summary']"
                            :features="$way['features']"
                            :featured="$way['featured'] ?? false"
                            :tag="($way['featured'] ?? false) ? __('Para empezar') : null"
                            :url="$way['url']"
                            :cta="$way['cta']"
                            :index="$index"
                            :reserve="true" />
                    @endforeach
                </div>

                @include('site.partials.custom-request')

                <p class="mt-6" data-reveal>
                    <a href="{{ lroute('pricing') }}" class="site-link-arrow">
                        {{ __('Ver todos los precios y qué trae cada nivel') }}
                        <x-phosphor-arrow-right aria-hidden="true" />
                    </a>
                </p>
            </div>
        </section>

        @include('site.partials.faqs', ['faqs' => $faqs])
    </main>

    @include('site.partials.footer', ['navLinks' => $navLinks, 'socials' => $socials])

    {{-- ═══ Temporadas: un botón discreto abajo a la derecha que abre las que se venden hoy ═══ --}}
    @if(count($seasons))
        @include('site.partials.season', ['seasons' => $seasons])
    @endif
@endsection
