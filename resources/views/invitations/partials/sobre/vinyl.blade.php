{{--
    Escena de la canción (módulo musica): un vinilo que gira mientras suena. El botón enciende o pausa
    la misma canción del reproductor de la página (vinylPlayer en resources/js/cards/sobre/index.js).
    Sin canción no se muestra. Recibe $data (titulo, artista, audio_url).
--}}
@if(! empty($data['audio_url']))
    <section class="inv-section reveal inv-vinyl" id="musica" x-data="vinylPlayer()" :class="{ 'is-playing': playing }">
        <div class="inv-wrap">
            @include('invitations.partials.section-header', [
                'compact' => true,
                'eyebrow' => $invCopy['vinyl_eyebrow'] ?? 'Dale al botón',
                'title' => $invCopy['vinyl_title'] ?? 'Esta canción me recuerda a ti',
            ])

            <div class="inv-vinyl__stage">
                <div class="inv-vinyl__record" aria-hidden="true">
                    <span class="inv-vinyl__label"><svg viewBox="0 0 24 22"><use href="#amor-heart" /></svg></span>
                </div>

                <div class="inv-vinyl__card">
                    <p class="inv-vinyl__song">{{ $data['titulo'] ?? 'Nuestra canción' }}</p>
                    @if(! empty($data['artista']))
                        <p class="inv-vinyl__artist">{{ $data['artista'] }}</p>
                    @endif
                </div>
            </div>

            <button type="button" class="inv-vinyl__play" data-needs-js data-story-ignore @click="toggle()" :aria-pressed="playing.toString()">
                <span x-text="playing ? 'Pausar' : 'Escuchar'">Escuchar</span>
            </button>
        </div>
    </section>
@endif
