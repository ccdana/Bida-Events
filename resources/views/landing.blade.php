@extends('layouts.site')

{{-- Página por tipo de evento. El contenido vive en config/bida.php (landings); ver EventLandingController --}}
@section('title', $page['title'].' | '.$bida['brand'])
@section('description', $page['description'])

@php
    $navLinks = array_filter([
        '#muestra' => $demo ? 'Pruébala' : null,
        '#incluye' => 'Qué incluye',
        '#precios' => 'Precios',
        '#preguntas' => 'Preguntas',
    ]);
    $accountUrl = $user ? route('dashboard') : route('login');
    $accountLabel = $user ? 'Mi panel' : 'Ingresar';
    $faqs = array_merge($page['faqs'], [
        ['¿Mis invitados necesitan instalar algo?', 'No. La invitación se abre en el navegador del celular desde el enlace que compartes por WhatsApp.'],
        ['¿Cómo se realiza el pago?', 'Coordinamos el pago por WhatsApp cuando eliges tu paquete, antes de empezar el diseño.'],
    ]);
    $socials = array_values(array_filter([
        ! empty($bida['instagram']) ? ['label' => 'Instagram', 'url' => 'https://www.instagram.com/'.$bida['instagram'].'/', 'icon' => 'instagram-logo'] : null,
        ! empty($bida['facebook']) ? ['label' => 'Facebook', 'url' => 'https://www.facebook.com/'.$bida['facebook'], 'icon' => 'facebook-logo'] : null,
        ! empty($bida['tiktok']) ? ['label' => 'TikTok', 'url' => 'https://www.tiktok.com/@'.$bida['tiktok'], 'icon' => 'tiktok-logo'] : null,
    ]));
    $otherLandings = collect($landings)->reject(fn (array $landing) => $landing['slug'] === $slug)->values();
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
        {{-- ═══ Portada: la foto del evento y el teléfono con la apertura de su plantilla ═══ --}}
        <section class="mx-auto grid max-w-7xl items-center gap-12 px-5 pb-16 pt-8 md:pt-14 lg:min-h-[calc(100dvh-72px)] lg:grid-cols-[1.1fr_0.9fr] lg:gap-12 lg:px-8 lg:py-12">
            <div>
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
                <div class="site-enter mt-9 flex flex-col gap-3 sm:flex-row" style="--enter-index: 3">
                    <a href="{{ $contactUrl }}" target="_blank" rel="noopener" class="site-btn site-btn--lg justify-center" data-magnetic>
                        <x-phosphor-whatsapp-logo aria-hidden="true" />
                        Escríbenos
                    </a>
                    <a href="{{ $demo ? '#muestra' : '#precios' }}" class="site-btn site-btn--ghost site-btn--lg justify-center">
                        {{ $demo ? 'Probar la invitación' : 'Ver precios' }}
                        <x-phosphor-arrow-down class="site-btn__arrow" aria-hidden="true" />
                    </a>
                </div>
                <p class="site-enter mt-6 text-sm text-site-muted" style="--enter-index: 4">
                    Paquetes desde {{ collect($bida['packages'])->min('price') }} Bs · Pago único por invitación
                </p>
            </div>

            <div class="relative mx-auto w-full max-w-[22rem] pb-10 sm:max-w-md lg:max-w-none lg:pb-12">
                <div class="site-stage ml-auto aspect-[4/5] w-[80%] lg:w-[76%]">
                    <x-site.image :key="$page['image']" :priority="true" class="is-active" />
                </div>

                @if($demo)
                    <div class="site-phone absolute bottom-0 left-0">
                        <div class="site-phone__screen">
                            <iframe src="{{ $demo['coverUrl'] }}" title="Apertura de la invitación de muestra" tabindex="-1" aria-hidden="true"></iframe>
                        </div>
                        <p class="site-phone__tag" aria-hidden="true">
                            <span class="site-phone__tag-dot"></span>
                            <span>{{ $demo['label'] }}</span>
                        </p>
                    </div>
                @endif
            </div>
        </section>

        {{-- ═══ Qué incluye: filas numeradas con lo propio de este evento ═══ --}}
        <section id="incluye" class="scroll-mt-20 border-t border-site-line">
            <div class="mx-auto grid max-w-7xl gap-12 px-5 py-20 lg:grid-cols-12 lg:gap-8 lg:px-8 lg:py-28">
                <div class="lg:col-span-5">
                    <div class="lg:sticky lg:top-28">
                        <h2 class="max-w-[16ch] text-3xl font-semibold leading-[1.1] tracking-tight md:text-5xl" data-reveal>
                            Pensada para {{ collect($bida['showcase'])->firstWhere('event', $page['event'])['phrase'] ?? 'tu evento' }}
                        </h2>
                        <p class="mt-5 max-w-[42ch] text-lg leading-relaxed text-site-muted" data-reveal>
                            Además de la cuenta regresiva, el itinerario y el mapa que lleva toda invitación.
                        </p>
                    </div>
                </div>

                <ol class="site-features lg:col-span-6 lg:col-start-7">
                    @foreach($page['highlights'] as $index => $highlight)
                        <li class="site-feature" data-reveal>
                            <span class="site-feature__num">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            <div>
                                <h3 class="site-feature__title">{{ $highlight['title'] }}</h3>
                                <p class="site-feature__text">{{ $highlight['text'] }}</p>
                            </div>
                            <x-dynamic-component :component="'phosphor-'.$highlight['icon'].'-light'" class="site-feature__icon" aria-hidden="true" />
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        {{-- ═══ Muestra interactiva: la misma de la portada, nada se guarda ═══ --}}
        @if($demo)
            <section id="muestra" class="scroll-mt-20 border-t border-site-line bg-site-surface" x-data="{ loading: true }">
                <div class="mx-auto grid max-w-7xl gap-14 px-5 py-20 lg:grid-cols-12 lg:items-center lg:gap-8 lg:px-8 lg:py-28">
                    <div class="lg:col-span-6">
                        <p class="text-[0.95rem] font-medium text-site-accent" data-reveal>{{ $demo['label'] }}</p>
                        <h2 class="mt-4 max-w-[18ch] text-3xl font-semibold leading-[1.1] tracking-tight md:text-5xl" data-reveal>
                            Pruébala como un invitado
                        </h2>
                        <p class="mt-5 max-w-[46ch] text-lg leading-relaxed text-site-muted" data-reveal>
                            Ábrela dentro del teléfono, confirma tu asistencia, vota o sugiere una canción. Es una muestra: nada de lo que hagas se guarda.
                        </p>
                        <p class="mt-6 max-w-[46ch] leading-relaxed text-site-muted" data-reveal>
                            {{ $demo['description'] }}
                            <span class="text-site-ink">Ejemplo: {{ $demo['title'] }}.</span>
                        </p>
                        <a href="{{ $demo['demoUrl'] }}" target="_blank" rel="noopener" class="site-btn site-btn--ghost site-btn--lg mt-8" data-reveal>
                            Abrir en pantalla completa
                            <x-phosphor-arrow-up-right class="site-btn__arrow" aria-hidden="true" />
                        </a>
                    </div>

                    <div class="flex flex-col items-center gap-4 lg:col-span-5 lg:col-start-8" data-reveal style="--reveal-index: 1">
                        <div class="site-phone site-phone--showcase">
                            <div class="site-phone__screen" :class="{ 'is-loading': loading }">
                                <iframe src="{{ $demo['demoUrl'] }}" title="Invitación de muestra: {{ $demo['title'] }}" loading="lazy" @load="loading = false"></iframe>
                            </div>
                        </div>
                        <p class="text-sm text-site-muted">Toca y desliza dentro del teléfono</p>
                    </div>
                </div>
            </section>
        @endif

        @include('site.partials.plans')

        @include('site.partials.faqs', ['faqs' => $faqs])

        {{-- ═══ Contacto y otros eventos ═══ --}}
        <section class="border-t border-site-line bg-site-surface">
            <div class="mx-auto grid max-w-7xl gap-14 px-5 py-20 lg:grid-cols-12 lg:gap-8 lg:px-8 lg:py-28">
                <div class="lg:col-span-6" data-reveal>
                    <h2 class="max-w-[15ch] text-4xl font-semibold leading-[1.04] tracking-tight md:text-6xl">¿Ya tienes la fecha?</h2>
                    <p class="mt-6 max-w-[42ch] text-lg leading-relaxed text-site-muted">
                        Escríbenos por WhatsApp con la fecha y el lugar, y te ayudamos a elegir el paquete.
                    </p>
                    <a href="{{ $contactUrl }}" target="_blank" rel="noopener" class="site-btn site-btn--lg mt-9" data-magnetic>
                        <x-phosphor-whatsapp-logo aria-hidden="true" />
                        Escríbenos
                    </a>
                </div>

                @if($otherLandings->isNotEmpty())
                    <div class="lg:col-span-5 lg:col-start-8" data-reveal style="--reveal-index: 1">
                        <p class="text-[0.95rem] font-medium text-site-accent">También diseñamos</p>
                        <ul class="site-contact mt-4">
                            @foreach($otherLandings as $landing)
                                <li>
                                    <a href="{{ $landing['url'] }}" class="site-contact__row">
                                        <x-dynamic-component :component="'phosphor-'.(['boda' => 'heart', 'xv' => 'crown-simple', 'bautizo' => 'baby', 'cumple' => 'cake'][$landing['event']] ?? 'sparkle').'-light'" class="site-contact__icon" aria-hidden="true" />
                                        <span class="site-contact__value col-span-2 !text-left">{{ $landing['label'] }}</span>
                                        <x-phosphor-arrow-right class="site-contact__arrow" aria-hidden="true" />
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </section>
    </main>

    @include('site.partials.footer', ['navLinks' => $navLinks, 'socials' => $socials])
@endsection
