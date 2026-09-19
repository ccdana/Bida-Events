{{--
    Acto III · De frente: ya no el reflejo, la luna al frente y sin filtros. La anécdota (el momento
    pequeño que lo significó todo), su canción, la dedicatoria y la pareja hoy.
    Datos: historia (anecdota_titulo, anecdota, anecdota_foto), musica, dedicatoria, galeria.
    Sin foto de la anécdota se usa la de la portada, ahora nítida: el reflejo del Acto I, visto de frente.
--}}
@php
    $anecdoteTitle = $filled($story['anecdota_titulo'] ?? null) ?? $invCopy['act3_title_fallback'];
    $anecdote = $filled($story['anecdota'] ?? null) ?? $invCopy['act3_anecdote_fallback'];
    $anecdotePhoto = $filled($story['anecdota_foto'] ?? null) ?? $page->heroImage;
    $anecdoteAlt = $filled($story['anecdota_foto'] ?? null)
        ? ($filled($story['anecdota_foto_alt'] ?? null) ?? $anecdoteTitle)
        : ($filled($page->welcome['imagen_hero_alt'] ?? null) ?? $coupleLabel);
    $message = $filled($dedication['mensaje'] ?? null) ?? $invCopy['act3_dedication_fallback'];
    $signature = $filled($dedication['firma'] ?? null) ?? ($cardFrom !== '' ? $cardFrom : null);
@endphp

<section class="story-act story-act--3" data-act>
    <div class="story-act__body">
        @include('invitations.partials.historia.mark', ['number' => 'III', 'name' => $invCopy['act3_label']])

        <article class="story-anecdote">
            <h2 class="story-title reveal">{{ $anecdoteTitle }}</h2>

            @if($anecdotePhoto)
                <figure class="story-clear reveal">
                    <img src="{{ \App\Support\CloudinaryImage::url($anecdotePhoto, 900) }}"
                        alt="{{ $anecdoteAlt }}" width="900" height="1125" loading="lazy" decoding="async">
                </figure>
            @endif

            <div class="story-copy story-copy--prose reveal">
                @foreach($paragraphs($anecdote) as $paragraph)
                    <p>{!! nl2br(e($paragraph)) !!}</p>
                @endforeach
            </div>
        </article>

        @if(! empty($song['titulo'] ?? null) || ! empty($song['artista'] ?? null))
            <p class="story-song reveal">
                <span class="story-song__label">{{ $invCopy['act3_song_label'] }}</span>
                <span class="story-song__title">{{ $song['titulo'] ?? '' }}</span>
                @if(! empty($song['artista'] ?? null))
                    <span class="story-song__artist">{{ $song['artista'] }}</span>
                @endif
            </p>
        @endif

        <div class="story-dedication reveal" id="dedicatoria">
            @if($cardTo !== '')
                <p class="story-dedication__to">Para {{ $cardTo }}</p>
            @endif
            @foreach($paragraphs($message) as $paragraph)
                <p class="story-dedication__text">{!! nl2br(e($paragraph)) !!}</p>
            @endforeach
            @if($signature)
                <p class="story-dedication__sign">{{ $signature }}</p>
            @endif
        </div>

        @if($couplePhotos->isNotEmpty())
            <div class="story-now" id="galeria">
                <p class="story-now__label reveal">{{ $invCopy['act3_photos_label'] }}</p>
                <div class="story-now__photos story-now__photos--{{ $couplePhotos->count() }}">
                    @foreach($couplePhotos as $index => $photo)
                        <figure class="story-now__photo reveal" style="--i: {{ $index }}">
                            <img src="{{ \App\Support\CloudinaryImage::url($photo['url'], $index === 0 ? 900 : 600) }}"
                                alt="{{ $photo['alt'] !== '' ? $photo['alt'] : $coupleLabel.' hoy, foto '.($index + 1) }}"
                                loading="lazy" decoding="async">
                        </figure>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
