@extends('layouts.site')

{{--
    Publicidad para profesionales de eventos (revendedores): fotógrafos, organizadores, decoradores y
    salones que arman invitaciones para sus clientes con un plan mensual. Ver
    PublicPagesController::professionals. Los planes, sus precios y cupos salen de
    config('bida.reseller_plans') con lo que el administrador cambió en Ajustes; las muestras, de
    config('bida.professionals.demos'). Todo lo que se promete aquí existe en el panel del revendedor.
--}}
@section('title', 'Invitaciones digitales para profesionales de eventos | '.$bida['brand'])
@section('description', 'Fotógrafos, organizadores y decoradores: arma invitaciones digitales con tu marca para tus clientes, con un plan mensual y sin comisiones por invitación.')

@php
    $navLinks = [
        '#como-funciona' => 'Cómo funciona',
        '#planes' => 'Planes',
        '#preguntas' => 'Preguntas',
    ];
    $accountUrl = $user ? route('dashboard') : route('login');
    $accountLabel = $user ? 'Mi panel' : 'Ingresar';
    $socials = [];
    $demos = \App\Support\ShowcaseDemos::find($bida['professionals']['demos'] ?? []);
    $fromPrice = collect($plans)->min('price');
    $steps = [
        ['icon' => 'chat-circle-text', 'title' => 'Eliges tu plan', 'text' => 'Nos escribes, eliges el plan y pagas el mes por QR o transferencia. Te damos tu usuario y tu contraseña para entrar a tu panel.'],
        ['icon' => 'pencil-simple-line', 'title' => 'Armas la invitación', 'text' => 'Desde tu panel eliges la plantilla, subes las fotos y cambias colores, letras y cada texto mientras ves la invitación en vivo.'],
        ['icon' => 'paper-plane-tilt', 'title' => 'La entregas', 'text' => 'Tu cliente recibe el enlace, un acceso para ver sus confirmaciones y los reportes en PDF y Excel.'],
    ];
    $highlights = [
        ['icon' => 'eye', 'title' => 'Vista previa mientras editas', 'text' => 'La invitación se ve al lado del editor y salta a la sección que estás cambiando.'],
        ['icon' => 'text-aa', 'title' => 'Cada texto se puede cambiar', 'text' => 'Títulos, frases, botones y mensajes de cada sección, para que la invitación hable como tu cliente.'],
        ['icon' => 'frame-corners', 'title' => 'Una plantilla en blanco', 'text' => '«Lienzo»: fondo blanco, letra negra y nada de adornos, para diseñarla a tu manera.'],
        ['icon' => 'users-three', 'title' => 'Confirmaciones y reportes', 'text' => 'Enlace personal por invitado, pase con QR y la lista en PDF o Excel para el día del evento.'],
        ['icon' => 'identification-badge', 'title' => 'Acceso para tu cliente', 'text' => 'Tu cliente entra a ver sus invitados y descargar sus reportes, sin tocar el diseño. Uno por evento, en todos los planes.'],
        ['icon' => 'seal-check', 'title' => 'Tu marca al pie', 'text' => 'En los planes con marca blanca, las invitaciones llevan tu nombre comercial en lugar del nuestro.'],
    ];
    // Filas de la comparación: qué cambia de un plan a otro
    $compare = [
        ['label' => 'Invitaciones al mes', 'value' => fn (array $plan) => $plan['quota_per_month'] === null ? 'Sin tope' : (string) $plan['quota_per_month']],
        ['label' => $collections['lienzo']['label'], 'value' => fn (array $plan) => in_array('lienzo', $plan['collections'] ?? [], true)],
        ['label' => $collections['clasica']['label'], 'value' => fn (array $plan) => in_array('clasica', $plan['collections'] ?? [], true)],
        ['label' => $collections['tematica']['label'], 'value' => fn (array $plan) => in_array('tematica', $plan['collections'] ?? [], true)],
        ['label' => 'Accesos para tus clientes al mes', 'value' => fn (array $plan) => $plan['quota_per_month'] === null ? 'Sin tope' : (string) $plan['quota_per_month']],
        ['label' => 'Tu marca al pie', 'value' => fn (array $plan) => (bool) ($plan['white_label'] ?? false)],
    ];
    $faqs = [
        ['¿Necesito saber diseñar?', 'No. Eliges una plantilla, cargas las fotos y los datos del evento y la invitación ya se ve bien. Si quieres ir más lejos, la plantilla en blanco te deja cambiar colores, letras y cada texto.'],
        ['¿Cómo pago el plan?', 'Cada mes, por QR o transferencia, coordinado por WhatsApp. No hay cobros automáticos ni guardamos datos de tarjetas: cada pago extiende tu plan un mes.'],
        ['¿Qué pasa si no renuevo a tiempo?', 'Las invitaciones que ya publicaste siguen en línea y puedes seguir viendo invitados y descargando reportes. Lo que se pausa es crear y editar invitaciones hasta que renueves.'],
        ['¿Cuánto les cobro a mis clientes?', 'Lo que tú decidas. No cobramos comisión por invitación: pagas tu plan y el cupo de invitaciones del mes es tuyo.'],
        ['¿Qué pasa si me equivoco al crear el acceso de un cliente?', 'Eliminas ese acceso desde el evento y creas otro. Cada evento tiene su propio cliente.'],
        ['¿Puedo cambiar de plan?', 'Sí. Nos escribes y el cambio se aplica con tu siguiente pago.'],
    ];
@endphp

@section('content')
    <div data-header-sentinel class="pointer-events-none absolute inset-x-0 top-0 h-4" aria-hidden="true"></div>

    @include('site.partials.header', ['navLinks' => $navLinks])

    <main>
        {{-- ═══ Portada: a quién va dirigido y las muestras para probar ═══ --}}
        <section class="site-landing" @if(count($demos)) x-data="{ active: 0, loading: true, demos: @js($demos) }" @endif>
            <div class="mx-auto grid max-w-7xl items-center gap-12 px-5 pb-16 pt-8 md:pt-14 lg:min-h-[calc(100dvh-72px)] lg:grid-cols-12 lg:gap-8 lg:px-8 lg:py-12">
                <div class="lg:col-span-6">
                    <nav class="site-enter text-sm text-site-muted" aria-label="Ruta">
                        <a href="{{ route('home') }}" class="site-nav-link hover:text-site-ink">{{ $bida['brand'] }}</a>
                        <span class="mx-2" aria-hidden="true">/</span>
                        <span class="text-site-ink">Para profesionales</span>
                    </nav>
                    <p class="site-enter mt-6 text-[0.95rem] font-medium text-site-accent" style="--enter-index: 1">Para fotógrafos, organizadores, decoradores y salones</p>
                    <h1 class="site-enter mt-3 max-w-[16ch] text-[2.4rem] font-semibold leading-[1.06] tracking-tight sm:text-5xl xl:text-[3.4rem]" style="--enter-index: 1">
                        Invitaciones digitales con tu nombre, para tus clientes
                    </h1>
                    <p class="site-enter mt-6 max-w-[46ch] text-lg leading-relaxed text-site-muted" style="--enter-index: 2">
                        Suma las invitaciones digitales a lo que ya ofreces. Las armas tú desde tu panel, con nuestras plantillas, y las cobras como quieras.
                    </p>

                    @if(count($demos) > 1)
                        <div class="site-enter mt-9" style="--enter-index: 3">
                            <p class="text-sm font-medium text-site-muted" id="disenos-titulo">Diseños que puedes usar</p>
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
                        <a href="{{ $contactUrl }}" target="_blank" rel="noopener" class="site-btn site-btn--lg justify-center" data-magnetic>
                            <x-phosphor-whatsapp-logo aria-hidden="true" />
                            Quiero empezar
                        </a>
                        <a href="#planes" class="site-btn site-btn--ghost site-btn--lg justify-center">
                            Ver los planes
                            <x-phosphor-arrow-down class="site-btn__arrow" aria-hidden="true" />
                        </a>
                    </div>
                    <p class="site-enter mt-6 text-sm text-site-muted" style="--enter-index: 5">
                        Desde {{ $fromPrice }} Bs al mes · Sin comisión por invitación · Pago por QR o transferencia
                    </p>
                </div>

                @if(count($demos))
                    <div class="site-landing__stage lg:col-span-5 lg:col-start-8">
                        <div class="site-landing__photo" aria-hidden="true">
                            <x-site.image key="servicio-enlace" :priority="true" />
                        </div>
                        <div class="site-phone site-phone--showcase">
                            <div id="diseno-vista" @if(count($demos) > 1) role="tabpanel" aria-labelledby="diseno-tab-0" :aria-labelledby="'diseno-tab-' + active" @endif
                                class="site-phone__screen" :class="{ 'is-loading': loading }">
                                <iframe src="{{ $demos[0]['demoUrl'] }}" :src="demos[active].demoUrl"
                                    title="Invitación de muestra: {{ $demos[0]['title'] }}" :title="'Invitación de muestra: ' + demos[active].title"
                                    @load="loading = false"></iframe>
                            </div>
                        </div>
                        <p class="site-landing__hint">
                            Así la ven los invitados de tus clientes. Es una muestra: nada de lo que hagas se guarda.
                            <a href="{{ $demos[0]['demoUrl'] }}" :href="demos[active].demoUrl" target="_blank" rel="noopener">Abrir en pantalla completa</a>
                        </p>
                    </div>
                @endif
            </div>
        </section>

        {{-- ═══ Cómo funciona ═══ --}}
        <section id="como-funciona" class="scroll-mt-20 border-t border-site-line">
            <div class="mx-auto grid max-w-7xl gap-12 px-5 py-20 lg:grid-cols-12 lg:gap-8 lg:px-8 lg:py-28">
                <div class="lg:col-span-5">
                    <div class="lg:sticky lg:top-32">
                        <h2 class="text-3xl font-semibold leading-[1.1] tracking-tight md:text-5xl" data-reveal>Cómo funciona</h2>
                        <p class="mt-5 max-w-[38ch] text-lg leading-relaxed text-site-muted" data-reveal>
                            Un plan mensual con un cupo de invitaciones. Lo que cobras a cada cliente es tuyo.
                        </p>
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

        {{-- ═══ Qué tienes en tu panel ═══ --}}
        <section id="incluye" class="scroll-mt-20 border-t border-site-line">
            <div class="mx-auto max-w-7xl px-5 py-20 lg:px-8 lg:py-28">
                <div class="grid gap-6 lg:grid-cols-12 lg:items-end lg:gap-8">
                    <h2 class="max-w-[18ch] text-3xl font-semibold leading-[1.1] tracking-tight md:text-5xl lg:col-span-7" data-reveal>
                        El mismo editor con el que armamos nuestras invitaciones
                    </h2>
                    <p class="max-w-[42ch] text-lg leading-relaxed text-site-muted lg:col-span-4 lg:col-start-9" data-reveal>
                        Con las mismas plantillas, las de temporada y las que vayamos sumando.
                    </p>
                </div>

                <ul class="site-grid-features mt-14">
                    @foreach($highlights as $index => $highlight)
                        <li data-reveal style="--reveal-index: {{ $index % 2 }}">
                            <x-dynamic-component :component="'phosphor-'.$highlight['icon'].'-light'" class="site-grid-features__icon" aria-hidden="true" />
                            <h3>{{ $highlight['title'] }}</h3>
                            <p>{{ $highlight['text'] }}</p>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>

        {{-- ═══ Planes: precio del mes, cupo y qué desbloquea cada uno ═══ --}}
        <section id="planes" class="scroll-mt-20 border-t border-site-line bg-site-surface">
            <div class="mx-auto max-w-7xl px-5 py-20 lg:px-8 lg:py-28">
                <p class="text-[0.95rem] font-medium text-site-accent" data-reveal>Planes</p>
                <h2 class="mt-4 max-w-[20ch] text-3xl font-semibold leading-[1.1] tracking-tight md:text-5xl" data-reveal>Elige cuántas invitaciones necesitas al mes</h2>
                <p class="mt-5 max-w-[56ch] text-lg leading-relaxed text-site-muted" data-reveal>
                    Precios en bolivianos, por mes. Cada plan suma plantillas y funciones al anterior; puedes cambiar de plan cuando renueves.
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
                                <span class="flex items-baseline gap-2">
                                    <span class="site-plan__price">{{ $plan['price'] }}</span>
                                    <span class="text-xl text-site-muted">Bs al mes</span>
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

                {{-- Comparación: lo que cambia de un plan a otro, en una tabla que se desliza en el celular --}}
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

                <dl class="site-collections mt-12" data-reveal>
                    @foreach($collections as $collection)
                        @if(count($collection['templates']))
                            <div>
                                <dt>{{ $collection['label'] }}</dt>
                                <dd>{{ implode(' · ', $collection['templates']) }}</dd>
                            </div>
                        @endif
                    @endforeach
                </dl>

                <p class="mt-10 text-site-muted" data-reveal>
                    ¿Tienes un estudio o un salón con muchos eventos?
                    <a href="{{ $contactUrl }}" target="_blank" rel="noopener" class="font-medium text-site-ink underline underline-offset-4">Escríbenos</a>
                    y vemos el plan que te conviene.
                </p>
            </div>
        </section>

        @include('site.partials.faqs', ['faqs' => $faqs])
    </main>

    @include('site.partials.footer', ['navLinks' => $navLinks, 'socials' => $socials])
@endsection
