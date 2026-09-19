{{--
    Dedicatoria de la tarjeta «Sobre lacrado»: dos hojas de papel con las esquinas dobladas. A la
    izquierda una polaroid pegada con cinta y los dos dibujados; a la derecha la carta escrita a mano.
    El texto está entero en el HTML. Recibe $data (de, para, mensaje, firma).
--}}
@php
    $to = trim((string) ($data['para'] ?? ''));
    $from = trim((string) ($data['de'] ?? ''));
    $signature = trim((string) ($data['firma'] ?? '')) ?: $from;
    $message = trim((string) ($data['mensaje'] ?? ''));
    $paragraphs = $message !== '' ? preg_split('/\R{2,}/u', $message) : [];
    $letterPhoto = $page->heroImage;
@endphp

@if($message !== '' || $to !== '')
    <section class="inv-section reveal inv-sheets" id="dedicatoria">
        <div class="inv-sheets__pair">
            <div class="inv-sheet inv-sheet--photo" data-step style="--step: 0" aria-hidden="{{ $letterPhoto ? 'false' : 'true' }}">
                <span class="inv-sheet__tape" aria-hidden="true"></span>
                @if($letterPhoto)
                    <span class="inv-sheet__polaroid">
                        <img src="{{ \App\Support\CloudinaryImage::url($letterPhoto, 600) }}" alt="" loading="lazy" decoding="async" draggable="false">
                    </span>
                @endif
                <span class="inv-sheet__hearts" aria-hidden="true">
                    <svg viewBox="0 0 24 22"><use href="#amor-heart" /></svg>
                    <svg viewBox="0 0 24 22"><use href="#amor-heart" /></svg>
                    <svg viewBox="0 0 24 22"><use href="#amor-heart" /></svg>
                </span>
            </div>

            <article class="inv-sheet inv-sheet--text" data-step style="--step: 1" aria-label="{{ $to !== '' ? 'Carta para '.$to : 'Carta' }}">
                @if($to !== '')
                    <p class="inv-sheet__to">Hola, {{ $to }}.</p>
                @endif

                @foreach($paragraphs as $paragraph)
                    <p class="inv-sheet__p">{!! nl2br(e($paragraph)) !!}</p>
                @endforeach

                @if($signature !== '')
                    <p class="inv-sheet__closing">{{ $invCopy['letter_closing'] ?? 'Con todo mi cariño,' }}</p>
                    <p class="inv-sheet__signature">{{ $signature }}</p>
                @endif
            </article>
        </div>
    </section>
@endif
