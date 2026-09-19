{{--
    El cuaderno: tapa, una o varias hojas por cada módulo visible (en el orden de la plantilla),
    «Aventuras por vivir» y contratapa. Cada módulo tiene su vista en partials/aventura/pages/{modulo}.
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

    $bookHtml .= view('invitations.partials.aventura.pages.pendientes', $bookVars)->render();

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

    {{-- Solo con JavaScript: pasar hojas con botones y teclado, o verlas todas apiladas --}}
    <nav class="nb-controls" data-needs-js data-nb-controls aria-label="Pasar las hojas">
        <button type="button" class="nb-controls__btn" data-nb-prev aria-label="Hoja anterior">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 5l-7 7 7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <p class="nb-controls__status" data-nb-status aria-live="polite">Tapa</p>
        <button type="button" class="nb-controls__btn" data-nb-next aria-label="Hoja siguiente">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
    </nav>
    <p class="nb-view-all" data-needs-js>
        <a href="?hojas=todas" data-nb-all>Ver todas las hojas</a>
        <a href="?" data-nb-book hidden>Volver al libro</a>
    </p>
</div>
