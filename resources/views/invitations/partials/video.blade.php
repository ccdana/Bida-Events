<section class="invitation-section reveal invitation-video" id="video">
    <div class="section-inner-wide">
        <header class="section-header invitation-video__header">
            <span class="section-eyebrow invitation-video__eyebrow">Save the date</span>
            <h2 class="section-title invitation-video__title">{{ $video['titulo'] ?? 'Nuestro video' }}</h2>
            <div class="section-ornament invitation-video__ornament"></div>
        </header>
        @if(!empty($video['video_url'] ?? null))
            <div class="invitation-video__card">
                <div class="invitation-video__frame">
                    <video class="invitation-video__media" controls playsinline poster="{{ $video['poster'] ?? '' }}">
                        <source src="{{ $video['video_url'] ?? '' }}" type="video/mp4">
                    </video>
                    <div class="invitation-video__overlay" aria-hidden="true">
                        @include('invitations.partials.icon', ['name' => 'play', 'class' => 'invitation-video__icon w-10 h-10', 'animated' => true])
                    </div>
                </div>
            </div>
        @else
            <div class="invitation-video__empty">
                El video aún no tiene una URL configurada.
            </div>
        @endif
    </div>
</section>
