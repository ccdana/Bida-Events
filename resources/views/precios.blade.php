@extends('layouts.site')

{{--
    Precios (/precios): todo lo que cuesta tener la invitación, en una sola página y sin letra chica.
    Ver App\Support\PricingPage.
    - Hazlo tú: gratis con lo básico, o un pago único por el nivel de las partes que usó.
    - Planes: para quien arma muchas (el selector Mensual / 3 meses / Anual cambia el precio).
    Después, qué trae cada nivel parte por parte, en qué se distinguen las dos formas, las preguntas y un
    cierre con lo a medida (sin precios: se conversa por WhatsApp o correo).
    Poco texto: filas separadas por filetes, tablas de tres columnas que caben sin deslizar y íconos donde
    un ícono dice lo mismo. Lenguaje del sitio: titulares en serif y el dorado como único acento.
--}}
@section('title', __('Precios de las invitaciones digitales').' | '.$bida['brand'])
@section('description', __('Arma tu invitación gratis y paga solo si quieres más, o elige un plan si armas muchas. Precios en dólares, sin letra chica.'))

@use('App\Support\Money')

@php
    $accountUrl = $user ? route('dashboard') : lroute('login');
    $accountLabel = $user ? __('Mi panel') : __('Ingresar');
    $paidTiers = array_slice($diyTiers, 1);
@endphp

@push('head')
    @include('site.partials.structured-data', [
        'faqs' => $faqs,
        'serviceName' => __('Invitaciones digitales: hazla tú o con un plan'),
        'offers' => [
            ...array_map(fn (array $tier) => ['name' => __('Hazlo tú :name (pago único)', ['name' => $tier['name']]), 'price' => (int) $tier['price'], 'description' => $tier['summary']], $paidTiers),
            ...array_map(fn (array $plan) => ['name' => __('Plan :name (mensual)', ['name' => $plan['name']]), 'price' => $plan['final_price'], 'description' => $plan['summary'] ?? ''], $plans),
        ],
        'breadcrumbs' => [[$bida['brand'], lroute('home')], [__('Precios'), lroute('pricing')]],
    ])
@endpush

@section('content')
    <div data-header-sentinel class="pointer-events-none absolute inset-x-0 top-0 h-4" aria-hidden="true"></div>

    @include('site.partials.header')

    <main id="contenido" tabindex="-1">
        {{-- ═══ Título: en dólares, sin letra chica; y a dónde saltar ═══ --}}
        <section class="site-pagehead" aria-labelledby="precios-titulo">
            <h1 id="precios-titulo" class="site-display site-display--md" data-reveal>{{ __('Precios claros, sin letra chica') }}</h1>
            <p class="site-muted-lead" data-reveal>{{ __('En dólares (:value). Empieza gratis, paga solo lo que uses.', ['value' => Money::code()]) }}</p>
            <nav class="site-jump" aria-label="{{ __('En esta página') }}" data-reveal>
                <a href="#hazlo-tu">{{ __('Hazlo tú') }}</a>
                <a href="#planes">{{ __('Planes') }}</a>
                <a href="#comparar">{{ __('Comparar') }}</a>
            </nav>
        </section>

        {{-- ═══ Hazlo tú: gratis o un pago único por el nivel que usaste ═══ --}}
        <section id="hazlo-tu" class="site-section scroll-mt-20" aria-labelledby="hazlo-tu-titulo">
            <h2 id="hazlo-tu-titulo" class="site-display site-display--md" data-reveal>{{ __('Hazlo tú') }}</h2>
            <p class="site-muted-lead" data-reveal>{{ __('Gratis con lo básico o un pago único. Subir de nivel cobra solo la diferencia.') }}</p>

            <div class="site-plans site-plans--pro mt-5 lg:mt-8">
                @foreach($diyTiers as $index => $tier)
                    <x-site.price-card
                        :name="$tier['name']"
                        :price="$tier['price']"
                        :old="$tier['old']"
                        :suffix="$tier['suffix']"
                        :summary="$tier['summary']"
                        :features="$tier['features']"
                        :featured="$tier['featured'] ?? false"
                        :premium="$tier['premium'] ?? false"
                        :tag="($tier['featured'] ?? false) ? __('Recomendado') : null"
                        :url="$startUrl"
                        :cta="false"
                        :index="$index"
                        :reserve="true" />
                @endforeach
            </div>

            {{-- Los niveles se explican, no se eligen: lo dicen las partes que enciendes en el editor. Un solo botón --}}
            <div class="mt-5" data-reveal>
                <a href="{{ $startUrl }}" class="site-btn site-btn--lg site-btn--block">
                    {{ __('Crear mi invitación gratis') }}
                    <x-phosphor-arrow-right class="site-btn__arrow" aria-hidden="true" />
                </a>
            </div>
        </section>

        {{-- ═══ Planes: para quien arma muchas (el selector Mensual / 3 meses / Anual cambia el precio) ═══ --}}
        @include('site.partials.diy-plans', [
            'diyTitle' => __('Planes'),
            'diyLead' => __('Para quien arma muchas, sin comisión. Por mes, en dólares (:currency); menos pagando 3 o 12 meses.', ['currency' => Money::code()]),
        ])

        {{-- ═══ Comparar: qué trae cada nivel y qué cambia entre las dos formas ═══ --}}
        <section id="comparar" class="site-section scroll-mt-20" aria-labelledby="comparar-titulo">
            <h2 id="comparar-titulo" class="site-display site-display--md" data-reveal>{{ __('Qué trae cada nivel') }}</h2>
            <p class="site-muted-lead" data-reveal>{{ __('Cada nivel de «Hazlo tú» suma al anterior.') }}</p>

            <div class="site-compare mt-5" data-reveal>
                <table>
                    <caption class="sr-only">{{ __('Qué incluye cada nivel de la invitación') }}</caption>
                    <thead>
                        <tr>
                            <th scope="col"><span class="sr-only">{{ __('Parte de la invitación') }}</span></th>
                            @foreach($tierNames as $tier => $name)
                                <th scope="col" @class(['is-featured' => $tier === 'estandar'])>{{ $name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tierMatrix as $row)
                            <tr>
                                <th scope="row">{{ $row['label'] }}</th>
                                @foreach($row['tiers'] as $tier => $included)
                                    <td @class(['is-featured' => $tier === 'estandar'])>
                                        @if($included)
                                            <x-phosphor-check-bold class="site-compare__yes" aria-hidden="true" />
                                            <span class="sr-only">{{ __('Incluido') }}</span>
                                        @else
                                            <span class="site-compare__no" aria-hidden="true">—</span>
                                            <span class="sr-only">{{ __('No incluido') }}</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <h2 class="site-display site-display--sm mt-10 lg:mt-14" data-reveal>{{ __('Hazlo tú o un plan') }}</h2>
            <div class="site-table site-table--fit" data-reveal>
                <table>
                    <caption class="sr-only">{{ __('Diferencias entre Hazlo tú y los planes') }}</caption>
                    <thead>
                        <tr>
                            <th scope="col"><span class="sr-only">{{ __('Qué cambia') }}</span></th>
                            @foreach($products['columns'] as $column)
                                <th scope="col">{{ $column }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products['rows'] as $row)
                            <tr>
                                <th scope="row">{{ $row[0] }}</th>
                                @foreach(array_slice($row, 1) as $cell)
                                    <td>{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        @include('site.partials.faqs', ['faqs' => $faqs])

        {{-- ═══ Cierre: crear gratis y, si es algo a medida, WhatsApp o correo (sin precios) ═══ --}}
        <section id="a-medida" class="site-close scroll-mt-20" aria-labelledby="cierre-titulo">
            <h2 id="cierre-titulo" class="site-display site-display--md" data-reveal>{{ __('Empieza gratis. Paga solo si quieres más.') }}</h2>
            <div class="site-close__actions" data-reveal>
                <a href="{{ $startUrl }}" class="site-btn site-btn--lg">
                    {{ __('Crear mi invitación gratis') }}
                    <x-phosphor-arrow-right class="site-btn__arrow" aria-hidden="true" />
                </a>
                @include('site.partials.custom-request')
            </div>
        </section>
    </main>

    @include('site.partials.footer', ['socials' => []])
@endsection
