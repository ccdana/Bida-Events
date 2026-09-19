{{--
    Dedicatoria de una tarjeta (App\Modules\Card\DedicationModule): una carta escrita a mano.
    Recibe $data (de, para, mensaje, firma) y $page. Los estilos base están en invitation/modules.css
    y cada tema los ajusta (p. ej. resources/css/cards/amor.css).

    Llega doblada y lacrada: se abre manteniendo presionado el sello (holdToOpen en resources/js/story).
    La carta está entera en el HTML debajo del doblez, así se lee sin JavaScript y con lector de pantalla.
--}}
@php
    $to = trim((string) ($data['para'] ?? ''));
    $from = trim((string) ($data['de'] ?? ''));
    $signature = trim((string) ($data['firma'] ?? '')) ?: $from;
    $message = trim((string) ($data['mensaje'] ?? ''));
    $paragraphs = $message !== '' ? preg_split('/\R{2,}/u', $message) : [];
    $sealInitial = $from !== '' ? mb_strtoupper(mb_substr($from, 0, 1)) : null;
@endphp

@if($message !== '' || $to !== '')
    <section class="inv-section reveal inv-dedication" id="dedicatoria">
        <div class="inv-wrap">
            <div class="inv-letter-gate"
                x-data="holdToOpen({ duration: 1100 })"
                data-story-gate
                :data-gate-done="opened"
                :class="{ 'is-holding': holding, 'is-nudged': nudged }"
                :style="`--hold: ${progress.toFixed(3)}`">
                <article class="inv-letter" data-gate-content aria-label="{{ $to !== '' ? 'Carta para '.$to : 'Carta' }}">
                    @if($to !== '')
                        <p class="inv-letter__to" data-gate-step style="--step: 0">Para {{ $to }},</p>
                    @endif

                    @if($paragraphs)
                        <div class="inv-letter__body">
                            @foreach($paragraphs as $paragraph)
                                <p data-gate-step style="--step: {{ $loop->iteration }}">{!! nl2br(e($paragraph)) !!}</p>
                            @endforeach
                        </div>
                    @endif

                    @if($signature !== '')
                        <footer class="inv-letter__footer" data-gate-step style="--step: {{ count($paragraphs) + 1 }}">
                            <p class="inv-letter__closing">{{ $page->copy['letter_closing'] ?? 'Con todo mi amor,' }}</p>
                            <p class="inv-letter__signature">{{ $signature }}</p>
                        </footer>
                    @endif
                </article>

                {{-- Doblez con el sello: solo con JavaScript --}}
                <div class="inv-letter-gate__cover" data-needs-js>
                    <p class="inv-letter-gate__label">{{ $from !== '' ? 'Una carta de '.$from : 'Una carta para ti' }}</p>

                    <button type="button" class="inv-seal" data-gate-trigger
                        @pointerdown="start($event)"
                        @pointerup="stop()"
                        @pointerleave="stop()"
                        @pointercancel="stop()"
                        @keydown.space.prevent="start($event)"
                        @keydown.enter.prevent="start($event)"
                        @keyup.space="stop()"
                        @keyup.enter="stop()"
                        @click="activate($event)"
                        @contextmenu.prevent
                        aria-label="Mantén presionado el sello para abrir la carta">
                        <svg class="inv-seal__ring" viewBox="0 0 100 100" aria-hidden="true">
                            <circle cx="50" cy="50" r="46" pathLength="1" />
                            <circle class="inv-seal__ring-fill" cx="50" cy="50" r="46" pathLength="1" />
                        </svg>
                        @foreach(['left', 'right'] as $half)
                            <span class="inv-seal__half inv-seal__half--{{ $half }}" aria-hidden="true">
                                @if($sealInitial)
                                    <span class="inv-seal__mark">{{ $sealInitial }}</span>
                                @else
                                    <svg class="inv-seal__mark inv-seal__mark--heart" viewBox="0 0 24 24" fill="currentColor"><path d="M12 21s-7.5-4.6-9.6-9.3C.9 8.3 3 4.5 6.7 4.5c2.1 0 3.6 1.2 5.3 3.1 1.7-1.9 3.2-3.1 5.3-3.1 3.7 0 5.8 3.8 4.3 7.2C19.5 16.4 12 21 12 21z"/></svg>
                                @endif
                            </span>
                        @endforeach
                    </button>

                    <p class="inv-letter-gate__hint" aria-hidden="true">
                        <span x-show="!nudged">Mantén presionado el sello</span>
                        <span x-show="nudged" x-cloak>Déjalo apoyado un momento más</span>
                    </p>
                </div>
            </div>
        </div>
    </section>
@endif
