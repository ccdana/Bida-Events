@extends('layouts.site')

@section('title', $bida['brand'].' | Invitaciones y tarjetas digitales para tus eventos')
@section('description', 'Invitaciones digitales para bodas, bautizos, cumpleaños y XV años, con confirmación de asistencia, música, fotos y mapa. Paquetes desde '.$fromPrice.' Bs.')

@php
    $sections = array_filter([
        'servicios' => 'Servicios',
        'plantillas' => count($demos) ? 'Plantillas' : null,
        'precios' => 'Precios',
        'preguntas' => 'Preguntas',
    ]);
    $navLinks = collect($sections)->mapWithKeys(fn (string $label, string $id) => ['#'.$id => $label])->all()
        + [route('professionals') => 'Profesionales'];
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
    // Qué puede incluir una invitación, ordenado por el momento del evento en que se usa
    $features = [
        ['when' => 'Antes del evento', 'icon' => 'link-simple', 'title' => 'Tu invitación, en un enlace', 'text' => 'Portada, cuenta regresiva, itinerario y mapa. Se abre desde cualquier celular sin instalar nada.'],
        ['when' => 'Antes del evento', 'icon' => 'qr-code', 'title' => 'Confirmación con pase QR', 'text' => 'Cada invitado confirma desde su enlace y recibe un pase para la entrada. Tú ves la lista al día y la descargas en PDF o Excel.'],
        ['when' => 'Antes del evento', 'icon' => 'music-notes', 'title' => 'Música, fotos y video', 'text' => 'Tu canción de fondo, una galería que se desliza con el dedo y el video de tu save the date.'],
        ['when' => 'Antes del evento', 'icon' => 'chart-bar', 'title' => 'Encuestas y playlist', 'text' => 'Tus invitados votan, sugieren las canciones de la pista y participan antes de la fiesta.'],
        ['when' => 'Durante la fiesta', 'icon' => 'camera', 'title' => 'Recuerdos en vivo', 'text' => 'Tus invitados suben fotos desde su celular y todos las ven al instante en el fotomural.'],
        ['when' => 'Después', 'icon' => 'images', 'title' => 'Las fotos, en el mismo enlace', 'text' => 'Pasado el evento, la invitación reúne las fotos oficiales y las que subieron tus invitados.'],
    ];
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
        ['¿Hacen algo además de invitaciones?', 'Sí. También hacemos diseños de temporada, como las invitaciones de Halloween y las tarjetas del Día del Amor. Salen por unos días y cada fecha trae sus diseños.'],
        ['¿Cómo se realiza el pago?', 'Coordinamos el pago por WhatsApp cuando eliges tu paquete, antes de empezar el diseño.'],
    ];
    $socials = array_values(array_filter([
        ! empty($bida['instagram']) ? ['label' => 'Instagram', 'value' => '@'.$bida['instagram'], 'url' => 'https://www.instagram.com/'.$bida['instagram'].'/', 'icon' => 'instagram-logo'] : null,
        ! empty($bida['facebook']) ? ['label' => 'Facebook', 'value' => 'facebook.com/'.$bida['facebook'], 'url' => 'https://www.facebook.com/'.$bida['facebook'], 'icon' => 'facebook-logo'] : null,
        ! empty($bida['tiktok']) ? ['label' => 'TikTok', 'value' => '@'.$bida['tiktok'], 'url' => 'https://www.tiktok.com/@'.$bida['tiktok'], 'icon' => 'tiktok-logo'] : null,
    ]));
    // El teléfono de la portada recorre la apertura de cada plantilla (se abren solas)
    $coverReel = array_map(function (array $demo) use ($showcase): array {
        $position = array_search($demo['eventKey'], array_column($showcase, 'event'), true);

        return ['url' => $demo['coverUrl'], 'label' => $demo['label'], 'rotator' => $position === false ? null : $position];
    }, $demos);
@endphp

@section('content')
    <div data-header-sentinel class="pointer-events-none absolute inset-x-0 top-0 h-4" aria-hidden="true"></div>

    @include('site.partials.header', ['navLinks' => $navLinks])

    <main>
        {{-- ═══ Portada: el teléfono recorre las aperturas y la palabra y la foto del evento cambian con él ═══ --}}
        <section data-rotator data-rotator-interval="3200" @if(count($demos)) data-rotator-driven @endif
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
                    <a href="{{ count($demos) ? '#plantillas' : '#precios' }}" class="site-btn site-btn--ghost site-btn--lg justify-center">
                        {{ count($demos) ? 'Probar una invitación' : 'Ver precios' }}
                        <x-phosphor-arrow-down class="site-btn__arrow" aria-hidden="true" />
                    </a>
                </div>
                <p class="site-enter mt-7 text-site-muted" style="--enter-index: 3">
                    También hacemos
                    <a href="#servicios" class="font-medium text-site-ink underline decoration-site-line underline-offset-4 hover:decoration-site-accent">diseños de temporada</a>
                    y tenemos
                    <a href="{{ route('professionals') }}" class="font-medium text-site-ink underline decoration-site-line underline-offset-4 hover:decoration-site-accent">planes para profesionales</a>.
                </p>
            </div>

            <div class="relative mx-auto w-full max-w-[22rem] pb-10 sm:max-w-md lg:max-w-none lg:pb-12">
                <div class="site-stage ml-auto aspect-[4/5] w-[80%] lg:w-[76%]" data-rotator-group>
                    @foreach($showcase as $index => $event)
                        <x-site.image :key="$event['image']" :priority="$index === 0" :class="$index === 0 ? 'is-active' : ''" />
                    @endforeach
                </div>

                <div class="site-phone absolute bottom-0 left-0"
                    @if(count($demos)) data-cover-reel="{{ json_encode($coverReel, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}" @endif>
                    <div class="site-phone__screen">
                        @if(count($demos))
                            <iframe src="{{ $demos[0]['coverUrl'] }}" title="Apertura de las invitaciones de muestra" tabindex="-1" aria-hidden="true" data-cover-reel-frame></iframe>
                        @elseif($demoUrl)
                            <iframe src="{{ $demoUrl }}" title="Vista previa de una invitación de ejemplo" loading="lazy" tabindex="-1" aria-hidden="true"></iframe>
                        @else
                            {{-- TODO: captura real de una invitación en celular, 390x844 --}}
                            <img src="https://picsum.photos/seed/bida-invitacion-celular/390/844?grayscale" alt="" width="390" height="844" loading="lazy">
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

        {{-- ═══ Servicios: lo principal son las invitaciones; las tarjetas salen por temporada (config «bida.services») ═══ --}}
        <section id="servicios" class="scroll-mt-20">
            <div class="mx-auto max-w-7xl px-5 py-20 lg:px-8 lg:py-28">
                <div class="grid gap-6 lg:grid-cols-12 lg:items-end lg:gap-8">
                    <h2 class="max-w-[20ch] text-3xl font-semibold leading-[1.1] tracking-tight md:text-5xl lg:col-span-7" data-reveal>
                        Invitaciones para tu evento y tarjetas para las fechas que importan
                    </h2>
                    <p class="max-w-[40ch] text-lg leading-relaxed text-site-muted lg:col-span-4 lg:col-start-9" data-reveal>
                        Todo se abre desde el celular, se comparte por WhatsApp y lo diseñamos contigo.
                    </p>
                </div>

                <ol class="site-services mt-14">
                    @foreach($services as $index => $service)
                        <li data-reveal style="--reveal-index: {{ $index }}">
                            <a href="{{ $service['url'] ?? $contactUrl }}" @class(['site-service', 'site-service--main' => $index === 0])
                                @unless($service['url']) target="_blank" rel="noopener" @endunless>
                                <span class="site-service__num">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                <span class="site-service__body">
                                    <span class="site-service__name">{{ $service['name'] }}</span>
                                    <span class="site-service__text">{{ $service['text'] }}</span>
                                    <span class="site-service__for">
                                        @foreach($service['occasions'] as $occasion)
                                            <span>{{ $occasion }}</span>
                                        @endforeach
                                    </span>
                                </span>
                                <span class="site-service__side">
                                    @if($service['live'])
                                        <span class="site-service__live">Ahora: {{ $service['live'] }}</span>
                                    @endif
                                    @if($service['fromPrice'])
                                        <span class="site-service__price">desde <b>{{ $service['fromPrice'] }} Bs</b>@if($service['priceSuffix'] ?? null) {{ $service['priceSuffix'] }}@endif</span>
                                    @endif
                                    <span class="site-service__cta">
                                        {{ $service['cta'] }}
                                        <x-phosphor-arrow-right aria-hidden="true" />
                                    </span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        {{-- ═══ Qué incluye: por momento del evento, junto a la foto de los recuerdos en vivo ═══ --}}
        <section id="incluye" class="scroll-mt-20 border-t border-site-line">
            <div class="mx-auto grid max-w-7xl gap-12 px-5 py-20 lg:grid-cols-12 lg:gap-8 lg:px-8 lg:py-28">
                <div class="lg:col-span-5">
                    <div class="lg:sticky lg:top-28">
                        <h2 class="max-w-[16ch] text-3xl font-semibold leading-[1.1] tracking-tight md:text-5xl" data-reveal>
                            Todo lo que tu invitación puede incluir
                        </h2>
                        <p class="mt-5 max-w-[42ch] text-lg leading-relaxed text-site-muted" data-reveal>
                            Antes, durante y después de tu evento. Eliges lo que necesitas y todo se ve bien en el celular.
                        </p>
                        <figure class="site-photo site-photo--caption mt-10 aspect-[4/3] lg:aspect-[4/5]" data-reveal>
                            <x-site.image key="servicio-fotomural" />
                            <figcaption>
                                <span>Durante la fiesta</span>
                                Las fotos de tus invitados, al instante en el fotomural.
                            </figcaption>
                        </figure>
                    </div>
                </div>

                <ol class="site-features lg:col-span-6 lg:col-start-7">
                    @foreach($features as $index => $feature)
                        @if($index === 0 || $features[$index - 1]['when'] !== $feature['when'])
                            <li class="site-features__when" data-reveal>{{ $feature['when'] }}</li>
                        @endif
                        <li class="site-feature" data-reveal>
                            <span class="site-feature__num">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            <div>
                                <h3 class="site-feature__title">{{ $feature['title'] }}</h3>
                                <p class="site-feature__text">{{ $feature['text'] }}</p>
                            </div>
                            <x-dynamic-component :component="'phosphor-'.$feature['icon'].'-light'" class="site-feature__icon" aria-hidden="true" />
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        {{-- ═══ Plantillas: el visitante elige un evento y prueba la invitación de muestra (nada se guarda) ═══ --}}
        @if(count($demos))
            <section id="plantillas" class="scroll-mt-20 border-t border-site-line bg-site-surface"
                x-data="{ active: 0, loading: true, demos: @js($demos) }">
                <div class="mx-auto grid max-w-7xl gap-14 px-5 py-20 lg:grid-cols-12 lg:items-center lg:gap-8 lg:px-8 lg:py-28">
                    <div class="lg:col-span-6">
                        <p class="text-[0.95rem] font-medium text-site-accent" data-reveal>Plantillas</p>
                        <h2 class="mt-4 max-w-[18ch] text-3xl font-semibold leading-[1.1] tracking-tight md:text-5xl" data-reveal>
                            Pruébala como un invitado
                        </h2>
                        <p class="mt-5 max-w-[46ch] text-lg leading-relaxed text-site-muted" data-reveal>
                            Ábrela dentro del teléfono, confirma tu asistencia, vota, sugiere una canción o sube una foto. Es una muestra: nada de lo que hagas se guarda.
                        </p>

                        <ol class="site-template-list mt-10" role="tablist" aria-label="Plantillas de invitación" data-reveal>
                            @foreach($demos as $index => $demo)
                                <li>
                                    <button type="button" role="tab" id="plantilla-tab-{{ $index }}" aria-controls="plantilla-vista"
                                        aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                                        :aria-selected="(active === {{ $index }}).toString()"
                                        @click="if (active !== {{ $index }}) { active = {{ $index }}; loading = true }"
                                        @class(['site-template', 'is-active' => $index === 0])
                                        :class="{ 'is-active': active === {{ $index }} }">
                                        <span class="site-template__num">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                        <span class="site-template__name">{{ $demo['label'] }}</span>
                                        <span class="site-template__event">{{ $demo['event'] }}</span>
                                    </button>
                                </li>
                            @endforeach
                        </ol>

                        @foreach($demos as $index => $demo)
                            <div class="mt-8" x-show="active === {{ $index }}" @if($index > 0) x-cloak @endif>
                                <p class="max-w-[46ch] leading-relaxed text-site-muted">
                                    {{ $demo['description'] }}
                                    <span class="text-site-ink">Ejemplo: {{ $demo['title'] }}.</span>
                                </p>
                                <a href="{{ $demo['demoUrl'] }}" target="_blank" rel="noopener" class="site-btn site-btn--ghost site-btn--lg mt-6">
                                    Abrir en pantalla completa
                                    <x-phosphor-arrow-up-right class="site-btn__arrow" aria-hidden="true" />
                                </a>
                                @php($eventLanding = collect($landings)->firstWhere('event', $demo['eventKey']))
                                @if($eventLanding)
                                    <a href="{{ $eventLanding['url'] }}" class="mt-5 block w-fit font-medium text-site-ink underline decoration-site-line underline-offset-4 hover:decoration-site-accent">
                                        {{ $eventLanding['label'] }}
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="flex flex-col items-center gap-4 lg:col-span-5 lg:col-start-8" data-reveal style="--reveal-index: 1">
                        <div class="site-phone site-phone--showcase">
                            <div id="plantilla-vista" role="tabpanel" aria-labelledby="plantilla-tab-0"
                                :aria-labelledby="'plantilla-tab-' + active"
                                class="site-phone__screen" :class="{ 'is-loading': loading }">
                                <iframe src="{{ $demos[0]['demoUrl'] }}" :src="demos[active].demoUrl"
                                    title="Invitación de muestra: {{ $demos[0]['title'] }}" :title="'Invitación de muestra: ' + demos[active].title"
                                    loading="lazy" @load="loading = false"></iframe>
                            </div>
                        </div>
                        <p class="text-sm text-site-muted">Toca y desliza dentro del teléfono</p>
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

        @include('site.partials.plans')

        @include('site.partials.faqs', ['faqs' => $faqs])
    </main>

    @include('site.partials.footer', ['navLinks' => $navLinks, 'socials' => $socials])

    {{-- ═══ Temporadas: un botón flotante por cada una que se vende hoy (abajo a la derecha, apilados) ═══ --}}
    @foreach($seasons as $seasonIndex => $season)
        @include('site.partials.season', ['season' => $season, 'seasonIndex' => $seasonIndex])
    @endforeach
@endsection
