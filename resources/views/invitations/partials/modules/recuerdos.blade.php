{{--
    «Recuerdos especiales» (App\Modules\Card\MemoriesModule) fuera del cuaderno. El cuaderno usa
    partials/aventura/pages/recuerdos. Recibe $data (titulo, recuerdos).
--}}
@php
    $memoryItems = array_values(array_filter((array) ($data['recuerdos'] ?? []), 'is_array'));
@endphp

@if($memoryItems !== [])
    <section class="inv-section reveal" id="recuerdos">
        <div class="inv-wrap">
            @include('invitations.partials.section-header', ['compact' => true, 'title' => ($data['titulo'] ?? null) ?: 'Recuerdos especiales'])
            @foreach($memoryItems as $memory)
                <figure class="inv-card-entry">
                    @if(! empty($memory['foto']))
                        <img src="{{ \App\Support\CloudinaryImage::url($memory['foto'], 700) }}" alt="{{ $memory['alt'] ?? ($memory['titulo'] ?? '') }}" loading="lazy" decoding="async">
                    @endif
                    <figcaption>
                        @if(! empty($memory['titulo']))
                            <strong class="inv-card-entry__title">{{ $memory['titulo'] }}</strong>
                        @endif
                        @if(! empty($memory['texto']))
                            <p>{{ $memory['texto'] }}</p>
                        @endif
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </section>
@endif
