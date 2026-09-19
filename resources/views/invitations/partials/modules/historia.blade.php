{{--
    «Nuestra historia» (App\Modules\Card\StoryModule) fuera del cuaderno: los capítulos uno debajo del
    otro. El cuaderno usa partials/aventura/pages/historia. Recibe $data (titulo, capitulos).
--}}
@php
    $storyChapters = array_values(array_filter((array) ($data['capitulos'] ?? []), 'is_array'));
@endphp

@if($storyChapters !== [])
    <section class="inv-section reveal" id="historia">
        <div class="inv-wrap">
            @include('invitations.partials.section-header', ['compact' => true, 'title' => ($data['titulo'] ?? null) ?: 'Nuestra historia'])
            @foreach($storyChapters as $chapter)
                <article class="inv-card-entry">
                    @if(! empty($chapter['titulo']))
                        <h3 class="inv-card-entry__title">{{ $chapter['titulo'] }}</h3>
                    @endif
                    @if(! empty($chapter['foto']))
                        <img src="{{ \App\Support\CloudinaryImage::url($chapter['foto'], 700) }}" alt="{{ $chapter['alt'] ?? '' }}" loading="lazy" decoding="async">
                    @endif
                    @foreach(preg_split('/\R{2,}/u', trim((string) ($chapter['texto'] ?? ''))) as $paragraph)
                        @if(trim($paragraph) !== '')
                            <p>{!! nl2br(e($paragraph)) !!}</p>
                        @endif
                    @endforeach
                </article>
            @endforeach
        </div>
    </section>
@endif
