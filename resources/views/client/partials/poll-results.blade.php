{{--
    Resultados de las encuestas de la invitación: cuántos votos lleva cada opción. Los ve solo el cliente.
    Recibe $polls (ver InvitationDetailViewData::polls).
--}}
<section class="site-enter mt-10" style="--enter-index: 3" aria-labelledby="resultados-encuestas">
    <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-2 border-b border-site-line pb-3">
        <h2 id="resultados-encuestas" class="text-xl font-semibold tracking-tight">Resultados de las encuestas</h2>
        <p class="text-sm text-site-muted">
            {{ $polls->count() }} {{ $polls->count() === 1 ? 'pregunta' : 'preguntas' }}. Se actualizan con cada voto.
        </p>
    </div>

    <div class="mt-5 grid gap-4 md:grid-cols-2">
        @foreach($polls as $poll)
            <article class="rounded-[16px] border border-site-line bg-site-surface p-5">
                <h3 class="font-semibold leading-snug">{{ $poll['question'] }}</h3>
                <p class="mt-1 text-sm text-site-muted">
                    {{ $poll['total'] === 0 ? 'Todavía sin votos' : $poll['total'].' '.($poll['total'] === 1 ? 'voto' : 'votos') }}
                </p>

                <ol class="mt-4 space-y-3">
                    @foreach($poll['options'] as $option)
                        <li>
                            <div class="flex items-baseline justify-between gap-3 text-sm">
                                <span class="min-w-0 break-words {{ $option['leads'] ? 'font-semibold' : '' }}">{{ $option['label'] }}</span>
                                <span class="shrink-0 tabular-nums text-site-muted">
                                    <span class="font-medium text-site-ink">{{ $option['votes'] }}</span> · {{ $option['percent'] }}%
                                </span>
                            </div>
                            <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-site-tint" role="presentation">
                                <div class="h-full rounded-full {{ $option['leads'] ? 'bg-site-accent' : 'bg-site-line' }}" style="width: {{ $option['percent'] }}%"></div>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </article>
        @endforeach
    </div>
</section>
