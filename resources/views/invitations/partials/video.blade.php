<section class="inv-section reveal inv-video" id="video">
    <div class="inv-wrap inv-wrap--wide">
        @include('invitations.partials.section-header', [
            'lottie' => 'video',
            'eyebrow' => $invCopy['video_eyebrow'] ?? __('Save the date'),
            'title' => $video['titulo'] ?? __('Nuestro video'),
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
                {{-- controls es el respaldo sin JavaScript: el reproductor propio lo quita al iniciarse --}}
                <video
                    id="{{ $videoPlayerId }}"
                    class="inv-video__media"
                    playsinline
                    controls
                    preload="metadata"
                    data-video-player="true"
                >
                    {{-- El tipo solo cuando se conoce: un .mov o un .webm con type="video/mp4" el navegador lo descartaba y el video no cargaba --}}
                    @php($videoType = ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'ogg' => 'video/ogg'][strtolower(pathinfo((string) parse_url($video['video_url'], PHP_URL_PATH), PATHINFO_EXTENSION))] ?? null)
                    <source src="{{ $video['video_url'] }}"@if($videoType) type="{{ $videoType }}"@endif>
                </video>
                <button
                    type="button"
                    class="inv-video__toggle is-paused is-pinned is-visible"
                    data-video-toggle="true"
                    aria-label="{{ __('Reproducir video') }}"
                    title="{{ __('Reproducir video') }}"
                >
                    <span class="inv-video__toggle-icon" aria-hidden="true"></span>
                </button>
            </figure>
            <p class="inv-help inv-video__error" data-video-error hidden role="status" tabindex="0">{{ __('No pudimos cargar el video. Revisa tu conexión y toca aquí para reintentar.') }}</p>
            <p class="inv-help inv-video__help">{{ $invCopy['video_help'] ?? __('Toca el video para reproducirlo y sube el volumen de tu teléfono.') }}</p>
        @else
            <p class="inv-empty">{{ $invCopy['video_empty'] ?? __('Muy pronto compartiremos el video.') }}</p>
        @endif
    </div>
</section>
