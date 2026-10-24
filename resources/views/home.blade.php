@extends('layouts.site')

{{--
    Portada. Mobile first: cada bloque se lee primero en el celular y se abre en columnas en pantallas
    anchas. Sin numeraciones ni grillas de tarjetas: jerarquía con tipografía (serif editorial para los
    titulares), filetes finos y fotos. Poco texto: lo que decide, con íconos donde un ícono basta.
    Datos: HomeController (temporadas, servicios, muestras).
    Estilos: resources/css/site/site.css y resources/css/site/home.css.
--}}
@section('title', $bida['brand'].' | '.__('Crea tu invitación digital para bodas, XV años, bautizos y graduaciones'))
@section('description', __('Arma tu invitación digital gratis con plantillas profesionales y paga solo si quieres más: confirmación de asistencia, galería y pase QR.'))

@php
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
    // El recorrido: la persona arma su propia invitación gratis y paga solo si quiere más
    $steps = [
        ['icon' => 'user-plus', 'title' => __('Creas tu cuenta'), 'text' => __('Gratis, con tu correo o Google.')],
        ['icon' => 'pencil-simple-line', 'title' => __('Armas tu invitación'), 'text' => __('Fotos, colores y textos en vivo.')],
        ['icon' => 'paper-plane-tilt', 'title' => __('La compartes'), 'text' => __('Gratis o pago único, por WhatsApp.')],
        ['icon' => 'list-checks', 'title' => __('Sigues las confirmaciones'), 'text' => __('Quién va, con cuántos y quién llegó.')],
    ];
    // Lo que puede incluir, en el orden del evento: antes, en la puerta, durante y después
    $includes = [
        ['icon' => 'link-simple', 'title' => __('Un enlace para cualquier celular'), 'text' => __('Portada, cuenta regresiva, itinerario y mapa.')],
        ['icon' => 'check-circle', 'title' => __('Confirmación en un toque'), 'text' => __('Dice si va, con cuántos y qué come.')],
        ['icon' => 'music-notes', 'title' => __('Música, fotos y video'), 'text' => __('Tu canción, una galería y tu save the date en video.')],
        ['icon' => 'chart-bar', 'title' => __('Encuestas y playlist'), 'text' => __('Tus invitados votan y sugieren canciones.')],
        ['icon' => 'qr-code', 'title' => __('Pase QR en la puerta'), 'text' => __('Se escanea con el teléfono: confirmó y si ya se usó.')],
        ['icon' => 'camera', 'title' => __('Fotomural en vivo'), 'text' => __('Tus invitados suben fotos y todos las ven al instante.')],
        ['icon' => 'file-text', 'title' => __('Lista para imprimir'), 'text' => __('Invitados y confirmaciones en PDF o Excel.')],
    ];
    // Las que deciden una compra; el resto está en la guía
    $faqs = [
        [__('¿Puedo armar mi propia invitación?'), __('Sí. Creas tu cuenta gratis, eliges una plantilla y la armas con todas las partes que quieras. Al publicarla decides: gratis con lo básico, o un pago único si usaste más. Si armas muchas, hay planes.')],
        [__('¿Cómo se paga?'), __('Con tarjeta o con el QR de cualquier banco de Bolivia. Hazlo tú se paga una vez al publicar; los planes, por mes o por varios meses. Los precios están en dólares.')],
        [__('¿Mis invitados necesitan instalar algo?'), __('No. Se abre en el navegador del celular desde el enlace que compartes.')],
        [__('¿Puedo hacer cambios después de compartirla?'), __('Sí. Horarios, ubicación y textos se actualizan en el mismo enlace.')],
        [__('¿Hacen invitaciones a medida?'), __('Sí. Escríbenos por WhatsApp o al correo :email y lo conversamos.', ['email' => $bida['email']])],
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
    // Lo de temporada no es un precio más: va en una línea bajo los precios, con su panel o su página
    $seasonService = collect($services)->firstWhere('key', 'temporada');
@endphp

@push('head')
    @include('site.partials.structured-data', ['faqs' => $faqs])
@endpush

@section('content')
    <div data-header-sentinel class="pointer-events-none absolute inset-x-0 top-0 h-4" aria-hidden="true"></div>

    @include('site.partials.header', ['hasTemplates' => count($designs) > 0])

    <main id="contenido" tabindex="-1">
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
                    {{ __('Elige una plantilla, pon tus fotos y compártela por WhatsApp. Tus invitados confirman en un toque.') }}
                </p>
                {{-- La acción de la portada: crear la invitación gratis (el modelo principal); ver las muestras, una flecha --}}
                <div class="site-enter site-hero__actions" style="--enter-index: 2">
                    <a href="{{ $startUrl }}" class="site-btn site-btn--lg">
                        {{ __('Crear mi invitación gratis') }}
                        <x-phosphor-arrow-right class="site-btn__arrow" aria-hidden="true" />
                    </a>
                    @if(count($designs))
                        <a href="#plantillas" class="site-btn site-btn--ghost site-btn--lg site-btn--icon" aria-label="{{ __('Ver muestras') }}" title="{{ __('Ver muestras') }}">
                            <x-phosphor-arrow-down aria-hidden="true" />
                        </a>
                    @endif
                </div>
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

        {{-- ═══ Probar como invitado: los diseños de cada evento como capturas de cómo empieza su apertura ═══ --}}
        @if(count($designs))
            <section id="plantillas" class="site-tester scroll-mt-20" aria-labelledby="plantillas-titulo" x-data="{ active: 0 }">
                <div class="site-tester__layout">
                    <div class="site-tester__head">
                        <h2 id="plantillas-titulo" class="site-display site-display--md" data-reveal>{{ __('Pruébala como invitado') }}</h2>
                        <p class="site-muted-lead" data-reveal>
                            {{ __('Toca un diseño para abrir su muestra y pruébala: nada se guarda.') }}
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

        {{-- ═══ Cómo funciona y qué puede incluir: los cuatro pasos y, al lado, lo que se puede encender ═══ --}}
        <section id="incluye" class="site-how scroll-mt-20" aria-labelledby="incluye-titulo">
            <div class="site-how__steps">
                <h2 id="incluye-titulo" class="site-display site-display--md" data-reveal>{{ __('Así funciona') }}</h2>

                <ol class="site-steps">
                    @foreach($steps as $step)
                        <li class="site-step" data-reveal>
                            <span class="site-step__node">
                                <x-dynamic-component :component="'phosphor-'.$step['icon'].'-light'" class="size-5" aria-hidden="true" />
                            </span>
                            <div>
                                <h3 class="site-step__title">{{ $step['title'] }}</h3>
                                <p class="site-step__text">{{ $step['text'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>

            <div class="site-how__includes">
                <h2 class="site-display site-display--sm" data-reveal>{{ __('Y puede incluir') }}</h2>

                <ul class="site-include">
                    @foreach($includes as $item)
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
        </section>

        {{-- ═══ Precios: las formas en resumen (HomeController::pricingSummary); el detalle, en /precios ═══ --}}
        <section id="precios" class="site-pricing scroll-mt-20" aria-labelledby="precios-titulo">
            {{-- Foto real de una pareja con su invitación y, encima, la confirmación que llega por WhatsApp (solo en pantallas anchas) --}}
            <figure class="site-offer__visual site-pricing__photo">
                <img src="{{ \App\Support\SiteImage::url('servicio-pareja') }}" alt="{{ \App\Support\SiteImage::alt('servicio-pareja') }}"
                    width="{{ \App\Support\SiteImage::size('servicio-pareja')[0] }}" height="{{ \App\Support\SiteImage::size('servicio-pareja')[1] }}"
                    loading="lazy" decoding="async">
                <div class="site-offer__bubble" aria-hidden="true">
                    <span>{{ __('Hola, soy Familia Rojas. Confirmo mi asistencia con 3 personas.') }}</span>
                    <small>19:42 <x-phosphor-checks aria-hidden="true" /></small>
                </div>
            </figure>

            <div class="site-pricing__body">
                <h2 id="precios-titulo" class="site-display site-display--md" data-reveal>{{ __('Empieza gratis. Paga solo por lo que usas.') }}</h2>

                <div class="site-plans site-plans--duo">
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

                <div class="site-pricing__foot">
                    @if($seasonService && $seasonService['url'])
                        <a href="{{ $seasonService['url'] }}" class="site-pricing__season">
                            <x-phosphor-sparkle-light aria-hidden="true" />
                            @if($seasonService['live'])
                                <span class="site-live-dot" aria-hidden="true"></span>
                                {{ __('Ahora: :live', ['live' => $seasonService['live']]) }}
                            @else
                                {{ $seasonService['name'] }}
                            @endif
                        </a>
                    @endif

                    @include('site.partials.custom-request')

                    <a href="{{ lroute('pricing') }}" class="site-link-arrow">
                        {{ __('Ver todos los precios') }}
                        <x-phosphor-arrow-right aria-hidden="true" />
                    </a>
                </div>
            </div>
        </section>

        @include('site.partials.faqs', ['faqs' => $faqs])
    </main>

    @include('site.partials.footer', ['socials' => $socials])

    {{-- ═══ Temporadas: un botón discreto abajo a la derecha que abre las que se venden hoy ═══ --}}
    @if(count($seasons))
        @include('site.partials.season', ['seasons' => $seasons])
    @endif
@endsection
