{{--
    La carta, escrita a mano en papel rayado. Un texto largo sigue en las hojas siguientes
    (App\Support\NotebookPaginator) y la firma va al final. Recibe $data (de, para, mensaje, firma).
--}}
@php
    $letterTo = trim((string) ($data['para'] ?? '')) ?: $cardTo;
    $letterFrom = trim((string) ($data['de'] ?? ''));
    $letterSignature = trim((string) ($data['firma'] ?? '')) ?: ($letterFrom !== '' ? $letterFrom : '');
    $letterPages = \App\Support\NotebookPaginator::pages($data['mensaje'] ?? '', 680, 880);
    $letterPages = $letterPages ?: [''];
    $letterLast = count($letterPages) - 1;
@endphp

@if(trim((string) ($data['mensaje'] ?? '')) !== '' || $letterTo !== '')
    @foreach($letterPages as $letterIndex => $letterText)
        <article class="nb-page nb-page--lined nb-letter" data-nb-page @if($letterIndex === 0) id="dedicatoria" @endif>
            <div class="nb-page__inner">
                @if($letterIndex === 0)
                    <p class="nb-eyebrow">Una carta para ti</p>
                    @if($letterTo !== '')
                        <p class="nb-letter__greeting">Para {{ $letterTo }},</p>
                    @endif
                @else
                    <p class="nb-continued">La carta · continúa</p>
                @endif

                <div class="nb-letter__body">
                    @foreach(preg_split('/\n{2,}/', $letterText) as $paragraph)
                        @if(trim($paragraph) !== '')
                            <p>{!! nl2br(e($paragraph)) !!}</p>
                        @endif
                    @endforeach
                </div>

                @if($letterIndex === $letterLast)
                    @if($letterSignature !== '')
                        <p class="nb-letter__signature">{{ $letterSignature }}</p>
                    @endif
                    @include('invitations.partials.aventura.flower', ['kind' => 'ramita', 'class' => 'nb-corner nb-corner--br'])
                @else
                    <p class="nb-next">sigue en la próxima hoja ›</p>
                @endif
            </div>
            <span class="nb-folio"><!--nb-folio--></span>
        </article>
    @endforeach
@endif
