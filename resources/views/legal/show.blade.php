@extends('layouts.site')

{{--
    Página legal (privacidad, cookies o términos). El contenido vive en App\Support\LegalPages;
    esta vista solo lo dibuja: índice a la izquierda en pantallas anchas y las secciones numeradas.
--}}
@section('title', $content['title'].' | '.$bida['brand'])
@section('description', $content['description'])

@php
    $navLinks = [
        route('home') => 'Inicio',
        route('professionals') => 'Para profesionales',
    ];
    $accountUrl = $user ? route('dashboard') : route('login');
    $accountLabel = $user ? 'Mi panel' : 'Ingresar';
    $socials = [];
    $others = collect(\App\Support\LegalPages::links())->reject(fn (array $link) => $link['url'] === route('legal', $slug));
@endphp

@section('content')
    <div data-header-sentinel class="pointer-events-none absolute inset-x-0 top-0 h-4" aria-hidden="true"></div>

    @include('site.partials.header', ['navLinks' => $navLinks])

    <main class="mx-auto max-w-7xl px-5 pb-24 pt-10 lg:px-8 lg:pt-16">
        <nav class="text-sm text-site-muted" aria-label="Ruta">
            <a href="{{ route('home') }}" class="site-nav-link hover:text-site-ink">{{ $bida['brand'] }}</a>
            <span class="mx-2" aria-hidden="true">/</span>
            <span class="text-site-ink">{{ $content['title'] }}</span>
        </nav>

        <div class="mt-8 grid gap-12 lg:grid-cols-12 lg:gap-8">
            <header class="lg:col-span-4">
                <div class="lg:sticky lg:top-28">
                    <h1 class="max-w-[14ch] text-4xl font-semibold leading-[1.05] tracking-tight md:text-5xl">{{ $content['title'] }}</h1>
                    @if($updatedAt)
                        <p class="mt-4 text-sm text-site-muted">
                            Actualizada el {{ \Illuminate\Support\Carbon::parse($updatedAt)->locale('es')->translatedFormat('j \d\e F \d\e Y') }}
                        </p>
                    @endif

                    <ol class="site-legal__toc mt-8 hidden lg:block" aria-label="En esta página">
                        @foreach($content['sections'] as $index => $section)
                            <li>
                                <a href="#seccion-{{ $index + 1 }}">
                                    <span>{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                    {{ $section['title'] }}
                                </a>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </header>

            <article class="site-legal lg:col-span-7 lg:col-start-6">
                <p class="site-legal__intro">{{ $content['intro'] }}</p>

                @foreach($content['sections'] as $index => $section)
                    <section id="seccion-{{ $index + 1 }}" class="site-legal__section">
                        <h2>
                            <span class="site-legal__num">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            {{ $section['title'] }}
                        </h2>
                        @foreach($section['paragraphs'] ?? [] as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                        @if(! empty($section['items']))
                            <ul>
                                @foreach($section['items'] as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        @endif
                        @foreach($section['note'] ?? [] as $note)
                            <p>{{ $note }}</p>
                        @endforeach
                    </section>
                @endforeach

                <aside class="site-legal__more">
                    <p>¿Dudas sobre tus datos? <a href="{{ $contactUrl }}" target="_blank" rel="noopener">Escríbenos por WhatsApp</a> o a <a href="mailto:{{ $bida['email'] }}">{{ $bida['email'] }}</a>.</p>
                    <p>
                        También puedes leer
                        @foreach($others as $link)
                            <a href="{{ $link['url'] }}">{{ mb_strtolower($link['label']) === 'términos' ? 'los términos de uso' : 'la política de '.mb_strtolower($link['label']) }}</a>{{ $loop->last ? '.' : ' y ' }}
                        @endforeach
                    </p>
                </aside>
            </article>
        </div>
    </main>

    @include('site.partials.footer', ['navLinks' => $navLinks, 'socials' => $socials])
@endsection
