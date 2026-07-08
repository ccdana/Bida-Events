<section class="invitation-section reveal invitation-video" id="video">
    <div class="section-inner-wide">
        <header class="section-header invitation-video__header">
            <span class="section-eyebrow invitation-video__eyebrow">Save the date</span>
            <h2 class="section-title invitation-video__title">{{ $video['titulo'] ?? 'Nuestro video' }}</h2>
            <div class="invitation-video__rule" aria-hidden="true"></div>
        </header>
        @if(!empty($video['video_url'] ?? null))
            @php $videoPlayerId = 'invitation-video-' . uniqid(); @endphp
            <div class="invitation-video__player">
                <div class="invitation-video__frame is-idle" data-video-frame="true">
                    @if(!empty($video['poster'] ?? null))
                        <img
                            class="invitation-video__poster"
                            data-video-poster="true"
                            src="{{ $video['poster'] }}"
                            alt=""
                            loading="eager"
                            decoding="sync"
                            draggable="false"
                        >
                    @endif
                    <video
                        id="{{ $videoPlayerId }}"
                        class="invitation-video__media"
                        playsinline
                        preload="none"
                        data-video-player="true"
                    >
                        <source src="{{ $video['video_url'] ?? '' }}" type="video/mp4">
                    </video>
                    <button
                        type="button"
                        class="invitation-video__play-toggle is-paused is-pinned is-visible"
                        data-video-toggle="true"
                        aria-label="Reproducir video"
                        title="Reproducir video"
                    >
                        <span class="invitation-video__play-toggle-icon" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
        @else
            <div class="invitation-video__empty">
                El video aún no tiene una URL configurada.
            </div>
        @endif
    </div>
</section>
