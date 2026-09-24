@extends('layouts.site')

{{--
    «Hazlo tú»: planes mensuales para armar invitaciones propias con el panel de Bida (revendedores).
    Sirve para cualquiera que organice varios eventos o venda invitaciones: familias, fotógrafos,
    salones, organizadores. Ver PublicPagesController::diy.

    Pensada para leerse y citarse: cada beneficio dice qué es y qué significa en la práctica (listas),
    hay una tabla por perfil, cuentas claras con un ejemplo calculado con los precios reales, y la
    comparación de planes. Todo lo que se promete existe en el panel del revendedor.
--}}
@section('title', 'Hazlo tú: crea tus propias invitaciones digitales | '.$bida['brand'])
@section('description', 'Tu propio panel para crear invitaciones digitales con plantillas profesionales, confirmación de asistencia, pase QR y control de entrada. Planes desde '.\App\Support\Money::format(collect($plans)->min('final_price')).' al mes, sin comisión por invitación.')

@php
    $navLinks = [
        '#que-obtienes' => 'Qué obtienes',
        '#planes' => 'Planes',
        '#preguntas' => 'Preguntas',
    ];
    $accountUrl = $user ? route('dashboard') : route('login');
    $accountLabel = $user ? 'Mi panel' : 'Ingresar';
    $fromPrice = collect($plans)->min('final_price');
    $benefits = [
        [
            'icon' => 'pencil-simple-line',
            'title' => 'El mismo editor que usa nuestro equipo',
            'text' => 'Armas la invitación por secciones (portada, lugar, itinerario, fotos, confirmación…) y la ves al lado, tal como la verán tus invitados en el celular.',
            'points' => ['La vista previa salta a la sección que estás editando.', 'Cada módulo se enciende o se apaga con un interruptor.', 'Te avisa qué dato falta antes de publicar.'],
        ],
        [
            'icon' => 'text-aa',
            'title' => 'Todo el texto y el diseño se pueden cambiar',
            'text' => 'Nada queda fijo: títulos, frases, botones y mensajes de cada sección, además de colores y tipografías.',
            'points' => ['Paletas listas que pasan la prueba de lectura (contraste AA).', 'Si dejas un texto vacío, vuelve el de la plantilla.', 'La plantilla «Lienzo» empieza en blanco para diseñar desde cero.'],
        ],
        [
            'icon' => 'squares-four',
            'title' => 'Plantillas para cada evento',
            'text' => 'Diseños con apertura animada para bodas, XV años, bautizos, cumpleaños y graduaciones, más los de temporada.',
            'points' => ['Clásicas: '.implode(', ', $collections['clasica']['templates']).'.', 'Temáticas y de temporada: '.implode(', ', $collections['tematica']['templates']).'.', 'Las que se sumen durante el año quedan en tu panel según tu plan.'],
        ],
        [
            'icon' => 'check-circle',
            'title' => 'Confirmación de asistencia con pase QR',
            'text' => 'Cada invitado recibe su enlace personal, confirma cuántas personas van y obtiene un pase con código QR.',
            'points' => ['Ves en tiempo real quién confirmó y quién no respondió.', 'Recoge alergias o restricciones alimentarias.', 'Compartes cada enlace por WhatsApp desde el panel.'],
        ],
        [
            'icon' => 'qr-code',
            'title' => 'Control de entrada el día del evento',
            'text' => 'Quien recibe a los invitados abre un enlace de puerta y escanea el pase de cada uno con la cámara de su teléfono.',
            'points' => ['Ve al instante si puede pasar y cuántas personas entran.', 'Un pase no se puede usar dos veces.', 'Cuenta en vivo cuántas personas llegaron.'],
        ],
        [
            'icon' => 'identification-badge',
            'title' => 'Acceso para tu cliente y reportes',
            'text' => 'Si armas invitaciones para otros, les das un acceso para ver sus confirmaciones y descargar sus reportes, sin tocar el diseño.',
            'points' => ['Un acceso por evento; tantos por mes como invitaciones de tu plan.', 'Lista de invitados en PDF y Excel para el salón y el catering.', 'La invitación impresa, lista en dos hojas.'],
        ],
        [
            'icon' => 'seal-check',
            'title' => 'Tu marca, no la nuestra',
            'text' => 'En los planes Emprendedor y Agencia, el pie de cada invitación lleva tu nombre comercial en lugar del de '.$bida['brand'].'.',
            'points' => ['Tus clientes ven tu marca en cada invitación que comparten.', 'Sin enlaces a nuestro sitio en el pie.'],
        ],
        [
            'icon' => 'lock-simple',
            'title' => 'Privado y seguro',
            'text' => 'Las invitaciones no aparecen en buscadores: solo las abre quien tiene el enlace.',
            'points' => ['Conexión cifrada (HTTPS) y contraseñas guardadas cifradas.', 'Los datos de los invitados solo los ve quien organiza.', 'Si no renuevas, tus invitaciones publicadas siguen en línea.'],
        ],
    ];
    $profiles = [
        ['who' => 'Familias o personas que organizan varios eventos al año', 'how' => 'Arman el cumpleaños, el bautizo y la graduación de la casa sin pagar cada invitación por separado.', 'plan' => 'Inicial o Aliado'],
        ['who' => 'Fotógrafos y videógrafos', 'how' => 'Suman la invitación digital a su paquete de fotos y la entregan con sus propias imágenes.', 'plan' => 'Aliado'],
        ['who' => 'Salones y locales de eventos', 'how' => 'Regalan o venden la invitación a cada evento que reservan, con su marca al pie.', 'plan' => 'Emprendedor'],
        ['who' => 'Organizadores de eventos y agencias', 'how' => 'Entregan invitaciones todas las semanas y dan a cada cliente su acceso a las confirmaciones.', 'plan' => 'Agencia'],
    ];
    $steps = [
        ['icon' => 'chat-circle-text', 'title' => 'Eliges tu plan', 'text' => 'Nos escribes, eliges el plan y pagas el mes por QR o transferencia. Te damos tu usuario y tu contraseña para entrar a tu panel.'],
        ['icon' => 'pencil-simple-line', 'title' => 'Armas la invitación', 'text' => 'Eliges la plantilla, subes las fotos y cambias colores, letras y textos mientras ves la invitación en vivo.'],
        ['icon' => 'paper-plane-tilt', 'title' => 'La compartes', 'text' => 'Publicas, envías los enlaces por WhatsApp y sigues las confirmaciones y la entrada desde tu panel.'],
    ];
    $compare = [
        ['label' => 'Invitaciones al mes', 'value' => fn (array $plan) => $plan['quota_per_month'] === null ? 'Sin tope' : (string) $plan['quota_per_month']],
        ['label' => 'Accesos para clientes al mes', 'value' => fn (array $plan) => $plan['quota_per_month'] === null ? 'Sin tope' : (string) $plan['quota_per_month']],
        ['label' => $collections['lienzo']['label'], 'value' => fn (array $plan) => in_array('lienzo', $plan['collections'] ?? [], true)],
        ['label' => $collections['clasica']['label'], 'value' => fn (array $plan) => in_array('clasica', $plan['collections'] ?? [], true)],
        ['label' => $collections['tematica']['label'], 'value' => fn (array $plan) => in_array('tematica', $plan['collections'] ?? [], true)],
        ['label' => 'Confirmación, pase QR y control de entrada', 'value' => fn (array $plan) => true],
        ['label' => 'Tu marca al pie', 'value' => fn (array $plan) => (bool) ($plan['white_label'] ?? false)],
    ];
    $faqs = [
        ['¿Qué es Hazlo tú?', 'Es un plan mensual que te da tu propio panel en '.$bida['brand'].' para crear invitaciones digitales con nuestras plantillas: con confirmación de asistencia, pase QR y control de entrada. Pagas el mes y armas las invitaciones que tu plan permite.'],
        ['¿Necesito saber diseñar?', 'No. Eliges una plantilla, cargas las fotos y los datos del evento y la invitación ya se ve bien. Si quieres ir más lejos, puedes cambiar colores, letras y cada texto.'],
        ['¿Cuánto cuesta y cómo se paga?', 'Desde '.\App\Support\Money::format($fromPrice).' al mes. Se paga cada mes por QR o transferencia, coordinado por WhatsApp. No hay cobros automáticos: cada pago extiende tu plan un mes.'],
        ['¿Qué pasa si no renuevo a tiempo?', 'Las invitaciones que ya publicaste siguen en línea y puedes ver invitados y descargar reportes. Lo que se pausa es crear y editar invitaciones hasta que renueves.'],
        ['¿Puedo cobrar a mis clientes?', 'Sí, lo que tú decidas. No cobramos comisión por invitación.'],
        ['¿Cuántos accesos para clientes puedo crear?', 'Uno por evento y, en total, tantos por mes como invitaciones incluye tu plan. Si creaste uno mal, lo eliminas y creas otro.'],
        ['¿Puedo cambiar de plan?', 'Sí. Nos escribes y el cambio se aplica con tu siguiente pago.'],
    ];
@endphp

@push('head')
    @include('site.partials.structured-data', [
        'faqs' => $faqs,
        'serviceName' => 'Hazlo tú: panel mensual para crear invitaciones digitales',
        'offers' => array_map(fn (array $plan) => ['name' => 'Plan '.$plan['name'].' (mensual)', 'price' => $plan['final_price'], 'description' => $plan['summary'] ?? ''], $plans),
        'breadcrumbs' => [[$bida['brand'], route('home')], ['Hazlo tú', route('diy')]],
    ])
@endpush

@section('content')
    <div data-header-sentinel class="pointer-events-none absolute inset-x-0 top-0 h-4" aria-hidden="true"></div>

    @include('site.partials.header', ['navLinks' => $navLinks])

    <main>
        {{-- ═══ Portada ═══ --}}
        <section class="site-diy-hero">
            <div class="site-diy-hero__copy">
                <nav class="site-enter text-sm text-site-muted" aria-label="Ruta">
                    <a href="{{ route('home') }}" class="site-nav-link hover:text-site-ink">{{ $bida['brand'] }}</a>
                    <span class="mx-2" aria-hidden="true">/</span>
                    <span class="text-site-ink">Hazlo tú</span>
                </nav>
                <h1 class="site-enter site-display mt-5" style="--enter-index: 1">Tu propio panel para crear invitaciones digitales</h1>
                <p class="site-enter site-hero__lead" style="--enter-index: 2">
                    Armas las invitaciones de tus eventos, o las de tus clientes, con las mismas plantillas y el mismo editor que usa
                    nuestro equipo. Pagas un plan al mes y lo que cobras por cada invitación es tuyo.
                </p>
                <div class="site-enter site-hero__actions" style="--enter-index: 3">
                    <a href="{{ $contactUrl }}" target="_blank" rel="noopener" class="site-btn site-btn--lg">
                        <x-phosphor-whatsapp-logo aria-hidden="true" />
                        Quiero mi panel
                    </a>
                    <a href="#planes" class="site-btn site-btn--ghost site-btn--lg">
                        Ver los planes
                        <x-phosphor-arrow-down class="site-btn__arrow" aria-hidden="true" />
                    </a>
                </div>
                <ul class="site-enter site-hero__facts" style="--enter-index: 4">
                    <li>Desde {{ \App\Support\Money::format($fromPrice) }} al mes</li>
                    <li>Sin comisión por invitación</li>
                    <li>Sin cobros automáticos</li>
                </ul>
            </div>

            @if($demo)
                <div class="site-diy-hero__device">
                    <div class="site-phone site-phone--showcase">
                        <div class="site-phone__screen">
                            <iframe src="{{ $demo['demoUrl'] }}" title="Invitación de muestra: {{ $demo['title'] }}" loading="lazy"></iframe>
                        </div>
                    </div>
                    <p class="site-tester__hint">
                        <x-phosphor-hand-tap aria-hidden="true" />
                        Así la ven los invitados. Es una muestra: pruébala.
                    </p>
                </div>
            @endif
        </section>

        {{-- ═══ Qué obtienes, en detalle ═══ --}}
        <section id="que-obtienes" class="site-benefits scroll-mt-20" aria-labelledby="que-obtienes-titulo">
            <div class="site-benefits__head">
                <h2 id="que-obtienes-titulo" class="site-display site-display--md" data-reveal>Qué obtienes con Hazlo tú</h2>
                <p class="site-muted-lead" data-reveal>
                    Un panel con todo lo necesario para que una invitación funcione de principio a fin: diseño, confirmaciones,
                    entrada y reportes. Esto es lo que incluye y lo que significa en la práctica.
                </p>
            </div>

            <div class="site-benefits__list">
                @foreach($benefits as $benefit)
                    <article class="site-benefit" data-reveal>
                        <x-dynamic-component :component="'phosphor-'.$benefit['icon'].'-light'" class="site-benefit__icon" aria-hidden="true" />
                        <div>
                            <h3 class="site-benefit__title">{{ $benefit['title'] }}</h3>
                            <p class="site-benefit__text">{{ $benefit['text'] }}</p>
                            <ul class="site-benefit__points">
                                @foreach($benefit['points'] as $point)
                                    <li>{{ $point }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- ═══ Para quién es: tabla por perfil ═══ --}}
        <section class="site-diy-section" aria-labelledby="para-quien-titulo">
            <div class="site-diy-section__head">
                <h2 id="para-quien-titulo" class="site-display site-display--md" data-reveal>Para quién es</h2>
                <p class="site-muted-lead" data-reveal>Para cualquiera que arme más de una invitación: no hace falta ser profesional.</p>
            </div>
            <div class="site-table" data-reveal>
                <table>
                    <caption class="sr-only">Cómo usa Hazlo tú cada perfil y qué plan le conviene</caption>
                    <thead>
                        <tr>
                            <th scope="col">Si eres…</th>
                            <th scope="col">Cómo lo usas</th>
                            <th scope="col">Plan que suele convenir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($profiles as $profile)
                            <tr>
                                <th scope="row">{{ $profile['who'] }}</th>
                                <td>{{ $profile['how'] }}</td>
                                <td class="whitespace-nowrap font-medium">{{ $profile['plan'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        {{-- ═══ Cuentas claras: cuánto sale cada invitación y un ejemplo si las vendes ═══ --}}
        <section class="site-diy-section" aria-labelledby="cuentas-titulo">
            <div class="site-diy-section__head">
                <h2 id="cuentas-titulo" class="site-display site-display--md" data-reveal>Cuentas claras</h2>
                <p class="site-muted-lead" data-reveal>
                    Cuánto te sale cada invitación si usas todo el cupo del mes y, como ejemplo, cuánto te queda si vendes cada una
                    a {{ \App\Support\Money::format($examplePrice) }}. Los precios son los de hoy.
                </p>
            </div>
            <div class="site-table" data-reveal>
                <table>
                    <caption class="sr-only">Costo por invitación y ejemplo de ganancia por plan</caption>
                    <thead>
                        <tr>
                            <th scope="col">Plan</th>
                            <th scope="col">Al mes</th>
                            <th scope="col">Invitaciones</th>
                            <th scope="col">Cada invitación te sale</th>
                            <th scope="col">Si vendes cada una a {{ \App\Support\Money::format($examplePrice) }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($plans as $plan)
                            <tr>
                                <th scope="row">{{ $plan['name'] }}</th>
                                <td class="tabular-nums">{{ \App\Support\Money::format($plan['final_price']) }}</td>
                                <td class="tabular-nums">{{ $plan['quota_per_month'] ?? 'Sin tope' }}</td>
                                <td class="tabular-nums">{{ $plan['cost_per_invitation'] !== null ? \App\Support\Money::format($plan['cost_per_invitation']) : 'Menos, cuantas más hagas' }}</td>
                                <td class="tabular-nums">{{ $plan['example_margin'] !== null ? 'Te quedan '.\App\Support\Money::format($plan['example_margin']).' al mes' : 'Depende de cuántas hagas' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="site-table-note" data-reveal>Ejemplo con el cupo completo; el precio de venta lo decides tú.</p>
        </section>

        {{-- ═══ Cómo funciona ═══ --}}
        <section class="border-t border-site-line">
            <div class="mx-auto grid max-w-7xl gap-12 px-5 py-20 lg:grid-cols-12 lg:gap-8 lg:px-8 lg:py-28">
                <div class="lg:col-span-5">
                    <div class="lg:sticky lg:top-32">
                        <h2 class="site-display site-display--md" data-reveal>Cómo funciona</h2>
                        <p class="site-muted-lead" data-reveal>Un plan al mes con un cupo de invitaciones. Empiezas el mismo día que pagas.</p>
                    </div>
                </div>
                <ol class="site-steps grid gap-12 lg:col-span-6 lg:col-start-7 lg:gap-16">
                    @foreach($steps as $step)
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

        {{-- ═══ Planes ═══ --}}
        <section id="planes" class="scroll-mt-20 border-t border-site-line bg-site-surface" aria-labelledby="planes-titulo">
            <div class="mx-auto max-w-7xl px-5 py-20 lg:px-8 lg:py-28">
                <h2 id="planes-titulo" class="site-display site-display--md" data-reveal>Elige cuántas invitaciones necesitas al mes</h2>
                <p class="site-muted-lead" data-reveal>
                    Precios en dólares ({{ \App\Support\Money::code() }}), por mes. Cada plan suma plantillas y funciones al anterior.
                </p>

                <div class="site-plans site-plans--pro mt-14">
                    @foreach($plans as $index => $plan)
                        @php($featured = $plan['key'] === $featuredPlan)
                        <article @class(['site-plan', 'site-plan--featured' => $featured]) data-reveal style="--reveal-index: {{ $index }}">
                            <div class="site-plan__head">
                                <h3 class="site-plan__name">{{ $plan['name'] }}</h3>
                                @if($featured)
                                    <span class="site-plan__tag">Recomendado</span>
                                @endif
                            </div>

                            <p class="site-plan__amount">
                                @if($plan['old_price'])
                                    <del class="site-plan__old"><span class="sr-only">Antes </span>{{ \App\Support\Money::format($plan['old_price']) }}</del>
                                    <span class="sr-only">, ahora</span>
                                @endif
                                <span class="flex items-baseline gap-2">
                                    <span class="site-plan__price">{{ $plan['final_price'] }}</span>
                                    <span class="text-xl text-site-muted">{{ \App\Support\Money::code() }} al mes</span>
                                </span>
                            </p>
                            <p class="site-plan__saving">
                                {{ $plan['quota_per_month'] === null ? 'Invitaciones sin tope' : $plan['quota_per_month'].' invitaciones al mes' }}
                                · {{ $plan['templates_count'] }} {{ $plan['templates_count'] === 1 ? 'plantilla' : 'plantillas' }}
                            </p>

                            <p class="mt-4 max-w-[36ch] leading-relaxed text-site-muted">{{ $plan['summary'] ?? '' }}</p>

                            <ul class="site-plan__features">
                                @foreach($plan['features'] ?? [] as $feature)
                                    <li>
                                        <x-phosphor-check-bold class="site-plan__check" aria-hidden="true" />
                                        <span>{{ $feature }}</span>
                                    </li>
                                @endforeach
                            </ul>

                            <a href="{{ $plan['whatsapp'] }}" target="_blank" rel="noopener"
                                @class(['site-btn site-btn--lg mt-10 justify-center', 'site-btn--ghost' => ! $featured])>
                                Elegir {{ $plan['name'] }}
                                <x-phosphor-arrow-right class="site-btn__arrow" aria-hidden="true" />
                            </a>
                        </article>
                    @endforeach
                </div>

                <div class="site-compare mt-16" data-reveal>
                    <table>
                        <caption class="sr-only">Qué incluye cada plan</caption>
                        <thead>
                            <tr>
                                <th scope="col"><span class="sr-only">Función</span></th>
                                @foreach($plans as $plan)
                                    <th scope="col" @class(['is-featured' => $plan['key'] === $featuredPlan])>{{ $plan['name'] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($compare as $row)
                                <tr>
                                    <th scope="row">{{ $row['label'] }}</th>
                                    @foreach($plans as $plan)
                                        @php($value = $row['value']($plan))
                                        <td @class(['is-featured' => $plan['key'] === $featuredPlan])>
                                            @if($value === true)
                                                <x-phosphor-check-bold class="site-compare__yes" aria-hidden="true" /><span class="sr-only">Incluido</span>
                                            @elseif($value === false)
                                                <span class="site-compare__no" aria-hidden="true">—</span><span class="sr-only">No incluido</span>
                                            @else
                                                {{ $value }}
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <p class="mt-10 text-site-muted" data-reveal>
                    ¿Necesitas algo distinto?
                    <a href="{{ $contactUrl }}" target="_blank" rel="noopener" class="font-medium text-site-ink underline underline-offset-4">Escríbenos</a>
                    y vemos el plan que te conviene.
                </p>
            </div>
        </section>

        @include('site.partials.faqs', ['faqs' => $faqs])
    </main>

    @include('site.partials.footer', ['socials' => []])
@endsection
