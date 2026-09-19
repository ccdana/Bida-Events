{{-- Última hoja: «Aventuras por vivir», renglones en blanco para lo que todavía les falta hacer juntos. --}}
<article class="nb-page nb-page--paper nb-todo" data-nb-page>
    <div class="nb-page__inner">
        <p class="nb-eyebrow">Próximamente</p>
        <h2 class="nb-title">Aventuras por vivir</h2>
        <ul class="nb-todo__list" aria-label="Renglones en blanco para nuevas aventuras">
            @for($line = 0; $line < 7; $line++)
                <li><span class="nb-todo__box" aria-hidden="true"></span></li>
            @endfor
        </ul>
        @include('invitations.partials.aventura.flower', ['kind' => 'ramita', 'class' => 'nb-corner nb-corner--br'])
    </div>
    <span class="nb-folio"><!--nb-folio--></span>
</article>
