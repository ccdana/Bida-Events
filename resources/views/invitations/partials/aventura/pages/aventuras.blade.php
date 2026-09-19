{{--
    «Aventuras por vivir» (App\Modules\Card\AdventuresModule): lo que todavía quieren hacer juntos,
    cada aventura en un renglón con su casilla. Si son muchas, siguen en otra hoja. Sin aventuras no
    hay hoja. Recibe $data (titulo, lista: [{titulo}]).
--}}
@php
    $adventures = array_values(array_filter(
        array_map(fn ($item) => is_array($item) ? trim((string) ($item['titulo'] ?? '')) : '', (array) ($data['lista'] ?? [])),
        fn (string $text) => $text !== '',
    ));
@endphp

@foreach(array_chunk($adventures, 9) as $adventurePage => $adventureItems)
    <article class="nb-page nb-page--paper nb-todo" data-nb-page @if($adventurePage === 0) id="aventuras" @endif>
        <div class="nb-page__inner">
            @if($adventurePage === 0)
                <p class="nb-eyebrow">Próximamente</p>
                <h2 class="nb-title">{{ ($data['titulo'] ?? null) ?: 'Aventuras por vivir' }}</h2>
            @else
                <p class="nb-continued">Aventuras por vivir · continúa</p>
            @endif

            <ul class="nb-todo__list">
                @foreach($adventureItems as $adventure)
                    <li>
                        <span class="nb-todo__box" aria-hidden="true"></span>
                        <span class="nb-todo__text">{{ $adventure }}</span>
                    </li>
                @endforeach
            </ul>

            @include('invitations.partials.aventura.flower', ['kind' => 'ramita', 'class' => 'nb-corner nb-corner--br'])
        </div>
        <span class="nb-folio"><!--nb-folio--></span>
    </article>
@endforeach
