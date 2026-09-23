{{--
    Respuestas a una tarjeta: lo que escribió quien la recibió y con qué flor respondió.
    Se leen completas, no en una línea. Recibe $invitation y $replies.
--}}
<section class="site-enter mt-10" style="--enter-index: 3">
    <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-2 border-b border-site-line pb-3">
        <h2 class="text-xl font-semibold tracking-tight">Respuestas a tu tarjeta</h2>
        <p class="text-sm text-site-muted">
            {{ $replies->count() }} {{ $replies->count() === 1 ? 'respuesta' : 'respuestas' }}. Solo las ves tú.
        </p>
    </div>

    @if($replies->isEmpty())
        <div class="mt-5 rounded-[16px] border border-dashed border-site-line px-6 py-12 text-center">
            <x-phosphor-envelope-simple-light class="mx-auto size-12 text-site-accent" aria-hidden="true" />
            <p class="mt-4 font-medium">Todavía no hay respuestas</p>
            <p class="mt-1 text-site-muted">Cuando abran tu tarjeta y te contesten, lo verás aquí.</p>
        </div>
    @else
        <div class="mt-5 grid gap-4 md:grid-cols-2">
            @foreach($replies as $reply)
                <article class="flex flex-col rounded-[16px] border border-site-line bg-site-surface p-5 {{ $reply['isHidden'] ? 'opacity-60' : '' }}">
                    @if($reply['reaction'])
                        <p class="flex flex-wrap items-center gap-x-2 text-sm font-medium text-site-accent">
                            <x-phosphor-flower-tulip class="size-5" aria-hidden="true" />
                            Te respondió con {{ mb_strtolower($reply['reaction']['label']) }}
                            <span class="font-normal text-site-muted">· {{ $reply['reaction']['meaning'] }}</span>
                        </p>
                    @endif

                    @if($reply['text'] !== '')
                        <blockquote class="mt-3 whitespace-pre-line text-[1.05rem] leading-relaxed">{{ $reply['text'] }}</blockquote>
                    @endif

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-site-line pt-3 text-sm text-site-muted">
                        <p>
                            {{ $reply['author'] ?: 'Quien recibió tu tarjeta' }}
                            @if($reply['date'])
                                · {{ $reply['date'] }}
                            @endif
                            @if($reply['isHidden'])
                                <span class="font-medium text-site-ink">· Oculta</span>
                            @endif
                        </p>
                        <form method="POST" action="{{ route('client.contributions.update', [$invitation, $reply['id']]) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="moderation_status" value="{{ $reply['isHidden'] ? 'visible' : 'hidden' }}">
                            <button type="submit" class="admin-link-button">{{ $reply['isHidden'] ? 'Mostrar' : 'Ocultar' }}</button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</section>
