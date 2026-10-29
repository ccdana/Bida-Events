{{--
    Respuestas a una tarjeta: lo que escribió quien la recibió y con qué flor respondió. Se leen completas, en filas con
    filete (sin tarjetas); cada una se puede ocultar. Recibe $invitation y $replies.
--}}
@include('client.partials.moderation-script')

<section class="grid grid-cols-[minmax(0,1fr)] gap-4" aria-labelledby="respuestas-tarjeta">
    <h2 id="respuestas-tarjeta" class="sr-only">Respuestas a tu tarjeta</h2>

    @if($replies->isEmpty())
        <div class="ev-empty">
            <x-phosphor-envelope-simple-light class="ev-empty__icon" aria-hidden="true" />
            <h3 class="ev-empty__title">Todavía no hay respuestas</h3>
            <p class="ev-empty__text">Cuando abran tu tarjeta y te contesten, lo verás aquí. Solo las ves tú.</p>
        </div>
    @else
        <ul class="ev-list">
            @foreach($replies as $reply)
                <li class="ev-row eg-reply" :class="hidden && 'is-past'"
                    x-data="moderation(@js(route('client.contributions.update', [$invitation, $reply['id']])), @js($reply['isHidden']))">
                    <div class="ev-row__main">
                        @if($reply['reaction'])
                            <p class="ev-meta text-site-accent">
                                <span><x-phosphor-flower-tulip-light aria-hidden="true" /> Te respondió con {{ mb_strtolower($reply['reaction']['label']) }} <span class="text-site-muted">· {{ $reply['reaction']['meaning'] }}</span></span>
                            </p>
                        @endif

                        @if($reply['text'] !== '')
                            <blockquote class="eg-quote">{{ $reply['text'] }}</blockquote>
                        @endif

                        <div class="eg-reply__foot">
                            <p class="ev-meta">
                                <span>{{ $reply['author'] ?: 'Quien recibió tu tarjeta' }}</span>
                                @if($reply['date'])<span>{{ $reply['date'] }}</span>@endif
                                <span x-show="hidden" @if(! $reply['isHidden']) x-cloak @endif class="font-medium text-site-ink">Oculta</span>
                            </p>
                            <form method="POST" action="{{ route('client.contributions.update', [$invitation, $reply['id']]) }}" @submit.prevent="toggle()">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="moderation_status" value="{{ $reply['isHidden'] ? 'visible' : 'hidden' }}">
                                <button type="submit" class="ev-act" :disabled="busy" :title="hidden ? 'Mostrar' : 'Ocultar'" :aria-label="hidden ? 'Mostrar esta respuesta' : 'Ocultar esta respuesta'">
                                    <x-phosphor-eye-slash-light aria-hidden="true" x-show="!hidden" />
                                    <x-phosphor-eye-light aria-hidden="true" x-show="hidden" x-cloak />
                                </button>
                                <span class="sr-only" role="status" x-text="message"></span>
                            </form>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</section>
