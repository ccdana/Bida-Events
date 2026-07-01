<section class="invitation-section reveal invitation-video" id="video">
    <div class="section-inner-wide">
        <header class="section-header invitation-video__header">
            <span class="section-eyebrow invitation-video__eyebrow">Save the date</span>
            <div class="invitation-video__title-row">
                @include('invitations.partials.icon', ['name' => 'play', 'class' => 'invitation-video__title-icon w-7 h-7', 'animated' => true])
                <h2 class="section-title invitation-video__title">{{ $video['titulo'] ?? 'Nuestro video' }}</h2>
            </div>
            <div class="invitation-video__rule" aria-hidden="true"></div>
        </header>
        @if(!empty($video['video_url'] ?? null))
            <div class="invitation-video__card">
                <div class="invitation-video__frame">
                    <video class="invitation-video__media" controls playsinline poster="{{ $video['poster'] ?? '' }}">
                        <source src="{{ $video['video_url'] ?? '' }}" type="video/mp4">
                    </video>
                </div>
            </div>
        @else
            <div class="invitation-video__empty">
                El video aún no tiene una URL configurada.
            </div>
        @endif
    </div>
</section>
