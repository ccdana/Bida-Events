{{--
    El cuaderno: tapa, una o varias hojas por cada módulo visible (en el orden de la plantilla, que
    termina en «Aventuras por vivir») y contratapa. Cada módulo tiene su vista en partials/aventura/pages/{modulo}.
    Las hojas se arman primero como texto para numerarlas y completar un número par: el libro abierto
    muestra de a dos y la contratapa tiene que caer sola.
--}}
@php
    $bookVars = \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']);
    $bookHtml = '';

    foreach ($page->order as $bookModule) {
        $bookView = 'invitations.partials.aventura.pages.'.str_replace('_', '-', $bookModule);

        if ($page->visible($bookModule) && view()->exists($bookView)) {
            $bookHtml .= view($bookView, $bookVars + ['data' => $modulos[$bookModule] ?? []])->render();
        }
    }

    // Tapa + hojas + contratapa: si queda impar, una hoja con una flor prensada antes de cerrar
    if ((substr_count($bookHtml, 'data-nb-page') + 2) % 2 === 1) {
        $bookHtml .= view('invitations.partials.aventura.pages.flor-prensada', $bookVars)->render();
    }

    $bookFolio = 0;
    $bookHtml = preg_replace_callback('/<!--nb-folio-->/', function () use (&$bookFolio) {
        return (string) ++$bookFolio;
    }, $bookHtml);
@endphp

<div class="nb-book" data-notebook data-start="{{ empty($isPreview) ? 0 : 1 }}" aria-label="Libro de aventuras">
    <div class="nb-book__pages" data-nb-pages>
        @include('invitations.partials.aventura.pages.tapa')
        {!! $bookHtml !!}
        @include('invitations.partials.aventura.pages.contratapa')
    </div>

    {{--
        Solo con JavaScript: las hojas se pasan arrastrándolas (dedo o mouse), hacia la izquierda para
        avanzar y hacia la derecha para volver; también con las flechas del teclado. Sin botones.
    --}}
    <div class="nb-controls" data-needs-js data-nb-controls>
        <p class="nb-controls__status" data-nb-status aria-live="polite">Tapa</p>
        <p class="nb-controls__hint" data-nb-hint>
            <span class="nb-hint-touch">Desliza la hoja con el dedo: a la izquierda para seguir, a la derecha para volver</span>
            <span class="nb-hint-mouse">Arrastra la hoja con el mouse (o usa las flechas ← →) para pasarla</span>
        </p>
    </div>

    {{-- Mano que enseña el gesto la primera vez; se va con la primera vuelta de hoja --}}
    <div class="nb-teach" data-needs-js data-nb-teach aria-hidden="true">
        <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 26V11a3 3 0 0 1 6 0v12m0-2a3 3 0 0 1 6 0v3m0-1a3 3 0 0 1 6 0v3m0-1a3 3 0 0 1 6 0v8c0 7-5 12-12 12h-3c-4 0-7-2-9-5l-7-10a3 3 0 0 1 5-4l2 3"/></svg>
    </div>
    <p class="nb-view-all" data-needs-js>
        <a href="?hojas=todas" data-nb-all>Ver todas las hojas</a>
        <a href="?" data-nb-book hidden>Volver al libro</a>
    </p>
</div>
