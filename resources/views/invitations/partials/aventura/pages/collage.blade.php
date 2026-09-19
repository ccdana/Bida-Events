{{--
    Collage de fotos: se reparten en hojas con diseños que se turnan (corazón, girasol de fotos, tira
    de fotomatón, instantáneas encimadas y círculos con margaritas), todas con flores amarillas.
    Recibe $data (titulo, fotos: [url | {url, alt}]).
--}}
@php
    $collagePhotos = array_values(array_filter((array) ($data['fotos'] ?? []), fn ($photo) => is_string($photo) ? trim($photo) !== '' : ! empty($photo['url'] ?? null)));
    // Diseño => cuántas fotos lleva la hoja
    $collageLayouts = ['corazon' => 3, 'girasol' => 5, 'fotomaton' => 4, 'polaroids' => 3, 'circulos' => 4];
    $collageCaptions = [
        'corazon' => 'Todo lo que quiero está aquí',
        'girasol' => 'Contigo todo florece',
        'fotomaton' => 'Una, dos, tres… ¡sonrisa!',
        'polaroids' => 'Momentos que guardo',
        'circulos' => 'Mis fotos favoritas',
    ];
    $collageSheets = [];
    $collageNames = array_keys($collageLayouts);

    for ($offset = 0, $turn = 0; $offset < count($collagePhotos); $turn++) {
        $layout = $collageNames[$turn % count($collageNames)];
        $collageSheets[] = ['layout' => $layout, 'photos' => array_slice($collagePhotos, $offset, $collageLayouts[$layout])];
        $offset += $collageLayouts[$layout];
    }
@endphp

@foreach($collageSheets as $sheetIndex => $sheet)
    <article class="nb-page nb-page--{{ $sheetIndex % 2 === 0 ? 'paper' : 'kraft' }} nb-collage-page" data-nb-page @if($sheetIndex === 0) id="collage" @endif>
        <div class="nb-page__inner">
            <p class="nb-eyebrow">{{ $sheetIndex === 0 ? (($data['titulo'] ?? null) ?: 'Nuestro collage') : 'Collage' }}</p>

            <div class="nb-collage nb-collage--{{ $sheet['layout'] }} nb-collage--n{{ count($sheet['photos']) }}">
                @if($sheet['layout'] === 'girasol')
                    @include('invitations.partials.aventura.flower', ['kind' => 'girasol', 'class' => 'nb-collage__bloom'])
                @endif

                @foreach($sheet['photos'] as $photoIndex => $photo)
                    <figure class="nb-collage__item">
                        @if(in_array($sheet['layout'], ['polaroids', 'corazon'], true) && $photoIndex > 0)
                            <span class="nb-tape nb-tape--{{ $photoIndex % 2 === 0 ? 'a' : 'b' }}" aria-hidden="true"></span>
                        @endif
                        @include('invitations.partials.aventura.photo', ['photo' => $photo, 'width' => 600, 'alt' => 'Foto del collage'])
                    </figure>
                @endforeach

                @if($sheet['layout'] === 'circulos')
                    @for($daisy = 0; $daisy < 3; $daisy++)
                        @include('invitations.partials.aventura.flower', ['kind' => 'margarita', 'class' => 'nb-collage__daisy'])
                    @endfor
                @endif
            </div>

            <p class="nb-hand nb-collage__caption">{{ $collageCaptions[$sheet['layout']] }}</p>

            @include('invitations.partials.aventura.flower', ['kind' => 'ramita', 'class' => 'nb-corner nb-corner--'.($sheetIndex % 2 === 0 ? 'bl' : 'br')])
            @include('invitations.partials.aventura.flower', ['kind' => $sheetIndex % 2 === 0 ? 'margarita' : 'girasol', 'class' => 'nb-corner nb-corner--tr nb-corner--small'])
        </div>
        <span class="nb-folio"><!--nb-folio--></span>
    </article>
@endforeach
