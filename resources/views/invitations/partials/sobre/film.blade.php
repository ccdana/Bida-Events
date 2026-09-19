{{--
    Escena «Mi persona favorita» (módulo video): una tira de película con dos cuadros. Si hay video se
    reproduce en el primer cuadro; si no, los cuadros son las fotos de la galería. El icono de la carta
    lleva a la carta escrita a mano. Recibe $data (titulo, video_url, poster).
--}}
@php
    $filmPhotos = collect($modulos['galeria']['fotos'] ?? [])
        ->map(fn ($foto) => is_array($foto) ? ($foto['url'] ?? null) : $foto)
        ->filter(fn ($url) => is_string($url) && $url !== '')
        ->values();
    $filmVideo = trim((string) ($data['video_url'] ?? ''));
    $filmPoster = trim((string) ($data['poster'] ?? ''));
    // Con video: un cuadro de video y una foto; sin video: dos fotos
    $filmFrames = $filmVideo !== '' ? [['video' => $filmVideo, 'poster' => $filmPoster], ['photo' => $filmPhotos->last()]] : [['photo' => $filmPhotos->last()], ['photo' => $filmPhotos->first()]];
@endphp

<section class="inv-section reveal inv-film" id="video">
    <div class="inv-wrap inv-wrap--wide">
        <p class="inv-film__shout inv-film__shout--top">{{ ($data['titulo'] ?? null) ?: ($invCopy['film_title'] ?? 'Mi persona favorita') }}</p>

        <div class="inv-film__strip">
            @foreach($filmFrames as $frame)
                <div class="inv-film__frame">
                    @if(! empty($frame['video']))
                        <video class="inv-film__media" src="{{ $frame['video'] }}" @if($frame['poster'] !== '') poster="{{ $frame['poster'] }}" @endif
                            controls playsinline preload="metadata" data-story-ignore></video>
                    @elseif(! empty($frame['photo']))
                        <img class="inv-film__media" src="{{ \App\Support\CloudinaryImage::url($frame['photo'], 700) }}" alt="" loading="lazy" decoding="async" draggable="false">
                    @else
                        <span class="inv-film__media inv-film__media--empty" aria-hidden="true"></span>
                    @endif
                </div>
            @endforeach
        </div>

        <p class="inv-film__shout">{{ $invCopy['film_shout'] ?? '¡Te quiero muchísimo!' }}</p>

        @if($page->visible('dedicatoria'))
            <a href="#dedicatoria" class="inv-film__letter">
                <svg viewBox="0 0 48 56" aria-hidden="true"><rect x="6" y="4" width="34" height="46" rx="3" /><path d="M13 16h20M13 24h20M13 32h12" /><use href="#amor-heart" x="26" y="36" width="10" height="9" /></svg>
                <span>{{ $invCopy['film_note'] ?? 'Te escribí algo' }}</span>
            </a>
        @endif
    </div>
</section>
