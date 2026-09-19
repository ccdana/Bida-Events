{{--
    Galería de la tarjeta de amor: polaroids colgadas con pinzas en un tendedero. Se desliza a lo
    largo de la cuerda (las fotos se balancean con el movimiento y con la inclinación del teléfono)
    y un toque le da la vuelta a la foto: detrás está su descripción escrita a mano.
    Reemplaza a gallery-stack solo en esta plantilla (InvitationTemplates, «partials»);
    clothesline en resources/js/cards/amor/clothesline.js. Sin JavaScript es una fila de fotos
    que se desliza con el dedo. Recibe $data (titulo, fotos).
--}}
@php
    // La foto llega como URL suelta o como {url, alt}: la descripción la escribe el cliente en el editor
    $linePhotos = collect($data['fotos'] ?? [])
        ->map(fn ($foto) => is_array($foto)
            ? ['url' => $foto['url'] ?? null, 'alt' => trim((string) ($foto['alt'] ?? ''))]
            : ['url' => $foto, 'alt' => ''])
        ->filter(fn ($foto) => is_string($foto['url']) && $foto['url'] !== '')
        ->values();
    $lineCount = $linePhotos->count();
@endphp

<section class="inv-section reveal inv-gallery inv-line-scene" id="galeria">
    <div class="inv-wrap inv-wrap--wide">
        @include('invitations.partials.section-header', [
            'compact' => true,
            'eyebrow' => $invCopy['gallery_eyebrow'] ?? 'Nuestros momentos',
            'title' => ($data['titulo'] ?? null) ?: 'Galería',
        ])

        @if($lineCount > 0)
            <div class="inv-line" x-data="clothesline()" data-story-ignore>
                <ul class="inv-line__track" x-ref="track" tabindex="0" aria-label="Fotos colgadas en un tendedero. Desliza para ver más; toca una foto para darle la vuelta.">
                    @foreach($linePhotos as $index => $photo)
                        @php($caption = $photo['alt'] !== '' ? $photo['alt'] : 'Recuerdo '.($index + 1).' de '.$lineCount)
                        <li class="inv-line__item" style="--i: {{ $index }}; --lean: {{ [-4, 3, -2, 5, -3, 2][$index % 6] }}deg; --sway: {{ 3.2 + ($index % 3) * 0.6 }}s">
                            <button type="button" class="inv-line__card"
                                :class="{ 'is-flipped': flipped === {{ $index }} }"
                                :aria-pressed="(flipped === {{ $index }}).toString()"
                                @click="flip({{ $index }})">
                                <span class="inv-line__pin" aria-hidden="true"></span>
                                <span class="inv-line__face inv-line__face--front">
                                    <img src="{{ \App\Support\CloudinaryImage::url($photo['url'], 700) }}"
                                        alt="{{ $caption }}"
                                        loading="{{ $index < 2 ? 'eager' : 'lazy' }}" decoding="async" draggable="false">
                                </span>
                                <span class="inv-line__face inv-line__face--back" aria-hidden="true">
                                    <span class="inv-line__note">{{ $caption }}</span>
                                </span>
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>

            @if($lineCount > 1)
                <p class="inv-line__hint">Desliza por el tendedero · toca una foto para darle la vuelta</p>
            @endif
        @endif
    </div>

    @include('invitations.partials.amor.butterfly', ['butterflyId' => 'recuerdos', 'butterflyStyle' => '--bx: 84%; --by: 74%; --delay: 1.6s'])
</section>
