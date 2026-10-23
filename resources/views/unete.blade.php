@extends('layouts.site')

{{--
    «Únete»: el plan mensual para armar invitaciones propias y venderlas (revendedores). Es el
    modelo principal del negocio. Ver PublicPagesController::diy.

    Lenguaje del sitio: titulares en serif, filetes en lugar de tarjetas, sin sombras ni brillos y el
    dorado como único acento. Habla de lo que el plan le da a quien vende (su marca, sus clientes,
    su ganancia) y deja los números donde hacen falta: el precio de cada plan y las preguntas. Nada
    inventado: ni cuentas regresivas ni testimonios. Todo lo que se promete existe en el panel del
    revendedor.
--}}
@section('title', __('Planes para profesionales: haz tus propias invitaciones digitales y véndelas con tu marca').' | '.$bida['brand'])
@section('description', __('Arma tus propias invitaciones digitales con plantillas profesionales, para tu evento o para vender con tu marca. Desde :price al mes, sin comisión por invitación.', ['price' => \App\Support\Money::format(collect($plans)->min('final_price'))]))

@use('App\Support\Money')
@use('App\Support\PlanTerms')

@php
    $accountUrl = $user ? route('dashboard') : lroute('login');
    $accountLabel = $user ? __('Mi panel') : __('Ingresar');
    $fromPrice = collect($plans)->min('final_price');

    // Descuento por pagar 3 o 12 meses de una vez (Ajustes), para las preguntas
    $prepayText = \App\Payments\SubscriptionPrice::prepaySummary();
    $costAnswer = $prepayText !== ''
        ? __('Desde :price al mes, y menos pagando varios meses de una vez (:discounts).', ['price' => Money::format($fromPrice), 'discounts' => $prepayText]).' '.PlanTerms::payment()
        : __('Desde :price al mes.', ['price' => Money::format($fromPrice)]).' '.PlanTerms::payment();

    // El primer plan que trae cada cosa, para decir «desde» sin escribirlo a mano
    $firstWith = fn (callable $has): ?string => collect($plans)->first($has)['name'] ?? null;
    $fromWhatsapp = $firstWith(fn (array $plan) => in_array('whatsapp', $plan['rsvp'] ?? [], true));
    $fromPass = $firstWith(fn (array $plan) => in_array('pass', $plan['rsvp'] ?? [], true));
    $fromBrand = $firstWith(fn (array $plan) => (bool) ($plan['white_label'] ?? false));

    $benefits = [
        ['icon' => 'coins', 'title' => __('Lo que cobras es tuyo'), 'from' => null, 'text' => __('Pagas un plan fijo al mes. No hay comisión por invitación.')],
        ['icon' => 'gift', 'title' => __('Un producto que ya te piden'), 'from' => null, 'text' => __('Bodas, XV años, bautizos, cumpleaños, graduaciones: toda celebración necesita su invitación.')],
        ['icon' => 'pencil-simple-line', 'title' => __('Profesional sin saber diseñar'), 'from' => null, 'text' => __('Eliges la plantilla, cargas las fotos y ya se ve bien. Si quieres, cambias colores, letras y textos.')],
        ['icon' => 'seal-check', 'title' => __('Tu marca al frente'), 'from' => $fromBrand ? __('Desde :fromBrand', ['fromBrand' => $fromBrand]) : null, 'text' => __('Tu nombre comercial al pie de cada invitación, no el nuestro.')],
        ['icon' => 'check-circle', 'title' => __('Tu cliente confirma y tú lo ves'), 'from' => $fromWhatsapp ? __('Desde :fromWhatsapp', ['fromWhatsapp' => $fromWhatsapp]) : null, 'text' => __('Cada invitado responde desde su celular y la respuesta te llega por WhatsApp. Tu cliente ve su lista sin tocar el diseño.')],
        ['icon' => 'qr-code', 'title' => __('Entrada con QR el día del evento'), 'from' => $fromPass ? __('Desde :fromPass', ['fromPass' => $fromPass]) : null, 'text' => __('Quien recibe escanea el pase con la cámara. Un pase no se puede usar dos veces.')],
    ];
    $profiles = [
        ['who' => __('Fotógrafos y videógrafos'), 'how' => __('La invitación digital completa tu paquete.'), 'plan' => __('Inicial')],
        ['who' => __('Salones y locales'), 'how' => __('La regalas o la vendes con cada evento que reservan.'), 'plan' => __('Emprendedor')],
        ['who' => __('Organizadores y agencias'), 'how' => __('Entregas invitaciones todas las semanas, cada cliente con su acceso.'), 'plan' => __('Agencia')],
        ['who' => __('Quien organiza un solo evento'), 'how' => __('Tu cumpleaños, tu boda o tu graduación, con tu propia invitación.'), 'plan' => __('Hazlo tú (pago único)'), 'url' => lroute('pricing').'#hazlo-tu', 'isPlan' => false],
        ['who' => __('Familias con varios eventos'), 'how' => __('El cumpleaños, el bautizo y la graduación de la casa, sin pagar cada una.'), 'plan' => __('Inicial')],
    ];
    $steps = [
        ['icon' => 'credit-card', 'title' => __('Eliges tu plan'), 'text' => __('Pagas el mes y recibes tu usuario. Empiezas hoy.')],
        ['icon' => 'pencil-simple-line', 'title' => __('Armas la invitación'), 'text' => __('Plantilla, fotos y datos del evento.')],
        ['icon' => 'paper-plane-tilt', 'title' => __('La compartes y cobras'), 'text' => __('Envías el enlace por WhatsApp y sigues las confirmaciones.')],
    ];

    $example = collect($plans)->firstWhere('key', 'inicial') ?? collect($plans)->first();
    $exampleSales = ($example['quota_per_month'] ?? 0) * $examplePrice;
    $faqs = [
        [__('¿Qué son los planes?'), __('Son planes mensuales que te dan tu propio panel en :brand para armar invitaciones digitales con nuestras plantillas y venderlas con tu marca, o usarlas en tus propios eventos. Armas todos los borradores que quieras y publicas las que tu plan permite cada mes.', ['brand' => $bida['brand']])],
        [__('¿Cuánto puedo ganar?'), __('Depende de a cuánto vendas y cuántas armes. Por ejemplo, con el plan :name (:price al mes), :quota_per_month invitaciones vendidas a :price2 suman :price3 y te quedan :price4 después del plan. El precio de venta lo decides tú.', ['name' => $example['name'], 'price' => Money::format($example['final_price']), 'quota_per_month' => $example['quota_per_month'], 'price2' => Money::format($examplePrice), 'price3' => Money::format($exampleSales), 'price4' => Money::format($exampleSales - $example['final_price'])])],
        [__('¿Cobran comisión por invitación?'), __('No. Pagas el plan del mes y lo que cobras por cada invitación es tuyo.')],
        [__('¿Necesito saber diseñar?'), __('No. Eliges una plantilla, cargas las fotos y los datos del evento y la invitación ya se ve bien. Si quieres ir más lejos, cambias colores, letras y cada texto.')],
        [__('¿Cuánto cuesta y cómo se paga?'), $costAnswer],
        [__('¿Cuentan las invitaciones que todavía no publico?'), __('No. El cupo de tu plan cuenta las invitaciones que publicas en el mes: los borradores no lo gastan, y ocultar una y volver a publicarla no la cuenta dos veces.')],
        [__('¿Qué pasa si no renuevo a tiempo?'), __('Las invitaciones que ya publicaste siguen en línea y puedes ver invitados y descargar reportes. Lo que se pausa es crear y editar invitaciones hasta que renueves.')],
        [__('¿Cuántos accesos para clientes puedo crear?'), __('Uno por evento y, en total, tantos por mes como invitaciones incluye tu plan. Si creaste uno mal, lo eliminas y creas otro.')],
        [PlanTerms::question(), PlanTerms::answer()],
        [__('¿Y si solo necesito una invitación?'), __('Para una sola no hace falta un plan: crea tu cuenta y ármala con todo. La publicas gratis con lo básico, o pagas una sola vez el nivel que usaste.')],
        [__('¿Hacen invitaciones a medida?'), __('Si quieres algo a medida, escríbenos por WhatsApp o al correo :email y lo conversamos.', ['email' => $bida['email']])],
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
        <section id="que-obtienes" class="site-benefits scroll-mt-20" aria-labelledby="que-obtienes-titulo">
            <div class="site-benefits__head">
                <h1 id="que-obtienes-titulo" class="site-display site-display--md" data-reveal>{{ __('Un negocio de invitaciones, sin montarlo desde cero') }}</h1>
                <p class="site-muted-lead" data-reveal>{{ __('Tú pones al cliente y tu marca. El panel pone el resto.') }}</p>
            </div>

            <div class="site-benefits__list">
                @foreach($benefits as $benefit)
                    <article class="site-benefit" data-reveal>
                        <x-dynamic-component :component="'phosphor-'.$benefit['icon'].'-light'" class="site-benefit__icon" aria-hidden="true" />
                        <div>
                            <h3 class="site-benefit__title">{{ $benefit['title'] }}</h3>
                            @if($benefit['from'])
                                <p class="site-benefit__from">{{ $benefit['from'] }}</p>
                            @endif
                            <p class="site-benefit__text">{{ $benefit['text'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- ═══ Para quién ═══ --}}
        <section class="site-diy-section" aria-labelledby="para-quien-titulo">
            <h2 id="para-quien-titulo" class="site-display site-display--md" data-reveal>{{ __('Para quien arma invitaciones') }}</h2>
            <ul class="site-diy-who" data-reveal>
                @foreach($profiles as $profile)
                    <li>
                        <span class="site-diy-who__who">{{ $profile['who'] }}</span>
                        <span class="site-diy-who__how">{{ $profile['how'] }}</span>
                        <a href="{{ $profile['url'] ?? '#planes' }}" class="site-diy-who__plan site-link-arrow">{{ ($profile['isPlan'] ?? true) ? __('Plan :plan', ['plan' => $profile['plan']]) : $profile['plan'] }} <x-phosphor-arrow-right aria-hidden="true" /></a>
                    </li>
                @endforeach
            </ul>
        </section>

        {{-- ═══ Cómo empiezas ═══ --}}
        <section class="site-diy-section" aria-labelledby="como-titulo">
            <h2 id="como-titulo" class="site-display site-display--md" data-reveal>{{ __('Así empiezas') }}</h2>
            <ol class="site-diy-steps">
                @foreach($steps as $step)
                    <li data-reveal style="--reveal-index: {{ $loop->index }}">
                        <x-dynamic-component :component="'phosphor-'.$step['icon'].'-light'" class="site-diy-steps__icon" aria-hidden="true" />
                        <h3 class="site-diy-steps__title">{{ $step['title'] }}</h3>
                        <p class="site-diy-steps__text">{{ $step['text'] }}</p>
                    </li>
                @endforeach
            </ol>
        </section>

        {{-- ═══ Planes: con el detalle de cada uno ═══ --}}
        @include('site.partials.diy-plans', [
            'diyTitle' => __('Elige tu plan'),
            'diyLead' => __('Precios por mes, en dólares (:currency), y menos pagando 3 o 12 meses de una vez. Cada plan suma al anterior y trae lo que ves en la lista.', ['currency' => Money::code()]),
            'diyDetail' => true,
        ])

        {{-- ═══ A medida: sin precios; se conversa por WhatsApp o por correo ═══ --}}
        <section id="a-medida" class="scroll-mt-20 border-t border-site-line" aria-label="{{ __('A medida') }}">
            <div class="mx-auto max-w-7xl px-5 pb-16 lg:px-8">
                @include('site.partials.custom-request')
            </div>
        </section>

        @include('site.partials.faqs', ['faqs' => $faqs])

        {{-- ═══ Cierre ═══ --}}
        <section class="site-diy-final" aria-labelledby="cierre-titulo">
            <h2 id="cierre-titulo" class="site-display" data-reveal>{{ __('Empieza hoy con tu primera invitación.') }}</h2>
            <div class="site-hero__actions" data-reveal>
                <a href="#planes" class="site-btn site-btn--lg">
                    {{ __('Elegir mi plan') }}
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
