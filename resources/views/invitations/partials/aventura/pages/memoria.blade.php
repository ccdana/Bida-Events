{{--
    Juego de memoria con sus fotos: cada foto está dos veces boca abajo (dorso con una margarita) y hay
    que encontrar los pares. Al terminar caen pétalos y aparece el mensaje final.
    Las cartas salen del servidor en un orden fijo y el navegador las vuelve a mezclar
    (memoryGame en resources/js/aventura/memory-game.js). Sin JavaScript se ven las fotos y el mensaje.
    Recibe $data (titulo, mensaje_final, fotos).
--}}
@php
    $gamePhotos = array_values(array_filter((array) ($data['fotos'] ?? []), fn ($photo) => is_string($photo) ? trim($photo) !== '' : ! empty($photo['url'] ?? null)));
    $gamePhotos = array_slice($gamePhotos, 0, \App\Modules\Card\MemoryGameModule::MAX_PHOTOS);
    $gamePairs = count($gamePhotos);
    $gameCards = [];

    foreach ($gamePhotos as $pair => $photo) {
        $gameCards[] = ['pair' => $pair, 'photo' => $photo];
        $gameCards[] = ['pair' => $pair, 'photo' => $photo];
    }

    // Orden fijo por tarjeta (misma vista sin JavaScript en cada visita); el navegador vuelve a mezclar
    mt_srand((int) $invitation->id + 21);
    shuffle($gameCards);
    mt_srand();

    $gameMessage = trim((string) ($data['mensaje_final'] ?? '')) ?: '¡Los encontraste todos! Así de bien nos complementamos.';
@endphp

@if($gamePairs >= \App\Modules\Card\MemoryGameModule::MIN_PHOTOS)
    <article class="nb-page nb-page--grid nb-game" data-nb-page id="memoria">
        <div class="nb-page__inner">
            <p class="nb-eyebrow">Un juego para ti</p>
            <h2 class="nb-title">{{ ($data['titulo'] ?? null) ?: 'Encuentra los pares' }}</h2>

            <div class="nb-game__board" x-data="memoryGame({{ $gamePairs }})" data-nb-nodrag :class="{ 'is-won': won, 'is-playing': true }">
                <p class="nb-game__help" data-needs-js>Toca dos cartas: si son la misma foto, se quedan boca arriba.</p>

                <div class="nb-cards nb-cards--{{ $gamePairs * 2 <= 6 ? 3 : 4 }}" x-ref="grid">
                    @foreach($gameCards as $cardIndex => $card)
                        <button type="button" class="nb-card" data-pair="{{ $card['pair'] }}" @click="flip($el)"
                            aria-label="Carta {{ $cardIndex + 1 }}">
                            <span class="nb-card__inner">
                                <span class="nb-card__back" aria-hidden="true">
                                    @include('invitations.partials.aventura.flower', ['kind' => 'margarita'])
                                </span>
                                <span class="nb-card__face">
                                    @include('invitations.partials.aventura.photo', ['photo' => $card['photo'], 'width' => 300, 'alt' => 'Foto '.($card['pair'] + 1)])
                                </span>
                            </span>
                        </button>
                    @endforeach
                </div>

                <p class="nb-game__status" data-needs-js aria-live="polite">
                    <span x-text="`Pares: ${found} de ${pairs}`">Pares: 0 de {{ $gamePairs }}</span>
                    ·
                    <span x-text="`Intentos: ${moves}`">Intentos: 0</span>
                </p>

                <div class="nb-game__win" role="status">
                    @include('invitations.partials.aventura.flower', ['kind' => 'girasol', 'class' => 'nb-game__win-flower'])
                    <p class="nb-hand">{{ $gameMessage }}</p>
                    <button type="button" class="nb-game__again" data-needs-js @click="restart()">Jugar otra vez</button>
                </div>
            </div>
        </div>
        <span class="nb-folio"><!--nb-folio--></span>
    </article>
@endif
