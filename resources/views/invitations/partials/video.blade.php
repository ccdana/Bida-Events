<section class="inv-section reveal inv-video" id="video">
    <div class="inv-wrap inv-wrap--wide">
        @include('invitations.partials.section-header', [
            'lottie' => 'video',
            'eyebrow' => 'Save the date',
            'title' => $video['titulo'] ?? 'Nuestro video',
        ])

        @if(!empty($video['video_url'] ?? null))
            @php($videoPlayerId = 'invitation-video-' . substr(md5($video['video_url']), 0, 10))
            <figure class="inv-video__frame is-idle" data-video-frame="true">
                @if(!empty($video['poster'] ?? null))
                    <img
                        class="inv-video__poster"
                        src="{{ $video['poster'] }}"
                        alt=""
                        loading="lazy"
                        decoding="async"
                        draggable="false"
                    >
                @endif
                <video
                    id="{{ $videoPlayerId }}"
                    class="inv-video__media"
                    playsinline
                    preload="{{ empty($video['poster'] ?? null) ? 'metadata' : 'none' }}"
                    data-video-player="true"
                >
                    <source src="{{ $video['video_url'] }}" type="video/mp4">
                </video>
                <button
                    type="button"
                    class="inv-video__toggle is-paused is-pinned is-visible"
                    data-video-toggle="true"
                    aria-label="Reproducir video"
                    title="Reproducir video"
                >
                    <span class="inv-video__toggle-icon" aria-hidden="true"></span>
                </button>
            </figure>
            <p class="inv-help inv-video__help">Toca el video para reproducirlo y sube el volumen de tu teléfono.</p>
        @else
            <p class="inv-empty">Muy pronto compartiremos el video.</p>
        @endif
    </div>
</section>
