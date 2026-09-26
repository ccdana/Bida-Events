{{--
    Fondo en movimiento de las plantillas de la colección «nueva». Dos capas fijas detrás del
    contenido: un brillo que se desplaza despacio (__glow) y una textura con parallax (__texture, se
    mueve dentro de su baldosa: data-parallax-loop). Encima, las partículas de cada tema con el
    parcial de siempre (partials/drift). El dibujo de cada capa vive en la hoja de su tema.
    Con movimiento reducido no se anima nada (ambient.css y themed-motion.js). Necesita $page.
--}}
@php
    // Por tema: tamaño de la baldosa de la textura (0 = sin parallax) y sus partículas
    $themedAmbient = [
        // Destellos dorados sobre el terciopelo
        'carta' => [480, [['kind' => 'star', 'count' => 14, 'mobile' => 8, 'seed' => 3, 'class' => 'inv-drift--brillo']]],
        // Pétalos que caen sobre el mapa
        'caminos' => [480, [['kind' => 'leaf', 'count' => 10, 'mobile' => 6, 'seed' => 2, 'class' => 'inv-drift--petalos']]],
        // Papelitos de celebración con los colores de la terminal
        'salidas' => [48, [['kind' => 'streamer', 'count' => 12, 'mobile' => 7, 'seed' => 5, 'class' => 'inv-drift--terminal']]],
        // Burbujas que suben por el agua
        'gota' => [0, [['kind' => 'bokeh', 'count' => 10, 'mobile' => 6, 'seed' => 4, 'class' => 'inv-drift--burbujas']]],
        // Confeti de los tres colores de los stickers
        'stickers' => [28, [['kind' => 'streamer', 'count' => 14, 'mobile' => 8, 'seed' => 1, 'class' => 'inv-drift--confeti']]],
        // Polvo suspendido en el haz de la linterna
        'expediente' => [0, [['kind' => 'bokeh', 'count' => 12, 'mobile' => 7, 'seed' => 6, 'class' => 'inv-drift--polvo']]],
    ][$page->theme] ?? [0, []];

    [$textureLoop, $themedDrifts] = $themedAmbient;
@endphp

<div class="inv-themed-bg" aria-hidden="true">
    <span class="inv-themed-bg__glow"></span>
    <span class="inv-themed-bg__texture" @if($textureLoop) data-parallax="0.12" data-parallax-loop="{{ $textureLoop }}" @endif></span>
</div>

@foreach($themedDrifts as $drift)
    @include('invitations.partials.drift', $drift)
@endforeach
