{{--
    «Recuerdos especiales»: dos por hoja, cada uno como una instantánea pegada con cinta washi, con
    su fecha y una nota escrita a mano. Recibe $data (titulo, recuerdos: [{titulo, fecha, texto, foto, alt}]).
--}}
@php
    $memories = array_values(array_filter((array) ($data['recuerdos'] ?? []), 'is_array'));
@endphp

@foreach(array_chunk($memories, 2) as $memoryPage => $memoryPair)
    <article class="nb-page nb-page--dots nb-memories" data-nb-page @if($memoryPage === 0) id="recuerdos" @endif>
        <div class="nb-page__inner">
            @if($memoryPage === 0)
                <h2 class="nb-title">{{ ($data['titulo'] ?? null) ?: 'Recuerdos especiales' }}</h2>
            @endif

            @foreach($memoryPair as $memoryIndex => $memory)
                @php
                    $memoryDate = null;

                    try {
                        $memoryDate = ! empty($memory['fecha']) ? \Illuminate\Support\Carbon::parse($memory['fecha']) : null;
                    } catch (\Throwable) {
                        $memoryDate = null;
                    }
                @endphp
                <figure class="nb-memory nb-memory--{{ $memoryIndex % 2 === 0 ? 'left' : 'right' }}">
                    @if(! empty($memory['foto']))
                        <div class="nb-polaroid">
                            <span class="nb-tape nb-tape--{{ $memoryIndex % 2 === 0 ? 'a' : 'b' }}" aria-hidden="true"></span>
                            @include('invitations.partials.aventura.photo', [
                                'photo' => ['url' => $memory['foto'], 'alt' => $memory['alt'] ?? ''],
                                'width' => 600,
                                'alt' => $memory['titulo'] ?? '',
                            ])
                        </div>
                    @endif
                    <figcaption class="nb-memory__note">
                        @if($memoryDate)
                            <span class="nb-memory__date">{{ $memoryDate->locale('es')->translatedFormat('j \d\e F \d\e Y') }}</span>
                        @endif
                        @if(! empty($memory['titulo']))
                            <strong class="nb-memory__title">{{ $memory['titulo'] }}</strong>
                        @endif
                        @if(! empty($memory['texto']))
                            <span class="nb-memory__text">{{ $memory['texto'] }}</span>
                        @endif
                    </figcaption>
                </figure>
            @endforeach

            @include('invitations.partials.aventura.flower', ['kind' => 'margarita', 'class' => 'nb-corner nb-corner--tr nb-corner--small'])
        </div>
        <span class="nb-folio"><!--nb-folio--></span>
    </article>
@endforeach
