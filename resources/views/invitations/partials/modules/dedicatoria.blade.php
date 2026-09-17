{{--
    Dedicatoria de una tarjeta (App\Modules\Card\DedicationModule): una carta escrita a mano.
    Recibe $data (de, para, mensaje, firma) y $page. Los estilos base están en invitation/modules.css
    y cada tema los ajusta (p. ej. resources/css/cards/amor.css).
--}}
@php
    $to = trim((string) ($data['para'] ?? ''));
    $from = trim((string) ($data['de'] ?? ''));
    $signature = trim((string) ($data['firma'] ?? '')) ?: $from;
    $message = trim((string) ($data['mensaje'] ?? ''));
@endphp

@if($message !== '' || $to !== '')
    <section class="inv-section reveal inv-dedication" id="dedicatoria">
        <div class="inv-wrap">
            <article class="inv-letter" aria-label="{{ $to !== '' ? 'Carta para '.$to : 'Carta' }}">
                @if($to !== '')
                    <p class="inv-letter__to">Para {{ $to }},</p>
                @endif

                @if($message !== '')
                    <div class="inv-letter__body">
                        @foreach(preg_split('/\R{2,}/u', $message) as $paragraph)
                            <p>{!! nl2br(e($paragraph)) !!}</p>
                        @endforeach
                    </div>
                @endif

                @if($signature !== '')
                    <footer class="inv-letter__footer">
                        <p class="inv-letter__closing">{{ $page->copy['letter_closing'] ?? 'Con todo mi amor,' }}</p>
                        <p class="inv-letter__signature">{{ $signature }}</p>
                    </footer>
                @endif
            </article>
        </div>
    </section>
@endif
