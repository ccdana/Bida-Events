@extends('layouts.site')

{{--
    Precios (/precios): las formas de tener la invitación, lado a lado, para que cada quien
    encuentre la suya sin leer letra chica. Ver App\Support\PricingPage.
    - Hazlo tú: gratis con lo básico, o un pago único por el nivel de las partes que usó.
    - Planes: para quien arma muchas (el bloque de siempre, con el selector Mensual / 3 meses / Anual).
    Después, qué trae cada nivel parte por parte, qué cambia entre las dos formas, las preguntas y un
    aviso de lo a medida (sin precios: se conversa por WhatsApp o correo).
    Lenguaje del sitio: titulares en serif, filetes en lugar de tarjetas y el dorado como único acento.
--}}
@section('title', __('Precios de las invitaciones digitales').' | '.$bida['brand'])
@section('description', __('Arma tu invitación gratis y paga solo si quieres más, o elige un plan si armas muchas. Precios en dólares, sin letra chica.'))

@use('App\Support\Money')

@php
    $accountUrl = $user ? route('dashboard') : lroute('login');
    $accountLabel = $user ? __('Mi panel') : __('Ingresar');
    $fromPlan = collect($plans)->min('final_price');
    $paidTiers = array_slice($diyTiers, 1);
    $ways = [
        ['id' => 'hazlo-tu', 'name' => __('Hazlo tú'), 'text' => __('La armas tú: gratis con lo básico, o un pago único por todo lo que usaste.'), 'from' => __('Gratis')],
        ['id' => 'planes', 'name' => __('Planes'), 'text' => __('Para quien arma muchas: las tuyas o las de tus clientes, cada mes.'), 'from' => $fromPlan !== null ? __('Desde :price al mes', ['price' => Money::format($fromPlan)]) : null],
    ];
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
        {{-- ═══ Las formas de tenerla, de un vistazo ═══ --}}
        <section class="site-pricing-intro" aria-labelledby="precios-titulo">
            <div class="max-w-3xl">
                <h1 id="precios-titulo" class="site-display site-display--md" data-reveal>{{ __('Precios claros para cada forma de tener tu invitación') }}</h1>
                <p class="site-muted-lead" data-reveal>{{ __('Todo en dólares (:value), sin comisiones ni letra chica. Empieza gratis y paga solo por lo que usas.', ['value' => Money::code()]) }}</p>
            </div>

            <ol class="site-pricing-ways" data-reveal>
                @foreach($ways as $way)
                    <li>
                        <a href="#{{ $way['id'] }}" class="site-pricing-ways__link">
                            <span class="site-pricing-ways__name">{{ $way['name'] }}</span>
                            <span class="site-pricing-ways__text">{{ $way['text'] }}</span>
                            @if($way['from'])
                                <span class="site-pricing-ways__from">{{ $way['from'] }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ol>
        </section>

        {{-- ═══ Hazlo tú: gratis o un pago único por el nivel que usaste ═══ --}}
        <section id="hazlo-tu" class="scroll-mt-20 border-t border-site-line" aria-labelledby="hazlo-tu-titulo">
            <div class="mx-auto max-w-7xl px-5 py-10 lg:px-8 lg:py-28">
                <div class="max-w-3xl">
                    <h2 id="hazlo-tu-titulo" class="site-display site-display--md" data-reveal>{{ __('Hazlo tú') }}</h2>
                    <p class="site-muted-lead" data-reveal>
                        {{ __('Arma tu invitación con todas las partes que quieras y pruébala completa. Al publicarla decides: gratis con lo básico, o un pago único por el nivel de lo que usaste. Subir de nivel después cobra solo la diferencia.') }}
                    </p>
                </div>

                <div class="site-plans site-plans--pro mt-8 lg:mt-14">
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
                            :cta="$tier['key'] === 'gratis' ? __('Crear mi invitación gratis') : __('Crear mi invitación')"
                            :index="$index"
                            :reserve="true" />
                    @endforeach
                </div>

                <p class="mt-10 max-w-[70ch] text-site-muted" data-reveal>
                    {{ __('No eliges el nivel antes: lo dicen las partes que enciendes. Mientras la armas, el editor te muestra en cuál estás y, si quieres que sea gratis, qué apagar.') }}
                </p>
            </div>
        </section>

        {{-- ═══ Planes: para quien arma muchas (el selector Mensual / 3 meses / Anual cambia el precio) ═══ --}}
        @include('site.partials.diy-plans', [
            'diyTitle' => __('Planes, para quien arma muchas'),
            'diyLead' => __('Para tus eventos o para vender invitaciones con tu marca, sin comisión. Precios por mes, en dólares (:currency), y menos pagando 3 o 12 meses de una vez.', ['currency' => Money::code()]),
            'diyDetail' => true,
        ])

        {{-- ═══ Comparar: qué trae cada nivel y qué cambia entre las tres formas ═══ --}}
        <section id="comparar" class="scroll-mt-20 border-t border-site-line" aria-labelledby="comparar-titulo">
            <div class="mx-auto max-w-7xl px-5 py-10 lg:px-8 lg:py-28">
                <div class="max-w-3xl">
                    <h2 id="comparar-titulo" class="site-display site-display--md" data-reveal>{{ __('Qué trae cada nivel') }}</h2>
                    <p class="site-muted-lead" data-reveal>{{ __('Con «Hazlo tú», cada nivel trae estas partes. Con un plan, cada invitación trae todas las partes; la confirmación depende del plan.') }}</p>
                </div>

                {{-- En el celular la comparación queda tras un botón (las tarjetas de arriba ya dicen lo que trae cada una) --}}
                <details class="site-compare-more mt-8 lg:mt-12" open data-accordion="closed">
                <summary class="site-compare-more__summary">
                    <span class="site-plan__more-open">{{ __('Ver la comparación completa') }}</span>
                    <span class="site-plan__more-close">{{ __('Ocultar la comparación') }}</span>
                    <x-phosphor-caret-down class="site-plan__caret" aria-hidden="true" />
                </summary>
                <div class="site-compare" data-reveal>
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
                </details>

                <h2 class="site-display site-display--md mt-12 lg:mt-20" data-reveal>{{ __('Hazlo tú o un plan') }}</h2>
                <div class="site-table site-table--stack" data-reveal>
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
                                    @foreach(array_slice($row, 1) as $i => $cell)
                                        <td data-label="{{ $products['columns'][$i] ?? '' }}">{{ $cell }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        @include('site.partials.faqs', ['faqs' => $faqs])

        {{-- ═══ A medida: sin precios; se conversa por WhatsApp o por correo ═══ --}}
        <section id="a-medida" class="scroll-mt-20 border-t border-site-line" aria-label="{{ __('A medida') }}">
            <div class="mx-auto max-w-7xl px-5 pb-16 lg:px-8">
                @include('site.partials.custom-request')
            </div>
        </section>

        {{-- ═══ Cierre ═══ --}}
        <section class="site-diy-final" aria-labelledby="cierre-titulo">
            <h2 id="cierre-titulo" class="site-display" data-reveal>{{ __('Empieza gratis. Paga solo si quieres más.') }}</h2>
            <div class="site-hero__actions" data-reveal>
                <a href="{{ $startUrl }}" class="site-btn site-btn--lg">
                    {{ __('Crear mi invitación gratis') }}
                    <x-phosphor-arrow-right class="site-btn__arrow" aria-hidden="true" />
                </a>
                <a href="{{ $contactUrl }}" target="_blank" rel="noopener" class="site-btn site-btn--ghost site-btn--lg">
                    <x-phosphor-whatsapp-logo aria-hidden="true" />
                    {{ __('Tengo una duda') }}
                </a>
            </div>
        </section>
    </main>

    @include('site.partials.footer', ['socials' => []])
@endsection
