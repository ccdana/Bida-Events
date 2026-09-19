{{--
    «Nuestra historia»: una hoja de apertura con el índice y cada capítulo en las hojas que necesite.
    La primera hoja del capítulo lleva la fecha como sello, el título y la foto, así que le cabe menos
    texto; las siguientes dicen «continúa». Un globo avanza por una línea punteada de hoja en hoja.
    Recibe $data (titulo, capitulos: [{titulo, fecha, texto, foto, alt}]).
--}}
@php
    $chapters = [];

    foreach (array_values(array_filter((array) ($data['capitulos'] ?? []), 'is_array')) as $chapterIndex => $chapter) {
        $chapterDate = null;

        try {
            $chapterDate = ! empty($chapter['fecha']) ? \Illuminate\Support\Carbon::parse($chapter['fecha']) : null;
        } catch (\Throwable) {
            $chapterDate = null;
        }

        $hasPhoto = trim((string) ($chapter['foto'] ?? '')) !== '';
        $chapterPages = \App\Support\NotebookPaginator::pages($chapter['texto'] ?? '', $hasPhoto ? 400 : 720, 900);

        $chapters[] = [
            'number' => $chapterIndex + 1,
            'title' => trim((string) ($chapter['titulo'] ?? '')) ?: 'Capítulo '.($chapterIndex + 1),
            'date' => $chapterDate,
            'photo' => $hasPhoto ? ['url' => $chapter['foto'], 'alt' => $chapter['alt'] ?? ''] : null,
            'pages' => $chapterPages ?: [''],
        ];
    }

    $storyTotal = 1 + array_sum(array_map(fn (array $chapter) => count($chapter['pages']), $chapters));
    $storyStep = 0;
@endphp

@if($chapters !== [])
    {{-- Apertura con el índice de capítulos --}}
    <article class="nb-page nb-page--kraft nb-story-open" data-nb-page id="historia">
        <div class="nb-page__inner">
            <p class="nb-eyebrow">Capítulo por capítulo</p>
            <h2 class="nb-title nb-title--big">{{ ($data['titulo'] ?? null) ?: 'Nuestra historia' }}</h2>

            <ol class="nb-index">
                @foreach($chapters as $chapter)
                    <li>
                        <a href="#nb-cap-{{ $chapter['number'] }}" class="nb-index__link">
                            <span class="nb-index__number">{{ $chapter['number'] }}</span>
                            <span class="nb-index__title">{{ $chapter['title'] }}</span>
                            @if($chapter['date'])
                                <span class="nb-index__date">{{ $chapter['date']->locale('es')->translatedFormat('M Y') }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ol>

            @include('invitations.partials.aventura.flower', ['kind' => 'globo', 'class' => 'nb-story-open__balloon'])
            @include('invitations.partials.aventura.flower', ['kind' => 'ramita', 'class' => 'nb-corner nb-corner--bl'])
        </div>
        <span class="nb-folio"><!--nb-folio--></span>
    </article>

    @foreach($chapters as $chapter)
        @foreach($chapter['pages'] as $chapterPageIndex => $chapterText)
            @php($storyStep++)
            <article class="nb-page nb-page--paper nb-chapter" data-nb-page
                @if($chapterPageIndex === 0) id="nb-cap-{{ $chapter['number'] }}" @endif>
                <div class="nb-page__inner">
                    @if($chapterPageIndex === 0)
                        <header class="nb-chapter__head">
                            @if($chapter['date'])
                                <p class="nb-stamp">
                                    <span>{{ $chapter['date']->locale('es')->translatedFormat('j M') }}</span>
                                    <span>{{ $chapter['date']->year }}</span>
                                </p>
                            @endif
                            <p class="nb-chapter__number">Capítulo {{ $chapter['number'] }}</p>
                            <h3 class="nb-chapter__title">{{ $chapter['title'] }}</h3>
                        </header>

                        @if($chapter['photo'])
                            <figure class="nb-chapter__photo nb-corners">
                                @include('invitations.partials.aventura.photo', ['photo' => $chapter['photo'], 'width' => 700, 'alt' => $chapter['title']])
                            </figure>
                        @endif
                    @else
                        <p class="nb-continued">Capítulo {{ $chapter['number'] }} · continúa</p>
                    @endif

                    @if(trim($chapterText) !== '')
                        <div class="nb-chapter__body">
                            @foreach(preg_split('/\n{2,}/', $chapterText) as $paragraph)
                                @if(trim($paragraph) !== '')
                                    <p>{!! nl2br(e($paragraph)) !!}</p>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    @if($chapterPageIndex < count($chapter['pages']) - 1)
                        <p class="nb-next">sigue en la próxima hoja ›</p>
                    @else
                        <p class="nb-chapter__end">@include('invitations.partials.aventura.flower', ['kind' => 'margarita'])</p>
                    @endif

                    {{-- El globo avanza por la línea punteada a medida que avanza la historia --}}
                    <div class="nb-trail" aria-hidden="true" style="--trail: {{ round($storyStep / $storyTotal * 100, 1) }}%">
                        @include('invitations.partials.aventura.flower', ['kind' => 'globo', 'class' => 'nb-trail__balloon'])
                    </div>
                </div>
                <span class="nb-folio"><!--nb-folio--></span>
            </article>
        @endforeach
    @endforeach
@endif
