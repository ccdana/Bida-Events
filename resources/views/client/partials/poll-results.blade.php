{{--
    Resultados de las encuestas de la invitación: cuántos votos lleva cada opción, con su línea fina. Los ve solo el
    cliente. Una pregunta por bloque, separadas por filetes (sin tarjetas). Recibe $polls (ver InvitationDetailViewData::polls).
--}}
<section class="grid grid-cols-[minmax(0,1fr)] gap-6" aria-labelledby="resultados-encuestas">
    <h2 id="resultados-encuestas" class="sr-only">Resultados de las encuestas</h2>

    @foreach($polls as $poll)
        <article class="eg-poll">
            <h3 class="eg-poll__question">{{ $poll['question'] }}</h3>
            <p class="ev-meta">
                <span>{{ $poll['total'] === 0 ? 'Todavía sin votos' : $poll['total'].' '.($poll['total'] === 1 ? 'voto' : 'votos') }}</span>
            </p>

            <ol class="eg-poll__options">
                @foreach($poll['options'] as $option)
                    <li>
                        <div class="flex items-baseline justify-between gap-3 text-sm">
                            <span class="min-w-0 break-words {{ $option['leads'] ? 'font-semibold' : '' }}">{{ $option['label'] }}</span>
                            <span class="shrink-0 tabular-nums text-site-muted">
                                <span class="font-medium text-site-ink">{{ $option['votes'] }}</span> · {{ $option['percent'] }}%
                            </span>
                        </div>
                        <div class="ev-bar mt-1.5" role="presentation">
                            <i style="width: {{ $option['percent'] }}%" @class(['opacity-40' => ! $option['leads']])></i>
                        </div>
                    </li>
                @endforeach
            </ol>
        </article>
    @endforeach
</section>
