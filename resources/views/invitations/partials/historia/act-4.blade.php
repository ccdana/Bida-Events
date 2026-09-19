{{--
    Acto IV · La constelación: el cielo a lo Van Gogh. Cómo cambió su vida, la promesa, cuánto llevan
    escribiendo la historia, la fecha de la celebración y la respuesta de quien la lee (ReplyModule,
    con el parcial común modules/respuesta).
    Datos: historia (reflexion, promesa), juntos_desde (fecha), respuesta.
--}}
@php
    $reflection = $filled($story['reflexion'] ?? null) ?? $invCopy['act4_reflection_fallback'];
    $promise = $filled($story['promesa'] ?? null) ?? $invCopy['act4_promise_fallback'];
    $replyData = (array) ($modulos['respuesta'] ?? []);
    $replyData['titulo'] = $filled($replyData['titulo'] ?? null) ?? $invCopy['reply_title'];
    $replyData['descripcion'] = $filled($replyData['descripcion'] ?? null) ?? $invCopy['reply_intro'];
@endphp

<section class="story-act story-act--4" data-act data-act-finale>
    <div class="story-act__body">
        @include('invitations.partials.historia.mark', ['number' => 'IV', 'name' => $invCopy['act4_label']])

        <h2 class="story-title reveal">{{ $invCopy['act4_title'] }}</h2>

        <div class="story-copy story-copy--prose reveal">
            @foreach($paragraphs($reflection) as $paragraph)
                <p>{!! nl2br(e($paragraph)) !!}</p>
            @endforeach
        </div>

        <p class="story-promise reveal">{{ $promise }}</p>

        @if($together)
            <p class="story-together reveal">
                {{ $invCopy['act4_together'] }}
                <span class="story-together__span">{{ $together }}</span>
            </p>
        @endif
    </div>

    @if($page->visible('respuesta'))
        <div class="story-reply">
            @include('invitations.partials.modules.respuesta', ['data' => $replyData])
        </div>
    @endif
</section>
