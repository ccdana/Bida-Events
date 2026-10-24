@extends('layouts.site')

{{--
    Guía «Invitaciones digitales»: la página de referencia del sitio para Google y para las IA que
    responden preguntas (ChatGPT, Gemini, Perplexity, AI Overviews). Ver docs/geo-estrategia.md.

    Cómo está escrita: abre con la respuesta directa en una o dos frases (lo que una IA cita) y cada tema es un
    renglón plegable, como las preguntas frecuentes (details.site-fold): el texto está en el HTML, pero a la vista
    queda lo importante y la página no se alarga. Las tablas son de pocas columnas y celdas cortas, para que
    quepan en el celular sin deslizar. Los precios salen de la configuración de hoy; nada de estadísticas
    inventadas. Ver PublicPagesController::guide.
--}}
@php
    $brand = $bida['brand'];
    $money = fn ($amount) => \App\Support\Money::format($amount);
    $fromPrice = collect($packages)->min('final_price');
    $toPrice = collect($packages)->max('final_price');
    $fromPlan = collect($plans)->min('final_price');
    $accountUrl = $user ? route('dashboard') : lroute('login');
    $accountLabel = $user ? __('Mi panel') : __('Ingresar');
    $title = __('Invitaciones digitales: qué son, cuánto cuestan y cómo elegir la tuya');
    $description = __('Qué debe incluir una invitación digital, cuánto cuesta (desde :price), cuándo enviarla por WhatsApp y cómo funciona la confirmación de asistencia y el control de entrada con código QR.', ['price' => $money($fromPrice)]);

    $faqs = [
        [__('¿Qué es una invitación digital?'), __('Es una página web del evento que se comparte con un enlace, normalmente por WhatsApp. Reúne la fecha, el lugar con mapa, el itinerario, la música, las fotos y un botón para confirmar asistencia, y se ve bien en cualquier celular sin instalar nada.')],
        [__('¿Cuánto cuesta una invitación digital?'), __('En :brand armas tu invitación gratis, con lo básico, y los niveles de pago van de :price a :price2 por evento, en un solo pago. El precio cambia según lo que incluye: en Estándar se confirma por WhatsApp; en Premium, con pase QR, control de entrada, panel de invitados y reportes.', ['brand' => $brand, 'price' => $money($fromPrice), 'price2' => $money($toPrice)])],
        [__('¿Con cuánta anticipación se envía una invitación digital?'), __('Para bodas, entre seis y ocho semanas antes; para XV años, entre cuatro y seis; para bautizos, graduaciones y cumpleaños, entre dos y cuatro semanas. Conviene mandar un recordatorio una semana antes: con una invitación digital se reenvía el mismo enlace.')],
        [__('¿Cómo confirman asistencia los invitados?'), __('Cada invitado recibe su propio enlace, elige si va y cuántas personas de las que tiene asignadas lo acompañan. Quien organiza ve las respuestas al momento en su panel y puede descargarlas en PDF o Excel.')],
        [__('¿Cómo funciona el control de entrada con código QR?'), __('Al confirmar, cada invitado recibe un pase con código QR. El día del evento, quien recibe en la puerta abre un enlace en su teléfono y escanea el pase: ve el nombre, cuántas personas pueden entrar y si el pase ya se usó. Un pase no sirve dos veces.')],
        [__('¿Las invitaciones digitales aparecen en Google?'), __('No deberían. Las de :brand están marcadas para que los buscadores no las indexen: solo las abre quien tiene el enlace, y los datos de los invitados solo los ve quien organiza.', ['brand' => $brand])],
        [__('¿Puedo hacer mis propias invitaciones digitales?'), __('Sí. Con los planes tienes un panel propio con las mismas plantillas y el mismo editor, desde :price al mes y sin comisión por invitación. Sirven para familias con varios eventos y para fotógrafos, salones u organizadores.', ['price' => $money($fromPlan)])],
    ];

    $includes = [
        [__('Lo que el invitado necesita saber'), [__('Fecha y hora, con cuenta regresiva y botón para agendarla.'), __('Lugar con mapa y botón «Cómo llegar».'), __('Itinerario y código de vestimenta; mesa de regalos si hay.')]],
        [__('Lo que hace que se sienta del evento'), [__('Portada con tus fotos y una apertura animada.'), __('Música de fondo que el invitado puede pausar.'), __('Colores y tipografías propias; en :brand también se cambia cada texto.', ['brand' => $brand])]],
        [__('Lo que le sirve a quien organiza'), [__('Confirmación por invitado, con las personas asignadas a cada uno.'), __('Pase con código QR y control de entrada el día del evento.'), __('Lista en PDF o Excel para el salón y el catering.')]],
    ];

    // Qué hace cada forma de invitar: sí o no, con íconos (la tabla cabe en el celular sin deslizar)
    $comparison = [
        'columns' => [__('Digital'), __('Impresa'), __('Imagen')],
        'rows' => [
            [__('Llega al instante'), true, false, true],
            [__('Mapa con un toque'), true, false, false],
            [__('Confirmación por invitado'), true, false, false],
            [__('Cambios sin reimprimir'), true, false, false],
            [__('Control en la puerta con QR'), true, false, false],
            [__('Recuerdo para imprimir'), true, true, false],
        ],
    ];

    $timing = [
        [__('Boda'), __('Entre 6 y 8 semanas antes')],
        [__('XV años'), __('Entre 4 y 6 semanas antes')],
        [__('Bautizo'), __('Entre 3 y 4 semanas antes')],
        [__('Graduación'), __('Entre 3 y 4 semanas antes')],
        [__('Cumpleaños'), __('Entre 2 y 3 semanas antes')],
    ];

    $mistakes = [
        __('Mandarla sin el mapa: la pregunta que más se repite el día del evento es «¿cómo llego?».'),
        __('Pedir que confirmen «por mensaje»: las respuestas se pierden en el chat.'),
        __('No decir cuántas personas incluye cada invitación: la lista del salón nunca cierra.'),
        __('Olvidar el recordatorio: una semana antes, reenviar el mismo enlace sube las confirmaciones.'),
    ];
@endphp

@section('title', $title.' | '.$brand)
@section('description', $description)

@push('head')
    @include('site.partials.structured-data', [
        'faqs' => $faqs,
        'breadcrumbs' => [[$brand, lroute('home')], [__('Guía de invitaciones digitales'), lroute('guide')]],
        'article' => [
            'headline' => $title,
            'description' => $description,
            'url' => lroute('guide'),
            'published' => $updatedAt,
            'modified' => $updatedAt,
        ],
    ])
@endpush

@section('content')
    <div data-header-sentinel class="pointer-events-none absolute inset-x-0 top-0 h-4" aria-hidden="true"></div>

    @include('site.partials.header')

    <main id="contenido" tabindex="-1" class="mx-auto max-w-7xl px-5 pb-10 pt-3 lg:px-8 lg:pb-16 lg:pt-8">
        <nav class="text-sm text-site-muted" aria-label="{{ __('Ruta') }}">
            <a href="{{ lroute('home') }}" class="site-nav-link site-touch-y hover:text-site-ink">{{ $brand }}</a>
            <span class="mx-2" aria-hidden="true">/</span>
            <span class="text-site-ink">{{ __('Guía de invitaciones digitales') }}</span>
        </nav>

        <div class="mt-3 grid gap-5 lg:mt-6 lg:grid-cols-12 lg:gap-8">
            <header class="lg:col-span-4">
                <div class="lg:sticky lg:top-28">
                    <h1 class="site-display site-display--md">{{ $title }}</h1>
                    <p class="mt-3 text-sm text-site-muted">
                        {{ __('Por el equipo de :brand ·', ['brand' => $brand]) }}
                        <time datetime="{{ $updatedAt }}">{{ __('Revisada el :value', ['value' => \Illuminate\Support\Carbon::parse($updatedAt)->locale(app()->getLocale())->translatedFormat(\App\Support\Localization::dateFormat('j \d\e F \d\e Y'))]) }}</time>
                    </p>
                </div>
            </header>

            <article class="site-guide min-w-0 lg:col-span-7 lg:col-start-6">
                {{-- La respuesta corta: lo que un buscador o una IA puede citar sin leer el resto --}}
                <div class="site-guide__answer">
                    <p class="site-guide__answer-label">{{ __('En pocas palabras') }}</p>
                    <p>
                        {{ __('Una invitación digital es una página web del evento que se envía con un enlace por WhatsApp: fecha, lugar con mapa, itinerario, música y fotos, y cada invitado confirma su asistencia. Las más completas suman un pase con código QR para controlar la entrada. En :brand cuestan entre :value y :value2 por evento.', ['brand' => $brand, 'value' => $money($fromPrice), 'value2' => $money($toPrice)]) }}
                    </p>
                </div>

                <div class="site-folds" data-folds>
                    <details id="que-es" class="site-fold" open>
                        <summary class="site-fold__summary">
                            <h2>{{ __('¿Qué es una invitación digital?') }}</h2>
                            <x-phosphor-plus class="site-fold__sign" aria-hidden="true" />
                        </summary>
                        <div class="site-fold__body">
                            <p>{{ __('Es una página web hecha para un solo evento. No se instala nada: el invitado toca el enlace que le llegó por WhatsApp y se abre en el navegador de su celular, con los botones para llegar, agendar y confirmar.') }}</p>
                            <p>{{ __('A diferencia de una imagen o un PDF, se puede actualizar: si cambia la hora o el salón, se corrige una vez y todos ven la versión nueva con el mismo enlace.') }}</p>
                        </div>
                    </details>

                    <details id="que-incluye" class="site-fold">
                        <summary class="site-fold__summary">
                            <h2>{{ __('Qué debe incluir una invitación digital') }}</h2>
                            <x-phosphor-plus class="site-fold__sign" aria-hidden="true" />
                        </summary>
                        <div class="site-fold__body">
                            @foreach($includes as [$heading, $items])
                                <h3>{{ $heading }}</h3>
                                <ul>
                                    @foreach($items as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                            @endforeach
                        </div>
                    </details>

                    <details id="comparacion" class="site-fold">
                        <summary class="site-fold__summary">
                            <h2>{{ __('Invitación digital, impresa o imagen por WhatsApp') }}</h2>
                            <x-phosphor-plus class="site-fold__sign" aria-hidden="true" />
                        </summary>
                        <div class="site-fold__body">
                            <div class="site-compare">
                                <table>
                                    <caption class="sr-only">{{ __('Comparación entre invitación digital, impresa e imagen por WhatsApp') }}</caption>
                                    <thead>
                                        <tr>
                                            <th scope="col"><span class="sr-only">{{ __('Qué comparas') }}</span></th>
                                            @foreach($comparison['columns'] as $column)
                                                <th scope="col" @class(['is-featured' => $loop->first])>{{ $column }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($comparison['rows'] as [$what, $digital, $printed, $image])
                                            <tr>
                                                <th scope="row">{{ $what }}</th>
                                                @foreach([$digital, $printed, $image] as $has)
                                                    <td @class(['is-featured' => $loop->first])>
                                                        @if($has)
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
                            <p>{{ __('Muchas familias combinan las dos: la digital para todos y unas pocas impresas. Por eso el nivel Premium de :brand incluye una versión lista para imprimir.', ['brand' => $brand]) }}</p>
                        </div>
                    </details>

                    <details id="precios" class="site-fold">
                        <summary class="site-fold__summary">
                            <h2>{{ __('Cuánto cuesta una invitación digital') }}</h2>
                            <x-phosphor-plus class="site-fold__sign" aria-hidden="true" />
                        </summary>
                        <div class="site-fold__body">
                            <p>{{ __('En :brand es gratis con lo básico y cuesta entre :value y :value2 por evento, en un solo pago, según lo que uses. Lo que cambia el precio no es el diseño: es lo que la invitación hace por ti.', ['brand' => $brand, 'value' => $money($fromPrice), 'value2' => $money($toPrice)]) }}</p>
                            <div class="site-table site-table--fit">
                                <table>
                                    <caption class="sr-only">{{ __('Niveles de invitación digital y su precio de hoy') }}</caption>
                                    <thead>
                                        <tr>
                                            <th scope="col">{{ __('Nivel') }}</th>
                                            <th scope="col">{{ __('Precio de hoy') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($packages as $package)
                                            <tr>
                                                <th scope="row">{{ $package['name'] }}</th>
                                                <td class="tabular-nums">
                                                    {{ $money($package['final_price']) }}
                                                    @if($package['old_price'])
                                                        <s class="text-site-muted">{{ $money($package['old_price']) }}</s>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <p>
                                {{ __('Si armas varias invitaciones al año, o las vendes a tus clientes,') }} <a href="{{ lroute('diy') }}">{{ __('los planes') }}</a> {{ __('te dan un panel propio desde :value al mes, sin comisión por invitación.', ['value' => $money($fromPlan)]) }}
                                <a href="{{ lroute('pricing') }}">{{ __('Ver todos los precios') }}</a>.
                            </p>
                        </div>
                    </details>

                    <details id="entrada" class="site-fold">
                        <summary class="site-fold__summary">
                            <h2>{{ __('Confirmación de asistencia y control de entrada con QR') }}</h2>
                            <x-phosphor-plus class="site-fold__sign" aria-hidden="true" />
                        </summary>
                        <div class="site-fold__body">
                            <p>{{ __('La confirmación por invitado y el pase QR convierten la invitación en la lista del evento. Así funciona en el nivel Premium de :brand (en Estándar, el invitado confirma por WhatsApp con un mensaje que ya lleva su nombre y cuántas personas van):', ['brand' => $brand]) }}</p>
                            <h3>{{ __('Antes del evento') }}</h3>
                            <ul>
                                <li>{{ __('Cada invitado recibe su enlace, confirma cuántas personas van (nunca más de las asignadas) y recibe su pase con código QR.') }}</li>
                                <li>{{ __('Quien organiza ve en su panel quién confirmó, quién no respondió y cuántas personas van en total.') }}</li>
                            </ul>
                            <h3>{{ __('El día del evento, en la puerta') }}</h3>
                            <ul>
                                <li>{{ __('Quien recibe abre el enlace de puerta en su teléfono y escanea el pase con la cámara; si no se lee, escribe el código corto.') }}</li>
                                <li>{{ __('La pantalla muestra el nombre, cuántas personas pueden pasar y cuántas ya entraron. Un pase usado no vuelve a servir.') }}</li>
                                <li>{{ __('Un ingreso hecho por error se deshace en el momento, y el enlace de puerta se puede desactivar o cambiar.') }}</li>
                            </ul>
                        </div>
                    </details>

                    <details id="cuando" class="site-fold">
                        <summary class="site-fold__summary">
                            <h2>{{ __('Cuándo enviar la invitación digital') }}</h2>
                            <x-phosphor-plus class="site-fold__sign" aria-hidden="true" />
                        </summary>
                        <div class="site-fold__body">
                            <p>{{ __('La anticipación depende del tipo de evento. Como referencia:') }}</p>
                            <div class="site-table site-table--fit">
                                <table>
                                    <caption class="sr-only">{{ __('Cuánto antes enviar la invitación según el evento') }}</caption>
                                    <thead>
                                        <tr>
                                            <th scope="col">{{ __('Evento') }}</th>
                                            <th scope="col">{{ __('Enviarla') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($timing as [$event, $when])
                                            <tr>
                                                <th scope="row">{{ $event }}</th>
                                                <td>{{ $when }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <p>{{ __('En todos los casos, un recordatorio una semana antes con el mismo enlace ayuda a cerrar la lista.') }}</p>
                        </div>
                    </details>

                    <details id="errores" class="site-fold">
                        <summary class="site-fold__summary">
                            <h2>{{ __('Errores que conviene evitar') }}</h2>
                            <x-phosphor-plus class="site-fold__sign" aria-hidden="true" />
                        </summary>
                        <div class="site-fold__body">
                            <ul>
                                @foreach($mistakes as $mistake)
                                    <li>{{ $mistake }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </details>

                    <details id="tipos" class="site-fold">
                        <summary class="site-fold__summary">
                            <h2>{{ __('Invitaciones por tipo de evento') }}</h2>
                            <x-phosphor-plus class="site-fold__sign" aria-hidden="true" />
                        </summary>
                        <div class="site-fold__body">
                            <p>{{ __('Cada evento tiene sus propias secciones (padrinos, cortejo, promoción…). Mira los diseños y las muestras de cada uno:') }}</p>
                            <ul class="site-fold__links">
                                @foreach($landings as $landing)
                                    <li><a href="{{ $landing['url'] }}">{{ $landing['label'] }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                    </details>
                </div>

                <div class="mt-5">
                    @include('site.partials.custom-request', ['customLabel' => __('¿Quieres la tuya?')])
                </div>
            </article>
        </div>
    </main>

    @include('site.partials.faqs', ['faqs' => $faqs])

    @include('site.partials.footer', ['socials' => []])
@endsection
