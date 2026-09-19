@extends('layouts.site')

{{--
    Página por tipo de evento o de tarjeta. El contenido vive en config/bida.php (landings); ver
    EventLandingController. No repite la portada: arriba se prueba la muestra (con un selector si hay
    varios diseños), luego lo propio del evento, el precio y las preguntas. Las muestras salen de
    «demos» o, en las tarjetas, de la temporada, así sumar un diseño no cambia esta vista.
--}}
@section('title', $page['title'].' | '.$bida['brand'])
@section('description', $page['description'])

@php
    $isCard = ($page['kind'] ?? null) === 'card';
    $navLinks = [
        '#incluye' => 'Qué incluye',
        '#precios' => 'Precios',
        '#preguntas' => 'Preguntas',
    ];
    $accountUrl = $user ? route('dashboard') : route('login');
    $accountLabel = $user ? 'Mi panel' : 'Ingresar';
    $faqs = array_merge($page['faqs'], [
        [$isCard ? '¿Quien la recibe necesita instalar algo?' : '¿Mis invitados necesitan instalar algo?', 'No. Se abre en el navegador del celular desde el enlace que compartes por WhatsApp.'],
        ['¿Cómo se realiza el pago?', $isCard ? 'Coordinamos el pago por WhatsApp cuando nos pides la tarjeta, antes de armarla.' : 'Coordinamos el pago por WhatsApp cuando eliges tu paquete, antes de empezar el diseño.'],
    ]);
    $socials = array_values(array_filter([
        ! empty($bida['instagram']) ? ['label' => 'Instagram', 'url' => 'https://www.instagram.com/'.$bida['instagram'].'/', 'icon' => 'instagram-logo'] : null,
        ! empty($bida['facebook']) ? ['label' => 'Facebook', 'url' => 'https://www.facebook.com/'.$bida['facebook'], 'icon' => 'facebook-logo'] : null,
        ! empty($bida['tiktok']) ? ['label' => 'TikTok', 'url' => 'https://www.tiktok.com/@'.$bida['tiktok'], 'icon' => 'tiktok-logo'] : null,
    ]));
    $noun = $isCard ? 'tarjeta' : 'invitación';
    // Datos estructurados: Google puede mostrar las preguntas directamente en los resultados
    $faqSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => array_map(fn (array $faq) => [
            '@type' => 'Question',
            'name' => $faq[0],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq[1]],
        ], $faqs),
    ];
@endphp

@section('content')
    <script type="application/ld+json">{!! json_encode($faqSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>

    <div data-header-sentinel class="pointer-events-none absolute inset-x-0 top-0 h-4" aria-hidden="true"></div>

    @include('site.partials.header', ['navLinks' => $navLinks])

    <main>
        {{-- ═══ Portada: el texto del evento y la muestra para probar ahí mismo, con sus diseños ═══ --}}
        <section class="site-landing" @if(count($demos)) x-data="{ active: 0, loading: true, demos: @js($demos) }" @endif>
            <div class="mx-auto grid max-w-7xl items-center gap-12 px-5 pb-16 pt-8 md:pt-14 lg:min-h-[calc(100dvh-72px)] lg:grid-cols-12 lg:gap-8 lg:px-8 lg:py-12">
                <div class="lg:col-span-6">
                    <nav class="site-enter text-sm text-site-muted" aria-label="Ruta">
                        <a href="{{ route('home') }}" class="site-nav-link hover:text-site-ink">{{ $bida['brand'] }}</a>
                        <span class="mx-2" aria-hidden="true">/</span>
                        <span class="text-site-ink">{{ $page['link'] }}</span>
                    </nav>
                    <h1 class="site-enter mt-5 max-w-[18ch] text-[2.4rem] font-semibold leading-[1.06] tracking-tight sm:text-5xl xl:text-[3.4rem]" style="--enter-index: 1">
                        {{ $page['heading'] }}
                    </h1>
                    <p class="site-enter mt-6 max-w-[44ch] text-lg leading-relaxed text-site-muted" style="--enter-index: 2">
                        {{ $page['intro'] }}
                    </p>

                    @if(count($demos) > 1)
                        <div class="site-enter mt-9" style="--enter-index: 3">
                            <p class="text-sm font-medium text-site-muted" id="disenos-titulo">Diseños para probar</p>
                            <ol class="site-template-list mt-3" role="tablist" aria-labelledby="disenos-titulo">
                                @foreach($demos as $index => $demo)
                                    <li>
                                        <button type="button" role="tab" id="diseno-tab-{{ $index }}" aria-controls="diseno-vista"
                                            aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                                            :aria-selected="(active === {{ $index }}).toString()"
                                            @click="if (active !== {{ $index }}) { active = {{ $index }}; loading = true }"
                                            @class(['site-template', 'is-active' => $index === 0])
                                            :class="{ 'is-active': active === {{ $index }} }">
                                            <span class="site-template__num">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                            <span class="site-template__name">{{ $demo['label'] }}</span>
                                            <span class="site-template__event">{{ $demo['tagline'] ?? $demo['event'] }}</span>
                                        </button>
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    @endif

                    <div class="site-enter mt-9 flex flex-col gap-3 sm:flex-row" style="--enter-index: 4">
                        <a href="{{ $isCard && $season ? $season['whatsappUrl'] : $contactUrl }}" target="_blank" rel="noopener" class="site-btn site-btn--lg justify-center" data-magnetic>
                            <x-phosphor-whatsapp-logo aria-hidden="true" />
                            {{ $isCard && $season ? 'La quiero por '.$season['final_price'].' Bs' : 'Escríbenos' }}
                        </a>
                        <a href="#precios" class="site-btn site-btn--ghost site-btn--lg justify-center">
                            Ver precios
                            <x-phosphor-arrow-down class="site-btn__arrow" aria-hidden="true" />
                        </a>
                    </div>
                    <p class="site-enter mt-6 text-sm text-site-muted" style="--enter-index: 5">
                        @if($isCard)
                            @if($season)
                                <del>{{ $season['old_price'] }} Bs</del> <strong class="text-site-ink">{{ $season['final_price'] }} Bs</strong> por temporada · Lista el mismo día · Se manda por WhatsApp
                            @else
                                Lista el mismo día · Se manda por WhatsApp
                            @endif
                        @else
                            Paquetes desde {{ $fromPrice }} Bs · Pago único por invitación
                        @endif
                    </p>
                </div>

                @if(count($demos))
                    {{-- Muestra interactiva: nada se guarda. La foto del evento queda detrás del teléfono --}}
                    <div class="site-landing__stage lg:col-span-5 lg:col-start-8">
                        <div class="site-landing__photo" aria-hidden="true">
                            <x-site.image :key="$page['image']" :priority="true" />
                        </div>
                        <div class="site-phone site-phone--showcase">
                            <div id="diseno-vista" @if(count($demos) > 1) role="tabpanel" aria-labelledby="diseno-tab-0" :aria-labelledby="'diseno-tab-' + active" @endif
                                class="site-phone__screen" :class="{ 'is-loading': loading }">
                                <iframe src="{{ $demos[0]['demoUrl'] }}" :src="demos[active].demoUrl"
                                    title="{{ ucfirst($noun) }} de muestra: {{ $demos[0]['title'] }}" :title="'{{ ucfirst($noun) }} de muestra: ' + demos[active].title"
                                    @load="loading = false"></iframe>
                            </div>
                        </div>
                        <p class="site-landing__hint">
                            {{ $page['demo_note'] ?? 'Pruébala como un invitado: confirma, vota o sugiere una canción. Es una muestra, nada se guarda.' }}
                            <a href="{{ $demos[0]['demoUrl'] }}" :href="demos[active].demoUrl" target="_blank" rel="noopener">Abrir en pantalla completa</a>
                        </p>
                    </div>
                @else
                    <div class="site-photo aspect-[4/5] w-full lg:col-span-5 lg:col-start-8">
                        <x-site.image :key="$page['image']" :priority="true" />
                    </div>
                @endif
            </div>
        </section>

        {{-- ═══ Qué incluye: lo propio de este evento, en una grilla de filetes ═══ --}}
        <section id="incluye" class="scroll-mt-20 border-t border-site-line">
            <div class="mx-auto max-w-7xl px-5 py-20 lg:px-8 lg:py-28">
                <div class="grid gap-6 lg:grid-cols-12 lg:items-end lg:gap-8">
                    <h2 class="max-w-[18ch] text-3xl font-semibold leading-[1.1] tracking-tight md:text-5xl lg:col-span-7" data-reveal>
                        Pensada para {{ $page['for'] ?? (collect($bida['showcase'])->firstWhere('event', $page['event'])['phrase'] ?? 'tu evento') }}
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

        @if($isCard)
            {{-- Tarjetas de temporada: precio de temporada tachado y rebajado; pasada la fecha, se consulta --}}
            <section id="precios" class="scroll-mt-20 border-t border-site-line bg-site-surface">
                <div class="mx-auto flex max-w-7xl flex-col items-start gap-8 px-5 py-20 lg:flex-row lg:items-end lg:justify-between lg:px-8" data-reveal>
                    @if($season)
                        <div>
                            <p class="text-[0.95rem] font-medium text-site-accent">Precio de temporada</p>
                            <p class="mt-4 flex flex-wrap items-baseline gap-x-3 gap-y-1">
                                <del class="site-plan__old"><span class="sr-only">Antes </span>{{ $season['old_price'] }} Bs</del>
                                <span class="site-plan__price">{{ $season['final_price'] }}</span>
                                <span class="text-xl text-site-muted">Bs por tarjeta</span>
                            </p>
                            <p class="mt-3 text-site-muted">
                                {{ $season['promo_label'] }} hasta el {{ $season['endsAt']->locale('es')->translatedFormat('l j \d\e F') }}. Todos los diseños de la temporada cuestan lo mismo.
                            </p>
                        </div>
                        <a href="{{ $season['whatsappUrl'] }}" target="_blank" rel="noopener" class="site-btn site-btn--lg" data-magnetic>
                            <x-phosphor-whatsapp-logo aria-hidden="true" />
                            La quiero por {{ $season['final_price'] }} Bs
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
