@extends('layouts.site')

{{--
    Precios (/precios): las tres formas de tener la invitación, lado a lado, para que cada quien
    encuentre la suya sin leer letra chica. Ver App\Support\PricingPage.
    - Hazlo tú: gratis con lo básico, o un pago único por el nivel de las partes que usó.
    - Planes: para quien arma muchas (el bloque de siempre, con el selector Mensual / 3 meses / Anual).
    - Te la hacemos: el equipo la arma, con plazo de entrega y rondas de cambios.
    Después, qué trae cada nivel parte por parte, qué cambia entre las tres formas y las preguntas.
    Lenguaje del sitio: titulares en serif, filetes en lugar de tarjetas y el dorado como único acento.
--}}
@section('title', 'Precios de las invitaciones digitales | '.$bida['brand'])
@section('description', 'Arma tu invitación gratis y paga solo si quieres más, elige un plan si armas muchas o pídenos que la hagamos. Precios en dólares, sin letra chica.')

@use('App\Support\Money')

@php
    $navLinks = [
        '#hazlo-tu' => 'Hazlo tú',
        '#planes' => 'Planes',
        '#te-la-hacemos' => 'Te la hacemos',
        '#comparar' => 'Comparar',
        '#preguntas' => 'Preguntas',
    ];
    $accountUrl = $user ? route('dashboard') : route('login');
    $accountLabel = $user ? 'Mi panel' : 'Ingresar';
    $fromPlan = collect($plans)->min('final_price');
    $fromPackage = collect($packages)->min('final_price');
    $paidTiers = array_slice($diyTiers, 1);
    $ways = [
        ['id' => 'hazlo-tu', 'name' => 'Hazlo tú', 'text' => 'La armas tú: gratis con lo básico, o un pago único por todo lo que usaste.', 'from' => 'Gratis'],
        ['id' => 'planes', 'name' => 'Planes', 'text' => 'Para quien arma muchas: las tuyas o las de tus clientes, cada mes.', 'from' => $fromPlan !== null ? 'Desde '.Money::format($fromPlan).' al mes' : null],
        ['id' => 'te-la-hacemos', 'name' => 'Te la hacemos', 'text' => 'Nos cuentas tu evento y nuestro equipo la arma contigo.', 'from' => $fromPackage !== null ? 'Desde '.Money::format($fromPackage) : null],
    ];
@endphp

@push('head')
    @include('site.partials.structured-data', [
        'faqs' => $faqs,
        'serviceName' => 'Invitaciones digitales: hazla tú, con un plan o te la hacemos',
        'offers' => [
            ...array_map(fn (array $tier) => ['name' => 'Hazlo tú '.$tier['name'].' (pago único)', 'price' => (int) $tier['price'], 'description' => $tier['summary']], $paidTiers),
            ...array_map(fn (array $plan) => ['name' => 'Plan '.$plan['name'].' (mensual)', 'price' => $plan['final_price'], 'description' => $plan['summary'] ?? ''], $plans),
            ...array_map(fn (array $package) => ['name' => 'Te la hacemos '.$package['name'], 'price' => $package['final_price'], 'description' => $package['summary']], $packages),
        ],
        'breadcrumbs' => [[$bida['brand'], route('home')], ['Precios', route('pricing')]],
    ])
@endpush

@section('content')
    <div data-header-sentinel class="pointer-events-none absolute inset-x-0 top-0 h-4" aria-hidden="true"></div>

    @include('site.partials.header', ['navLinks' => $navLinks])

    <main>
        {{-- ═══ Las tres formas, de un vistazo ═══ --}}
        <section class="site-pricing-intro" aria-labelledby="precios-titulo">
            <div class="max-w-3xl">
                <h1 id="precios-titulo" class="site-display site-display--md" data-reveal>Precios claros para cada forma de tener tu invitación</h1>
                <p class="site-muted-lead" data-reveal>Todo en dólares ({{ Money::code() }}), sin comisiones ni letra chica. Empieza gratis y paga solo por lo que usas.</p>
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
            <div class="mx-auto max-w-7xl px-5 py-20 lg:px-8 lg:py-28">
                <div class="max-w-3xl">
                    <h2 id="hazlo-tu-titulo" class="site-display site-display--md" data-reveal>Hazlo tú</h2>
                    <p class="site-muted-lead" data-reveal>
                        Arma tu invitación con todas las partes que quieras y pruébala completa. Al publicarla decides:
                        gratis con lo básico, o un pago único por el nivel de lo que usaste. Subir de nivel después cobra solo la diferencia.
                    </p>
                </div>

                <div class="site-plans site-plans--pro mt-14">
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
                            :tag="($tier['featured'] ?? false) ? 'Recomendado' : null"
                            :url="$startUrl"
                            :cta="$tier['key'] === 'gratis' ? 'Crear mi invitación gratis' : 'Crear mi invitación'"
                            :index="$index"
                            :reserve="true" />
                    @endforeach
                </div>

                <p class="mt-10 max-w-[70ch] text-site-muted" data-reveal>
                    No eliges el nivel antes: lo dicen las partes que enciendes. Mientras la armas, el editor te muestra en cuál estás
                    y, si quieres que sea gratis, qué apagar.
                </p>
            </div>
        </section>

        {{-- ═══ Planes: para quien arma muchas (el selector Mensual / 3 meses / Anual cambia el precio) ═══ --}}
        @include('site.partials.diy-plans', [
            'diyTitle' => 'Planes, para quien arma muchas',
            'diyLead' => 'Para tus eventos o para vender invitaciones con tu marca, sin comisión. Precios por mes, en dólares ('.Money::code().'), y menos pagando 3 o 12 meses de una vez.',
            'diyDetail' => true,
        ])

        {{-- ═══ Te la hacemos: el mismo nivel, armado por el equipo, con plazo y cambios ═══ --}}
        @include('site.partials.plans', [
            'plansId' => 'te-la-hacemos',
            'plansTitle' => 'Te la hacemos',
            'plansLead' => 'Nos cuentas tu evento y nuestro equipo arma la invitación contigo. Cada paquete trae las mismas partes que el nivel del mismo nombre, más el servicio: un plazo de entrega y rondas de cambios. Pago único, en dólares ('.Money::code().').',
        ])

        {{-- ═══ Comparar: qué trae cada nivel y qué cambia entre las tres formas ═══ --}}
        <section id="comparar" class="scroll-mt-20 border-t border-site-line" aria-labelledby="comparar-titulo">
            <div class="mx-auto max-w-7xl px-5 py-20 lg:px-8 lg:py-28">
                <div class="max-w-3xl">
                    <h2 id="comparar-titulo" class="site-display site-display--md" data-reveal>Qué trae cada nivel</h2>
                    <p class="site-muted-lead" data-reveal>Los mismos en «Hazlo tú» y en «Te la hacemos». Con un plan, cada invitación trae todas las partes; la confirmación depende del plan.</p>
                </div>

                <div class="site-compare mt-12" data-reveal>
                    <table>
                        <caption class="sr-only">Qué incluye cada nivel de la invitación</caption>
                        <thead>
                            <tr>
                                <th scope="col"><span class="sr-only">Parte de la invitación</span></th>
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
                                                <span class="sr-only">Incluido</span>
                                            @else
                                                <span class="site-compare__no" aria-hidden="true">—</span>
                                                <span class="sr-only">No incluido</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <h2 class="site-display site-display--md mt-20" data-reveal>Hazlo tú, un plan o te la hacemos</h2>
                <div class="site-table" data-reveal>
                    <table>
                        <caption class="sr-only">Diferencias entre Hazlo tú, los planes y Te la hacemos</caption>
                        <thead>
                            <tr>
                                <th scope="col"><span class="sr-only">Qué cambia</span></th>
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
            </div>
        </section>

        @include('site.partials.faqs', ['faqs' => $faqs])

        {{-- ═══ Cierre ═══ --}}
        <section class="site-diy-final" aria-labelledby="cierre-titulo">
            <h2 id="cierre-titulo" class="site-display" data-reveal>Empieza gratis. Paga solo si quieres más.</h2>
            <div class="site-hero__actions" data-reveal>
                <a href="{{ $startUrl }}" class="site-btn site-btn--lg">
                    Crear mi invitación gratis
                    <x-phosphor-arrow-right class="site-btn__arrow" aria-hidden="true" />
                </a>
                <a href="{{ $contactUrl }}" target="_blank" rel="noopener" class="site-btn site-btn--ghost site-btn--lg">
                    <x-phosphor-whatsapp-logo aria-hidden="true" />
                    Tengo una duda
                </a>
            </div>
        </section>
    </main>

    @include('site.partials.footer', ['socials' => []])
@endsection
