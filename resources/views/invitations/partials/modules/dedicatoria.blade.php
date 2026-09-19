{{--
    Dedicatoria de una tarjeta (App\Modules\Card\DedicationModule): una carta escrita a mano.
    Recibe $data (de, para, mensaje, firma) y $page. Los estilos base están en invitation/modules.css
    y cada tema los ajusta (p. ej. resources/css/cards/amor/scenes.css).

    Llega en un sobre cerrado con un sello de lacre: se abre manteniendo presionado el sello
    (holdToOpen en resources/js/story). El sello se parte, la solapa se abre, la hoja asoma y la carta
    aparece. La carta está entera en el HTML, así se lee sin JavaScript y con lector de pantalla.
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

                {{-- El sobre cerrado con el sello: solo con JavaScript --}}
                <div class="inv-letter-gate__cover" data-needs-js>
                    <p class="inv-letter-gate__label">{{ $from !== '' ? 'Una carta de '.$from : 'Una carta para ti' }}</p>

                    <div class="inv-envelope">
                        <span class="inv-envelope__back" aria-hidden="true"></span>
                        <span class="inv-envelope__paper" aria-hidden="true">
                            <b>{{ $to !== '' ? $to.',' : '' }}</b><i></i><i></i><i></i>
                        </span>
                        <span class="inv-envelope__front" aria-hidden="true">
                            @if($to !== '')
                                <span class="inv-envelope__to">Para {{ $to }}</span>
                            @endif
                        </span>
                        <span class="inv-envelope__flap" aria-hidden="true"></span>

                        {{-- Ramita seca bajo el sello --}}
                        <svg class="inv-envelope__sprig" viewBox="0 0 80 40" aria-hidden="true">
                            <path class="inv-envelope__sprig-stem" d="M4 34 C 24 28, 44 20, 76 6" />
                            <path class="inv-envelope__sprig-leaf" d="M22 29 C 18 20, 22 14, 28 12 C 30 19, 28 25, 22 29 Z" />
                            <path class="inv-envelope__sprig-leaf" d="M34 24 C 34 34, 40 38, 46 36 C 44 29, 40 25, 34 24 Z" />
                            <path class="inv-envelope__sprig-leaf" d="M46 18 C 44 9, 48 3, 54 2 C 56 9, 53 15, 46 18 Z" />
                            <circle class="inv-envelope__sprig-bud" cx="63" cy="11" r="3.2" />
                            <circle class="inv-envelope__sprig-bud" cx="70" cy="8" r="2.6" />
                            <circle class="inv-envelope__sprig-bud" cx="57" cy="15" r="2.4" />
                        </svg>

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
                                <circle cx="50" cy="50" r="47" pathLength="1" />
                                <circle class="inv-seal__ring-fill" cx="50" cy="50" r="47" pathLength="1" />
                            </svg>

                            {{-- Lacre: una gota irregular con relieve; se dibuja una vez y cada mitad la recorta --}}
                            <svg class="inv-seal__wax" viewBox="0 0 100 104" aria-hidden="true">
                                <defs>
                                    <radialGradient id="inv-seal-gloss" cx="38%" cy="32%" r="75%">
                                        <stop offset="0" style="stop-color: var(--seal-light)" />
                                        <stop offset="0.45" style="stop-color: var(--seal)" />
                                        <stop offset="1" style="stop-color: var(--seal-dark)" />
                                    </radialGradient>
                                    <clipPath id="inv-seal-left"><path d="M0 0 H52 L46 22 L56 38 L44 56 L54 74 L47 104 H0 Z" /></clipPath>
                                    <clipPath id="inv-seal-right"><path d="M52 0 H100 V104 H47 L54 74 L44 56 L56 38 L46 22 Z" /></clipPath>
                                    <g id="inv-seal-face">
                                        <path class="inv-seal__drip" d="M70 84 C 75 91, 77 99, 72 101 C 67 102, 66 95, 67 88 Z" />
                                        <path class="inv-seal__blob" d="M50 7 C 62 6, 71 11, 79 17 C 89 24, 94 35, 93 47 C 95 59, 90 71, 82 80 C 74 89, 62 94, 50 93 C 38 95, 26 90, 18 82 C 9 73, 5 61, 7 49 C 6 36, 11 24, 21 16 C 29 10, 39 6, 50 7 Z" />
                                        <circle class="inv-seal__groove" cx="50" cy="50" r="31" />
                                        <circle class="inv-seal__groove-light" cx="50" cy="51" r="31" />
                                        @for($dot = 0; $dot < 20; $dot++)
                                            <circle class="inv-seal__bead" cx="{{ round(50 + 35.5 * cos(deg2rad($dot * 18)), 2) }}" cy="{{ round(50 + 35.5 * sin(deg2rad($dot * 18)), 2) }}" r="1.3" />
                                        @endfor
                                        @if($sealInitial)
                                            <text class="inv-seal__initial inv-seal__initial--light" x="50" y="63" text-anchor="middle">{{ $sealInitial }}</text>
                                            <text class="inv-seal__initial" x="50" y="62" text-anchor="middle">{{ $sealInitial }}</text>
                                        @else
                                            <path class="inv-seal__heart" d="M50 66s-15-9.2-19.2-18.6C28 40.6 32 33 39.4 33c4.2 0 7.2 2.4 10.6 6.2 3.4-3.8 6.4-6.2 10.6-6.2 7.4 0 11.4 7.6 8.6 14.4C65 56.8 50 66 50 66z" />
                                        @endif
                                        <ellipse class="inv-seal__shine" cx="35" cy="28" rx="13" ry="6" transform="rotate(-32 35 28)" />
                                    </g>
                                </defs>
                                <g class="inv-seal__half inv-seal__half--left" clip-path="url(#inv-seal-left)"><use href="#inv-seal-face" /></g>
                                <g class="inv-seal__half inv-seal__half--right" clip-path="url(#inv-seal-right)"><use href="#inv-seal-face" /></g>
                            </svg>
                        </button>
                    </div>

                    <p class="inv-letter-gate__hint" aria-hidden="true">
                        <span x-show="!nudged">Mantén presionado el sello para abrirla</span>
                        <span x-show="nudged" x-cloak>Déjalo apoyado un momento más</span>
                    </p>
                </div>
            </div>
        </div>
    </section>
@endif
