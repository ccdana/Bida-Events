@extends('layouts.site')

{{--
    «Únete»: para quien arma invitaciones para otros (revendedores): fotógrafos, salones, organizadores. Cuenta
    qué gana (su marca, sus clientes, su ganancia), para quién es cada plan y cómo se empieza; los planes
    con su detalle y su precio viven en /precios#planes, así no se repiten dos veces. Ver PublicPagesController::diy.

    Lenguaje del sitio: titulares en serif, filetes en lugar de tarjetas, sin sombras ni brillos y el dorado como
    único acento. Nada inventado: ni cuentas regresivas ni testimonios. Todo lo que se promete existe en el panel
    del revendedor.
--}}
@section('title', __('Planes para profesionales: haz tus propias invitaciones digitales y véndelas con tu marca').' | '.$bida['brand'])
@section('description', __('Arma tus propias invitaciones digitales con plantillas profesionales, para tu evento o para vender con tu marca. Desde :price al mes, sin comisión por invitación.', ['price' => \App\Support\Money::format(collect($plans)->min('final_price'))]))

@use('App\Support\Money')
@use('App\Support\PlanTerms')

@php
    $accountUrl = $user ? route('dashboard') : lroute('login');
    $accountLabel = $user ? __('Mi panel') : __('Ingresar');
    $plansUrl = lroute('pricing').'#planes';

    // El primer plan que trae cada cosa, para decir «desde» sin escribirlo a mano
    $firstWith = fn (callable $has): ?string => collect($plans)->first($has)['name'] ?? null;
    $fromWhatsapp = $firstWith(fn (array $plan) => in_array('whatsapp', $plan['rsvp'] ?? [], true));
    $fromPass = $firstWith(fn (array $plan) => in_array('pass', $plan['rsvp'] ?? [], true));
    $fromBrand = $firstWith(fn (array $plan) => (bool) ($plan['white_label'] ?? false));

    $benefits = [
        ['icon' => 'coins', 'title' => __('Lo que cobras es tuyo'), 'from' => null, 'text' => __('Un plan fijo al mes, sin comisión por invitación.')],
        ['icon' => 'gift', 'title' => __('Un producto que ya te piden'), 'from' => null, 'text' => __('Bodas, XV años, bautizos, graduaciones: toda fiesta necesita su invitación.')],
        ['icon' => 'pencil-simple-line', 'title' => __('Profesional sin saber diseñar'), 'from' => null, 'text' => __('Eliges la plantilla, cargas las fotos y listo. Si quieres, cambias colores y textos.')],
        ['icon' => 'seal-check', 'title' => __('Tu marca al frente'), 'from' => $fromBrand ? __('Desde :fromBrand', ['fromBrand' => $fromBrand]) : null, 'text' => __('Tu nombre al pie de cada invitación, no el nuestro.')],
        ['icon' => 'check-circle', 'title' => __('Tu cliente confirma y tú lo ves'), 'from' => $fromWhatsapp ? __('Desde :fromWhatsapp', ['fromWhatsapp' => $fromWhatsapp]) : null, 'text' => __('Cada invitado responde desde su celular y la respuesta te llega por WhatsApp.')],
        ['icon' => 'qr-code', 'title' => __('Entrada con QR'), 'from' => $fromPass ? __('Desde :fromPass', ['fromPass' => $fromPass]) : null, 'text' => __('Escaneas el pase con la cámara; un pase no se usa dos veces.')],
    ];
    $profiles = [
        ['who' => __('Fotógrafos y videógrafos'), 'how' => __('La invitación digital completa tu paquete.'), 'plan' => __('Plan :plan', ['plan' => __('Inicial')]), 'url' => $plansUrl],
        ['who' => __('Salones y locales'), 'how' => __('La regalas o la vendes con cada reserva.'), 'plan' => __('Plan :plan', ['plan' => __('Emprendedor')]), 'url' => $plansUrl],
        ['who' => __('Organizadores y agencias'), 'how' => __('Entregas cada semana, cada cliente con su acceso.'), 'plan' => __('Plan :plan', ['plan' => __('Agencia')]), 'url' => $plansUrl],
        ['who' => __('Quien organiza un solo evento'), 'how' => __('Tu cumpleaños, tu boda o tu graduación, con tu propia invitación.'), 'plan' => __('Hazlo tú (pago único)'), 'url' => lroute('pricing').'#hazlo-tu'],
    ];
    $steps = [
        ['icon' => 'credit-card', 'title' => __('Eliges tu plan'), 'text' => __('Pagas el mes y recibes tu usuario.')],
        ['icon' => 'pencil-simple-line', 'title' => __('Armas la invitación'), 'text' => __('Plantilla, fotos y datos del evento.')],
        ['icon' => 'paper-plane-tilt', 'title' => __('La compartes y cobras'), 'text' => __('Envías el enlace por WhatsApp y sigues las confirmaciones.')],
    ];
    $quotaLabel = fn (array $plan): string => $plan['quota_per_month'] === null ? __('Sin tope') : __(':quota_per_month invitaciones al mes', ['quota_per_month' => $plan['quota_per_month']]);

    $example = collect($plans)->firstWhere('key', 'inicial') ?? collect($plans)->first();
    $exampleSales = ($example['quota_per_month'] ?? 0) * $examplePrice;
    $faqs = [
        [__('¿Cuánto puedo ganar?'), __('Depende de a cuánto vendas. Con el plan :name (:price al mes), :quota_per_month invitaciones a :price2 suman :price3 y te quedan :price4 tras el plan. El precio de venta lo decides tú.', ['name' => $example['name'], 'price' => Money::format($example['final_price']), 'quota_per_month' => $example['quota_per_month'], 'price2' => Money::format($examplePrice), 'price3' => Money::format($exampleSales), 'price4' => Money::format($exampleSales - $example['final_price'])])],
        [__('¿Cobran comisión por invitación?'), __('No. Pagas el plan del mes y lo que cobras por cada invitación es tuyo.')],
        [__('¿Necesito saber diseñar?'), __('No. Eliges una plantilla, cargas las fotos y los datos, y ya se ve bien. Si quieres, cambias colores, letras y textos.')],
        [__('¿Cuentan las invitaciones que todavía no publico?'), __('No. El cupo cuenta las que publicas en el mes: los borradores no, y volver a publicar una no la cuenta dos veces.')],
        [__('¿Qué pasa si no renuevo a tiempo?'), __('Lo que publicaste sigue en línea y puedes ver invitados y descargar reportes. Se pausa crear y editar hasta que renueves.')],
        [PlanTerms::question(), PlanTerms::answer()],
    ];
@endphp

@push('head')
    @include('site.partials.structured-data', [
        'faqs' => $faqs,
        'serviceName' => __('Planes para profesionales: crea y vende invitaciones digitales'),
        'offers' => array_map(fn (array $plan) => ['name' => __('Plan :name (mensual)', ['name' => $plan['name']]), 'price' => $plan['final_price'], 'description' => $plan['summary'] ?? ''], $plans),
        'breadcrumbs' => [[$bida['brand'], lroute('home')], [__('Planes'), lroute('diy')]],
    ])
@endpush

@section('content')
    <div data-header-sentinel class="pointer-events-none absolute inset-x-0 top-0 h-4" aria-hidden="true"></div>

    @include('site.partials.header')

    <main id="contenido" tabindex="-1">
        {{-- ═══ Qué ganas ═══ --}}
        <section id="que-obtienes" class="site-section site-section--first scroll-mt-20" aria-labelledby="que-obtienes-titulo">
            <h1 id="que-obtienes-titulo" class="site-display site-display--md" data-reveal>{{ __('Un negocio de invitaciones, sin montarlo desde cero') }}</h1>
            <p class="site-muted-lead" data-reveal>{{ __('Tú pones al cliente y tu marca. El panel pone el resto.') }}</p>
            <div class="site-hero__actions" data-reveal>
                <a href="{{ $plansUrl }}" class="site-btn site-btn--lg">
                    {{ __('Ver los planes') }}
                    <x-phosphor-arrow-right class="site-btn__arrow" aria-hidden="true" />
                </a>
                <a href="{{ $contactUrl }}" target="_blank" rel="noopener" class="site-icon-btn site-icon-btn--whatsapp site-icon-btn--lg" aria-label="{{ __('Escribir por WhatsApp') }}" title="{{ __('Escribir por WhatsApp') }}">
                    <x-phosphor-whatsapp-logo aria-hidden="true" />
                </a>
            </div>

            <div class="site-benefits__list">
                @foreach($benefits as $benefit)
                    <article class="site-benefit" data-reveal>
                        <x-dynamic-component :component="'phosphor-'.$benefit['icon'].'-light'" class="site-benefit__icon" aria-hidden="true" />
                        <div>
                            <h3 class="site-benefit__title">{{ $benefit['title'] }} @if($benefit['from'])<small class="site-benefit__from">{{ $benefit['from'] }}</small>@endif</h3>
                            <p class="site-benefit__text">{{ $benefit['text'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- ═══ Para quién y cómo se empieza, lado a lado ═══ --}}
        <section class="site-section site-duo" aria-label="{{ __('Para quién es y cómo se empieza') }}">
            <div aria-labelledby="para-quien-titulo" role="group">
                <h2 id="para-quien-titulo" class="site-display site-display--md" data-reveal>{{ __('Para quien arma invitaciones') }}</h2>
                <ul class="site-diy-who" data-reveal>
                    @foreach($profiles as $profile)
                        <li>
                            <span class="site-diy-who__who">{{ $profile['who'] }}</span>
                            <span class="site-diy-who__how">{{ $profile['how'] }}</span>
                            <a href="{{ $profile['url'] }}" class="site-diy-who__plan site-link-arrow">{{ $profile['plan'] }} <x-phosphor-arrow-right aria-hidden="true" /></a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div aria-labelledby="como-titulo" role="group">
                <h2 id="como-titulo" class="site-display site-display--md" data-reveal>{{ __('Así empiezas') }}</h2>
                <ol class="site-steps site-steps--solo">
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
        </section>

        {{-- ═══ Los planes en tres renglones; el detalle y el pago, en /precios ═══ --}}
        <section class="site-section site-section--surface" aria-labelledby="planes-titulo">
            <h2 id="planes-titulo" class="site-display site-display--md" data-reveal>{{ __('Planes') }}</h2>
            <p class="site-muted-lead" data-reveal>{{ __('Desde :price al mes, sin comisión por invitación.', ['price' => Money::format(collect($plans)->min('final_price'))]) }}</p>

            <ul class="site-planlines" data-reveal>
                @foreach($plans as $plan)
                    <li>
                        <a href="{{ $plansUrl }}" class="site-planlines__row">
                            <span class="site-planlines__name">{{ $plan['name'] }}</span>
                            <span class="site-planlines__quota">{{ $quotaLabel($plan) }}</span>
                            <span class="site-planlines__price">{{ __(':price al mes', ['price' => Money::format($plan['final_price'])]) }}</span>
                            <x-phosphor-arrow-right class="site-planlines__arrow" aria-hidden="true" />
                        </a>
                    </li>
                @endforeach
            </ul>

            <p class="mt-4" data-reveal>
                <a href="{{ $plansUrl }}" class="site-link-arrow">
                    {{ __('Ver el detalle de cada plan') }}
                    <x-phosphor-arrow-right aria-hidden="true" />
                </a>
            </p>
        </section>

        @include('site.partials.faqs', ['faqs' => $faqs])

        {{-- ═══ Cierre: los planes y, si es algo a medida, WhatsApp o correo (sin precios) ═══ --}}
        <section id="a-medida" class="site-close scroll-mt-20" aria-labelledby="cierre-titulo">
            <h2 id="cierre-titulo" class="site-display site-display--md" data-reveal>{{ __('Empieza hoy con tu primera invitación.') }}</h2>
            <div class="site-close__actions" data-reveal>
                <a href="{{ $plansUrl }}" class="site-btn site-btn--lg">
                    {{ __('Elegir mi plan') }}
                    <x-phosphor-arrow-right class="site-btn__arrow" aria-hidden="true" />
                </a>
                @include('site.partials.custom-request')
            </div>
        </section>
    </main>

    @include('site.partials.footer', ['socials' => []])
@endsection
