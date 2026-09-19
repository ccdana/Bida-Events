{{--
    Fotos con marco: dos por hoja y un marco distinto para cada una (dorado antiguo, estampilla,
    óvalo con corona de flores amarillas, instantánea y boleto). La descripción de la foto va de pie
    escrito a mano. Recibe $data (titulo, fotos: [url | {url, alt}]).
--}}
@php
    $framePhotos = array_values(array_filter((array) ($data['fotos'] ?? []), fn ($photo) => is_string($photo) ? trim($photo) !== '' : ! empty($photo['url'] ?? null)));
    $frameStyles = ['dorado', 'estampilla', 'ovalo', 'instantanea', 'boleto'];
@endphp

@foreach(array_chunk($framePhotos, 2) as $framePage => $framePair)
    <article class="nb-page nb-page--paper nb-frames" data-nb-page @if($framePage === 0) id="marcos" @endif>
        <div class="nb-page__inner">
            @if($framePage === 0)
                <h2 class="nb-title">{{ ($data['titulo'] ?? null) ?: 'Enmarcados para siempre' }}</h2>
            @endif

            @foreach($framePair as $frameIndex => $photo)
                @php
                    $frameStyle = $frameStyles[($framePage * 2 + $frameIndex) % count($frameStyles)];
                    $frameCaption = is_array($photo) ? trim((string) ($photo['alt'] ?? '')) : '';
                @endphp
                <figure class="nb-frame nb-frame--{{ $frameStyle }}">
                    <div class="nb-frame__border">
                        @include('invitations.partials.aventura.photo', ['photo' => $photo, 'width' => 600, 'alt' => 'Foto enmarcada'])
                        @if($frameStyle === 'ovalo')
                            @include('invitations.partials.aventura.flower', ['kind' => 'ramita', 'class' => 'nb-frame__wreath nb-frame__wreath--a'])
                            @include('invitations.partials.aventura.flower', ['kind' => 'ramita', 'class' => 'nb-frame__wreath nb-frame__wreath--b'])
                            @include('invitations.partials.aventura.flower', ['kind' => 'margarita', 'class' => 'nb-frame__bud'])
                        @elseif($frameStyle === 'boleto')
                            <span class="nb-frame__ticket" aria-hidden="true">Pase para dos · N.º {{ str_pad((string) ($framePage * 2 + $frameIndex + 1), 3, '0', STR_PAD_LEFT) }}</span>
                        @endif
                    </div>
                    @if($frameCaption !== '')
                        <figcaption class="nb-hand">{{ $frameCaption }}</figcaption>
                    @endif
                </figure>
            @endforeach
        </div>
        <span class="nb-folio"><!--nb-folio--></span>
    </article>
@endforeach
