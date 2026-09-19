{{-- Hoja de relleno (el libro necesita un número par de hojas): una flor prensada con su etiqueta. --}}
<article class="nb-page nb-page--kraft nb-pressed" data-nb-page>
    <div class="nb-page__inner">
        <figure class="nb-pressed__flower">
            <span class="nb-tape nb-tape--a" aria-hidden="true"></span>
            @include('invitations.partials.aventura.flower', ['kind' => 'girasol'])
            <figcaption class="nb-hand">Flor amarilla, para ti</figcaption>
        </figure>
    </div>
    <span class="nb-folio"><!--nb-folio--></span>
</article>
