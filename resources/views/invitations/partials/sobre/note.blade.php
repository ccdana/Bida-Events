{{-- Mensaje corto de la portada en un marco de luz, entre dos fotos. Solo aparece si hay mensaje. --}}
@php
    $noteText = trim((string) ($page->welcome['mensaje'] ?? ''));
    $notePhotos = collect($modulos['galeria']['fotos'] ?? [])
        ->map(fn ($foto) => is_array($foto) ? ($foto['url'] ?? null) : $foto)
        ->filter(fn ($url) => is_string($url) && $url !== '')
        ->values();
@endphp

@if($noteText !== '')
    <section class="inv-section inv-note" id="mensaje">
        <div class="inv-note__grid">
            @foreach([0, 1] as $slot)
                @if($photo = $notePhotos[$slot] ?? null)
                    <figure class="inv-note__photo inv-note__photo--{{ $slot }}" data-step style="--step: {{ $slot === 0 ? 1 : 2 }}">
                        <img src="{{ \App\Support\CloudinaryImage::url($photo, 500) }}" alt="" loading="lazy" decoding="async" draggable="false">
                    </figure>
                @endif
            @endforeach

            <article class="inv-note__frame" data-step style="--step: 0">
                <svg class="inv-note__crest" viewBox="0 0 60 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true"><path d="M4 18 C 16 4, 24 4, 30 14 C 36 4, 44 4, 56 18" stroke-linecap="round"/><use href="#amor-heart" x="24" y="6" width="12" height="11" /></svg>
                <h2 class="inv-note__title">{{ $invCopy['note_title'] ?? '¡Feliz día!' }}</h2>
                <p class="inv-note__text">{!! nl2br(e($noteText)) !!}</p>
            </article>
        </div>
    </section>
@endif
