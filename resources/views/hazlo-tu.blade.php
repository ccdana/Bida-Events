@extends('layouts.site')

{{--
    «Hazlo tú»: el plan mensual para armar invitaciones propias y venderlas (revendedores). Es el
    modelo principal del negocio; lo secundario es que el equipo se las haga («paquetes»). Ver
    PublicPagesController::diy.

    Lenguaje del sitio: titulares en serif, filetes en lugar de tarjetas, sin sombras ni brillos y el
    dorado como único acento. Habla de lo que el plan le da a quien vende (su marca, sus clientes,
    su ganancia) y deja los números donde hacen falta: el precio de cada plan y las preguntas. Nada
    inventado: ni cuentas regresivas ni testimonios. Todo lo que se promete existe en el panel del
    revendedor.
--}}
@section('title', 'Hazlo tú: haz tus propias invitaciones digitales y véndelas con tu marca | '.$bida['brand'])
@section('description', 'Arma tus propias invitaciones digitales con plantillas profesionales, para tu evento o para vender con tu marca. Desde '.\App\Support\Money::format(collect($plans)->min('final_price')).' al mes, sin comisión por invitación.')

@use('App\Support\Money')

@php
    $navLinks = [
        '#que-obtienes' => 'Qué ganas',
        '#comparar' => 'Planes',
        '#te-la-hacemos' => 'Te la hacemos',
        '#preguntas' => 'Preguntas',
    ];
    $accountUrl = $user ? route('dashboard') : route('login');
    $accountLabel = $user ? 'Mi panel' : 'Ingresar';
    $fromPrice = collect($plans)->min('final_price');
    $lowestPackage = collect($packages)->min('final_price');

    // El plan de una sola invitación (su fila va en los planes; aquí, en las preguntas y en «Para quién»)
    $single = collect($plans)->first(fn (array $plan) => $plan['quota_per_month'] === 1);

    // El primer plan que trae cada cosa, para decir «desde» sin escribirlo a mano
    $firstWith = fn (callable $has): ?string => collect($plans)->first($has)['name'] ?? null;
    $fromWhatsapp = $firstWith(fn (array $plan) => in_array('whatsapp', $plan['rsvp'] ?? [], true));
    $fromPass = $firstWith(fn (array $plan) => in_array('pass', $plan['rsvp'] ?? [], true));
    $fromBrand = $firstWith(fn (array $plan) => (bool) ($plan['white_label'] ?? false));

    $benefits = [
        ['icon' => 'coins', 'title' => 'Lo que cobras es tuyo', 'from' => null, 'text' => 'Pagas un plan fijo al mes. No hay comisión por invitación.'],
        ['icon' => 'gift', 'title' => 'Un producto que ya te piden', 'from' => null, 'text' => 'Bodas, XV años, bautizos, cumpleaños, graduaciones: toda celebración necesita su invitación.'],
        ['icon' => 'pencil-simple-line', 'title' => 'Profesional sin saber diseñar', 'from' => null, 'text' => 'Eliges la plantilla, cargas las fotos y ya se ve bien. Si quieres, cambias colores, letras y textos.'],
        ['icon' => 'seal-check', 'title' => 'Tu marca al frente', 'from' => $fromBrand ? 'Desde '.$fromBrand : null, 'text' => 'Tu nombre comercial al pie de cada invitación, no el nuestro.'],
        ['icon' => 'check-circle', 'title' => 'Tu cliente confirma y tú lo ves', 'from' => $fromWhatsapp ? 'Desde '.$fromWhatsapp : null, 'text' => 'Cada invitado responde desde su celular y la respuesta te llega por WhatsApp. Tu cliente ve su lista sin tocar el diseño.'],
        ['icon' => 'qr-code', 'title' => 'Entrada con QR el día del evento', 'from' => $fromPass ? 'Desde '.$fromPass : null, 'text' => 'Quien recibe escanea el pase con la cámara. Un pase no se puede usar dos veces.'],
    ];
    $profiles = [
        ['who' => 'Fotógrafos y videógrafos', 'how' => 'La invitación digital completa tu paquete.', 'plan' => 'Aliado'],
        ['who' => 'Salones y locales', 'how' => 'La regalas o la vendes con cada evento que reservan.', 'plan' => 'Emprendedor'],
        ['who' => 'Organizadores y agencias', 'how' => 'Entregas invitaciones todas las semanas, cada cliente con su acceso.', 'plan' => 'Agencia'],
        ['who' => 'Quien organiza un solo evento', 'how' => 'Tu cumpleaños, tu boda o tu graduación, con tu propia invitación.', 'plan' => $single['name'] ?? 'Inicial'],
        ['who' => 'Familias con varios eventos', 'how' => 'El cumpleaños, el bautizo y la graduación de la casa, sin pagar cada una.', 'plan' => 'Inicial'],
    ];
    $steps = [
        ['icon' => 'credit-card', 'title' => 'Eliges tu plan', 'text' => 'Pagas el mes y recibes tu usuario. Empiezas hoy.'],
        ['icon' => 'pencil-simple-line', 'title' => 'Armas la invitación', 'text' => 'Plantilla, fotos y datos del evento.'],
        ['icon' => 'paper-plane-tilt', 'title' => 'La compartes y cobras', 'text' => 'Envías el enlace por WhatsApp y sigues las confirmaciones.'],
    ];

    $example = collect($plans)->firstWhere('key', 'aliado') ?? collect($plans)->first();
    $exampleSales = ($example['quota_per_month'] ?? 0) * $examplePrice;
    $faqs = [
        ['¿Qué es Hazlo tú?', 'Es un plan mensual que te da tu propio panel en '.$bida['brand'].' para armar invitaciones digitales con nuestras plantillas y venderlas con tu marca, o usarlas en tus propios eventos. Pagas el mes y armas las invitaciones que tu plan permite.'],
        ['¿Cuánto puedo ganar?', 'Depende de a cuánto vendas y cuántas armes. Por ejemplo, con el plan '.$example['name'].' ('.Money::format($example['final_price']).' al mes), '.$example['quota_per_month'].' invitaciones vendidas a '.Money::format($examplePrice).' suman '.Money::format($exampleSales).' y te quedan '.Money::format($exampleSales - $example['final_price']).' después del plan. El precio de venta lo decides tú.'],
        ['¿Cobran comisión por invitación?', 'No. Pagas el plan del mes y lo que cobras por cada invitación es tuyo.'],
        ['¿Necesito saber diseñar?', 'No. Eliges una plantilla, cargas las fotos y los datos del evento y la invitación ya se ve bien. Si quieres ir más lejos, cambias colores, letras y cada texto.'],
        ['¿Cuánto cuesta y cómo se paga?', 'Desde '.Money::format($fromPrice).' al mes. Se paga cada mes por QR o transferencia, coordinado por WhatsApp. No hay cobros automáticos: cada pago extiende tu plan un mes.'],
        ['¿Qué pasa si no renuevo a tiempo?', 'Las invitaciones que ya publicaste siguen en línea y puedes ver invitados y descargar reportes. Lo que se pausa es crear y editar invitaciones hasta que renueves.'],
        ['¿Cuántos accesos para clientes puedo crear?', 'Uno por evento y, en total, tantos por mes como invitaciones incluye tu plan. Si creaste uno mal, lo eliminas y creas otro.'],
        ['¿Puedo cambiar de plan?', 'Sí. Nos escribes y el cambio se aplica con tu siguiente pago.'],
        ['¿Y si solo necesito una invitación?', 'Elige el plan '.($single['name'] ?? 'Inicial').': '.Money::format($single['final_price'] ?? $fromPrice).' por un mes y una invitación, para tu propio evento o para un cliente.'],
        ['¿Y si prefiero que la hagan ustedes?', 'También lo hacemos: eliges un paquete, nos mandas los datos y te entregamos la invitación lista. Desde '.Money::format($lowestPackage).' por invitación, pago único.'],
    ];
@endphp

@push('head')
    @include('site.partials.structured-data', [
        'faqs' => $faqs,
        'serviceName' => 'Hazlo tú: plan mensual para crear y vender invitaciones digitales',
        'offers' => array_map(fn (array $plan) => ['name' => 'Plan '.$plan['name'].' (mensual)', 'price' => $plan['final_price'], 'description' => $plan['summary'] ?? ''], $plans),
        'breadcrumbs' => [[$bida['brand'], route('home')], ['Hazlo tú', route('diy')]],
    ])
@endpush

@section('content')
    <div data-header-sentinel class="pointer-events-none absolute inset-x-0 top-0 h-4" aria-hidden="true"></div>

    @include('site.partials.header', ['navLinks' => $navLinks])

    <main>
        {{-- ═══ Qué ganas ═══ --}}
        <section id="que-obtienes" class="site-benefits scroll-mt-20" aria-labelledby="que-obtienes-titulo">
            <div class="site-benefits__head">
                <h1 id="que-obtienes-titulo" class="site-display site-display--md" data-reveal>Un negocio de invitaciones, sin montarlo desde cero</h1>
                <p class="site-muted-lead" data-reveal>Tú pones al cliente y tu marca. El panel pone el resto.</p>
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
            <h2 id="para-quien-titulo" class="site-display site-display--md" data-reveal>Para quien arma invitaciones</h2>
            <ul class="site-diy-who" data-reveal>
                @foreach($profiles as $profile)
                    <li>
                        <span class="site-diy-who__who">{{ $profile['who'] }}</span>
                        <span class="site-diy-who__how">{{ $profile['how'] }}</span>
                        <a href="#comparar" class="site-diy-who__plan site-link-arrow">Plan {{ $profile['plan'] }} <x-phosphor-arrow-right aria-hidden="true" /></a>
                    </li>
                @endforeach
            </ul>
        </section>

        {{-- ═══ Cómo empiezas ═══ --}}
        <section class="site-diy-section" aria-labelledby="como-titulo">
            <h2 id="como-titulo" class="site-display site-display--md" data-reveal>Así empiezas</h2>
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

        {{-- ═══ Planes: la comparación punto por punto, con «Elegir» en la última fila ═══ --}}
        @include('site.partials.diy-compare')

        {{-- ═══ Lo secundario: te la hacemos nosotros ═══ --}}
        <section id="te-la-hacemos" class="site-diy-service scroll-mt-20" aria-labelledby="te-la-hacemos-titulo">
            <div class="site-diy-service__head">
                <h2 id="te-la-hacemos-titulo" class="site-display site-display--md" data-reveal>¿Prefieres no armarla? Te la hacemos nosotros</h2>
                <p class="site-muted-lead" data-reveal>Pago único por invitación. Nos mandas los datos y te la entregamos lista.</p>
            </div>

            <ul class="site-diy-service__list" data-reveal>
                @foreach($packages as $package)
                    <li>
                        <div>
                            <p class="site-diy-service__name">{{ $package['name'] }}</p>
                            <p class="site-diy-service__text">{{ $package['summary'] }}</p>
                        </div>
                        <p class="site-diy-service__price">
                            @if(! empty($package['old_price']))
                                <del><span class="sr-only">Antes </span>{{ Money::format($package['old_price']) }}</del>
                            @endif
                            <span>{{ Money::format($package['final_price']) }}</span>
                        </p>
                        <a href="{{ $package['whatsapp'] }}" target="_blank" rel="noopener" class="site-link-arrow">
                            Pedir {{ $package['name'] }}
                            <x-phosphor-arrow-right aria-hidden="true" />
                        </a>
                    </li>
                @endforeach
            </ul>

            <p class="site-table-note" data-reveal>
                <a href="{{ route('home') }}#precios" class="font-medium text-site-ink underline underline-offset-4">Ver todo lo que incluye cada paquete</a>
            </p>
        </section>

        @include('site.partials.faqs', ['faqs' => $faqs])

        {{-- ═══ Cierre ═══ --}}
        <section class="site-diy-final" aria-labelledby="cierre-titulo">
            <h2 id="cierre-titulo" class="site-display" data-reveal>Empieza hoy con tu primera invitación.</h2>
            <div class="site-hero__actions" data-reveal>
                <a href="#comparar" class="site-btn site-btn--lg">
                    Elegir mi plan
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
