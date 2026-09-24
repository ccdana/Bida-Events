@extends('layouts.site')

{{--
    Guía «Invitaciones digitales»: la página de referencia del sitio para Google y para las IA que
    responden preguntas (ChatGPT, Gemini, Perplexity, AI Overviews). Ver docs/geo-estrategia.md.

    Cómo está escrita: cada sección abre con la respuesta directa en una o dos frases (lo que una IA
    cita), después el detalle en listas o tablas, y los datos que solo tenemos nosotros (cómo funciona
    la puerta, qué incluye cada paquete, cuándo conviene enviarla). Los precios salen de la
    configuración de hoy; nada de estadísticas inventadas. Ver PublicPagesController::guide.
--}}
@php
    $brand = $bida['brand'];
    $money = fn ($amount) => \App\Support\Money::format($amount);
    $fromPrice = collect($packages)->min('final_price');
    $toPrice = collect($packages)->max('final_price');
    $fromPlan = collect($plans)->min('final_price');
    $accountUrl = $user ? route('dashboard') : route('login');
    $accountLabel = $user ? 'Mi panel' : 'Ingresar';
    $title = 'Invitaciones digitales: qué son, cuánto cuestan y cómo elegir la tuya';
    $description = 'Qué debe incluir una invitación digital, cuánto cuesta (desde '.$money($fromPrice).'), cuándo enviarla por WhatsApp y cómo funciona la confirmación de asistencia y el control de entrada con código QR.';

    $sections = [
        'que-es' => '¿Qué es una invitación digital?',
        'que-incluye' => 'Qué debe incluir',
        'comparacion' => 'Digital, impresa o imagen por WhatsApp',
        'precios' => 'Cuánto cuesta',
        'entrada' => 'Confirmación y control de entrada con QR',
        'cuando' => 'Cuándo enviarla',
        'errores' => 'Errores que conviene evitar',
        'preguntas' => 'Preguntas frecuentes',
    ];

    $faqs = [
        ['¿Qué es una invitación digital?', 'Es una página web del evento que se comparte con un enlace, normalmente por WhatsApp. Reúne la fecha, el lugar con mapa, el itinerario, la música, las fotos y un botón para confirmar asistencia, y se ve bien en cualquier celular sin instalar nada.'],
        ['¿Cuánto cuesta una invitación digital?', 'En '.$brand.' los paquetes van de '.$money($fromPrice).' a '.$money($toPrice).' por evento, pago único. El precio cambia según lo que incluye: en Estándar se confirma por WhatsApp; en Premium, con pase QR, control de entrada, panel de invitados y reportes.'],
        ['¿Con cuánta anticipación se envía una invitación digital?', 'Para bodas, entre seis y ocho semanas antes; para XV años, entre cuatro y seis; para bautizos, graduaciones y cumpleaños, entre dos y cuatro semanas. Conviene mandar un recordatorio una semana antes: con una invitación digital se reenvía el mismo enlace.'],
        ['¿Cómo confirman asistencia los invitados?', 'Cada invitado recibe su propio enlace, elige si va y cuántas personas de las que tiene asignadas lo acompañan. Quien organiza ve las respuestas al momento en su panel y puede descargarlas en PDF o Excel.'],
        ['¿Cómo funciona el control de entrada con código QR?', 'Al confirmar, cada invitado recibe un pase con código QR. El día del evento, quien recibe en la puerta abre un enlace en su teléfono y escanea el pase: ve el nombre, cuántas personas pueden entrar y si el pase ya se usó. Un pase no sirve dos veces.'],
        ['¿Las invitaciones digitales aparecen en Google?', 'No deberían. Las de '.$brand.' están marcadas para que los buscadores no las indexen: solo las abre quien tiene el enlace, y los datos de los invitados solo los ve quien organiza.'],
        ['¿Puedo hacer mis propias invitaciones digitales?', 'Sí. Con Hazlo tú tienes un panel propio con las mismas plantillas y el mismo editor, desde '.$money($fromPlan).' al mes y sin comisión por invitación. Sirve para familias con varios eventos y para fotógrafos, salones u organizadores.'],
    ];

    $includes = [
        ['Lo que el invitado necesita saber', ['Fecha y hora, con cuenta regresiva y botón para agregarla al calendario.', 'Lugar con mapa y un botón «Cómo llegar» que abre Google Maps.', 'Itinerario: ceremonia, recepción, fiesta.', 'Código de vestimenta y, si hay, mesa de regalos o cuenta para aportes.']],
        ['Lo que hace que se sienta del evento', ['Portada con las fotos de la familia o de la pareja y una apertura animada.', 'Música de fondo que el invitado puede pausar.', 'Colores y tipografías propias; en '.$brand.' también se cambia cada texto.']],
        ['Lo que le sirve a quien organiza', ['Confirmación de asistencia por invitado, con la cantidad de personas asignadas a cada uno (por WhatsApp o guardada en un panel).', 'Pase con código QR y control de entrada el día del evento.', 'Lista de invitados en PDF o Excel para el salón y el catering.']],
    ];

    $comparison = [
        ['Qué comparas', 'Invitación digital (página web)', 'Invitación impresa', 'Imagen o PDF por WhatsApp'],
        ['Llega a los invitados', 'Al instante, por WhatsApp o redes', 'En mano o por correo; toma días', 'Al instante'],
        ['Mapa y cómo llegar', 'Sí, con un toque', 'Solo la dirección escrita', 'Solo la dirección o un enlace aparte'],
        ['Confirmación de asistencia', 'Por invitado, con la cantidad de personas', 'Por llamada o mensaje', 'Por mensaje, sin orden'],
        ['Cambios de último momento', 'Se corrigen y todos ven la versión nueva', 'Hay que volver a imprimir', 'Hay que reenviar la imagen'],
        ['Control en la puerta', 'Pase QR que no se puede usar dos veces', 'Lista en papel', 'Lista en papel'],
        ['Recuerdo físico', 'Versión para imprimir incluida', 'Sí', 'No'],
    ];

    $timing = [
        ['Boda', 'Entre 6 y 8 semanas antes', 'Hay invitados que viajan y el salón necesita el número final con tiempo.'],
        ['XV años', 'Entre 4 y 6 semanas antes', 'Suele haber padrinos y cortejo que coordinar antes de la fiesta.'],
        ['Bautizo', 'Entre 3 y 4 semanas antes', 'La lista es más corta y casi siempre familiar.'],
        ['Graduación', 'Entre 3 y 4 semanas antes', 'La fecha del acto la fija el colegio o la universidad; conviene esperar a tenerla confirmada.'],
        ['Cumpleaños', 'Entre 2 y 3 semanas antes', 'Basta con que los invitados puedan reservar la fecha.'],
    ];

    $mistakes = [
        'Mandar la invitación sin el mapa: la pregunta que más se repite el día del evento es «¿cómo llego?».',
        'Pedir que confirmen «por mensaje»: las respuestas se pierden en el chat. Un botón de confirmación por invitado las ordena solo.',
        'No decir cuántas personas incluye cada invitación: sin ese dato, la lista del salón nunca cierra.',
        'Usar fotos pesadas o de baja calidad: la portada es lo primero que se ve y tiene que cargar rápido en datos móviles.',
        'Olvidar el recordatorio: una semana antes, reenviar el mismo enlace sube las confirmaciones sin costo.',
        'Dejar la puerta sin control: si el evento tiene cupo, un pase QR por invitado evita que entre más gente de la prevista.',
    ];
@endphp

@section('title', $title.' | '.$brand)
@section('description', $description)

@push('head')
    @include('site.partials.structured-data', [
        'faqs' => $faqs,
        'breadcrumbs' => [[$brand, route('home')], ['Guía de invitaciones digitales', route('guide')]],
        'article' => [
            'headline' => $title,
            'description' => $description,
            'url' => route('guide'),
            'published' => $updatedAt,
            'modified' => $updatedAt,
        ],
    ])
@endpush

@section('content')
    <div data-header-sentinel class="pointer-events-none absolute inset-x-0 top-0 h-4" aria-hidden="true"></div>

    @include('site.partials.header', ['navLinks' => [route('home') => 'Inicio', route('diy') => 'Hazlo tú', '#preguntas' => 'Preguntas']])

    <main class="mx-auto max-w-7xl px-5 pb-24 pt-10 lg:px-8 lg:pt-16">
        <nav class="text-sm text-site-muted" aria-label="Ruta">
            <a href="{{ route('home') }}" class="site-nav-link hover:text-site-ink">{{ $brand }}</a>
            <span class="mx-2" aria-hidden="true">/</span>
            <span class="text-site-ink">Guía de invitaciones digitales</span>
        </nav>

        <div class="mt-8 grid gap-12 lg:grid-cols-12 lg:gap-8">
            <header class="lg:col-span-4">
                <div class="lg:sticky lg:top-28">
                    <p class="site-overline">Guía</p>
                    <h1 class="site-display site-display--md mt-3">{{ $title }}</h1>
                    <p class="mt-4 text-sm text-site-muted">
                        Por el equipo de {{ $brand }} ·
                        <time datetime="{{ $updatedAt }}">Revisada el {{ \Illuminate\Support\Carbon::parse($updatedAt)->locale('es')->translatedFormat('j \d\e F \d\e Y') }}</time>
                    </p>

                    <nav class="site-legal__toc mt-8 hidden lg:block" aria-label="En esta guía">
                        <ul>
                            @foreach($sections as $id => $label)
                                <li><a href="#{{ $id }}">{{ $label }}</a></li>
                            @endforeach
                        </ul>
                    </nav>
                </div>
            </header>

            <article class="site-legal site-guide min-w-0 lg:col-span-7 lg:col-start-6">
                {{-- La respuesta corta: lo que un buscador o una IA puede citar sin leer el resto --}}
                <div class="site-guide__answer">
                    <p class="site-guide__answer-label">En pocas palabras</p>
                    <p>
                        Una invitación digital es una página web del evento que se envía con un enlace por WhatsApp. Reúne fecha,
                        lugar con mapa, itinerario, música y fotos, y permite que cada invitado confirme su asistencia. Las más
                        completas agregan un pase con código QR para controlar la entrada. En {{ $brand }} cuestan entre
                        {{ $money($fromPrice) }} y {{ $money($toPrice) }} por evento.
                    </p>
                </div>

                <p class="site-legal__intro">
                    Elegir una invitación digital ya no es solo elegir un diseño bonito. La diferencia real está en lo que pasa
                    después de enviarla: si los invitados encuentran el lugar sin preguntar, si las confirmaciones llegan ordenadas
                    y si el día del evento la puerta sabe quién puede entrar. Esta guía explica qué mirar, cuánto cuesta y cómo
                    se usa, con lo que vemos en cada evento que armamos.
                </p>

                <section id="que-es" class="site-legal__section">
                    <h2>¿Qué es una invitación digital?</h2>
                    <p>
                        Es una página web hecha para un solo evento. No se instala nada: el invitado toca el enlace que le llegó por
                        WhatsApp y la invitación se abre en el navegador de su celular, con la información del evento y los botones
                        para llegar, agendar y confirmar.
                    </p>
                    <p>
                        A diferencia de una imagen o un PDF, una invitación digital se puede actualizar: si cambia la hora o el salón,
                        se corrige una vez y todos ven la versión nueva con el mismo enlace.
                    </p>
                    <blockquote class="site-guide__quote">
                        <p>«La pregunta que más recibe una familia el día del evento es cómo llegar. Si la invitación trae el mapa y el botón para abrirlo, esa pregunta desaparece.»</p>
                        <footer>Equipo de {{ $brand }}</footer>
                    </blockquote>
                </section>

                <section id="que-incluye" class="site-legal__section">
                    <h2>Qué debe incluir una invitación digital</h2>
                    <p>Una invitación digital completa resuelve tres cosas: informa al invitado, transmite el estilo del evento y ordena el trabajo de quien organiza.</p>
                    @foreach($includes as [$heading, $items])
                        <h3>{{ $heading }}</h3>
                        <ul>
                            @foreach($items as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    @endforeach
                </section>

                <section id="comparacion" class="site-legal__section">
                    <h2>Invitación digital, impresa o imagen por WhatsApp</h2>
                    <p>Las tres llegan al invitado, pero no hacen lo mismo. Esta es la diferencia práctica:</p>
                    <div class="site-table">
                        <table>
                            <caption class="sr-only">Comparación entre invitación digital, impresa e imagen por WhatsApp</caption>
                            <thead>
                                <tr>
                                    @foreach($comparison[0] as $heading)
                                        <th scope="col">{{ $heading }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(array_slice($comparison, 1) as $row)
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
                    <p>Muchas familias combinan las dos: la digital para todos y unas pocas impresas para los abuelos. Por eso el paquete Premium de {{ $brand }} incluye una versión lista para imprimir.</p>
                </section>

                <section id="precios" class="site-legal__section">
                    <h2>Cuánto cuesta una invitación digital</h2>
                    <p>
                        Una invitación digital armada por {{ $brand }} cuesta entre {{ $money($fromPrice) }} y {{ $money($toPrice) }}
                        por evento, en un solo pago. Lo que cambia el precio no es el diseño: es lo que la invitación hace por ti.
                    </p>
                    <div class="site-table">
                        <table>
                            <caption class="sr-only">Paquetes de invitación digital y su precio de hoy</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Paquete</th>
                                    <th scope="col">Precio de hoy</th>
                                    <th scope="col">Para quién</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($packages as $package)
                                    <tr>
                                        <th scope="row">{{ $package['name'] }}</th>
                                        <td class="whitespace-nowrap tabular-nums">
                                            {{ $money($package['final_price']) }}
                                            @if($package['old_price'])
                                                <s class="text-site-muted">{{ $money($package['old_price']) }}</s>
                                            @endif
                                        </td>
                                        <td>{{ $package['summary'] ?? '' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p>
                        Si armas varias invitaciones al año, o las vendes a tus clientes, <a href="{{ route('diy') }}">Hazlo tú</a> te da
                        un panel propio desde {{ $money($fromPlan) }} al mes, sin comisión por invitación.
                    </p>
                </section>

                <section id="entrada" class="site-legal__section">
                    <h2>Confirmación de asistencia y control de entrada con QR</h2>
                    <p>
                        La confirmación por invitado y el pase QR convierten la invitación en la lista de invitados del evento. Así
                        funciona en el paquete Premium de {{ $brand }} (en Estándar, el invitado confirma por WhatsApp con un mensaje
                        que ya lleva su nombre y cuántas personas van):
                    </p>
                    <h3>Antes del evento</h3>
                    <ul>
                        <li>Cada invitado recibe su propio enlace, con la cantidad de personas que incluye su invitación.</li>
                        <li>Al confirmar, elige cuántas personas van (nunca más de las asignadas) y recibe su pase con un código QR y un código corto.</li>
                        <li>Quien organiza ve en su panel quién confirmó, quién no respondió y cuántas personas van en total.</li>
                    </ul>
                    <h3>El día del evento, en la puerta</h3>
                    <ul>
                        <li>Quien organiza activa el control de entrada y comparte un enlace de puerta con el portero o la persona que recibe.</li>
                        <li>Esa persona abre el enlace en su teléfono y escanea el pase con la cámara; si el pase no se lee, escribe el código corto.</li>
                        <li>La pantalla muestra el nombre, cuántas personas pueden pasar y cuántas ya entraron. Un pase usado no vuelve a servir.</li>
                        <li>Si se registró un ingreso por error, se deshace en el momento, y el enlace de puerta se puede desactivar o cambiar cuando haga falta.</li>
                    </ul>
                    <blockquote class="site-guide__quote">
                        <p>«El control de entrada no es para desconfiar de los invitados: es para que el salón no reciba a más personas de las que contrataste y la fiesta empiece a tiempo.»</p>
                        <footer>Equipo de {{ $brand }}</footer>
                    </blockquote>
                </section>

                <section id="cuando" class="site-legal__section">
                    <h2>Cuándo enviar la invitación digital</h2>
                    <p>La anticipación depende del tipo de evento. Como referencia:</p>
                    <div class="site-table">
                        <table>
                            <caption class="sr-only">Cuánto antes enviar la invitación según el evento</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Evento</th>
                                    <th scope="col">Enviarla</th>
                                    <th scope="col">Por qué</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($timing as [$event, $when, $why])
                                    <tr>
                                        <th scope="row">{{ $event }}</th>
                                        <td class="whitespace-nowrap">{{ $when }}</td>
                                        <td>{{ $why }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p>En todos los casos, un recordatorio una semana antes con el mismo enlace ayuda a cerrar la lista.</p>
                </section>

                <section id="errores" class="site-legal__section">
                    <h2>Errores que conviene evitar</h2>
                    <ul>
                        @foreach($mistakes as $mistake)
                            <li>{{ $mistake }}</li>
                        @endforeach
                    </ul>
                </section>

                <section class="site-legal__section">
                    <h2>Invitaciones por tipo de evento</h2>
                    <p>Cada evento tiene sus propias secciones (padrinos, cortejo, promoción…). Mira los diseños y las muestras de cada uno:</p>
                    <ul>
                        @foreach($landings as $landing)
                            <li><a href="{{ $landing['url'] }}">{{ $landing['label'] }}</a></li>
                        @endforeach
                    </ul>
                </section>

                <aside class="site-legal__more">
                    <p>¿Quieres la tuya? <a href="{{ $contactUrl }}" target="_blank" rel="noopener">Escríbenos por WhatsApp</a> y te mostramos los diseños.</p>
                </aside>
            </article>
        </div>
    </main>

    @include('site.partials.faqs', ['faqs' => $faqs])

    @include('site.partials.footer', ['socials' => []])
@endsection
