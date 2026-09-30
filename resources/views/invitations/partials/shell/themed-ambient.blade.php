{{--
    Fondo en movimiento de las plantillas de la colección «nueva». Dos capas fijas detrás del
    contenido: un brillo que se desplaza despacio (__glow) y una textura con parallax (__texture, se
    mueve dentro de su baldosa: data-parallax-loop). Encima, las partículas de cada tema con el
    parcial de siempre (partials/drift) y, si el tema la tiene, su escena de fondo propia (un parcial
    con sus piezas: la ropa que se mece, las notas que suben, los focos que barren la sala…). El
    dibujo de cada capa vive en la hoja de su tema. Con movimiento reducido no se anima nada
    (ambient.css y themed-motion.js). Necesita $page.
--}}
@php
    // Por tema: tamaño de la baldosa de la textura (0 = sin parallax), sus partículas y su escena de fondo (opcional)
    $themedAmbient = [
        // Pétalos que caen sobre el mapa
        'caminos' => [480, [['kind' => 'leaf', 'count' => 10, 'mobile' => 6, 'seed' => 2, 'class' => 'inv-drift--petalos']]],
        // Papelitos de celebración con los colores de la terminal
        'salidas' => [48, [['kind' => 'streamer', 'count' => 12, 'mobile' => 7, 'seed' => 5, 'class' => 'inv-drift--terminal']]],
        // Nubes que cruzan el cielo a distinta altura y destellos del sol
        'nubes' => [0, [['kind' => 'twinkle', 'count' => 12, 'mobile' => 7, 'seed' => 4, 'class' => 'inv-drift--cielo']], 'invitations.partials.nubes.ambient'],
        // Birretes que siguen en el aire dando tumbos y papelitos dorados de la promoción
        'birrete' => [0, [['kind' => 'streamer', 'count' => 12, 'mobile' => 7, 'seed' => 3, 'class' => 'inv-drift--promocion']], 'invitations.partials.birrete.ambient'],
        // Burbujas que suben por el agua (la luz del agua en el piso la dibuja themes/gota.css)
        'gota' => [0, [['kind' => 'bokeh', 'count' => 10, 'mobile' => 6, 'seed' => 4, 'class' => 'inv-drift--burbujas']]],
        // Confeti de los tres colores de los stickers
        'stickers' => [28, [['kind' => 'streamer', 'count' => 14, 'mobile' => 8, 'seed' => 1, 'class' => 'inv-drift--confeti']]],
        // Polvo suspendido en el haz de la linterna
        'expediente' => [0, [['kind' => 'bokeh', 'count' => 12, 'mobile' => 7, 'seed' => 6, 'class' => 'inv-drift--polvo']]],
        // Hilos sueltos del taller y, a los costados, las cintas métricas que cuelgan
        'atelier' => [0, [['kind' => 'streamer', 'count' => 8, 'mobile' => 5, 'seed' => 4, 'class' => 'inv-drift--hilo']], 'invitations.partials.atelier.ambient'],
        // ── Colección «tendencias» ──
        // Estrellitas de papel recortado y, a los costados, el canto de las páginas del libro
        'cuento' => [0, [['kind' => 'star', 'count' => 10, 'mobile' => 6, 'seed' => 5, 'class' => 'inv-drift--papel']], 'invitations.partials.tendencias.cuento.ambient'],
        // Un mandala enorme que gira despacio y destellos de dispersión
        'caleidoscopio' => [0, [['kind' => 'twinkle', 'count' => 12, 'mobile' => 7, 'seed' => 3, 'class' => 'inv-drift--prisma']], 'invitations.partials.tendencias.caleidoscopio.ambient'],
        // El capitoné del forro con sus botones que destellan y chispas de oro
        'joyero' => [0, [['kind' => 'twinkle', 'count' => 10, 'mobile' => 6, 'seed' => 5, 'class' => 'inv-drift--joya']], 'invitations.partials.tendencias.joyero.ambient'],
        // La bruma nacarada del atomizador y luces difusas
        'esencia' => [0, [['kind' => 'bokeh', 'count' => 10, 'mobile' => 6, 'seed' => 8, 'class' => 'inv-drift--bruma']], 'invitations.partials.tendencias.esencia.ambient'],
        // Notas que suben por pentagramas que ondulan y el brillo de las luces de la sala
        'partitura' => [0, [['kind' => 'twinkle', 'count' => 10, 'mobile' => 6, 'seed' => 7, 'class' => 'inv-drift--sala']], 'invitations.partials.tendencias.partitura.ambient'],
        // La luz de las velas de la mesa, que tiembla a los dos lados, y su resplandor
        'mesa' => [0, [['kind' => 'bokeh', 'count' => 10, 'mobile' => 6, 'seed' => 9, 'class' => 'inv-drift--vela']], 'invitations.partials.tendencias.mesa.ambient'],
        // Engranajes de latón que giran despacio en los bordes, en espejo, y destellos del metal
        'reloj' => [0, [['kind' => 'twinkle', 'count' => 8, 'mobile' => 5, 'seed' => 6, 'class' => 'inv-drift--laton']], 'invitations.partials.tendencias.reloj.ambient'],
        // Estrellitas quietas del cuarto del bebé
        'movil' => [0, [['kind' => 'twinkle', 'count' => 12, 'mobile' => 7, 'seed' => 4, 'class' => 'inv-drift--cuarto']]],
        // El cielo de la noche que gira despacio y las estrellas fugaces que lo cruzan
        'estrellas' => [0, [['kind' => 'twinkle', 'count' => 8, 'mobile' => 5, 'seed' => 8, 'class' => 'inv-drift--noche']], 'invitations.partials.tendencias.estrellas.ambient'],
        // El pespunte de los bordes que sigue avanzando y botones de costura que bajan girando
        'bordado' => [0, [], 'invitations.partials.tendencias.bordado.ambient'],
        // Papelitos del final del show
        'gira' => [0, [['kind' => 'streamer', 'count' => 12, 'mobile' => 7, 'seed' => 6, 'class' => 'inv-drift--show']]],
        // Hojas arrancadas del almanaque que bajan dando vueltas a los costados, en espejo, y papelitos
        'feriado' => [0, [['kind' => 'streamer', 'count' => 8, 'mobile' => 5, 'seed' => 8, 'class' => 'inv-drift--papelitos']], 'invitations.partials.tendencias.feriado.ambient'],
        // El resplandor de los flashes que se disparan despacio a los costados y el brillo que queda en el aire
        'cabina' => [0, [['kind' => 'bokeh', 'count' => 10, 'mobile' => 6, 'seed' => 5, 'class' => 'inv-drift--flash']], 'invitations.partials.tendencias.cabina.ambient'],
        // Recortes de revista con las letras del nombre, que caen despacio
        'revista' => [0, [], 'invitations.partials.tendencias.revista.ambient'],
        // Polvo en el haz del proyector
        'funcion' => [0, [['kind' => 'bokeh', 'count' => 12, 'mobile' => 7, 'seed' => 2, 'class' => 'inv-drift--proyector']]],
        // Bruma de colores por el piso del laboratorio, burbujas que suben y lucecitas que flotan
        'caldero' => [0, [['kind' => 'twinkle', 'count' => 10, 'mobile' => 6, 'seed' => 9, 'class' => 'inv-drift--luces']], 'invitations.partials.tendencias.caldero.ambient'],
        // Tendederos lejanos con ropita que se mece y burbujas de jabón que suben
        'tendedero' => [0, [], 'invitations.partials.tendencias.tendedero.ambient'],
        // Bloquecitos con letras que caen dando vueltas
        'bloques' => [0, [['kind' => 'streamer', 'count' => 8, 'mobile' => 5, 'seed' => 7, 'class' => 'inv-drift--juego']], 'invitations.partials.tendencias.bloques.ambient'],
        // Estampillas y cajitas que bajan despacio, y papel picado de seda
        'encomienda' => [0, [['kind' => 'streamer', 'count' => 8, 'mobile' => 5, 'seed' => 3, 'class' => 'inv-drift--seda']], 'invitations.partials.tendencias.encomienda.ambient'],
    ][$page->theme] ?? [0, []];

    [$textureLoop, $themedDrifts, $themedScene] = $themedAmbient + [2 => null];
@endphp

<div class="inv-themed-bg" aria-hidden="true">
    <span class="inv-themed-bg__glow"></span>
    <span class="inv-themed-bg__texture" @if($textureLoop) data-parallax="0.12" data-parallax-loop="{{ $textureLoop }}" @endif></span>
</div>

@if($themedScene)
    @include($themedScene)
@endif

@foreach($themedDrifts as $drift)
    @include('invitations.partials.drift', $drift)
@endforeach
