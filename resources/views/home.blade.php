@extends('layouts.site')

{{--
    Portada. Mobile first: cada bloque se lee primero en el celular y se abre en columnas en pantallas
    anchas. Sin numeraciones ni grillas de tarjetas: jerarquía con tipografía (serif editorial para los
    titulares), filetes finos y fotos. Datos: HomeController (paquetes, temporadas, servicios, muestras).
    Estilos: resources/css/site/site.css y resources/css/site/home.css.
--}}
@section('title', $bida['brand'].' | Invitaciones digitales para bodas, XV años, bautizos y graduaciones')
@section('description', 'Invitaciones digitales con confirmación de asistencia, pase QR y control de entrada, música, fotos y mapa. Se comparten por WhatsApp. Paquetes desde '.\App\Support\Money::format($fromPrice).'.')

@php
    $sections = array_filter([
        'plantillas' => count($demos) ? 'Plantillas' : null,
        'incluye' => 'Qué incluye',
        'precios' => 'Precios',
        'preguntas' => 'Preguntas',
    ]);
    $navLinks = collect($sections)->mapWithKeys(fn (string $label, string $id) => ['#'.$id => $label])->all()
        + [route('diy') => 'Hazlo tú'];
    $accountUrl = $user ? route('dashboard') : route('login');
    $accountLabel = $user ? 'Mi panel' : 'Ingresar';
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
        'Antes del evento' => [
            ['icon' => 'link-simple', 'title' => 'Un enlace que se abre en cualquier celular', 'text' => 'Portada, cuenta regresiva, itinerario y mapa con «Cómo llegar». Sin instalar nada.'],
            ['icon' => 'check-circle', 'title' => 'Confirmación en un toque', 'text' => 'Cada invitado tiene su enlace personal: dice si va, con cuántas personas y si tiene alguna restricción alimentaria.'],
            ['icon' => 'music-notes', 'title' => 'Música, fotos y video', 'text' => 'Tu canción de fondo, una galería que se desliza con el dedo y el video de tu save the date.'],
            ['icon' => 'chart-bar', 'title' => 'Encuestas y playlist', 'text' => 'Tus invitados votan y sugieren las canciones de la pista antes de la fiesta.'],
        ],
        'En la puerta' => [
            ['icon' => 'qr-code', 'title' => 'Pase QR y control de entrada', 'text' => 'En Premium, quien recibe a los invitados escanea el pase con su teléfono: ve si confirmó, cuántas personas entran y si ese pase ya se usó.'],
        ],
        'Durante y después' => [
            ['icon' => 'camera', 'title' => 'Fotomural en vivo', 'text' => 'Tus invitados suben fotos desde su celular y todos las ven al instante.'],
            ['icon' => 'images', 'title' => 'Las fotos, en el mismo enlace', 'text' => 'Pasado el evento, la invitación reúne las fotos oficiales y las de tus invitados.'],
            ['icon' => 'file-text', 'title' => 'Tu lista, lista para imprimir', 'text' => 'En Premium: invitados, confirmaciones y alimentación en PDF o Excel para el salón y el catering, y la invitación para imprimir.'],
        ],
    ];
    $steps = [
        ['icon' => 'chat-circle-text', 'title' => 'Nos escribes', 'text' => 'Cuéntanos qué celebras, la fecha y el lugar. Te ayudamos a elegir el paquete por WhatsApp.'],
        ['icon' => 'pencil-simple-line', 'title' => 'Diseñamos contigo', 'text' => 'Armamos tu invitación con tus fotos, colores y textos. Revisas la vista previa y pides cambios.'],
        ['icon' => 'paper-plane-tilt', 'title' => 'Compartes tu enlace', 'text' => 'Recibes el enlace general y, según tu paquete, uno personal para cada invitado.'],
        ['icon' => 'list-checks', 'title' => 'Sigues las confirmaciones', 'text' => 'Ves en tu panel quién confirmó, cuántas personas asistirán y, el día del evento, quién ya llegó.'],
    ];
    $faqs = [
        ['¿Qué es una invitación digital?', 'Es una página web con los datos de tu evento (fecha, lugar, itinerario, fotos y música) que tus invitados abren desde un enlace, por lo general enviado por WhatsApp. A diferencia de una imagen, permite confirmar asistencia, ver el mapa y recibir un pase de entrada con código QR.'],
        ['¿Para qué tipo de eventos sirve?', 'Bodas, XV años, bautizos, cumpleaños, graduaciones, fiestas de temporada como Halloween y cualquier celebración. Adaptamos los textos y las secciones a tu evento.'],
        ['¿Mis invitados necesitan instalar algo?', 'No. La invitación se abre en el navegador del celular desde el enlace que compartes.'],
        ['¿Cómo confirman asistencia y cómo se controla la entrada?', 'En el paquete Estándar, cada invitado recibe su enlace personal y confirma por WhatsApp: se abre el mensaje con su nombre y cuántas personas van. En Premium la respuesta queda guardada en tu panel y el invitado recibe un pase con código QR; el día del evento, quien recibe a los invitados lo escanea con su teléfono y ve si puede pasar.'],
        ['¿Puedo hacer cambios después de compartirla?', 'Sí. Horarios, ubicación y textos se actualizan en el mismo enlace, así tus invitados siempre ven la versión correcta.'],
        ['¿Cuánto tardan en entregarla?', 'Depende del paquete y de cuándo nos envíes las fotos y los datos. Te damos una fecha de entrega al confirmar tu pedido.'],
        ['¿Puedo armar mis propias invitaciones?', 'Sí. Con Hazlo tú tienes tu propio panel y nuestras plantillas por un plan mensual, sin comisión por invitación. Sirve si organizas varios eventos o si vendes invitaciones a tus clientes.'],
        ['¿Cómo se realiza el pago?', 'Coordinamos el pago por WhatsApp (transferencia o QR) cuando eliges tu paquete, antes de empezar el diseño. Los precios están en dólares.'],
    ];
    $socials = array_values(array_filter([
        ! empty($bida['instagram']) ? ['label' => 'Instagram', 'url' => 'https://www.instagram.com/'.$bida['instagram'].'/', 'icon' => 'instagram-logo'] : null,
        ! empty($bida['facebook']) ? ['label' => 'Facebook', 'url' => 'https://www.facebook.com/'.$bida['facebook'], 'icon' => 'facebook-logo'] : null,
        ! empty($bida['tiktok']) ? ['label' => 'TikTok', 'url' => 'https://www.tiktok.com/@'.$bida['tiktok'], 'icon' => 'tiktok-logo'] : null,
    ]));
    // El teléfono de la portada recorre la apertura de cada plantilla (se abren solas)
    $coverReel = array_map(function (array $demo) use ($showcase): array {
        $position = array_search($demo['eventKey'], array_column($showcase, 'event'), true);

        return ['url' => $demo['coverUrl'], 'label' => $demo['label'], 'rotator' => $position === false ? null : $position];
    }, $demos);
    [$mainService, $otherServices] = [$services[0] ?? null, array_slice($services, 1)];
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
                    <span class="sr-only">Invitaciones digitales para bodas, XV años, bautizos, cumpleaños y graduaciones</span>
                    <span aria-hidden="true">
                        Invitaciones digitales
                        <span class="site-hero__for">para
                            <span class="site-rotator site-hero__word" data-rotator-group>
                                @foreach($showcase as $index => $event)
                                    <span @class(['is-active' => $index === 0])>{{ $event['phrase'] }}</span>
                                @endforeach
                            </span>
                        </span>
                    </span>
                </h1>
                <p class="site-enter site-hero__lead" style="--enter-index: 1">
                    Tus invitados la abren desde WhatsApp, confirman en un toque y entran a la fiesta con su pase QR.
                </p>
                <div class="site-enter site-hero__actions" style="--enter-index: 2">
                    <a href="{{ count($demos) ? '#plantillas' : '#precios' }}" class="site-btn site-btn--lg">
                        {{ count($demos) ? 'Probar una invitación' : 'Ver precios' }}
                        <x-phosphor-arrow-down class="site-btn__arrow" aria-hidden="true" />
                    </a>
                    <a href="{{ $contactUrl }}" target="_blank" rel="noopener" class="site-btn site-btn--ghost site-btn--lg">
                        <x-phosphor-whatsapp-logo aria-hidden="true" />
                        Escríbenos
                    </a>
                </div>
                <ul class="site-enter site-hero__facts" style="--enter-index: 3">
                    <li>Desde {{ \App\Support\Money::format($fromPrice) }}</li>
                    <li>Sin instalar nada</li>
                    <li>Pase QR en la puerta</li>
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
                            <iframe src="{{ $demos[0]['coverUrl'] }}" title="Apertura de las invitaciones de muestra" tabindex="-1" aria-hidden="true" data-cover-reel-frame></iframe>
                        @elseif($demoUrl)
                            <iframe src="{{ $demoUrl }}" title="Vista previa de una invitación de ejemplo" loading="lazy" tabindex="-1" aria-hidden="true"></iframe>
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
        <section class="border-y border-site-line py-6 lg:py-8" aria-label="Eventos para los que diseñamos">
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

        {{-- ═══ Lo que ofrecemos: la invitación hecha por el equipo, lo de temporada y el panel propio ═══ --}}
        @if($mainService)
            <section id="servicios" class="site-offer scroll-mt-20" aria-labelledby="servicios-titulo">
                <div class="site-offer__intro">
                    <h2 id="servicios-titulo" class="site-display site-display--md" data-reveal>Tres maneras de tener tu invitación</h2>
                    <p class="site-muted-lead" data-reveal>La diseñamos contigo, la eliges de la temporada o la armas tú mismo.</p>
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
                                <span>Hola, soy Familia Rojas. Confirmo mi asistencia con 3 personas.</span>
                                <small>19:42 <x-phosphor-checks aria-hidden="true" /></small>
                            </div>
                        </figure>
                        <div class="site-offer__body">
                            <p class="site-overline">Te la diseñamos</p>
                            <h3 class="site-offer__name">{{ $mainService['name'] }}</h3>
                            <p class="site-offer__text">{{ $mainService['text'] }}</p>
                            <p class="site-offer__meta">
                                @if($mainService['fromPrice'])
                                    <span>Desde <b>{{ \App\Support\Money::format($mainService['fromPrice']) }}</b></span>
                                @endif
                                <span class="site-link-arrow">{{ $mainService['cta'] }} <x-phosphor-arrow-right aria-hidden="true" /></span>
                            </p>
                        </div>
                    </a>

                    <div class="site-offer__side">
                        @foreach($otherServices as $service)
                            <a href="{{ $service['url'] ?? $contactUrl }}" class="site-offer__row" data-reveal style="--reveal-index: {{ $loop->index + 1 }}"
                                @unless($service['url']) target="_blank" rel="noopener" @endunless>
                                <p class="site-overline">
                                    @if($service['live'])
                                        <span class="site-live-dot" aria-hidden="true"></span> Ahora: {{ $service['live'] }}
                                    @else
                                        {{ $service['key'] === 'hazlo' ? 'Tu propio panel' : 'Por temporada' }}
                                    @endif
                                </p>
                                <h3 class="site-offer__name site-offer__name--sm">{{ $service['name'] }}</h3>
                                <p class="site-offer__text">{{ $service['text'] }}</p>
                                <p class="site-offer__meta">
                                    @if($service['fromPrice'])
                                        <span>Desde <b>{{ \App\Support\Money::format($service['fromPrice']) }}</b>@if($service['priceSuffix'] ?? null) {{ $service['priceSuffix'] }}@endif</span>
                                    @endif
                                    <span class="site-link-arrow">{{ $service['cta'] }} <x-phosphor-arrow-right aria-hidden="true" /></span>
                                </p>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- ═══ Probar como invitado: a la izquierda los diseños y su descripción, a la derecha el teléfono ═══ --}}
        @if(count($demos))
            <section id="plantillas" class="site-tester scroll-mt-20" aria-labelledby="plantillas-titulo"
                x-data="{ active: 0, loading: true, demos: @js($demos), choose(index) { if (this.active !== index) { this.active = index; this.loading = true; } } }">
                <div class="site-tester__layout">
                    <div class="site-tester__side">
                        <div class="site-tester__head">
                            <h2 id="plantillas-titulo" class="site-display site-display--md" data-reveal>Pruébala como invitado</h2>
                            <p class="site-muted-lead" data-reveal>
                                Elige un diseño y úsalo dentro del teléfono: confirma, vota, sugiere una canción. Es una muestra, nada se guarda.
                            </p>
                        </div>

                        <div class="site-tester__picker" role="tablist" aria-label="Diseños para probar">
                            @foreach($demos as $index => $demo)
                                <button type="button" role="tab" id="plantilla-tab-{{ $index }}" aria-controls="plantilla-vista"
                                    aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                                    :aria-selected="(active === {{ $index }}).toString()"
                                    @click="choose({{ $index }}); $el.scrollIntoView({ inline: 'center', block: 'nearest', behavior: 'smooth' })"
                                    @class(['site-chip', 'is-active' => $index === 0])
                                    :class="{ 'is-active': active === {{ $index }} }">
                                    <span class="site-chip__icon" aria-hidden="true">
                                        <x-dynamic-component :component="'phosphor-'.$demo['icon']" />
                                    </span>
                                    <span class="min-w-0">
                                        <span class="site-chip__event">{{ $demo['event'] }}</span>
                                        <span class="site-chip__name">{{ $demo['label'] }}</span>
                                    </span>
                                </button>
                            @endforeach
                        </div>

                        <div class="site-tester__info">
                            @foreach($demos as $index => $demo)
                                <div x-show="active === {{ $index }}" @if($index > 0) x-cloak @endif>
                                    <p class="site-tester__text">
                                        <span class="site-tester__name">{{ $demo['label'] }}.</span>
                                        {{ $demo['description'] }}
                                    </p>
                                    <div class="site-tester__actions">
                                        <a href="{{ $demo['demoUrl'] }}" target="_blank" rel="noopener" class="site-btn site-btn--ghost">
                                            Abrir en pantalla completa
                                            <x-phosphor-arrow-up-right class="site-btn__arrow" aria-hidden="true" />
                                        </a>
                                        @php($eventLanding = collect($landings)->firstWhere('event', $demo['eventKey']))
                                        @if($eventLanding)
                                            <a href="{{ $eventLanding['url'] }}" class="site-link-arrow">
                                                {{ $eventLanding['label'] }} <x-phosphor-arrow-right aria-hidden="true" />
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="site-tester__device" data-reveal>
                        <div class="site-phone site-phone--showcase">
                            <div id="plantilla-vista" role="tabpanel" aria-labelledby="plantilla-tab-0"
                                :aria-labelledby="'plantilla-tab-' + active"
                                class="site-phone__screen" :class="{ 'is-loading': loading }">
                                <iframe src="{{ $demos[0]['demoUrl'] }}" :src="demos[active].demoUrl"
                                    title="Invitación de muestra: {{ $demos[0]['title'] }}" :title="'Invitación de muestra: ' + demos[active].title"
                                    loading="lazy" @load="loading = false"></iframe>
                            </div>
                        </div>
                        <p class="site-tester__hint">
                            <x-phosphor-hand-tap aria-hidden="true" />
                            Toca y desliza dentro del teléfono
                        </p>
                    </div>
                </div>
            </section>
        @endif

        {{-- ═══ Qué incluye: agrupado por el momento del evento en que se usa ═══ --}}
        <section id="incluye" class="site-include scroll-mt-20" aria-labelledby="incluye-titulo">
            <div class="site-include__aside">
                <div class="lg:sticky lg:top-28">
                    <h2 id="incluye-titulo" class="site-display site-display--md" data-reveal>Todo lo que tu invitación puede hacer</h2>
                    <p class="site-muted-lead" data-reveal>Antes, en la puerta y después del evento. Eliges lo que necesitas; todo se ve bien en el celular.</p>
                    <figure class="site-photo site-photo--caption mt-8 aspect-[4/3] lg:aspect-[4/5]" data-reveal>
                        <x-site.image key="servicio-fotomural" />
                        <figcaption>
                            <span>Durante la fiesta</span>
                            Las fotos de tus invitados, al instante en el fotomural.
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

        {{-- ═══ Cómo trabajamos: la línea se dibuja con el scroll ═══ --}}
        <section class="border-t border-site-line">
            <div class="mx-auto grid max-w-7xl gap-12 px-5 py-20 lg:grid-cols-12 lg:gap-8 lg:px-8 lg:py-28">
                <div class="lg:col-span-5">
                    <div class="lg:sticky lg:top-32">
                        <h2 class="site-display site-display--md" data-reveal>Así trabajamos</h2>
                        <p class="site-muted-lead" data-reveal>De tu primer mensaje a las confirmaciones de tus invitados.</p>
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

        @include('site.partials.plans')

        @include('site.partials.faqs', ['faqs' => $faqs])
    </main>

    @include('site.partials.footer', ['navLinks' => $navLinks, 'socials' => $socials])

    {{-- ═══ Temporadas: un botón discreto abajo a la derecha que abre las que se venden hoy ═══ --}}
    @if(count($seasons))
        @include('site.partials.season', ['seasons' => $seasons])
    @endif
@endsection
